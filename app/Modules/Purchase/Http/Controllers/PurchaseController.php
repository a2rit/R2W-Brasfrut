<?php

namespace App\Modules\Purchase\Http\Controllers;


use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Upload;
use App\Jobs\Queue;
use App\logsError;
use App\SapUtilities;
use App\Modules\Purchase\Jobs\PurchaseToSAP;
use App\Modules\Purchase\Jobs\CanceledPurchaseOrderToSAP;
use App\Modules\Purchase\Jobs\ClosePurchaseOrderToSAP;
use App\Jobs\UploadsToSAP;
use App\User;
use App\Models\Alertas;
use Auth;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\Item;
use App\Modules\Purchase\Models\PurchaseOrder\Expenses;
use App\Modules\Purchase\Models\PurchaseOrder\Payment;
use App\Modules\Purchase\Models\PurchaseOrder\Approve;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item as ItemR;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use  App\Modules\Settings\Models\Lofted;
use App\Modules\Administration\Models\Approver;
use Litiano\Sap\Company;
use App\ConfigSAP;
use App\Modules\Partners\Models\Partner;
use App\Notifications\NewPurchaseOrder;
use App\JasperReport;
use App\Modules\InternConsumption\Models\InternConsumption;
use App\Modules\Settings\Models\Config;
use Notification;


class PurchaseController extends Controller
{
    use SapUtilities;


    public function index()
    {
        $user = Auth::user();

        $approver = Approver::where('approverUser', Auth::user()->id)->first();
        $query = PurchaseOrder::with('user')
            ->select(
                'codSAP',
                'code',
                'idUser',
                'id',
                'cardName',
                'cardCode',
                'identification',
                'taxDate',
                'docTotal',
                'status',
                'created_at',
                (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year"))
            );

        $buscaGraph = PurchaseOrder::select('status')
            ->whereBetween('taxDate', [Carbon::now()->subYear(), Carbon::now()]);

        if ($user->tipoCompra == 'S' && !isset($approver)) {
            $query->where('idUser', '=', $user->id);
            $buscaGraph->where('idUser', '=', $user->id);
        }

        $query = $query->orderBy('taxDate', 'desc')->orderBy('codSAP', 'desc')->orderBy('id', 'desc')->paginate(30);
        $buscaGraph = $buscaGraph->get();

        $p_requests = PurchaseRequest::select(
            'codSAP',
            'requriedDate',
            'solicitante',
            'observation',
            'id',
            'code',
            'name'
        )
            ->where('codSAP', '!=', null)
            ->where('codStatus', '1')
            ->orWhere('codStatus', '3')
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'purchase::purchaseOrder.index',
            [
                'p_requests' => $p_requests,
                'buscaGraph' => $buscaGraph,
                'items' => $query,
                'OPOR' => new PurchaseOrder
            ]
        );
    }


    public function copyFromRequest(Request $request)
    {
        DB::beginTransaction();
        try {

            if (!isset($request->id_doc)) {
                return redirect()->back()->withErrors('Para prosseguir, é preciso selecionar 1 ou mais Solicitações');
            }

            $purchase = new PurchaseOrder();
            $purchase->idUser = auth()->user()->id;
            $purchase->isRequest = 1;
            $purchase->code = $purchase->createCode();
            $purchase->cardCode = $request->cardCode;
            $purchase->cardName = $purchase->getPartnerName($request->cardCode);
            $purchase->identification = $purchase->getPartnerIdentification($request->cardCode);
            $purchase->docDate = DATE('Y-m-d');
            $purchase->docDueDate = DATE('Y-m-d');
            $purchase->taxDate = DATE('Y-m-d');
            $purchase->paymentTerms = '';
            $purchase->is_locked = false;
            $purchase->status = $purchase::STATUS_OPEN;
            $purchase->origem = "R2W";

            if ($purchase->save()) {
                foreach ($request->id_doc as $key => $id_request) {
                    $p_request = PurchaseRequest::find($id_request);
                    foreach ($p_request->getItems($p_request->id) as $key => $value) {
                        $sap = new Company(false);
                        $item = new Item();
                        $value->codSAP = $value->itemCode;
                        $value->qtd = $value->quantity;
                        $value->itemName = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'];
                        $value->projeto = $value->project;
                        $value->centroCusto = $value->distrRule;
                        $value->centroCusto2 = $value->distriRule2;
                        $value->idItemPurchaseRequest = $value->id;
                        $value->accounting_account = $value->accounting_account;
                        $item->saveInDBFromRequest($value, $purchase->id);
                        $item->save();
                    }
                    $p_request->codStatus = (string)$p_request::STATUS_PENDING;
                    $p_request->codePC = $purchase->code;
                }
                DB::commit();
                return redirect()->route('purchase.order.read.from.request', $purchase->id);
            }
            DB::rollback();
            return redirect()->route('purchase.order.index')->withErrors('Houve um erro desconhecido, contate o desenvolvedor');
        } catch (\Throwable $e) {
            DB::rollback();
            $logsError = new logsError();
            $logsError->saveInDB('EPOFR01', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }

    public function copyFromQuotation(Request $request)
    {
        try {

            if (!isset($request->id_doc))
                return redirect()->back()->withErrors('Para prosseguir, é preciso selecionar 1 ou mais Cotações');

            $p_quotation = PurchaseQuotation::find($request);
            $purchase = new PurchaseOrder();
            //criação do pedido        
            $purchase->idUser = auth()->user()->id;
            $purchase->code = $purchase->createCode();
            $purchase->cardCode = '';
            $purchase->cardName = '';
            $purchase->isQuotation = 1;
            $purchase->identification = $purchase->getPartnerIdentification($request->provider);
            $purchase->docDate = DATE('Y-m-d');
            $purchase->docDueDate = DATE('Y-m-d');
            $purchase->taxDate = DATE('Y-m-d');
            $purchase->paymentTerms = '';
            // $purchase->comments =  '';
            $purchase->is_locked = false;
            $purchase->origem = "R2W";

            $purchase->status = $purchase::STATUS_OPEN;
            if ($purchase->save()) {
                foreach ($request->id_doc as $key => $id_quotation) {
                    $p_quotation = PurchaseQuotation::find($id_quotation);
                    foreach ($p_quotation->getItems($p_quotation->id) as $key => $value) {
                        $sap = new Company(false);
                        $item = new Item();
                        $value->codSAP = $value->itemCode;
                        $value->qtd = $value->qtd;
                        $value->price = 0.00;
                        $value->itemName = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'];
                        $value->projeto = '';
                        $value->centroCusto = '';
                        $value->centroCusto2 = '';
                        $value->idPurchaseRequest = $p_quotation->idRequest;
                        $item->saveInDBFromQuotationF($value, $purchase->id);
                    }
                }
                return redirect()->route('purchase.order.read', $purchase->id);
            }
            return redirect()->route('purchase.order.index')->withErrors('Houve um erro desconhecido, contate o desenvolvedor');
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EPOFR01', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }


    public function indexData(Request $request)
    {
        $columns = $request->get('columns');

        $length = max($request->get('length'), 100);
        $order = $request->get('order');
        $order = $order[0];
        $orderBy = $columns[$order['column']];
        $columnsSelect = ['id', 'requester_name', 'status', 'docDate'];

        $query = PurchaseOrder::orderBy($orderBy['name'], $order['dir']);
        if ($request->get('status')) {
            $query->where('status', '=', $request->get('status'));
        }
        $recordsFiltered = $query->count();
        $query->offset($request->get('start'));
        $query->limit($length);


        $return = [];
        $return['recordsTotal'] = PurchaseOrder::count();
        $return['recordsFiltered'] = $recordsFiltered;
        $return['draw'] = $request->get('draw');
        $return['data'] = $query->get($columnsSelect);

        return response()->json($return);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Factory|Response|View
     */
    public function show($id)
    {
        $po = PurchaseOrder::find($id);
        return view("purchaseOrder::show", compact('po'));
    }

    public function read($id)
    {
        try {
            Session::put('idSAP', $id);

            $head = PurchaseOrder::find($id);
            $body = Item::with(['purchaseRequest'])->where('idPurchaseOrders', '=', $id)->where('status', 1)->get();
            $head_expenses = Expenses::where('idPurchaseOrder', '=', $head->id)->get();
            $advancePayment = AdvanceProvider::where('idPurchaseOrder', '=', $id)->get();
            $payment = DB::SELECT("SELECT * FROM purchase_order_payments WHERE idPurchaseOrders = '{$id}' ");
            // $head->updateR2WUploadsFromSAP();
            $upload = $head->uploads;
            $approvers = Approve::join('purchase_orders', 'purchase_orders.id', 'purchase_order_approves.idPurchaseOrder')
                ->join('users', 'users.id', '=', 'purchase_order_approves.idUser')
                ->where('purchase_orders.id', '=', $head->id)
                ->select('users.name', 'purchase_order_approves.status', 'purchase_order_approves.created_at as created')
                ->get();
            $contracts = Partner::partnerContracts($head->cardCode);

            // Alertas::checkAlerts($head->id);// atualiza o status dos alertas pertencentes ao documento para verificado.

            return view("purchase::purchaseOrder.create", array_merge(['OPPR' => new Approve, 'approvers' => $approvers, 'OPOR' => new PurchaseOrder, 'advancePayment' => $advancePayment, 'head_expenses' => $head_expenses, 'head' => $head, 'body' => $body, 'payment' => $payment, 'upload' => $upload, 'contracts' => $contracts], $this->options()));
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EFX(1)', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }

    public function readFromRequest($id)
    {
        try {
            Session::put('idSAP', $id);
            $head = PurchaseOrder::find($id);
            if (!empty($head)) {
                $head->isRequest = 1;
                $head_expenses = Expenses::where('idPurchaseOrder', '=', $head->id)->get();
                PurchaseOrder::destroy($id);
                $CF = DB::SELECT("SELECT T0.id from cash_flows as T0 JOIN  cash_flow_items as T1 on T0.id = T1.idCashFlow
                                  WHERE T1.idTransation = '{$id}' and T1.transation like 'purchase_orders'");

                $body = Item::with(['purchaseRequest'])->where('idPurchaseOrders', '=', $id)->select(
                    'idPurchaseOrders',
                    'itemCode',
                    'idPurchaseRequest',
                    'idItemPurchaseRequest',
                    'itemName',
                    'itemUnd',
                    'price',
                    'codProject',
                    'costCenter',
                    'costCenter2',
                    'accounting_account',
                    'quantity',
                    'lineSum',
                    'warehouseCode'
                )->get();
                $expenses = Expenses::where('idPurchaseOrder', '=', $id)->get();
                $advancePayment = AdvanceProvider::where('idPurchaseOrder', '=', $id)->get();
                $payments = Payment::where('idPurchaseOrders', '=', $id)->get();
                $payment = DB::SELECT("SELECT * FROM purchase_order_payments WHERE idPurchaseOrders = '{$id}' ");
                $upload = Upload::where('reference', 'purchase_orders')->where('idReference', $id)->get();

                $approvers = Approve::join('purchase_orders', 'purchase_orders.id', 'purchase_order_approves.idPurchaseOrder')
                    ->join('users', 'users.id', '=', 'purchase_order_approves.idUser')
                    ->where('purchase_orders.id', '=', $head->id)
                    ->where('purchase_order_approves.status', '=', '1')
                    ->select('users.name', 'purchase_order_approves.created_at as created')
                    ->get();

                $contracts = Partner::partnerContracts($head->cardCode);

                $OPPR = new Approve();

                return view("purchase::purchaseOrder.create", array_merge(['OPPR' => new Approve, 'approvers' => $approvers, 'OPOR' => new PurchaseOrder(), 'head_expenses' => $head_expenses, 'advancePayment' => $advancePayment, 'payments' => $payments, 'expenses' => $expenses, 'head' => $head, 'CF' => $CF, 'body' => $body, 'payment' => $payment, 'upload' => $upload, 'contracts' => $contracts], $this->options()));
            } else {
                return redirect()->back();
            }
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EFX(2)', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }


    public function readCode($code)
    {
        try {

            $head = PurchaseOrder::where('code', $code)->first();
            if (!empty($head)) {
                $body = Item::with(['purchaseRequest'])->where('idPurchaseOrders', '=', $head->id)->get();
                $head_expenses = $head->expenses()->get();
                $payments = Payment::where('idPurchaseOrders', '=', $head->id)->get();
                $payment = DB::SELECT("SELECT * FROM purchase_order_payments WHERE idPurchaseOrders = '{$head->id}' ");
                $upload = Upload::where('reference', 'purchase_orders')->where('idReference', $head->id)->get();
                $approvers = Approve::join('purchase_orders', 'purchase_orders.id', 'purchase_order_approves.idPurchaseOrder')
                    ->join('users', 'users.id', '=', 'purchase_order_approves.idUser')
                    ->where('purchase_orders.id', '=', $head->id)
                    ->select('users.name', 'purchase_order_approves.created_at as created')
                    ->get();
                $contracts = Partner::partnerContracts($head->cardCode);

                // Alertas::checkAlerts($head->id);// atualiza o status dos alertas pertencentes ao documento para verificado.

                return view("purchase::purchaseOrder.create", array_merge(['OPPR' => new Approve, 'approvers' => $approvers, 'OPOR' => new PurchaseOrder, 'payments' => $payments, 'head_expenses' => $head_expenses, 'head' => $head, 'body' => $body, 'payment' => $payment, 'upload' => $upload, 'contracts' => $contracts], $this->options()));
            } else {
                return redirect()->back();
            }
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EFX(3)', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }


    private function getItemName($array)
    {
        $newArray = [];
        $purchase_order = PurchaseOrder::find($array[0]['idPurchaseOrders']);
        $cont = 0;
        foreach ($array as $key => $value) {
            $sap = new Company(false);

            $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];
            $query = $sap->query("SELECT TOP 1  T0.[CardCode], T1.[Price] FROM OPCH T0  
            INNER JOIN PCH1 T1 ON T0.DocEntry = T1.DocEntry 
            WHERE  
            T1.[ItemCode] = '{$value->itemCode}' and 
            T0.DocEntry NOT IN (SELECT T4.BaseEntry FROM ORPC T3 INNER JOIN RPC1 T4 ON T3.DocEntry = T4.DocEntry
            WHERE T4.BaseEntry IS NOT NULL and  T3.SeqCode = 1) ORDER BY T0.[DocDate] desc
            ");


            $lastProvider = isset($query[0]['CardCode']) ? $query[0]['CardCode'] : null;
            $lastPrice = isset($query[0]['Price']) ? $query[0]['Price'] : null;

            $lineStatus = !empty($purchase_order->codSAP) ? $sap->query("SELECT LineStatus FROM POR1 WHERE DocEntry = $purchase_order->codSAP and LineNum = $cont") : [];
            $newArray[] = [
                'id' => $value->id,
                'idPurchaseOrders' => $value->idPurchaseOrders,
                'idPurchaseRequest' => $value->idPurchaseRequest,
                'idItemPurchaseRequest' => $value->idItemPurchaseRequest,
                'requestCode' => !is_null($value->purchaseRequest) ? $value->purchaseRequest->code : null,
                'itemCode' => $value->itemCode,
                // 'itemName' => $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
                'itemName' => $value->itemName,
                // 'itemUnd' => (!is_null($und['InvntryUom']) ? $und['InvntryUom'] : $und['BuyUnitMsr']),
                'itemUnd' => (!is_null($value->itemUnd) ? $value->itemUnd : (!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom'])),
                'quantity' => (float)$value->quantity ?? 0,
                'quantityRequest' => !empty($value->idItemPurchaseRequest) ? ItemR::find($value->idItemPurchaseRequest)->quantity : NULL,
                'taxCode' => $value->taxCode,
                'price' => (float)$value->price,
                'lineSum' => $value->lineSum,
                'codUse' => $value->codUse,
                'codProject' => $value->codProject,
                //'codCost' => $value->codCost,
                'costCenter' => $value->costCenter,
                'costCenter2' => $value->costCenter2,
                'status' => (!empty($purchase_order->codSAP) && array_key_exists('0', $lineStatus)) ? $lineStatus[0]['LineStatus'] : $value->status,
                'lastProvider' => $lastProvider,
                'lastPrice' => $lastPrice,
                'contract' => $value->contract,
                'whsCode' => $value->warehouseCode,
                'synced' => $value->synced
            ];
            $cont++;
        }

        return $newArray;
    }

    public function create()
    {
        return view("purchase::purchaseOrder.create", $this->options());
    }


    public function report(Request $request)
    {

        $purchase_order = new PurchaseOrder();
        return view("purchase::purchaseOrder.report", compact('purchase_order'));
    }

    public function gerarReport(Request $request)
    {


        $data = [
            'code' => $request->code ?? 'NULL',
            'docStatus' => $request->status ?? 'NULL',
            'initialDate' =>  $request->data_ini ?? '2015-01-01',
            'lastDate' =>  $request->data_fim ?? date('Y-m-d'),
            'idUser' =>  $request->name ?? 'NULL',
        ];


        if ($request->tipo == 1) {

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder-Sintetico.jasper";
            $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
            $output = public_path('/relatorios' . '/' . $file_name);

            if (!file_exists($relatory_model)) {
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        } else if ($request->tipo == 2) {

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder-Analitico.jasper";
            $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
            $output = public_path('/relatorios' . '/' . $file_name);

            if (!file_exists($relatory_model)) {
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder-Analitico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        }
    }

    public function listOrdersTopNav(Request $request)
    {
        $columns = [];

        foreach ($request->fields as $index => $value) {
            $columns[$index] = $value['fieldName'];
        }

        $columns = implode(',', $columns);
        $sql = "SELECT DISTINCT TOP 10 $columns FROM [VW_R2W_PURCHASE_ORDERS_TOP_NAV]";

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

    private function options()
    {
        $sap = new Company(false);
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $expenses = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        $use = $sap->query('SELECT T0.ID as code, T0.Descr as value FROM OUSG T0');
        $accounts = $this->getAccountOptions($sap);
        // $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
        $model = $sap->query("SELECT T0.NfmName,T0.NfmDescrip,  T0.NfmCode FROM ONFM T0");
        $tax = $this->getTaxOptions($sap);
        $warehouses = $this->getWHSOptions($sap);
        $sellers = $this->getSalesEmployersOptions($sap);
        $budgetAccountingAccounts = $sap->query("SELECT DISTINCT a.name as value, b.AcctName as name FROM [@A2RORCPC] a INNER JOIN OACT b ON a.Name = b.AcctCode");

        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);

        $incoterm = [
            '1' => 'CIF',
            '2' => 'FOB',
            '3' => 'TER',
            '4' => 'SEM',
        ];

        $OPPR = new Approve();
        $approval_methods = [
            "R2W" => Config::get("approvePurchaseOrderR2W"),
            "SAP" => Config::get("approvePurchaseOrderSAP")
        ];
        $purchase_order_model = new PurchaseOrder;
        return compact('approval_methods', 'purchase_order_model', 'budgetAccountingAccounts', 'expenses', 'OPPR', 'incoterm', 'warehouses', 'accounts', 'tax', 'paymentConditions', 'centroCusto', 'centroCusto2', 'projeto', 'use', 'model', 'sellers');
    }

    public function save(Request $request)
    {
        try {
            $id = $request->get('id', false);
            DB::beginTransaction();
            $po = PurchaseOrder::find($id);
            if (!empty($po)) {
                if (Config::get('approvePurchaseOrderR2W')) {
                    $qtd = Approve::where('purchase_order_approves.idPurchaseOrder', $id)
                        ->where('purchase_order_approves.status', Approve::STATUS_OPEN)->count('purchase_order_approves.id');

                    if ($qtd > 0) {
                        DB::commit();
                        return redirect()->route('purchase.order.read', $id)->withErrors('Esse pedido não pode ser mais editado, pois já foi aprovado por um ou mais aprovadores!');
                    } else {
                        Approve::join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_approves.idPurchaseOrder')
                            ->where('purchase_order_approves.idPurchaseOrder', $id)
                            ->where('purchase_order_approves.status', Approve::STATUS_OPEN)->update(['purchase_order_approves.status' => Approve::STATUS_CLOSE]);
                    }
                }

                $obj = new PurchaseOrder();
                $obj->updateInDB($request, $po);

                $attributes = [];
                if ($po->needApproval) {
                    $items = Item::where('idPurchaseOrders', $po->id)->get();
                    $loftedId = null;
                    foreach ($items as $index => $item) {
                        $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
                            ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
                            ->where('docNum', '=', Lofted::PURCHASE_ORDER)
                            ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
                            ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
                            ->orderby('nivel')
                            ->get();

                        if (count($search) > 0 && $loftedId != $item->lofted_approveds_id) {
                            foreach ($search as $key => $value) {
                                $attributes['idPurchaseOrder'] = $po->id;
                                $attributes['idLofted'] = $value->idLofted;
                                $attributes['idApproverDocuments'] = $value->id;
                                $attributes['nivel'] = $value->nivel;
                                $attributes['idUser'] = $value->approverUser;
                                $attributes['status'] = Approve::STATUS_CLOSE;

                                Approve::create($attributes);
                            }
                            $loftedId = $item->lofted_approveds_id;
                        }
                    }
                }

                foreach ($this->getNotifiableUsers($po->idLofted, $po->id) as $key => $value) {
                    Alertas::create([
                        'id_document' => $po->id,
                        'type_document' => '3',
                        'id_user' => $value->id,
                        'text' => 'Criador: ' . Auth::user()->name . ' / Fornecedor: ' . $po->cardCode . ' / Valor: R$ ' . number_format($po->docTotal, 2, ',', '.'),
                        'title' => 'Pedido de Compra',
                        'status' => '1'
                    ]);
                }
            } else {

                $po = new PurchaseOrder();
                $po->saveInDB($request);
                Notification::send($this->getNotifiableUsers($po->idLofted, $po->id), new NewPurchaseOrder("Novo pedido de compra de " . Auth::user()->name));


                $attributes = [];

                if($po->needApproval){
                    $items = Item::where('idPurchaseOrders', $po->id)->get();
                    $loftedId = null;

                    foreach($items as $index => $item){
                        $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
                            ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
                            ->where('docNum', '=', Lofted::PURCHASE_ORDER)
                            ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
                            ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
                            ->orderby('nivel')
                            ->get();

                        
                        if (count($search) > 0 && $loftedId != $item->lofted_approveds_id) {

                            foreach($search as $key => $value){

                                $attributes['idPurchaseOrder'] = $po->id;
                                $attributes['idLofted'] = $value->idLofted;
                                $attributes['idApproverDocuments'] = $value->id;
                                $attributes['nivel'] = $value->nivel;
                                $attributes['idUser'] = $value->approverUser;
                                $attributes['status'] = Approve::STATUS_CLOSE;
    
                                Approve::create($attributes);
                            }
                            $loftedId = $item->lofted_approveds_id;
                        }
                    }
                }

                foreach ($this->getNotifiableUsers($po->idLofted, $po->id) as $key => $value) {
                    Alertas::create([
                        'id_document' => $po->id,
                        'type_document' => '3',
                        'id_user' => $value->id,
                        'text' => 'Criador: ' . Auth::user()->name . ' / Fornecedor: ' . $po->cardCode . ' / Valor: R$ ' . number_format($po->docTotal, 2, ',', '.'),
                        'title' => 'Pedido de Compra',
                        'status' => '1'
                    ]);
                }
            }

            saveUpload($request, 'purchase_orders', $po->id);
            DB::commit();
            if(auth()->user()->freeCompra == '1' || $po->needApproval === false){

                unset($po->needApproval);
                $po->status = PurchaseOrder::STATUS_OPEN;
                $po->save();

                $uploads = Upload::where('idReference', $po->id)->where('reference', 'purchase_orders')->first();
                if (!empty($uploads)) {
                    UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
                PurchaseToSAP::dispatch($po)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }

            return redirect()->route('purchase.order.read', $po->id)->withSuccess("Pedido gravado com sucesso no R2W. Enviando pedido para o SAP");
        } catch (\Throwable $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('E0A81', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors('Falha ao tentar salvar, tente novamente ou contate o suporte!' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        try {
            $oldPurchase = PurchaseOrder::find($id);
            if (!empty($oldPurchase)) {
                DB::beginTransaction();
                $purchase = new PurchaseOrder();
                $purchase->duplicate($oldPurchase);
                DB::commit();

                $opor = PurchaseOrder::find($purchase->id);
                $opor->status = PurchaseOrder::STATUS_OPEN;
                $opor->save();

                return redirect()->route('purchase.order.read', $purchase->id);
            }
        } catch (\Throwable $e) {
            DB::rollback();
            $logsError = new logsError();
            $logsError->saveInDB('PD(1)', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }

    protected function getNotifiableUsers($idLofted, $idPurchase)
    {
        $qtd =  Approve::join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_approves.idPurchaseOrder')
            ->where('purchase_order_approves.idPurchaseOrder', '=', $idPurchase)
            ->count('purchase_order_approves.id');

        return User::join('approver_documents', 'approver_documents.approverUser', '=', 'users.id')->where('idLoftedApproveds', $idLofted)->where('approver_documents.nivel', ($qtd + 1))->select('users.*', 'users.id as id')->get();
    }

    public function approve($id)
    {
        try {
            $opor = PurchaseOrder::find($id);

            $approve = Approve::where('idPurchaseOrder', $id)
                ->where('idUser', auth()->user()->id)
                ->first();

            $search = Lofted::find($approve->idLofted);

            if (!is_null($search)) {

                $qtd = Approve::where('idLofted', $search->idLofted)
                    ->where('idPurchaseOrder', $id)
                    ->where('status', Approve::STATUS_OPEN)->count('id');


                if ($qtd < (int)$search->quantity) {

                    $approve->status = Approve::STATUS_OPEN;
                    $approve->save();
                }

                $qtd = Approve::where('idLofted', $search->id)
                    ->where('idPurchaseOrder', $id)
                    ->where('status', Approve::STATUS_OPEN)->count('id');

                Notification::send($this->getNotifiableUsers($opor->idLofted, $opor->id), new NewPurchaseOrder("Novo pedido de compra de " . User::find($opor->idUser)->name));

                foreach ($this->getNotifiableUsers($opor->idLofted, $opor->id) as $key => $value) {
                    Alertas::create([
                        'id_document' => $opor->id,
                        'type_document' => '3',
                        'id_user' => $value->id,
                        'text' => 'Novo pedido de compra de ' . User::find($opor->idUser)->name,
                        'text' => 'Criador: ' . User::find($opor->idUser)->name . ' / Fornecedor: ' . $opor->cardCode . ' / Valor: R$ ' . number_format($opor->docTotal, 2, ',', '.'),
                        'title' => 'Pedido de Compra',
                        'status' => '1'
                    ]);
                }

                if ($qtd == (int)$search->quantity) {
                    $opor = PurchaseOrder::find($id);
                    $opor->status = PurchaseOrder::STATUS_OPEN;
                    $opor->save();
                    PurchaseToSAP::dispatch($opor);
                }
                return redirect()->route('purchase.order.index')->withSuccess('Operação realizada com sucesso! Salvando as Informações no SAP, Por favor aguarde.');
            }
        } catch (\Throwable $e) {
            $logsErro = new logsError();
            $logsErro->saveInDB('1FAXAA81', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.filter.index')->withErrors($e->getMessage());
        }
    }

    // public function getBudgetInfo(Request $request){
    //     $sap = new Company(false);
    //     $cost_center_code = $request->input("cost_center_code", null);
    //     $expenseAcct = Item::getAccountingWarehouseAccount($request->input("itemCode", null), $request->input("whsCode", null));
    //     $budgetSAP = $sap->getDb()->table("@A2RORCPC")
    //         ->select("U_A2RVLRORCS", DB::raw("(U_A2RVLRORCU * 100) / U_A2RVLROPC AS PORCENTAGE"))
    //         ->where("U_A2RVLROPC", ">", 0)
    //         ->where("U_A2RVLROPC", ">", 0)
    //         ->where("U_A2RCC", "=", "{$cost_center_code}")
    //         ->where("Name", "=", $expenseAcct)
    //         ->first();
    //     return response()->json($budgetSAP);
    // }

    public function reprove(Request $request)
    {
        try {
            DB::beginTransaction();
            $opor = PurchaseOrder::find($request->id_P);
            $opor->status = PurchaseOrder::STATUS_REPROVE;
            $opor->reprove_justify = $request->justify;
            $opor->reprove_user = auth()->user()->id;
            $opor->reprove_date = DATE('Y-m-d');
            $opor->save();
            DB::commit();

            return redirect()->route('purchase.order.index')->withSuccess('Pedido reprovado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollback();
            $logsErro = new logsError();
            $logsErro->saveInDB('1FAXAA81', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }

    public function updateUploads(Request $request)
    {
        saveUpload($request, $request->table, $request->id);
        $purchase_order = PurchaseOrder::find($request->id);
        $purchase_order->updateUpload();
    }

    public function removeUpload($id, $idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::find($id);
            $diretory = public_path($upload->get()->first()->diretory);
            // dd(file_exists($diretory), $diretory);
            if (file_exists($diretory)) {
                unlink($diretory);
            };
            $upload->delete();
            $oPOR = PurchaseOrder::find($idReference);
            DB::commit();

            return redirect()->route('purchase.order.read', $oPOR->id)->withSuccess("Anexo excluido com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    public function canceled(Request $request)
    {
        $oPOR = PurchaseOrder::findOrFail($request->id);
        if(!empty($oPOR->id)){
            CanceledPurchaseOrderToSAP::dispatch($oPOR, $request->justification);
            return redirect()->route('purchase.order.read', $oPOR->id)->withSuccess("Documento enviado para cancelamento.");
        }
        return redirect()->route('purchase.order.index')->withErrors("Documento não encontrado.");
    }

    public function closed($id)
    {
        try {
            DB::beginTransaction();
            $oPOR = PurchaseOrder::find($id);
            $oPOR->closedInSAP($oPOR);
            DB::commit();
            if ($oPOR->is_locked) {
                return redirect()->route('purchase.order.read', $oPOR->id)->withErrors($oPOR->message);
            } else {
                return redirect()->route('purchase.order.read', $oPOR->id)->withSuccess("Fechado com sucesso!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('E0F81', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function preview($id)
    {
        $purchase_order = DB::SELECT("SELECT T0.itemCode, T0.itemName, T0.quantity, T0.price, T0.lineSum, T0.codProject,
                            T2.OcrName as distrRule, T3.OcrName as distriRule2, T4.WhsName as wareHouseCode
                        FROM purchase_order_items T0
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OITM T1 ON T0.itemCode = T1.ItemCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T2 ON T0.costCenter = T2.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T3 ON T0.costCenter2 = T3.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OWHS T4 ON T0.wareHouseCode = T4.WhsCode
                        WHERE T0.idPurchaseOrders = '{$id}'");
        return json_encode([
            'data' => $purchase_order
        ]);
    }

    public function update(Request $request)
    {
        $this->saveItems($request, Session::get('idSAP'));
        return redirect()->back()->withSuccess("Atualizado com sucesso!");
    }

    public function filter(Request $request)
    {
        try {

            $busca = PurchaseOrder::with('user')
                ->select(
                    'codSAP',
                    'code',
                    'idUser',
                    'id',
                    'cardName',
                    'cardCode',
                    'identification',
                    'taxDate',
                    'docTotal',
                    'status',
                    'created_at',
                    (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year"))
                );

            $user = auth()->user();
            if ($user->tipoCompra == 'S') {
                $busca->where('purchase_orders.idUser', '=', $user->id);
            }

            if (!is_null($request->code)) {
                $busca->where("purchase_orders.code", "like", "%{$request->code}%");
            }

            if (!is_null($request->codSAP)) {
                $busca->where("purchase_orders.codSAP", "like", "%{$request->codSAP}%");
            }
            if (!is_null($request->cardName)) {
                $busca->where("purchase_orders.cardCode", "=", "{$request->cardName}");
            }

            if (!is_null($request->usuario)) {
                $busca->where("purchase_orders.idUser", "=", "{$request->usuario}");
            }

            if (!is_null($request->cpf_cnpj)) {
                $aux = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
                $busca->where("purchase_orders.identification", "like", "%{$aux}%");
            }
            if ((!is_null($request->data_fist))) {
                $busca->whereDate("purchase_orders.TaxDate", ">=", "{$request->data_fist}");
            }
            if ((!is_null($request->data_last))) {
                $busca->whereDate("purchase_orders.TaxDate", "<=", "{$request->data_last}");
            }

            if (!is_null($request->status)) {
                $busca->where("purchase_orders.status", "{$request->status}");
            }

            $buscaGraph = $busca->get();

            $p_requests = PurchaseRequest::select(
                'codSAP',
                'requriedDate',
                'solicitante',
                'observation',
                'id',
                'code',
                'name'
            )
                ->where('codSAP', '!=', null)
                ->where('codStatus', '1')
                ->orWhere('codStatus', '3')
                ->orderBy('id', 'desc')
                ->get();

            $request->flash();

            return view('purchase::purchaseOrder.index')
                ->with([
                    'items' => $busca->orderBy('purchase_orders.id', 'desc')->paginate(30)->appends(request()->query()),
                    'buscaGraph' => $buscaGraph,
                    'p_requests' => $p_requests,
                    'OPOR' => new PurchaseOrder
                ])
                ->withInput($request->input());
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E45VX', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return view('purchase::purchaseOrder.index')->withErrors('<p>Algo não está correto, atualize a página e tente novamente!</p>');
        }
    }


    public function print($id, $type)
    {
        try {

            $head = PurchaseOrder::find($id);

            if (!empty($head)) {
                if ($type == "excel" && $head->codSAP) {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrderExcel.jasper";

                    if (!file_exists($relatory_model)) {
                        $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrderExcel.jrxml";
                    }

                    $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
                    $output = public_path('/relatorios' . '/' . $file_name);
                    $report = $report->generateReport($relatory_model, $output, ['xls'], ['codSAP' => $head->codSAP], 'pt_BR', 'sap');

                    return response()->download($report)->deleteFileAfterSend(true);

                } elseif ($type == "pdf") {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder.jasper";

                    if (!file_exists($relatory_model)) {
                        $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrder.jrxml";
                    }

                    $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
                    $output = public_path('/relatorios' . '/' . $file_name);
                    $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id' => $head->id], 'pt_BR', 'r2w');

                    return response()->file($report)->deleteFileAfterSend(true);

                }elseif ($type == "pdf-budget") {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrderBudget.jasper";

                    if (!file_exists($relatory_model)) {
                        $relatory_model = storage_path('app/public/relatorios_modelos') . "/PurchaseOrderBudget.jrxml";
                    }

                    $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
                    $output = public_path('/relatorios' . '/' . $file_name);
                    $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id' => $head->id], 'pt_BR', 'r2w');

                    return response()->file($report)->deleteFileAfterSend(true);
                }
                
            } else {
                return redirect()->back()->withErrors("É necessário salvar o documento antes de gerar o relatório.");
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getPurchaseOrders(Request $request)
    {
        $query = PurchaseOrder::join("users", "users.id", '=', "purchase_orders.idUser")
            ->select("purchase_orders.id", "purchase_orders.codSAP", "purchase_orders.code", "purchase_orders.status", "users.name", "purchase_orders.cardName", "purchase_orders.taxDate", "purchase_orders.docTotal")
            ->where('status', PurchaseOrder::STATUS_OPEN);

        $recordsTotal = $query->count();

        $columns = $request->get("columns");

        $search = $request->get('search');

        if ($search) {
            if ($search['value']) {
                $query->where(function ($query) use ($search) {
                    $query->where("purchase_orders.codSAP", "like", "%{$search['value']}%");
                    $query->orWhere("purchase_orders.code", "like", "%{$search['value']}%");
                    $query->orWhere("users.name", "like", "%{$search['value']}%");
                });
            }
        }

        if (!empty($request->get('partner'))) {
            $query->where('purchase_orders.cardCode', '=', $request->get('partner'));
        }

        if (!empty($request->get('requester'))) {
            $query->where('purchase_requests.requesterUser', '=', $request->get('requester'));
        }

        if (!empty($request->get('date'))) {
            $date = str_replace("/", "-", explode(" - ", $request->get('date')));
            $query->whereDate('purchase_orders.taxDate', '>=', date_format(date_create($date[0]), "Y-m-d"))
                ->whereDate('purchase_orders.taxDate', '<=', date_format(date_create($date[1]), "Y-m-d"));
        }


        $recordsFiltered = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $order = $request->get('order');
        $columns = $request->get("columns");

        if (!empty($order) && $order[0]['column'] != 0) {
            $query->orderBy("purchase_orders." . $columns[$order[0]['column']]['name'], $order[0]['dir']);
        } else {
            $query->orderBy("purchase_orders.id", "desc");
        }

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $query->get()
        ]);
    }

    public function editingMass(Request $request)
    {

        try {
            $action = $request->massEditingAction == 'cancel' ? 2 : 0;
            foreach ($request->idPurchaseOrder as $key => $value) {
                $purchase_order = PurchaseOrder::find($key);
                if ($action === 2) {
                    CanceledPurchaseOrderToSAP::dispatch($purchase_order)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                } elseif ($action === 0) {
                    ClosePurchaseOrderToSAP::dispatch($purchase_order)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Os documentos foram enviados para processamento.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => $e->getMessage()
            ]);
        }
        return redirect()->route('purchase.order.index');
    }
}
