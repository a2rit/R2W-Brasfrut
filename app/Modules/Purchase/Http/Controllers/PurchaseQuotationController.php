<?php

namespace App\Modules\Purchase\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item as ItemR;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotationSearch;
use App\Modules\Purchase\Models\PurchaseQuotation\Item;
use App\Modules\Inventory\Jobs\PurchaseRequestToSAP;
use App\Modules\Purchase\Jobs\CanceledPurchaseQuotationToSAP;
use App\Modules\Purchase\Jobs\PurchaseQuotationToSAP;
use App\LogsError;
use App\Upload;
use App\Jobs\Queue;
use App\Jobs\UploadsToSAP;
use App\User;
use App\SapUtilities;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\GrupoWhs;
use App\JasperReport;
use App\Modules\Purchase\Mail\PurchaseQuotationMail;
use Auth;
use Illuminate\Support\Facades\Mail;

class PurchaseQuotationController extends Controller
{
    use SapUtilities;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $busca = PurchaseQuotation::select('purchase_quotation.name_solicitante', 'purchase_quotation.created_at',
            'purchase_quotation.id', 'purchase_quotation.code', 'purchase_quotation.codSAP', 'purchase_quotation.idRequest',
            'purchase_quotation.data_i as data_i', 'purchase_quotation.status', 'purchase_quotation.id_solicitante',
            'purchase_quotation.provider1', 'OCRD.CardName as provider1Name')
            ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'purchase_quotation.provider1');


        $buscaGraph = PurchaseQuotation::select('purchase_quotation.status')
            ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'purchase_quotation.provider1')
            ->whereBetween('data_i', [Carbon::now()->subYear(), Carbon::now()])->get();
        $busca = $busca->orderBy('purchase_quotation.id', 'desc')->paginate(30);

        $p_requests = PurchaseRequest::join('purchase_request_items', 'purchase_request_items.idPurchaseRequest', '=', 'purchase_requests.id')
            ->whereDate('purchase_requests.created_at', '>', date("Y-m-d", strtotime(date('Y-m-d')."-2 MONTH")))
            ->where('codSAP', '!=', null)
            ->where('codStatus','1')
            ->orWhere('codStatus', '3')
            ->get();
        $POR = new PurchaseQuotation();
       
        return view("purchase::purchaseQuotation.index", compact('busca', 'POR','p_requests', 'buscaGraph'));
    }

    public function copyFromRequest(Request $request){
        try{
           
            if(!isset($request->id_doc)) return redirect()->route('purchase.quotation.index')->withErrors('Para prosseguir, é preciso selecionar 1 ou mais Solicitações'); 

            $p_quotation = new PurchaseQuotation();
            $p_quotation->copyFromPurchaseRequest($request);
            
            return redirect()->route('purchase.quotation.read', $p_quotation->id);

        }catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EPOFR01', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.quotation.index')->withErrors($e->getMessage());
        }
    }

    public function filter(Request $request){
        try{
            $data = PurchaseQuotation::select(
                'purchase_quotation.name_solicitante','purchase_quotation.id', 'purchase_quotation.created_at', 
                'purchase_quotation.code', 'purchase_quotation.codSAP','purchase_quotation.idRequest', 
                'purchase_quotation.data_i as data_i','purchase_quotation.status','purchase_quotation.id_solicitante',
                'purchase_quotation.provider1', 'purchase_quotation.provider2', 'purchase_quotation.provider3',
                'purchase_quotation.provider4', 'purchase_quotation.provider5', 'OCRD.CardName as provider1Name')
                ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'purchase_quotation.provider1');            

            if (!is_null($request->code)) {
                $data->where('purchase_quotation.code','like', "%$request->code%");
            }
            if (!is_null($request->codSAP)) {
                $data->where('purchase_quotation.codSAP','like', "%$request->codSAP%");
            }
            if (!is_null($request->solicitante)) {
                $data->where('purchase_quotation.id_solicitante', '=', $request->solicitante);
            }
            if (!is_null($request->provider)) {
                $data->where('purchase_quotation.provider1','=', "{$request->provider}");
            }
            if ((!is_null($request->data_fist)) ) {
                $data->whereDate('purchase_quotation.data_i','>=', "{$request->data_fist}");
            }
            if ((!is_null($request->data_last))) {
                $data->whereDate('purchase_quotation.data_i','<=', "{$request->data_last}");
            }
            if (!is_null($request->status) && ($request->status != '-1') ) {
                $data->where('purchase_quotation.status','=', "{$request->status}");
            }

            $POR = new PurchaseQuotation();

            $buscaGraph = $data->get();

            $data = $data->orderBy('codSAP', 'desc')->paginate(30)->appends(request()->query());

            $p_requests = PurchaseRequest::where('codStatus','1')->get();
            $request->flash();

            return view("purchase::purchaseQuotation.index", [
                'busca' => $data,
                'buscaGraph' => $buscaGraph,
                'POR' => $POR,
                'p_requests' => $p_requests]);
        }catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0010', 'Listando o entrada de mercadoria', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function filterBestItems(Request $request){
        $items = [];
        
        foreach($request->idItemsPurchaseRequest as $indexA => $value){
            $comparative_items = Item::where('idItemPurchaseRequest', $value)
                ->select('purchase_quotation.code', 'purchase_quotation.data_f', 'purchase_quotation_items.id', 'purchase_quotation_items.idPurchaseQuotation',
                            'purchase_quotation_items.itemCode', 'purchase_quotation_items.itemName', 'purchase_quotation_items.qtdP1',
                            'purchase_quotation_items.quantityPendente', 'purchase_quotation_items.priceP1', 'purchase_quotation_items.totalP1', 'OCRD.CardCode', 'OCRD.CardName',
                            'OCTG.PymntGroup')
                ->join('purchase_quotation', 'purchase_quotation.id', '=', 'purchase_quotation_items.idPurchaseQuotation')
                ->leftJoin(config('sap.db.database').".dbo.OCRD", "OCRD.CardCode", '=', 'purchase_quotation.provider1')
                ->leftJoin(config('sap.db.database').'.dbo.OCTG', 'OCTG.GroupNum', '=', 'purchase_quotation.paymentTerms');
    
            if ($request->get('cardCode')) {
                $comparative_items->where('purchase_quotation.provider1', '=', $request->get('cardCode'));
            }

            if ($request->get('deliveryDate')) {
                $comparative_items->where('purchase_quotation.provider1', '=', $request->get('cardCode'));
            }

            $comparative_items = $comparative_items->get();
            $comparative_params = ['qtd' => 0.00, 'price' => 999999999.00, 'item_index' => 0]; // se mudar da bug
            if(!$comparative_items->isEmpty()){
                foreach($comparative_items as $index => $item){
                    $price = (Double)$item->qtdP1 * (Double)$item->priceP1;
                    if(!empty($item->qtdP1) && $item->qtdP1 >= $comparative_params['qtd'] && $price <= $comparative_params['price']){
                        $comparative_params['price'] = (Double)$price;
                        $comparative_params['item_index'] = $index;
                        $comparative_params['qtd'] = (Double)$item->qtdP1;
                    }
                }
                array_push($items, $comparative_items[$comparative_params['item_index']] ?? null);
            }
        }

        // if ($request->get('status')) {
        //     $comparative_items->where('status', '=', $request->get('status'));
        // }

        $return = [];
        $return['recordsTotal'] = count($items);
        $return['recordsFiltered'] = count($items);
        $return['draw'] = $request->get('draw');
        $return['data'] = $items;

        return response()->json($return);
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {      
        $purchase_quotation = new PurchaseQuotation;
        return view("purchase::purchaseQuotation.create", compact('purchase_quotation'), $this->getOptions());
    }

    public function save(Request $request)
    {
        try{
            $id = $request->get('id', false);

            DB::beginTransaction();
            if($id){
                $p_quotation = PurchaseQuotation::find($id);
                $p_quotation->updateInDB($request);
                
                saveUpload($request, 'purchase_quotation', $p_quotation->id);
                DB::commit();
                $uploads = Upload::where('idReference', $p_quotation->id)->where('reference', 'purchase_quotation')->first();
                if(!empty($uploads)){
                    UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }

                PurchaseQuotationToSAP::dispatch($p_quotation)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }
            return redirect()->route('purchase.quotation.read',$id)->withSuccess('Cotação salva com sucesso!');
        }catch (\Exception $e){
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas231', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.quotation.index')->withErrors($e->getMessage());
        }
    }

    public function read($id)
    {
        $head = PurchaseQuotation::join('purchase_requests', 'purchase_requests.id', '=', 'purchase_quotation.idRequest')
                    ->select('purchase_quotation.id', 'purchase_quotation.codSAP', 'purchase_quotation.code', 'purchase_quotation.id_solicitante', 'purchase_quotation.message',
                        'purchase_quotation.name_solicitante', 'purchase_quotation.provider1', 'purchase_quotation.provider1_email', 'purchase_quotation.update',
                        'purchase_quotation.status', 'purchase_quotation.created_at', 'purchase_quotation.data_i', 'purchase_quotation.data_f', 'purchase_quotation.created_at',
                        'purchase_quotation.id_order', 'purchase_quotation.code_order', 'purchase_quotation.isRequest',
                        'purchase_quotation.idRequest', 'paymentTerms', 'purchase_quotation.parent', 'purchase_requests.code as codeRequest',
                        'OCRD.CardName as provider1Name')
                    ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'purchase_quotation.provider1')
                    ->where('purchase_quotation.id', '=', $id)
                    ->first();
        
        $body = Item::where('idPurchaseQuotation', '=', $id)->get();
        $head_expenses = $head->expenses()->get();
        
        if($head->parent == null){
            $comparative_head = PurchaseQuotation::where('parent', $head->id)->orWhere('id', $head->id)->get();
        }else{
            $comparative_head = PurchaseQuotation::where('parent', $head->parent)->orWhere('id', $head->parent)->get();
        }
        
        $upload = Upload::where('reference','purchase_quotation')->where('idReference',$id)->get();
        $purchase_orders = PurchaseOrder::where('idQuotation', $head->id)->get();
        
        return view("purchase::purchaseQuotation.create", array_merge([
            'head' => $head,
            'body' => $body,
            'head_expenses' => $head_expenses,
            'comparative_head' => $comparative_head,
            'purchase_orders' => $purchase_orders,
            'upload' => $upload,
        ], $this->getOptions()));  
    }

    public function updateUploads(Request $request){
        saveUpload($request, $request->table, $request->id);
        $sap = new Company(false);
        $purchase_quotation = PurchaseQuotation::find($request->id);
        $docNums = $sap->query("SELECT DocNum as codSAP FROM OPQT WHERE U_R2W_CODE = '$purchase_quotation->code'");
        
        if(!empty($docNums)){
            $purchase_quotation->updateUpload();
        }
    }


    public function fromPurchase(Request $request)
    {
        //Criar pedido de compra por item, PARCIAL
        try{
            DB::beginTransaction();
            $p_order = new PurchaseOrder();
            $att = [];
            foreach($request->get('itemPedido') as $key => $value){
                if(count($value) > 3){
                    $att[$value['provider']]['item-'.$key]['id'] = $value['itemID'];
                    $att[$value['provider']]['idQuotation'] = $value['idQuotation'];
                    // $docTotal += floatval($value['total']);
                }
            }
         
            $p_order->saveInDBFromQuotationI($att);
            DB::commit();
            return redirect()->route('purchase.quotation.read', $request->id)->withSuccess("Pedido de compras criado com sucesso!");
        }catch (\Exception $e){
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas228', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.quotation.index')->withErrors($e->getMessage());
        }
    }

    public function duplicate($id){
        $head = PurchaseQuotation::find($id);
        $purchase_request_items = ItemR::where('idPurchaseRequest', $head->idRequest)->where('quantityPendente', '>', 0)->get();
        if(!empty($purchase_request_items)){
            try{
                DB::beginTransaction();
                $p_quotation = new PurchaseQuotation();
                $p_quotation->code = $p_quotation->createCode();
                $p_quotation->id_solicitante = auth()->user()->id;
                $p_quotation->name_solicitante = auth()->user()->name;
                $p_quotation->idRequest = $head->idRequest;
                $p_quotation->isRequest = 1;
                $p_quotation->parent = $head->id;
                          
                $p_quotation->data_i = DATE('Y-m-d');
                $p_quotation->data_f = DATE('Y-m-d');
                $p_quotation->status = $p_quotation::STATUS_OPEN;
                if($p_quotation->save()){
                    foreach ($purchase_request_items as $key => $value) {
                        $sap = new Company(false);
                        $item = new Item();
                        $value['parent'] = null;
                        $value['priceP1'] = (Float)0;
                        $value['totalP1'] = (Float)0;
                        $value['idItemPurchaseRequest'] = $value->id;
                        $value['qtd'] = $value->quantityPendente;
                        $item->saveInDB($value, $p_quotation->id);
                        
                    }
                    DB::commit();
                    return redirect()->route('purchase.quotation.read', $p_quotation->id)->withSuccess("Quotação associada gerada com sucesso!");
                }
                DB::rollback();
                return redirect()->route('purchase.quotation.index')->withErrors('Houve um erro desconhecido, contate o desenvolvedor');
    
            }catch (\Throwable $e) {
                DB::rollback();
                $logsError = new logsError();
                $logsError->saveInDB('EPOFR01', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
                return redirect()->route('purchase.quotation.index')->withErrors($e->getMessage());
            }     
        }
    }

    function getPartnerName($code){
        $sap = new Company(false);
        $partners = $sap->query("SELECT T0.CardCode,T0.GroupCode, T0.CardName, T1.TaxId4, T1.TaxId0 FROM OCRD  T0
        INNER JOIN CRD7 T1 ON T0.CardCode = T1.CardCode
        WHERE T0.CardType = 'S' AND t1.Address='' AND T1.CardCode = '{$code}'
        AND T1.TaxId4 is not null");
        if($partners){
            return $partners[0]['CardName'];
        }else{
            return '';
        }
    }

    
    public function anyDataPurchaseRequest($id)
    {
        $producs = DB::SELECT("SELECT * FROM purchase_request_items where purchase_request_items.idPurchaseRequest = '{$id}'");
        
        return response()->json([
            "data" => $this->getNameItem($producs)
        ]);

    }

    public function anyData($id){
        $producs = DB::SELECT("SELECT T0.itemCode, T0.itemName, T0.qtd, T0.quantityPendente, T0.priceP1, T0.totalP1
                FROM purchase_quotation_items T0
                WHERE T0.idPurchaseQuotation = {$id}");
                
        return response()->json([
            "data" => $producs
        ]);
    }

    public function listQuotationsTopNav(Request $request)
    {        
        $columns = [];

        foreach($request->fields as $index => $value){
            $columns[$index] = $value['fieldName'];
        }

        $columns = implode(',', $columns);
        $sql = "SELECT DISTINCT TOP 10 $columns FROM [VW_R2W_PURCHASE_QUOTATION_TOP_NAV]";
        
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
    

    private function getNameItem($producs)
    {
        $newArray = [];
        foreach ($producs as $key => $value) {
            $sap = new Company(false);
            $newArray[$key]['id'] = $value->id;
            $newArray[$key]['codSAP'] = $value->itemCode;
            $newArray[$key]['idPurchaseRequest'] = $value->idPurchaseRequest;
            $newArray[$key]['quantity'] = $value->quantity;
            $newArray[$key]['qtdInventory'] = $sap->query("SELECT  B.ONHAND  FROM OITW B WHERE '$value->itemCode' = B.ItemCode")[0]['ONHAND'];
            $newArray[$key]['itemName'] = $sap->query("SELECT T0.ItemName FROM OITM T0 WHERE T0.ItemCode = '{$value->itemCode}' ")[0]['ItemName'];
        }
        return $newArray;
    }

    // private function getNameItemPurchaseRequest($producs)
    // {
    //     $newArray = [];
    //     foreach ($producs as $key => $value) {
    //         $sap = new Company(false);
    //         $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];
            

    //         $newArray[] = [
    //             'id' => $value->id,
    //             'idPurchaseRequest' => $value->idPurchaseRequest,
    //             'itemCode' => $value->itemCode,
    //             'itemName' => $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
    //             'itemUnd' => (!is_null($value->itemUnd) ? $value->itemUnd : (!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom']) ),
    //             'quantity' => $value->quantity,
    //             'quantityPendente' => $value->quantityPendente,
    //             'project' => $value->project,
    //             'centroCusto' => $value->distrRule,
    //             'centroCusto2' => $value->distriRule2,
    //         ];
    //     }
    //     return $newArray;
    // }

    public function getOptions($fornecedor = null){
        $sap = new Company(false);

        $query = $sap->query("SELECT TOP 1  T0.[CardCode], T1.[Price] FROM OPCH T0  
            INNER JOIN PCH1 T1 ON T0.DocEntry = T1.DocEntry 
            WHERE  
            T0.DocEntry NOT IN (SELECT T4.BaseEntry FROM ORPC T3 INNER JOIN RPC1 T4 ON T3.DocEntry = T4.DocEntry
            WHERE T4.BaseEntry IS NOT NULL and  T3.SeqCode = 1) ORDER BY T0.[DocDate] desc
            ");

        $lastProvider = isset($query[0]['CardCode']) ? $query[0]['CardCode'] : null;
        $lastPrice = isset($query[0]['Price']) ? $query[0]['Price'] : null;
        $date = DATE('Y-m-d');
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
        $expenses = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        $tax = $this->getTaxOptions($sap);
    
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        
        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");

        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);

        return compact('centroCusto','date','requesters','paymentConditions', 'centroCusto2', 'projeto', 'warehouses', 'lastProvider', 'lastPrice', 'tax', 'expenses');
            
    }
    

   public function canceled($id)
    {
        $oPOQ = PurchaseQuotation::find($id);
        CanceledPurchaseQuotationToSAP::dispatch($oPOQ);
        return redirect()->route('purchase.quotation.read', $oPOQ->id)->withSuccess("Documento enviado para cancelamento.");
    }

    public function getItem(Request $request){
        try {
            $head = PurchaseQuotation::find($request->get('head'));
            $item = Item::join("purchase_quotation", "purchase_quotation.id", "=", "purchase_quotation_items.idPurchaseQuotation")
                        ->select("purchase_quotation_items.totalP1", "purchase_quotation_items.id", "purchase_quotation_items.itemCode", "purchase_quotation_items.idPurchaseQuotation", "purchase_quotation.codSAP")
                        ->where('idPurchaseQuotation', $head->id)->where('idItemPurchaseRequest', $request->item)->first();
            
            $provider = getProviderData($head->provider1);
            
            if(!is_null($item) && !empty($provider)){
                $item->providerName = $provider['CardName'] ?? '';
                $item->providerCode = $provider['CardCode'] ?? '';
            }
            
            return response()->json(['item' => $item]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage().' | '.$e->getLine()], 500);
        }
    }


    public function removeUpload($id,$idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::where('id',$id);
            $diretory = public_path($upload->get()->first()->diretory);
            if(file_exists($diretory)){
                unlink($diretory);
            };
            $upload->delete();
            $oPOR = PurchaseQuotation::find($idReference);
            DB::commit();
           
            return redirect()->route('purchase.quotation.read', $oPOR->id)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function print($id, $type){
        if($type=="excel")
            {
                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseQuotationExcel.jasper";
                
                if(!file_exists($relatory_model)){
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseOrderExcel.jrxml";
                }

                $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_order';
                $output = public_path('/relatorios'.'/'.$file_name);
                $report = $report->generateReport($relatory_model, $output, ['xls'], ['codSAP'=>$head->codSAP], 'pt_BR', 'sap');
                
                return response()->download($report)->deleteFileAfterSend(true);

        }elseif($type=="pdf")
        {
            
            $purchase_quotation = PurchaseQuotation::find($id);

            if($purchase_quotation->parent == null){
                $purchase_quotation->parent = $purchase_quotation->id;
            }
            
            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseQuotation.jasper";
            
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseQuotation.jrxml";
            }
            
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_quotation';
            $output = public_path('/relatorios'.'/'.$file_name);
            $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id'=>$purchase_quotation->parent, 'parent' => $purchase_quotation->parent, 'idPurchaseRequest' => $purchase_quotation->idRequest], 'pt_BR', 'r2w');
            
            return response()->file($report)->deleteFileAfterSend(true);
        }
    }

    public function sendToPartner(Request $request){

        if($request->type === '1'){ // email
            try {
                $purchase_quotation = PurchaseQuotation::find($request->id);
                $purchase_quotation->external_id = str_random(100);
                if($purchase_quotation->save()){
                    Mail::to($request->contact)
                        ->send(new PurchaseQuotationMail("Cotação de Compras", $request->message, $purchase_quotation));
        
                    if(count(Mail::failures()) > 0){
                        return response()->json(['message' => "Não foi possivel enviar a cotação para o parceiro. Tente novamente mais tarde!"], 500);
                    }else{
                        
                        return response()->json(['message' => "Cotação enviada com sucesso!"], 200);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['message' => "Não foi possivel enviar a cotação para o parceiro. Erro: {$e->getMessage()}"], 500);
            }
        }elseif($request->type === '2'){ // whatsapp

        }
    }

    public function externalAccess($external_id){
        try {
            $head = PurchaseQuotation::where('external_id', '=', $external_id)->first();
            if(!empty($head)){
                $body = $head->items;
                $partner = $head->partner();
                $body_purchase_request = ItemR::where('idPurchaseRequest', $head->idRequest)->get();
                $head_expenses = $head->expenses()->get();
                
                if($head->parent == null){
                    $comparative_head = PurchaseQuotation::where('parent', $head->id)->orWhere('id', $head->id)->get();
                }else{
                    $comparative_head = PurchaseQuotation::where('parent', $head->parent)->orWhere('id', $head->parent)->get();
                }
                
                return view("purchase::purchaseQuotation.external-edit", 
                        compact('head',
                                'body',
                                'partner',
                                'head_expenses',
                                'comparative_head',
                                'body_purchase_request')
                , $this->getOptions()); 
            }
            return view('errors.404');
        } catch (\Exception $e) {
            return view('errors.404');
        }
    }

    public function saveExternalQuotation(Request $request){
        try{
            $p_quotation = PurchaseQuotation::where("external_id", "=", $request->external_id)->first();
            if(!empty($p_quotation) && $p_quotation->updateInDB($request)){
                saveUpload($request, 'purchase_quotation', $p_quotation->id);
                $uploads = Upload::where('idReference', $p_quotation->id)->where('reference', 'purchase_quotation')->first();
                if(!empty($uploads)){
                    UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
                PurchaseQuotationToSAP::dispatch($p_quotation)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);

                $p_quotation->external_id = null;
                $p_quotation->save();
                return view("purchase::purchaseQuotation.external-edit-success");
            }
        }catch (\Exception $e){
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas231', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.quotation.index')->withErrors($e->getMessage());
        }
    }
}
