<?php

namespace App\Modules\Purchase\Http\Controllers;


use App\Modules\Partners\Models\Partner\Catalog;
use App\Modules\Purchase\Jobs\ReceiptGoodsToDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\LogsError;
use App\CFItems;
use App\ConfigSAP;
use App\Modules\Purchase\Jobs\ReceiptGoodsCopyToSAP;
use App\Modules\Purchase\Jobs\ReceiptGoodsToSAP;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\Item;
use App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods;
use App\Modules\Purchase\Models\ReceiptGoods\Tax;
use App\Modules\Purchase\Models\ReceiptGoods\Items;
use App\Modules\Purchase\Models\ReceiptGoods\Expenses;
use App\Modules\Inventory\Models\Requisicao\Requests;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoRcptInvTypes;
use Litiano\Sap\Enum\BoYesNoEnum;
use Litiano\Sap\Enum\DownPaymentTypeEnum;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use Litiano\Sap\Enum\BoORCTPaymentTypeEnum;
use Litiano\Sap\Enum\BoDocumentTypes;
use Litiano\Sap\Enum\BoRcptTypes;
use App\SapUtilities;

use App\Jobs\Set\POCopyRG;

class ReceiptsGoodsController extends Controller
{
    use SapUtilities;
/*
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!checkAccess('receipt_goods')) {
                return redirect()->route('home')->withErrors(auth()->user()->name . ' você não possui acesso! consulte o Admin do Sistema');
            } else {
                return $next($request);
            }
        });
    } */

    public function index()
    {
        $query = ReceiptGoods::orderBy('id', 'desc')->get();
        return view('purchase::receiptsGoods.index', ['items' => $query]);

    }

    public function create()
    {
        return view("purchase::receiptsGoods.create", $this->options());
    }

    private function options()
    {
        $sap = new Company(false);
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup, InstNum FROM OCTG T0");
        $obpl = $sap->query("SELECT T0.BPLId as code, T0.BPLName as value FROM OBPL T0");
        $centroCusto = $this->getDistributionRulesOptions($sap);
        $projeto = $this->getProjectOptions($sap);
        $typeOut = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        $use = $sap->query('SELECT T0.ID as code, T0.Descr as value FROM OUSG T0');
        $accounts = $this->getAccountOptions($sap);
        $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
        $cashFlow = DB::SELECT("SELECT T0.id, T0.description as value FROM cash_flows as T0 WHERE T0.module = 'C' and T0.status = '1'");
        $model = $this->getModelOptions($sap);
        $tax = $this->getTaxOptions($sap);
        $cfop = $this->getCFOPOptions($sap);
        $types = [
            ['name' => 'Moeda Corrente', 'value' => 'L'],
            ['name' => 'Moeda do Sistema', 'value' => 'S'],
            ['name' => 'Moeda do Parceiro', 'value' => 'C'],
        ];
        $configSAP = new ConfigSAP();
        return compact('configSAP', 'cfop', 'cashFlow', 'cartao', 'accounts', 'tax', 'paymentConditions', 'obpl', 'centroCusto', 'projeto', 'typeOut', 'use', 'types', 'model');
    }

    private function getItemName($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            $sap = new Company(false);
            $newArray[] = [
                /*'id'=> $value->id,
                'idPurchaseOrders'=> $value->idPurchaseOrders,*/
                'itemCode' => $value->itemCode,
                'itemName' => $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
                'quantity' => $value->quantity,
                'price' => $value->price,
                'lineSum' => $value->lineSum,
                'codUse' => $value->codUse,
                'codProject' => $value->codProject,
                'codCost' => $value->codCost,
                #'status'=> $value->status
            ];
        }

        return $newArray;

    }
    /**
     * @param Request $request
     * @return bool
     */
    private function checkItemCodeItems(Request $request)
    {
        $check = true;
        foreach ($request->get('requiredProducts') as $key => $value) {
            if (!isset($value['codSAP'])) {
                if (!isset(Catalog::where('substitute', '=', $value['codPartners'])->select('itemCode')->first()->itemCode) ||
                    is_null(Catalog::where('substitute', '=', $value['codPartners'])->select('itemCode')->first()->itemCode)) {
                    $check = false;
                }
            }
        }
        return $check;
    }

    public function save(Request $request)
    {
        try {
            $id = $request->get('id', false);
            DB::beginTransaction();
            $ORG = new ReceiptGoods();
            if ($id) {
                $obj = new ReceiptGoods();
                $ORG = ReceiptGoods::find($id);
                $obj->updateInDB($ORG, $request);
            } else {
                if ($this->checkItemCodeItems($request)) {
                    $ORG->saveInDB($request);
                    if (is_null($ORG->idPurchaseOrders)) {
                        if (isset($request->idXML) && !is_null($request->idXML)) {
                            // ReceiptGoodsCopyToSAP::dispatch($ORG, $request->idXML);
                        } else {
                            // ReceiptGoodsToSAP::dispatch($ORG);
                        }
                    } else {
                        if (isset($request->idXML)) {
                            $ORG->saveCopyInSAP($ORG, $request->idXML);
                        } else {
                            // ReceiptGoodsToSAP::dispatch($ORG, true);
                        }
                    }

                } else {
                    // ReceiptGoodsToDB::dispatch($request->all());
                }
            }
            DB::commit();

            return redirect()->route('purchase.receipts.goods.index')->withSuccess("Salvo com sucesso! Estamos processando...");
        } catch (\Throwable $e) {
            DB::rollBack();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0078', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.receipts.goods.index')->withErrors($e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $sap = new Company(false);
            $head = DB::SELECT("SELECT * from receipt_goods WHERE id = '{$id}'")[0];
            $body = DB::SELECT("SELECT * FROM receipt_goods_items WHERE idReceiptGoods = '{$id}'");
            $body = $this->getItemNameSAP($body);
            $company = DB::SELECT('SELECT TOP 1 id,company,cnpj,address,number,neighborhood,cep,city,telephone,telephone2,email FROM companies order by id desc');
            $idCompany = $company[0]->id;
            $partner = $sap->query("SELECT distinct T0.CardCode,T0.GroupCode, T0.CardName,T1.AdresType, T2.TaxId4,T1.Street, T1.StreetNo, T1.Block, T1.City, T1.State FROM OCRD  T0
                                          INNER JOIN CRD7 T2 ON T0.CardCode = T2.CardCode
                                          INNER JOIN CRD1 T1 ON T0.CardCode = T1.CardCode
                                          WHERE  T0.CardCode =  '{$head->cardCode}' and T1.AdresType = 'S' and T2.TaxId4 is not null");
            $img = DB::SELECT("SELECT TOP 1 reference,idReference,diretory FROM uploads WHERE reference like 'companies' and idReference = '$idCompany' order by id desc");
            $address = $sap->query("SELECT distinct T0.[CardName], T0.[Phone1], T0.[LicTradNum], T1.[Address],
           T1.[Street], T1.[ZipCode], T1.[City], T1.[County], T1.[Country], T1.[State] FROM
           OCRD T0 LEFT JOIN CRD1 T1 ON T0.[CardCode] = T1.[CardCode] WHERE T0.[CardCode]  =  '{$head->cardCode}'")[0];
            $payment = $this->getPayment($head->paymentTerms);
            $expenses = Expenses::where('idReceiptGoods', '=', $id)->get();

            return \PDF1::setOptions(['uplouds' => true])->loadView('relatory.layouts.receiptGoods', compact('expenses', 'payment', 'address', 'head', 'body', 'img', 'company', 'partner'))->setPaper('a4', 'portrait')->stream('pdf.pdf');
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    private function getPayment($id)
    {
        $sap = new Company(false);
        return $sap->query("SELECT T1.[GroupNum], T1.[PymntGroup] FROM [dbo].[OCTG]  T1 WHERE  T1.[GroupNum] = '{$id}'")[0];
    }

    private function getItemNameSAP($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            $sap = new Company(false);
            $newArray[] = [
                'itemCode' => $value->itemCode,
                'itemName' => $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
                'quantity' => $value->quantity,
                'price' => $value->price,
                'lineSum' => $value->lineSum,
                'codUse' => $sap->query("SELECT T0.[Usage] FROM OUSG T0 WHERE T0.[ID]  = '{$value->codUse}'")[0]['Usage'],
                'codProject' => $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->codProject}'")[0]['PrjName'],
                'codCost' => $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->codCost}'")[0]['OcrName']
            ];
        }

        return $newArray;

    }

    public function canceled($id)
    {
        try {
            DB::beginTransaction();
            $item = ReceiptGoods::find($id);
            $ORG = new ReceiptGoods();
            $ORG->canceledInSAP($item);
            DB::commit();
            if ($item->is_locked) {
                return redirect()->route('purchase.receipts.goods.read', $item->id)->withErrors($item->message);
            } else {
                return redirect()->route('purchase.receipts.goods.read', $item->id)->withSuccess("Item $item->code Cancelado com sucesso!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('E0FAS', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function closed($id)
    {
        try {
            DB::beginTransaction();
            $item = ReceiptGoods::find($id);
            $ORG = new ReceiptGoods();
            $ORG->closedInSAP($item);
            DB::commit();
            if ($item->is_locked) {
                return redirect()->route('purchase.receipts.goods.read', $item->id)->withErrors($item->message);
            } else {
                return redirect()->route('purchase.receipts.goods.read', $item->id)->withSuccess("Item $item->code Encerrado com sucesso!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('E0FAT', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function tablePN(Request $request)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OCRD");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");
        $columnsToSelect = ['CardCode', 'CardName', 'LicTradNum'];

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("CardCode", "like", "%{$search['value']}%")
                ->orWhere("CardName", "like", "%{$search['value']}%")
                ->orWhere("LicTradNum", "like", "%{$search['value']}%");
        }

        $order = $request->get('order');
        $query->orderBy($columns[$order[0]['column']]['name'], $order[0]['dir']);

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->distinct()->get($columnsToSelect)
        ]);

    }

    public function filter(Request $request)
    {
        try {
            $sap = new Company(false);
            $sql = "SELECT T1.name, T0.id,T0.codSAP,T0.code,T0.coin,T0.quotation,T0.identification,T0.cardCode,T0.cardName,
                              T0.taxDate,T0.docTotal,T0.status
                      FROM receipt_goods as T0
                      JOIN users T1 on T0.idUser = T1.id
                      where T0.id != '-1'";

            if (!is_null($request->code)) {
                $sql .= " and T0.code like '%{$request->code}%'";
            }
            if (!is_null($request->codSAP)) {
                $sql .= " and T0.codSAP like '%{$request->codSAP}%'";
            }
            if (!is_null($request->cardName)) {
                $sql .= " and T0.cardName like '%{$request->cardName}%'";
            }
            if (!is_null($request->cpf_cnpj)) {
                $aux = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
                $sql .= " and (replace(replace(replace(T0.identification, '.', ''), '/', ''), '-', '') = '{$aux}' )";
            }
            if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
                $sql .= " and T0.taxDate >=   '" . $request->data_fist . "' and T0.taxDate <='" . $request->data_last . "'";
            }
            if (!is_null($request->status)) {
                $sql .= " and T0.status = {$request->status}";
            }

            $sql .= " order by  T0.id desc ";
            $query = DB::select($sql);
            return view('purchase::receiptsGoods.index', ['items' => $query]);
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0012', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return view('purchase::receiptsGoods.index')->withErrors($e->getMessage());
        }

    }

    public function read($id)
    {
        $head = ReceiptGoods::find($id);
        $CF = DB::SELECT("SELECT T0.id from cash_flows as T0 JOIN  cash_flow_items as T1 on T0.id = T1.idCashFlow
                        WHERE T1.idTransation = '{$id}' and T1.transation like 'receipt_goods'");
        $body = Items::where('idReceiptGoods', '=', $id)->get();
        $expenses = Expenses::where('idReceiptGoods', '=', $id)->get();
        $taxes = Tax::where('idReceiptGoods', '=', $id)->get();
        return view("purchase::receiptsGoods.create", array_merge(['ORG' => new ReceiptGoods(), 'taxes' => $taxes, 'expenses' => $expenses, 'rg' => true, 'head' => $head, 'CF' => $CF, 'body' => $body], $this->options()));
    }

    public function cFromNFE($id)
    {
        $head = ReceiptGoods::find($id);
        if(!$head->codSAP) {
            return redirect()->back()->withErrors('Recebimento não sincronizado com o SAP!');
        }
        $body = Items::where('idReceiptGoods', '=', $id)->get();
        $expenses = Expenses::where('idReceiptGoods', '=', $id)->get();
        $taxes = Tax::where('idReceiptGoods', '=', $id)->get();
        $CF = CFItems::join('cash_flows', 'cash_flows.id', '=', 'cash_flow_items.idCashFlow')
            ->where('cash_flow_items.idTransation', '=', $id)
            ->where('cash_flow_items.transation', 'like', 'receipt_goods')
            ->get(['cash_flows.id']);

        return view("purchase::incoingInvoice.copy_receipt", array_merge(['CF' => $CF, 'idReceiptGoods' => $id, 'taxes' => $taxes, 'expenses' => $expenses, 'head' => $head, 'body' => $body], $this->options()));

    }

    public function copy($id)
    {
        $head = PurchaseOrder::find($id);
        DB::SELECT("SELECT T0.id from cash_flows as T0 JOIN  cash_flow_items as T1 on T0.id = T1.idCashFlow
                        WHERE T1.idTransation = '{$id}' and T1.transation like 'receipt_goods'");
        $body = Item::where('idPurchaseOrders', '=', $id)->get();
        $CF = DB::SELECT("SELECT T0.id from cash_flows as T0 JOIN  cash_flow_items as T1 on T0.id = T1.idCashFlow
                        WHERE T1.idTransation = '{$id}' and T1.transation like 'purchase_orders'");
        #$expenses = Expenses::where('idReceiptGoods','=', $id)->get();
        #$taxes = Tax::where('idReceiptGoods','=', $id)->get();
        return view("purchase::receiptsGoods.copy", array_merge(['head' => $head, 'CF' => $CF, 'body' => $body], $this->options()));

    }

    public function report(Request $request){
        
        $requests = new Requests();
        return view("purchase::receiptsGoods.report",compact('requests'));
    }

    public function gerarReport(Request $request){

        $sap = new Company(false);
        $sql = "SELECT T1.name, T0.id,T0.codSAP,T0.code,T0.coin,T0.quotation,T0.identification,T0.cardCode,T0.cardName,
                    T0.taxDate,T0.docTotal,T0.status
                FROM receipt_goods as T0
                JOIN users T1 on T0.idUser = T1.id
                where T0.id != '-1'";

        if (!is_null($request->code)) {
            $sql .= " and T0.code = '{$request->code}'";
        }
        if (!is_null($request->name)) {
            $sql .= " and T0.cardName like '%{$request->name}%'";
        }
        if ((!is_null($request->data_ini)) && (!is_null($request->data_fim))) {
            $sql .= " and T0.taxDate >=   '" . $request->data_ini . "' and T0.taxDate <='" . $request->data_fim . "'";
        }
        if (!is_null($request->status)) {
            $sql .= " and T0.status = {$request->status}";
        }

        $sql .= " order by  T0.id desc ";

        $items = DB::select($sql);
                
        $tipo = $request->tipo;
            
        $requests = new Requests();

        $body = [];
        $count = 0;


        foreach($items as $value){
            $corpo = Items::where('idReceiptGoods', '=', $value->id)->get();
            $body[$count] = $this->getItemName($corpo);
            $count++;
        }
        
        $pdf = SnappyPdf::loadView('purchase::receiptsGoods.reportview',compact('requests', 'items', 'tipo', 'body'));
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-top', 10);
        $pdf->setOrientation('landscape')->setOption('margin-bottom', 0);
        return $pdf->inline('requisicao_compra.pdf');
    }

}
