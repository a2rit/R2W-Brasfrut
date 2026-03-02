<?php

namespace App\Modules\Purchase\Http\Controllers;


use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item;
use App\Modules\Purchase\Jobs\PurchaseRequestToSAP;
use App\Modules\Purchase\Jobs\CanceledPurchaseRequestToSAP;
use App\Jobs\UploadsToSAP;
use App\Upload;
use App\LogsError;
use App\User;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\SapUtilities;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\Notifications\NewPurchaseRequest;
use Notification;
use App\Models\Alertas;
use App\JasperReport;
use Carbon\Carbon;
use App\Jobs\Queue;
use App\Modules\Purchase\Jobs\ClosePurchaseRequestToSAP;

class PurchaseRequestController extends Controller
{
    use SapUtilities;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        $busca = PurchaseRequest::leftJoin('users', 'users.id', '=', 'purchase_requests.requesterUser')
            ->select('purchase_requests.solicitante as solicitante', 
                    'purchase_requests.codSAP', 'purchase_requests.id', 
                    'purchase_requests.code', 'purchase_requests.requriedDate', 
                    'purchase_requests.created_at', 'users.name', 'purchase_requests.codStatus');
        
        $buscaGraph = PurchaseRequest::select('purchase_requests.codStatus')
                        ->whereBetween('purchase_requests.requriedDate', [Carbon::now()->subYear(), Carbon::now()]);

        if($user->group->name == "Requisição"){
            $busca->where('purchase_requests.requesterUser', '=', $user->id);
            $buscaGraph->where('purchase_requests.requesterUser', '=', $user->id);
        }
        
        $busca = $busca->orderBy('purchase_requests.created_at', 'desc')->paginate(30);
        $buscaGraph = $buscaGraph->get();

        $POR = new PurchaseRequest();
        return view("purchase::purchaseRequest.index", compact('busca', 'POR', 'buscaGraph'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {      
        return view("purchase::purchaseRequest.create", $this->getOptions());
    }

    public function save(Request $request)
    {  
        try{
            DB::beginTransaction();
            $id = $request->get('id', false);
            if($id){
                $p_request = PurchaseRequest::find($id);
                $p_request->updateInDBRequest($request);
            }else{
                $p_request = new PurchaseRequest();
                $p_request->saveInDBRequest($request);
                
                Notification::send($this->getNotifiableUsers(), new NewPurchaseRequest("Nova Solicitacao de Compra de " . $p_request->solicitante));

                foreach( $this->getNotifiableUsers() as $key => $value ){
                    Alertas::create([
                        'id_document' => $p_request->id,
                        'type_document' => '2',
                        'id_user' => $value->id,
                        'text' => 'Nova Solicitacao de Compra de ' . $p_request->solicitante,
                        'title' => 'Solicitacao de Compra',
                        'status' => '1'
                    ]); 
                }
            }

            saveUpload($request, 'purchase_requests', $p_request->id);
            DB::commit();

            $uploads = Upload::where('idReference', $p_request->id)->where('reference', 'purchase_requests')->first();
            if(!empty($uploads)){
                UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }
            PurchaseRequestToSAP::dispatch($p_request)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            return redirect()->route('purchase.request.read', $p_request->id)->withSuccess("Solicitação de compras salva com sucesso. Em breve será sincronizada com o SAP.");
        }catch (\Exception $e){
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas228', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.request.index')->withErrors($e->getMessage());
        }
    }
    public function fromPurchase(Request $request)
    {
        //Criar pedido de compra 
        try{
            DB::beginTransaction();
            $id = $request->get('id', false);
            $p_request = PurchaseRequest::find($id);
            
            $p_order = new PurchaseOrder();
            $p_order->saveInDBFromPurchase($request,$p_request);
          
            $p_request->codStatus = (string)$p_request::STATUS_PENDING;
            $p_request->codePC = $p_order->code;
            DB::commit();
            return redirect()->route('purchase.order.read.from.request', $p_order->id);
        }catch (\Exception $e){
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas230', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.request.index')->withErrors($e->getMessage());
        }
    }
    public function fromQuotation(Request $request)
    {
        //Criar cotacao de compra 
        try{
            
            DB::beginTransaction();
            $id = $request->get('id', false);
            $p_request = PurchaseRequest::find($id);
            
            $p_quotation = new PurchaseQuotation();
            $p_quotation->saveInDB($request);
            $p_quotation->isRequest = true;
            $p_quotation->idRequest = $p_request->id;
            $p_quotation->save();

            $p_request->codStatus = (string)$p_request::STATUS_PQ_G;
            $p_request->isQuotation = true;
            $p_request->idQuotation = $p_quotation->id;
            $p_request->save();
            DB::commit();
            return redirect()->route('purchase.quotation.read', $p_quotation->id)->withSuccess("Cotação salva com sucesso!");
        }catch (\Exception $e){
            DB::rollback();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas229', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.request.index')->withErrors($e->getMessage());
        }
    }

    public function canceled($id)
    {
        try {
            $oPOR = PurchaseRequest::find($id);
            CanceledPurchaseRequestToSAP::dispatch($oPOR)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);

            return redirect()->route('purchase.request.read', $oPOR->id)->withSuccess("Documento enviado para cancelamento!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }


    public function read($id)
    {
        try{
            $head = PurchaseRequest::with('internal_request')->find($id);
            $body = $head->items()->get();
            $quotations = PurchaseQuotation::where('idRequest', $head->id);
            $upload = Upload::where('reference','purchase_requests')->where('idReference',$id)->get();
            // Alertas::checkAlerts($head->id);// atualiza o status dos alertas pertencentes ao documento para verificado.
            
            return view("purchase::purchaseRequest.create", array_merge([
                    'OPRR' => new PurchaseRequest,
                    'head' => $head,
                    'body' => $body,
                    'quotations' => $quotations,
                    'upload' => $upload
                ], 
                $this->getOptions()));   

        }catch(\Throwable $e){
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0011', 'Abrindo solicitação de compra: ', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
        
    }

    public function readCode($code)
    {

        try{
            $head = PurchaseRequest::where('code', $code)->first();
            $body = Item::where('idPurchaseRequest', '=', $id)->get();
            $quotations = PurchaseQuotation::where('idRequest', $head->id);
            return view("purchase::purchaseRequest.create", array_merge([
                    'OPRR' => new PurchaseRequest,
                    'head' => $head,
                    'body' => $this->getItemName($body),
                    'quotations' => $quotations,
                ], 
                $this->getOptions()));   

        }catch(\Throwable $e){
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0012', 'Abrindo solicitação de compra: ', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateUploads(Request $request){
        saveUpload($request, $request->table, $request->id);
        $purchase_request = PurchaseRequest::find($request->id);
        $response = $purchase_request->updateUpload();
    }

    public function removeUpload($id,$idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::find($id);
            $diretory = public_path($upload->get()->first()->diretory);
            if(file_exists($diretory)){
                unlink($diretory);
            };
            $upload->delete();
            DB::commit();
           
            return redirect()->route('purchase.request.read', $idReference)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    
    public function filter(Request $request){
        try{
            $user = Auth::user();

            $busca = PurchaseRequest::select('purchase_requests.solicitante as solicitante', 
                        'purchase_requests.codSAP', 'purchase_requests.id', 
                        'purchase_requests.code', 'purchase_requests.requriedDate as requiredDate', 
                        'purchase_requests.created_at', 'T1.name', 'purchase_requests.codStatus')
                    ->join('users as T1', 'T1.id', '=', 'purchase_requests.idUser')
                    ->join('users as T2', 'T2.userClerk', '=', 'purchase_requests.idSolicitante');
        
            if($user->group->name == "Requisição"){
                $busca->where('purchase_requests.requesterUser', '=', $user->id);
            }

            if (!is_null($request->code)) {
                $busca->where('purchase_requests.code','like', "%$request->code%");
            }
            if (!is_null($request->codSAP)) {
                $busca->where('purchase_requests.codSAP','like', "%$request->codSAP%");
            }
            if (!is_null($request->usuario)) {
                $busca->where('T1.id', '=', (Integer)$request->usuario);
            }
            if (!is_null($request->solicitante)) {
                $busca->where('T2.id', '=', (Integer)$request->solicitante);
            }
            if ((!is_null($request->data_fist))) {
                $busca->whereDate('purchase_requests.created_at','>=', "{$request->data_fist}");
                
            }
            if ((!is_null($request->data_last))) {
                
                $busca->whereDate('purchase_requests.created_at','<=', "{$request->data_last}");
            }
            if (!is_null($request->status) && ($request->status != '-1') ) {
                $busca->where('purchase_requests.codStatus','=', "{$request->status}");
            }

            

            $buscaGraph = $busca->distinct()->get();
            $request->flash();
            
            $requests = new PurchaseRequest();
            return view("purchase::purchaseRequest.index", [
                'busca' => $busca->distinct()->orderBy('purchase_requests.id', 'desc')->paginate(30)->appends(request()->query()), 
                'buscaGraph' => $buscaGraph,
                'POR' => $requests])->withInput($request->all());
        }catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0013', 'Listando o entrada de mercadoria', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }

    }
    public function anyDataPurchaseRequest($id)
    {
        $producs = DB::SELECT("SELECT T0.itemCode, T0.quantity, T0.quantityPendente, T0.project, T1.ItemName, 
                            T2.OcrName as distrRule, T3.OcrName as distriRule2, T4.WhsName as wareHouseCode
                        FROM purchase_request_items T0
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OITM T1 ON T0.itemCode = T1.ItemCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T2 ON T0.distrRule = T2.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OOCR T3 ON T0.distriRule2 = T3.OcrCode
                        LEFT JOIN SAPHOMOLOGACAO.dbo.OWHS T4 ON T0.wareHouseCode = T4.WhsCode
                        WHERE T0.idPurchaseRequest = '{$id}'");
        return response()->json([
            "data" => $producs
        ]);

    }
    public function anyDataPurchaseRequest2($id)
    {
        $producs = DB::SELECT("SELECT name, solicitante FROM purchase_requests where purchase_requests.id = '{$id}'");
        
        return response()->json([
            "data" => $producs
        ]);
    }

    public function anyDataPurchaseRequest3(Request $request)
    {        
        $user = Auth::user();
        
        if($user->group->name != "Requisição"){
            $p_requests = PurchaseRequest::select('purchase_requests.solicitante as solicitante', 
                'purchase_requests.codSAP', 'purchase_requests.id', 
                'purchase_requests.code', 'purchase_requests.requriedDate as requiredDate', 
                'purchase_requests.created_at', 'T1.name', 'T2.name as atendente', 'purchase_requests.codStatus')
                ->leftJoin('users as T1', 'T1.id', '=', 'purchase_requests.requesterUser')
                ->leftJoin('users as T2', 'T2.id', '=', 'purchase_requests.clerkUser')
                ->where('purchase_requests.codSAP', null)
                ->where('purchase_requests.code', 'like', '%SLC%')
                ->whereDate('purchase_requests.created_at', '>=', date('Y-m-d', strtotime(date('Y-m-d'). ' - 30 days')))
                ->orderBy('purchase_requests.id', 'desc')->get();
            return response()->json([
                "data" => $p_requests
            ]);
        }
    }

    public function listRequestsTopNav(Request $request)
    {        
        $user = Auth::user();

        if($user->group->name != "Requisição"){
            $columns = [];

            foreach($request->fields as $index => $value){
                $columns[$index] = $value['fieldName'];
            }

            $columns = implode(',', $columns);
            $sql = "SELECT DISTINCT TOP 10 $columns FROM [VW_R2W_PURCHASE_REQUEST_TOP_NAV]";
            
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
    }

    public function print($id, $type)
    {
        
        try {
            $purchase_request = PurchaseRequest::find($id);
            if($purchase_request->codSAP){
                if($type=="excel")
                {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Excel.jasper";
                    
                    if(!file_exists($relatory_model)){
                        $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Excel.jrxml";
                    }

                    $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_request';
                    $output = public_path('/relatorios'.'/'.$file_name);
                    $report = $report->generateReport($relatory_model, $output, ['xls'], ['codSAP'=>$purchase_request->codSAP], 'pt_BR', 'sap');
                    
                    return response()->download($report)->deleteFileAfterSend(true);

                }elseif($type=="pdf")
                {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest.jasper";
                    $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_request';
                    $output = public_path('relatorios'.'/'.$file_name);
                    if(!file_exists($relatory_model)){
                        $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest.jrxml";
                    }

                    $report = $report->generateReport($relatory_model, $output, ['pdf'], ['codSAP'=>$purchase_request->codSAP], 'pt_BR', 'sap');
                }
                
                return response()->file($report)->deleteFileAfterSend(true);
            }else{
                return redirect()->back()->withErrors('Apenas é possivel gerar relátorios com documentos sincronizados com o SAP!');
            }

        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    public function duplicate($id)
    {
        try {
            $oldRequest = PurchaseRequest::find($id);
            if(!empty($oldRequest)){
                DB::beginTransaction();
                $p_request = new PurchaseRequest();
                $p_request->duplicate($oldRequest);
                DB::commit();

                Notification::send($this->getNotifiableUsers(), new NewPurchaseRequest("Nova Solicitacao de Compra de " . $p_request->solicitante));

                foreach( $this->getNotifiableUsers() as $key => $value ){
                    Alertas::create([
                        'id_document' => $p_request->id,
                        'type_document' => '2',
                        'id_user' => $value->id,
                        'text' => 'Nova Solicitacao de Compra de ' . $p_request->solicitante,
                        'title' => 'Solicitacao de Compra',
                        'status' => '1'
                    ]); 
                }

                return redirect()->route('purchase.request.read', $p_request->id);
            }

        } catch (\Throwable $e) {
            DB::rollback();
            $logsError = new logsError();
            $logsError->saveInDB('PR(1)', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.index')->withErrors($e->getMessage());
        }
    }

    public function getOptions(){
        $POR = new PurchaseRequest();
        $sap = new Company(false);
        $date = DATE('Y-m-d');
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $wareHouseCode = $sap->query("select WhsCode as value, WhsName as name from OWHS");
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $budgetAccountingAccounts = $sap->query("SELECT DISTINCT a.name as value, b.AcctName as name FROM [@A2RORCPC] a INNER JOIN OACT b ON a.Name = b.AcctCode");
        
        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");

        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);

        return compact('centroCusto','POR','date','requesters', 'paymentConditions', 'centroCusto2', 'projeto', 'wareHouseCode', 'budgetAccountingAccounts');
            
    }

    // private function getItemName($array)
    // {
    //     $newArray = [];
    //     foreach ($array as $key => $value) {
    //         $sap = new Company(false);
    //         $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];
    //         $wareHouse = $sap->getDb()->table('OWHS')->where('OWHS.WhsCode', $value->wareHouseCode)->first();

    //         if(!is_null($wareHouse))
    //             $wareHouseName = $wareHouse->WhsName;
    //         else
    //             $wareHouseName = "";

    //         $lastProvider = isset($query[0]['CardCode']) ? $query[0]['CardCode'] : null;
            
    //         $newArray[] = [
    //             'id' => $value->id,
    //             'idPurchaseRequest' => $value->idPurchaseRequest,
    //             'idPurchaseOrders' => !is_null($value->idPurchaseOrders) ? $value->idPurchaseOrders : null,
    //             'itemCode' => $value->itemCode,
    //             'itemName' => $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
    //             'itemUnd' => (!is_null($value->itemUnd) ? $value->itemUnd : (!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom']) ),
    //             // 'price' => $sap->query("SELECT T0.[ItemCode], T0.[AvgPrice] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['AvgPrice'],
    //             'quantity' => $value->quantity,
    //             'quantityPendente' => $value->quantityPendente,
    //             'lastProvider' => $lastProvider,
    //             'project' => $value->project,
    //             //'codCost' => $value->codCost,
    //             'centroCusto' => $value->distrRule,
    //             'centroCusto2' => $value->distriRule2,
    //             'wareHouseCode' => $value->wareHouseCode,
    //             'wareHouseName' => $wareHouseName
    //         ];
    //     }

    //     return $newArray;

    // }

    protected function getNotifiableUsers()
    {
        return User::where('tipoCompra','A')->get();
    }

    public function report(Request $request){
        
        $requests = new Requests();
        $usuarios = User::where('ativo','=','1')->select('id','name')->get();
        $POR = new PurchaseRequest();
        return view("purchase::purchaseRequest.report",compact('requests',  'usuarios', 'POR'));
    }

    public function gerarReport(Request $request){
        
        $data = [
            'code' => $request->code ?? 'NULL',
            'docStatus' => $request->status ?? 'NULL',
            'initialDate' => $request->data_ini ?? '2015-01-01',
            'lastDate' => $request->data_fim ?? date('Y-m-d'),
            'idUser' => $request->name ?? 'NULL',
        ];

        if($request->tipo == 2){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Analitico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_request';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Analitico.jrxml";
            }
    
            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }else if($request->tipo == 1){
            
            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Sintetico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_request';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PurchaseRequest-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        }
    }

    public function forceToSAP($id){
        try {
            $p_request = new PurchaseRequest();
            $p_request->saveInSAP($p_request->find($id));
            $p_request = $p_request->find($id);
            if(!is_null($p_request->codSAP)){
                return response()->json(['status' => "success"]);
            }else{
                return response()->json(['status' => 'error', 'message' => $p_request->message]);
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function getPurchaseRequests(Request $request){
        $query = PurchaseRequest::join("users", "users.id", '=', "purchase_requests.requesterUser")
        ->select("purchase_requests.id","purchase_requests.codSAP", "purchase_requests.code","purchase_requests.codStatus", "users.name", "purchase_requests.requriedDate")
            ->where(function($query){
                $query->where('purchase_requests.codStatus', PurchaseRequest::STATUS_OPEN)
                    ->orWhere('purchase_requests.codStatus', PurchaseRequest::STATUS_PENDING);
            });
        
        $recordsTotal = $query->count();
        $columns = $request->get("columns");
        $search = $request->get('search');
        
        if(!empty($search['value'])) {
            $query->where(function ($query) use ($search) {
                $query->where("purchase_requests.codSAP", "like", "%{$search['value']}%");
                $query->orWhere("purchase_requests.code", "like", "%{$search['value']}%");
            });
        }

        if(!empty($request->get('requester'))){
            $query->where('purchase_requests.requesterUser', '=', $request->get('requester'));
        }

        if(!empty($request->get('date'))){
            $date = str_replace("/", "-", explode(" - ", $request->get('date')));
            $query->whereDate('purchase_requests.requriedDate', '>=', date_format(date_create($date[0]),"Y-m-d"))
                ->whereDate('purchase_requests.requriedDate', '<=', date_format(date_create($date[1]),"Y-m-d"));
        }

        $recordsFiltered = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $order = $request->get('order');
        
        if(!empty($order) && $order[0]['column'] != 0){
            $query->orderBy("purchase_requests.".$columns[$order[0]['column']]['name'], $order[0]['dir']);
        }else{
            $query->orderBy("purchase_requests.id", "desc");
        }
        
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $query->get()
        ]);
    }

    public function editingMass(Request $request){
        try {
            $action = $request->massEditingAction == 'cancel' ? 2 : 0;
            foreach ($request->idPurchaseRequest as $key => $value) {
                $purchase_request = PurchaseRequest::find($key);
                if($action === 2){
                    CanceledPurchaseRequestToSAP::dispatch($purchase_request)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }elseif($action === 0){
                    ClosePurchaseRequestToSAP::dispatch($purchase_request)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
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
