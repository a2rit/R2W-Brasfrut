<?php

namespace App\Modules\Purchase\Http\Controllers;

use Auth;
use App\Http\Controllers\Controller;
use App\LogsError;
use App\Upload;
use App\Jobs\Queue;
use App\Jobs\UploadsToSAP;
use App\Modules\Partners\Models\Partner;
use App\Modules\Purchase\Jobs\InvoiceCopyRGToSAP;
use App\Modules\Purchase\Jobs\InvoiceToSAP;
use App\Modules\Purchase\Jobs\canceledInvoiceToSap;
use App\Modules\Purchase\Models\IncoingInvoice\Expenses;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use App\Modules\Purchase\Models\IncoingInvoice\Items;
use App\Modules\Purchase\Models\IncoingInvoice\Tax;
use App\Modules\Purchase\Models\IncoingInvoice\AdvancePayments;
use App\Modules\Purchase\Models\PurchaseOrder\Expenses as POExpenses;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\SapUtilities;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Throwable;
use App\JasperReport;
use App\Modules\Purchase\Models\IncoingInvoice\Approve;
use App\Modules\Settings\Models\Lofted;

class InvoiceController extends Controller
{
    use SapUtilities;

    public function index()
    {

        $items = IncoingInvoice::select(
            'incoing_invoices.codSAP',
            'incoing_invoices.code',
            'incoing_invoices.id',
            'incoing_invoices.cardCode',
            'incoing_invoices.cardName',
            'incoing_invoices.taxDate',
            'incoing_invoices.docTotal',
            'incoing_invoices.status',
            'incoing_invoices.created_at',
            'T1.name as user'
        )
            ->leftJoin('users as T1', 'T1.id', '=', 'incoing_invoices.idUser')
            ->where('incoing_invoices.idUser', '!=', null);

        $buscaGraph = IncoingInvoice::select('incoing_invoices.status')
            ->leftJoin('users as T1', 'T1.id', '=', 'incoing_invoices.idUser')
            ->where('incoing_invoices.idUser', '!=', null)
            ->whereBetween('incoing_invoices.taxDate', [Carbon::now()->subYear(), Carbon::now()])->get();

        return view('purchase::incoingInvoice.index', [
            'items' => $items->orderBy('code', 'desc')->paginate(30),
            'buscaGraph' => $buscaGraph,
            'OPCH' => new IncoingInvoice
        ]);
    }

    public function create()
    {
        return view("purchase::incoingInvoice.create", $this->options());
    }

    private function options()
    {
        $sap = new Company(false);
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup, InstNum FROM OCTG T0");
        // $centroCusto = $this->getDistributionRulesOptions($sap);
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $typeOut = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        $use = $sap->query('SELECT T0.ID as code, T0.Descr as value, T0.USAGE as utilizacao FROM OUSG T0');
        $accounts = $this->getAccountOptions($sap);
        $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
        $withheldTaxesSap = $sap->query("SELECT T0.[WTCode], T0.[WTName], T0.[Rate], T0.[Category],
                                                CASE  
                                                    WHEN T0.[Category]= 'I' THEN 'Nota Fiscal'  
                                                    WHEN T0.[Category]= 'P' THEN 'Pagamento'  
                                                END as TYPE
                                                FROM OWHT T0");
        $budgetAccountingAccounts = $sap->query("SELECT DISTINCT a.name as value, b.AcctName as name FROM [@A2RORCPC] a INNER JOIN OACT b ON a.Name = b.AcctCode");
        $model = $this->getModelOptions($sap);

        $tax = $this->getTaxOptions($sap);

        $cfop = $this->getCFOPOptions($sap);
        $incoing_invoice_model = new IncoingInvoice;

        return compact('cfop', 'budgetAccountingAccounts', 'cartao', 'accounts', 'tax', 'paymentConditions', 'centroCusto', 'centroCusto2', 'projeto', 'typeOut', 'use', 'model', 'withheldTaxesSap', 'incoing_invoice_model');
    }

    public function anyData($id)
    {
        $items = DB::SELECT("SELECT T0.itemCode, T0.itemName, T0.quantity, T0.price, T0.lineSum,
                            T0.codCFOP, T0.codProject, T2.OcrName as distrRule, T3.OcrName as distriRule2, T4.Descr
                        FROM incoing_invoice_items T0
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OITM T1 ON T0.itemCode = T1.ItemCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T2 ON T0.costCenter = T2.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T3 ON T0.costCenter2 = T3.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OUSG T4 ON T0.codUse = T4.id
                        WHERE T0.idIncoingInvoice = '{$id}'");

        return json_encode([
            'data' => $items
        ]);
    }

    public function saveNFE(Request $request)
    {
        try {
            DB::beginTransaction();
            $ORG = new IncoingInvoice();
            $ORG->saveInDB($request);
            DB::commit();
            if (is_null($ORG->idReceiptGoods)) {
                InvoiceToSAP::dispatch($ORG);
            } else {
                InvoiceCopyRGToSAP::dispatch($ORG);
            }
            if ($ORG->is_locked) {
                return redirect()->route('purchase.ap.invoice.read', $ORG->id)->withErrors($ORG->message);
            } else {
                return redirect()->route('purchase.ap.invoice.read', $ORG->id)->withSuccess("Salvo com sucesso!");
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0078', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.ap.invoice.index')->withErrors($e->getMessage());
        }
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();

            $ORG = new IncoingInvoice();
            $id = $request->get('id', false);
            if ($id) {
                $obj = new IncoingInvoice();
                $ORG = IncoingInvoice::find($id);
                $obj->updateInDB($ORG, $request);
            } else {
                $ORG->saveInDB($request);
            }
            if ($ORG->id) {
                $ORG->installments()->delete();
                if (count($request->input('installments', [])) > 0) {
                    foreach ($request->input('installments', []) as $index => $value) {
                        if (!empty($value['due_date'])) {
                            $ORG->installments()->create($value);
                        }
                    }
                }
            }

            saveUpload($request, 'incoing_invoices', $ORG->id);
            DB::commit();
            if($ORG->status == $ORG::STATUS_OPEN){
                $uploads = Upload::where('idReference', $ORG->id)->where('reference', 'incoing_invoices')->first();
                if (!empty($uploads)) {
                    UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
                InvoiceToSAP::dispatch($ORG)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }
            return json_encode([
                'status' => "success",
                'message' => "Nota fiscal de entrada enviada para cadastro",
                'invoiceId' => $ORG->id
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0078', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return json_encode([
                'status' => "error",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function filter(Request $request)
    {
        try {
            $sql = IncoingInvoice::select(
                'incoing_invoices.codSAP',
                'incoing_invoices.code',
                'incoing_invoices.id',
                'incoing_invoices.cardCode',
                'incoing_invoices.cardName',
                'incoing_invoices.taxDate',
                'incoing_invoices.docTotal',
                'incoing_invoices.status',
                'incoing_invoices.created_at',
                'T1.name as user'
            )
                ->leftJoin('users as T1', 'T1.id', '=', 'incoing_invoices.idUser')
                ->where('incoing_invoices.idUser', '!=', null);
            // dd($request->all());
            if (!is_null($request->code)) {
                $sql->where('code', 'like', "%{$request->code}%");
            }
            if (!is_null($request->codSAP)) {
                $sql->where('codSAP', 'like', "%{$request->codSAP}%");
            }
            if (!is_null($request->cardName)) {
                $sql->where('cardCode', '=', "{$request->cardName}");
            }
            if (!is_null($request->sequenceSerial)) {
                $sql->whereHas('taxes', function($query) use($request){
                    $query->where('sequenceSerial', 'like', "%{$request->sequenceSerial}%");
                });
            }
            if (!is_null($request->user)) {
                $sql->where('idUser', '=', "$request->user");
            }
            if (!is_null($request->cpf_cnpj)) {
                $aux = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
                $sql->where('identification', 'like', "%{$aux}%");
            }
            if ((!is_null($request->data_fist))) {
                $sql->whereDate('taxDate', '>=', "{$request->data_fist}");
            }
            if ((!is_null($request->data_last))) {
                $sql->whereDate('taxDate', '<=', "{$request->data_last}");
            }
            if (!is_null($request->status)) {
                $sql->where('status', '=', $request->status);
            }

            $buscaGraph = $sql->get();
            $request->flash();
            return view('purchase::incoingInvoice.index', [
                'items' => $sql->orderBy('code', 'desc')->paginate(30)->appends(request()->query()),
                'buscaGraph' => $buscaGraph,
                'OPCH' => new IncoingInvoice
            ])->withInput($request->input());
        } catch (Throwable $e) {
            dd($e->getMessage());
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0012', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return view('purchase::incoingInvoice.index')->withErrors($e->getMessage());
        }
    }

    public function read($id)
    {
        try {
            // dd(IncoingInvoice::find($id));
            $head = IncoingInvoice::join('users', 'users.id', '=', 'incoing_invoices.idUser')
                ->leftJoin('purchase_orders', 'purchase_orders.id', '=', 'incoing_invoices.idPurchaseOrder')
                ->select(
                    'incoing_invoices.id',
                    'incoing_invoices.codSAP',
                    'incoing_invoices.message',
                    'incoing_invoices.is_locked',
                    'incoing_invoices.code',
                    'incoing_invoices.status',
                    'incoing_invoices.idPurchaseOrder',
                    'purchase_orders.code as purchase_order_code',
                    'incoing_invoices.cardCode',
                    'incoing_invoices.taxDate',
                    'incoing_invoices.docDate',
                    'incoing_invoices.docDueDate',
                    'incoing_invoices.sync_at',
                    'incoing_invoices.updated_at',
                    'incoing_invoices.paymentTerms',
                    'incoing_invoices.comments',
                    'incoing_invoices.JrnlMemo',
                    'incoing_invoices.total_a_pagar',
                    'incoing_invoices.impostos_r',
                    'incoing_invoices.docTotal',
                    'incoing_invoices.contract',
                    'users.name'
                )
                ->find($id);

            $body = $head->items()->get();
            $expenses = Expenses::where('idIncoingInvoice', '=', $id)->get();
            $taxes = Tax::where('idIncoingInvoice', '=', $id)->get();
            $withheld_taxes_items = DB::table('incoing_invoice_withheldtaxes')
                ->whereIn('itemId', $body->pluck('id'))
                ->select('WTCode', 'Rate', 'Value', 'itemId')
                ->get()
                ->groupBy('itemId');
            $advancePayments = AdvancePayments::where('idIncoingInvoice', '=', $id)->get();
            $upload = Upload::where('reference', 'incoing_invoices')->where('idReference', $id)->get();

            $contracts = Partner::partnerContracts($head->cardCode);
            return view("purchase::incoingInvoice.create", array_merge(['OPPR' => new Approve, 'upload' => $upload, 'taxes' => $taxes, 'expenses' => $expenses, 'head' => $head, 'body' => $body, 'withheld_taxes_items' => $withheld_taxes_items, 'advancePayments' => $advancePayments, 'contracts' => $contracts], $this->options()));
        } catch (Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E03$12A', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.ap.invoice.index')->withErrors($e->getMessage());
        }
    }

    public function removeUpload($id, $idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::where('id', $id);
            $diretory = public_path($upload->get()->first()->diretory);
            if (file_exists($diretory)) {
                unlink($diretory);
            };
            $upload->delete();
            $oINV = IncoingInvoice::find($idReference);
            DB::commit();

            return redirect()->route('purchase.ap.invoice.read', $oINV->id)->withSuccess("Anexo excluido com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function listInvoicesTopNav(Request $request)
    {
        $columns = [];

        foreach ($request->fields as $index => $value) {
            $columns[$index] = $value['fieldName'];
        }

        $columns = implode(',', $columns);
        $sql = "SELECT DISTINCT TOP 10 $columns FROM [VW_R2W_INCOING_INVOICE_TOP_NAV]";

        $search = $request->get('search');
        if ($search['value']) {
            $sql .= "WHERE codSAP LIKE '%{$search['value']}%' OR code LIKE '%{$search['value']}%'";
        }

        $sql .= "ORDER BY id DESC";
        $query = DB::SELECT($sql);
        $recordsFiltered = count($query);
        $recordsTotal = count($query);

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $query
        ]);
    }

    public function print($id)
    {
        try {
            $invoice = IncoingInvoice::find($id);
            if ($invoice->codSAP) {
                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice.jasper";
                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'NFE';
                $output = public_path('/relatorios' . '/' . $file_name);

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice.jrxml";
                }

                $report = $report->generateReport($relatory_model, $output, ['pdf'], ['codSAP' => $invoice->codSAP], 'pt_BR', 'sap');
                return response()->file($report)->deleteFileAfterSend(true);
            } else {
                return redirect()->back()->withErrors('Apenas é possivel gerar relátorios com documentos sincronizados com o SAP!');
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getWithheldTax(Request $request)
    {
        try {
            $items = DB::table('incoing_invoice_withheldtaxes')
                ->where('itemId', '=', $request->id)
                ->get();

            return json_encode([
                'status' => 200,
                'data' => $items
            ]);
        } catch (Exception $e) {

            return json_encode([
                'status' => 500,
                'data' => $e->getMessage()
            ]);
        }
    }

    public function canceled($id)
    {
        $oPOR = IncoingInvoice::find($id);
        CanceledInvoiceToSAP::dispatch($oPOR);

        return redirect()->route('purchase.ap.invoice.read', $oPOR->id)
            ->withSuccess("Item $oPOR->code enviado para cancelamento!");
    }

    public function duplicate($id)
    {
        try {
            $oldInvoice = IncoingInvoice::find($id);

            if (!empty($oldInvoice)) {
                DB::beginTransaction();
                $invoice = new IncoingInvoice;
                $invoice->duplicate($oldInvoice);
                DB::commit();
            }

            return redirect()->route('purchase.ap.invoice.read', $invoice->id)
                ->withSuccess("Documento duplicado com sucesso!");
        } catch (\Throwable $e) {
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0088', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function copy($id)
    {
        try {
            $head = PurchaseOrder::find($id);
            $body = $head->items()->get();
            $contracts = Partner::partnerContracts($head->cardCode);
            $expenses = POExpenses::where('idPurchaseOrder', '=', $id)->get();
            return view("purchase::incoingInvoice.copy", array_merge(['expenses' => $expenses, 'head' => $head, 'body' => $body, 'idPurchaseOrder' => $id, 'contracts' => $contracts], $this->options()));
        } catch (Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E03$12A', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function report(Request $request)
    {
        $sap = new Company(false);
        $requests = new Requests();
        $incoing_invoice = new IncoingInvoice;
        $warehouses = $sap->query("select WhsCode, WhsName from OWHS");
        $itemGroups = $sap->getDb()->table('OITB')->select('ItmsGrpCod', 'ItmsGrpNam')->get();
        $itemProperties = $sap->getDb()->table('OITG')->select('ItmsTypCod as value', 'ItmsGrpNam as name')->get();
        return view("purchase::incoingInvoice.report", compact('requests', 'warehouses', 'itemGroups', 'incoing_invoice', 'itemProperties'));
    }

    public function gerarReport(Request $request)
    {

        if ($request->category == 1) {

            $data = [
                'code' => $request->code ?? 'NULL',
                'docStatus' => $request->status ?? 'NULL',
                'initialDate' =>  $request->data_ini ?? '2015-01-01',
                'lastDate' =>  $request->data_fim ?? date('Y-m-d'),
                'idUser' =>  $request->name ?? 'NULL'
            ];

            if ($request->tipo == 1) {

                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice-Sintetico.jasper";
                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'incoing_invoice';
                $output = public_path('/relatorios' . '/' . $file_name);

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice-Sintetico.jrxml";
                }

                $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
                return response()->file($report)->deleteFileAfterSend(true);
            } else if ($request->tipo == 2) {

                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice-Analitico.jasper";
                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'incoing_invoice';
                $output = public_path('/relatorios' . '/' . $file_name);

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/IncoingInvoice-Analitico.jrxml";
                }

                $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
                return response()->file($report)->deleteFileAfterSend(true);
            }
        } else if ($request->category == 2) {

            $data = [
                'partner' => $request->partner ?? 'NULL',
                'warehouse' => $request->warehouse ?? 'NULL',
                'initialDate' =>  $request->data_ini ?? 'NULL',
                'lastDate' =>  $request->data_fim ?? 'NULL',
                'item' =>  $request->item ?? 'NULL',
                // 'untilItem' =>  $request->untilItem ?? 'NULL',
                'itemGroup' =>  $request->group ?? 'NULL',
                'property' => $request->property ?? 'NULL',
            ];

            if ($request->tipo == 1) {

                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/Analise-Compra.jasper";
                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'AnaliseCompra';
                $output = public_path('/relatorios' . '/' . $file_name);

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/Analise-Compra.jrxml";
                }

                $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'sap');
                return response()->file($report)->deleteFileAfterSend(true);
            } else if ($request->tipo == 2) {

                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/Analise-CompraExcel.jasper";
                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'AnaliseCompra';
                $output = public_path('/relatorios' . '/' . $file_name);

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/Analise-CompraExcel.jrxml";
                }

                $report = $report->generateReport($relatory_model, $output, ['xls'], $data, 'pt_BR', 'sap');
                return response()->file($report)->deleteFileAfterSend(true);
            }
        }
    }

    public function updateUploads(Request $request)
    {
        saveUpload($request, $request->table, $request->id);
        $invoice = IncoingInvoice::find($request->id);
        $invoice->updateUpload();
    }

    public function getProductsSAP(Request $request)
    {
        if (!is_null(auth()->user()->whsDefault)) {
            $id = auth()->user()->whsDefault;
        } else {
            $id = '01';
        }

        $sap = new Company(false);
        $query = $sap->getDb()->table('OITM')
            ->join('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode')
            ->where('OITM.validFor', '=', 'Y')
            ->where('OITM.ItmsGrpCod', '=', '109');

        $query->distinct();

        $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITM.BuyUnitMsr', 'OITM.DfltWH'];

        $query->offset($request->get("start"));
        $query->limit($request->get("length"));

        $search = $request->get('search');

        if ($search) {
            if ($search['value']) {
                $query->where(function (Builder $where) use ($search) {
                    $where->orWhere("OITM.ItemCode", "like", "%{$search['value']}%")
                        ->orWhere("OITM.ItemName", "like", "%{$search['value']}%");
                });
            }
        }

        $query = $query->get($columnsToSelect);
        $recordsTotal = $query->count();
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query
        ]);
    }

    public function getAdvancePayments(Request $request)
    {
        $invoice = new IncoingInvoice();
        $adPayments = $invoice->getAdvancePayments($request->cardCode);
        if ($adPayments) {
            return response()->json(['status' => 'success', 'adPayments' => $adPayments], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Não existe nenhum adiantamento para esse fornecedor!']);
        }
    }

    public function approve($id)
    {
        try {
            $approve = Approve::where('idIncoingInvoice', $id)
                ->where('idUser', auth()->user()->id)
                ->first();

            $search = Lofted::find($approve->idLofted);
            if (!is_null($search)) {

                $qtd = Approve::where('idLofted', $search->idLofted)
                    ->where('idIncoingInvoice', $id)
                    ->where('status', Approve::STATUS_OPEN)->count('id');

                DB::beginTransaction();
                if ($qtd < (int)$search->quantity) {
                    $approve->status = Approve::STATUS_OPEN;
                    $approve->save();
                }
                
                $qtd = Approve::where('idLofted', $search->id)
                    ->where('idIncoingInvoice', $id)
                    ->where('status', Approve::STATUS_OPEN)->count('id');

                if ($qtd == (int)$search->quantity) {
                    $invoice = IncoingInvoice::find($id);
                    $invoice->status = IncoingInvoice::STATUS_OPEN;
                    $invoice->save();
                    InvoiceToSAP::dispatch($invoice)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
                DB::commit();
                return redirect()->back()->withSuccess('Operação realizada com sucesso! Salvando as Informações no SAP, Por favor aguarde.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('1FAXAA81', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
