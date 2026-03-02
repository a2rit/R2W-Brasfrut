<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\Notifications\NewTransferTaking;
use Notification;
use App\User;
use Auth;
use App\Models\Alertas;
use App\Modules\Inventory\Models\Requisicao\Requests;

use App\LogsError;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\Transfer\Transfer;
use App\Modules\Inventory\Models\Transfer\Item as ItemTransfer;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Modules\Inventory\Models\TransferTaking\Item;

use Illuminate\Support\Facades\DB;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
#use App\Jobs\Set\TrasnferWhs;
use App\Modules\Inventory\Jobs\TransferTakingToSAP;
use App\Modules\Inventory\Jobs\TransferToSAP;
use Barryvdh\Snappy\Facades\SnappyPdf;

use App\JasperReport;

class TransferTakingController extends Controller{

    public function index(){
      $items = TransferTaking::with(['transfers'])
                ->join('users', 'users.id', '=', 'transfersTaking.idUser')
                ->join('SAPHOMOLOGACAO.dbo.OWHS as T1', 'T1.WhsCode', '=', 'transfersTaking.fromWarehouse')
                ->join('SAPHOMOLOGACAO.dbo.OWHS as T2', 'T2.WhsCode', '=', 'transfersTaking.toWarehouse')
                ->select('transfersTaking.id', 'users.name', 'idTransf', 'codSAPTransf', 'codWEBTransf', 'status', 'docDate', 'taxDate', 'code', 
                          'codSAP', 'T1.WhsName as fromWarehouse', 'T2.WhsName as toWarehouse', 'comments', 'is_locked', 'message', 'transfersTaking.created_at')
                ->orderBy('transfersTaking.id', 'desc')
                ->paginate(30);
                // dd($items);

      return view("inventory::transferTaking.index", compact('items'));
    }

    public function create(Request $request){
      return view("inventory::transferTaking.create", $this->getOption());
    }

    public function check(Request $request){
      Session::put('toWarehouse',$request->warehouse);
      Session::put('fomWarehouse',$request->fromWarehouse);

      $sap = new Company(false);
      $centroCusto = $sap->query("SELECT A.PrcCode as code, A.PrcName as value FROM OPRC A WHERE A.Active = 'Y'");
      $projeto = $sap->query("select OPRJ.PrjCode as code, OPRJ.PrjName as value from OPRJ where OPRJ.Active = 'Y'");
      $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
      $rule = $sap->query("SELECT OOCR.OcrCode as code, OOCR.OcrName as value FROM oocr");

      return view("inventory::transferTaking.create", ['dt'=> $request->data,'whs'=>$request->fromWarehouse,'wh'=>$request->warehouse,'check'=>true,'role'=>$rule,'projeto'=> $projeto,'warehouses'=> $warehouses, 'centroCusto' => $centroCusto]);
    }

    public function save(Request $request){
      try {
        DB::beginTransaction();
          $transfer = new TransferTaking();
          $transfer->saveInDB($request);
          saveUpload($request,'transfersTaking',$transfer->id);
        DB::commit();

        if(!empty($transfer->id)){
          Notification::send($this->getNotifiableUsers(), new NewTransferTaking("Novo pedido de transferencia de " . Auth::user()->name));
  
          foreach( $this->getNotifiableUsers() as $key => $value ){
            Alertas::create([
                'id_document' => $transfer->id,
                'type_document' => '1',
                'id_user' => $value->id,
                'text' => 'Novo pedido de transferencia de ' . Auth::user()->name,
                'title' => 'Pedido de Transferencia',
                'status' => '1'
            ]); 
          }
            return redirect()->route('inventory.transferTaking.edit', $transfer->id)->withSuccess('Salvo com sucesso');
        }else{
          return redirect()->route('inventory.transferTaking.index')->withErrors("Não foi possivel processar o Pedido de Transferência, atualize a página e tente novamente!");
        }
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.transferTaking.index')->withErrors($e->getMessage());
      }
    }

    public function cancel($id){
      try {
        
        $transfer = TransferTaking::where('id', (int)$id)->where("idUser", '=', Auth::user()->id)->first();
        if(!empty($transfer)){
          DB::beginTransaction();
          $transfer->status = 3;
          $transfer->message = "Transferência cancelada";
          $transfer->save();
          DB::commit();

          return redirect()->route('inventory.transferTaking.edit', $transfer->id)
                  ->withSuccess("Documento cancelado com sucesso!");

        }else{
          return redirect()->route('inventory.transferTaking.edit', $id)
                  ->withErrors("Não foi possivel cancelar o documento, o mesmo só pode ser cancelado pelo solicitante!");
        }
        
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        
        return redirect()->route('inventory.transferTaking.edit', $transfer->id)
                  ->withSuccess("{$e->getMessage()}");

      }
    }

    protected function getNotifiableUsers()
    {
        return User::where('tipoTransf','A')->get();
    }

    public function search(){
      return view('inventory::transferTaking.search');
    }

    public function anyData(Request $request){
        $sap = new Company(false);
        $query = $sap->getDb()->table("OWTR");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");
        $columnsToSelect = ['DocNum', 'DocDate','TaxDate', 'Comments', 'U_R2W_USERNAME', 'U_R2W_CODE'];

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("DocNum", "like", "%{$search['value']}%")
                  ->orWhere("DocDate", "like", "%{$search['value']}%")
                 ->orWhere("TaxDate", "like", "%{$search['value']}%")
                  ->orWhere("Comments", "like", "%{$search['value']}%")
                  ->orWhere("U_R2W_USERNAME", "like", "%{$search['value']}%")
                  ->orWhere("U_R2W_CODE", "like", "%{$search['value']}%");
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

    public function searchData($code){
      $sap = new Company(false);
      $query = $sap->query("SELECT T3.DocNum, T0.ItemCode, T0.Dscription, CONCAT(T0.FromWhsCod,' - ', T4.WhsName)as FromWhsCod, CONCAT(T0.WhsCode,' - ', T5.WhsName)as WhsCode, T1.PrjName, T2.OcrName FROM WTR1 T0
                            INNER JOIN OPRJ T1 ON T0.Project = T1.PrjCode
                            INNER JOIN OOCR T2 ON T0.OcrCode = T2.OcrCode
                            INNER JOIN OWTR T3 ON T0.DocEntry = T3.DocEntry
                            INNER JOIN OWHS T4 ON T0.FromWhsCod = T4.WhsCode
                            INNER JOIN OWHS T5 ON T0.WhsCode = T5.WhsCode
                            WHERE T3.DocNum = '$code'");
      return response()->json([
          "recordsTotal" => count($query),
          "recordsFiltered" => count($query),
          "data" => $query
      ]);
    }
    
    public function filter(Request $request){
        try{
          $request->flash();
          $items = TransferTaking::join('users','users.id','=','transfersTaking.idUser')
                    ->join('SAPHOMOLOGACAO.dbo.OWHS as T1', 'T1.WhsCode', '=', 'transfersTaking.fromWarehouse')
                    ->join('SAPHOMOLOGACAO.dbo.OWHS as T2', 'T2.WhsCode', '=', 'transfersTaking.toWarehouse')
                    ->select('transfersTaking.id', 'users.name', 'codSAPTransf', 'codWEBTransf', 'status', 'docDate', 'taxDate', 'code', 
                    'codSAP', 'T1.WhsName as fromWarehouse', 'T2.WhsName as toWarehouse', 'comments', 'is_locked', 'message', 'transfersTaking.created_at');

          if (!is_null($request->codSAP)) {
              $items->where('transfersTaking.codSAPTransf','like', "%{$request->codSAP}%");
          }
          if (!is_null($request->codWEB)) {
              $items->where('code','like', "%{$request->codWEB}%");
          }
          if (!is_null($request->codWEBTransf)) {
            $items->where('codWEBTransf','like', "%{$request->codWEBTransf}%");
        }
          if (!is_null($request->name)) {
              $items->where('users.id','=', "{$request->name}");
          }
          if ((!is_null($request->warehouse)) ) {
              $items->where('warehouse', $request->warehouse);
          }
          if ((!is_null($request->data_fist)) ) {
              $items->where('TaxDate', '>=', $request->data_fist);
          }
          if ((!is_null($request->data_last)) ) {
              $items->where('TaxDate', '<=', $request->data_last);
          }
          if ((!is_null($request->status)) && ($request->status > 0)) {
              switch ($request->status) {
                case 1:
                  $items->where('is_locked', 0);
                  $items->whereNull('message');
                  break;
                case 2:
                  $items->where('is_locked', 0)->whereNotNull('message');
                  break;
                case 3:
                  $items->where('is_locked', 1);
                  break;
                case 4:
                  $items->where(['dbUpdate' => 1, 'is_locked' => 0]);
                  break;
                case 5:
                  $items->where('status', '1');
                  break;
                case 6:
                  $items->where('status', '2');
                  break;
                case 7:
                  $items->where('status', '3');
                  break;
               
                default:
                  die('Houve um erro durante o processo, atualize a pagina e tente novamente!');
                  break;
              }
          }

          $items = $items->orderBy('transfersTaking.id', 'desc')->paginate(30)->appends(request()->query());
          
          return view('inventory::transferTaking.index', compact('items'));
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('E0008', 'Listando o adiantamentos ao fornecedor',$e->getMessage());
           return view('inventory::transferTaking.index')->withErrors($e->getMessage());
        }
    }

    public function edit($id){
      $head = DB::SELECT("SELECT T0.id,T1.name,T0.codSAPTransf,T0.status,T0.codWEBTransf,T0.docDate,T0.idUser,T0.taxDate,T0.code,T0.codSAP,
        T0.fromWarehouse,T0.toWarehouse,T0.comments,T0.is_locked,T0.message, T0.idTransf,
        CASE 
          WHEN T0.status = '1' THEN 'PARCIAL'
          WHEN T0.status = '2' THEN 'RECEBIDO'
          WHEN T0.status = '3' THEN 'CANCELADO'
          WHEN T0.is_locked ='0' AND T0.message is null THEN 'AGUARDANDO'
          WHEN T0.dbUpdate = '1' AND T0.is_locked ='0' THEN 'ATUALIZANDO'
          WHEN T0.is_locked ='0' AND T0.message is not null THEN 'SINCRONIZADO'
          WHEN T0.is_locked = '1' THEN 'ERROR'
        END  AS docStatus
        FROM transfersTaking as T0 
        JOIN users T1 on T0.idUser = T1.id
        WHERE T0.id = '{$id}'")[0];
      
      $body =  DB::SELECT("SELECT * from transferTaking_items WHERE transferTaking_items.idTransferTaking = '{$id}'");
      $transfers = Transfer::select('id', 'code', 'codSAP')
                    ->where('idTransferTaking', $head->id)->get();
      $upload = Upload::where('reference','transfersTaking')->where('idReference',$id)->get();
      // dd(Upload::where('reference','transfersTaking')->get());
      // Alertas::checkAlerts($head->id);// atualiza o status dos alertas pertencentes ao documento para verificado.

      return view("inventory::transferTaking.edit", array_merge($this->getOption(),['head'=>$head,'body'=>$this->geDiscribre($body,$head->fromWarehouse),'upload' => $upload, 'transfers'=>$transfers]));
    }

    public function updateUploads(Request $request){
      saveUpload($request, $request->table, $request->id);
      // $transferTaking = TransferTaking::find($request->id);
      // $transferTaking->updateUpload();
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
            $transfer = TransferTaking::find($idReference);
            DB::commit();
           
            return redirect()->route('inventory.transferTaking.edit', $transfer->id)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }





    public function geDiscribre($body,$whs){
      $new = [];
      foreach ($body as $key => $value) {

        $sap = new Company(false);

        $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];

        $sap = new Company(false);

        $itemName = $sap->query("SELECT ItemName From OITM WHERE OITM.ItemCode = '{$value->itemCode}'")[0]['ItemName'];
        $qtdEstoque = $sap->getDb()->table("OITW")
        ->join("OWHS", "OITW.WhsCode", "=", "OWHS.WhsCode")
        ->orWhere("OITW.ItemCode", "=", "{$value->itemCode}")
        ->where("OWHS.WhsCode","{$whs}")->get()->first();

        if(!is_null($qtdEstoque))
          $qtdEstoque = $qtdEstoque->OnHand;
        

        $new[] = [
          'id' => $value->id,
          'idTransferTaking' => $value->idTransferTaking,
          'itemCode' => $value->itemCode,
          'quantity' => $value->quantity,
          'quantityRequest' => $value->quantityRequest,
          'quantityServed' => $value->quantityServed,
          'quantityPending' => $value->quantityPending,
          'projectCode' => $value->projectCode,
          'distributionRule' => $value->distributionRule,
          'costCenter' => $value->costCenter,
          'costCenter2' => $value->costCenter2,
          'itemName' => $itemName,
          'qtdEstoque' => $qtdEstoque,
          'itemUnd' => ((!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom']) ),
        ];

      }
     
      return $new;
    }
    private function getOption(){
      $sap = new Company(false);
      #$centroCusto = $sap->query("SELECT A.PrcCode as code, A.PrcName as value FROM OPRC A WHERE A.Active = 'Y'");
      $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
      $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
      $projeto = $sap->query("select OPRJ.PrjCode as code, OPRJ.PrjName as value from OPRJ where OPRJ.Active = 'Y'");
      $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
      $role = $sap->query("SELECT OOCR.OcrCode as code, OOCR.OcrName as value FROM oocr");
      // dd($centroCusto);
      return compact('centroCusto','projeto','warehouses','role','centroCusto','centroCusto2');
    }

    public function store(Request $request){

      try {
        DB::beginTransaction();
        $sap = new Company(false);
        $check = false;
        
        //Numero de itens do pedido
        $n_itens = count($request->items);
        //Array que sinaliza pendencia ou não de acordo com a quantidade de itens
        $t = [];
        //Array que verifica se alguma entrega esta sendo realizada
        $s = [];
        //Contador 
        $i = 0;

        foreach ($request->items as $key => $value) {
          $i++;
          $item = Item::find($key);
          /*
          if(!empty($old_item)){
            Item::find($key)->delete();
          }
          */
          //$item = new Item();
          //$item->idTransferTaking = $request->idTransferTaking;
          //$item->itemCode = $value['itemCode'];
          //$item->quantity = $value['quantityRequest'];
          $item->quantityRequest = clearNumberDouble($value['quantityRequest']);
          // Soma o valor servido
          $item->quantityServed = isset($value['quantityServed']) ? clearNumberDouble($value['quantityServed']) : 0;
          $item->quantityTransfer = ( $item->quantityServed > 0) ? $item->quantityServed : 0 ;
          // Salva o novo valor pendente
          if(is_numeric($item->quantityPending)){
            $item->quantityPending = (Double)number_format($item->quantityPending - $item->quantityServed, 4, '.', '');
          } else {
            $item->quantityPending = clearNumberDouble($item->quantityPending) - $item->quantityServed;
          }

          //$item->quantityPending = $item->quantityPending - $item->quantityServed;
          //dd($item);
          // Se existir alguma pendencia, sinaliza no array que existe = 0, sendo verificado mais tarde
          
          if($item->quantityPending == '0'){
            $t[$i] = true;
          }else{
            $t[$i] = false;
          }
          if($item->quantityTransfer > 0){
            $s[$i] = false;
          }else{
            $s[$i] = true;
          }

          $item->projectCode = isset($value['projectCode']) ? $value['projectCode'] : $item->projectCode ;
          $item->distributionRule = isset($value['centroCusto']) ? $value['centroCusto'] : $item->distributionRule;
          $item->costCenter = isset($value['centroCusto']) ? $value['centroCusto'] : $item->costCenter;
          $item->costCenter2 = isset($value['centroCusto2']) ? $value['centroCusto2'] : $item->costCenter2;
         
          $item->save();
          //dd($item);
        
          $trans = TransferTaking::find($value['id']);
          $trans->toWarehouse = isset($request->toWarehouse) ? $request->toWarehouse : $trans->toWarehouse ;
          $trans->fromWarehouse = isset($request->fromWarehouse) ? $request->fromWarehouse : $trans->fromWarehouse ;
         
          $trans->comments = mb_convert_encoding($request->comments, 'UTF-8');
          $trans->dbUpdate = true;
          $trans->is_locked = true;
          $trans->save();
          #$trans->saveInSAP($trans);
          
       
          //ultimo loop, usuário precisa ser atendente de transferencia e ter acesso ao modulo de transferencia
          if($i == $n_itens && checkAccess('inventory_transfer') && auth()->user()->tipoTransf == "A" && array_search(false,$s)){
        
            //Se já existir uma transferência pro pedido, atualiza a transferência 
        
            //  if($trans->idTransf != null){
            //    $transfer = Transfer::find($trans->idTransf);

            //    //Atualiza itens da transferência
               
            //    foreach ($request->items as $chave => $valor) {
            //      $item_transfer = ItemTransfer::where('id_transfer_taking_item',$chave)->first();
            //      if(empty($item_transfer)){
            //       dd('ad', $item_transfer, $valor, $chave );
            //      }
            //      $item_transfer->quantity = $item_transfer->quantity + clearNumberDouble($valor['quantityServed']);
            //      $item_transfer->save();
            //     }
            //     dd($item_transfer);
              
            //      //Se existir pendencia ainda
            //    if(array_search(false,$t)){
            //      $transfer->status = '1';
            //      $transfer->save();
            //      $trans->status = '1';
            //      $trans->save();
            //      //Status = Parcial / Aberto
            //    }else{
            //      //Status = Fechado
            //      $transfer->status = '2';
            //      $transfer->save();
            //      $trans->status = '2';
            //      $trans->save();
            //      TransferTakingToSAP::dispatch($transfer,$trans);
            //      //$obj = new Transfer();
            //      //$obj->saveInSAP($transfer,$trans);
            //    }
    
            //  //Se não existir tranferência, cria uma e atualiza o pedido com o codigo da transferência
            //  }else{
            $transfer = new Transfer();
            $transfer->saveFromTransferTaking($trans);

            if($transfer){

              if(array_search(false,$t)){
                //Status = Parcial / Aberto
                $transfer->status = '1';
                $transfer->save();
                $trans->status = '1';
                $trans->save();
              }else{
                //Status = Fechado
                $transfer->status = '2';
                $transfer->save();
                $trans->status = '2';
                $trans->save();
              }
              //dd(!empty($trans->codWEBTransf) ? $trans->codWEBTransf.', '. $transfer->code : $transfer->code);
              $trans->codWEBTransf = !empty($trans->codWEBTransf) ? $trans->codWEBTransf.', '. $transfer->code : $transfer->code;
              $trans->idTransf = $transfer->id;
              $trans->message = "Transferência gerada. cod WEB: ".$transfer->code ;
              $trans->save();

              TransferTakingToSAP::dispatch($transfer,$trans);
            }
             //}
          
          }
         
        }
        saveUpload($request,'transfersTaking',$trans->id);

        DB::commit();
        return redirect()->back()->withSuccess('Operação realizada com sucesso!');

      } catch (\Exception $e) {
        DB::rollback();
        $LogsError = new LogsError();
        $LogsError->saveInDB('E0102', 'Atualização de Pedido Transferencia de Estoque',$e->getMessage());
        return redirect()->back()->withErrors($e->getMessage());
      }

    }

    public function goTransfer(Request $request){
      try{
        
        $transferTaking = TransferTaking::find($request->idTransferTaking);
        
        $transfer = new Transfer();
        $transfer->saveFromTransferTaking($transferTaking);
        // dd($transfer);
        $transferTaking->codWEBTransf = $transfer->code;
        $transferTaking->message = "Transferência gerada. cod WEB: ".$transfer->code ;
        $transferTaking->save();

        TransferTakingToSAP::dispatch($transfer,$transferTaking);
     
        return redirect()->route('inventory.transfer.index')->withSuccess('Salvo com sucesso');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.transfer.index')->withErrors($e->getMessage());
      }

    }

    public function print($id){
      $sap =  new Company(false);
      $head = TransferTaking::find($id);
      $user = DB::SELECT("SELECT name from users WHERE id = '{$head->idUser}'")[0];
      $body = $this->getItemName(DB::SELECT("SELECT * FROM transferTaking_items WHERE idTransferTaking = '{$head->id}'"));
      // $company =  DB::SELECT('SELECT TOP 1 id,company,cnpj,address,number,neighborhood,cep,city,telephone,telephone2,email FROM companies order by id desc');
      // $idCompany = $company[0]->id;
      // $img = DB::SELECT("SELECT TOP 1 reference,idReference,diretory FROM uploads WHERE reference like 'companies' and idReference = '$idCompany' order by id desc");
      // return  \PDF1::setOptions(['uplouds'=>true ])->loadView('relatory.layouts.transfer',compact('user','head','body', 'img', 'company','partner'))->setPaper('a4','portrait')->stream('pdf.pdf');
      return  \PDF1::setOptions(['uplouds'=>true ])->loadView('relatory.layouts.transferTaking',compact('user','head','body'))->setPaper('a4','portrait')->stream('pdf.pdf');
    }

    private function getItemName($array){
      $newArray = [];
      foreach ($array as $key => $value) {
        $sap = new Company(false);
         $newArray[] = [
          'id'=> $value->id,
          'itemCode'=> $value->itemCode,
          'itemName'=> $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
          'quantity'=> $value->quantity,
          'projectCode'=> $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->projectCode}'")[0]['PrjName'],
          'distributionRule'=> $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->distributionRule}'")[0]['OcrName']
        ];
      }

      return $newArray;

    }

    public function report(Request $request){
      $sap = new Company(false);
      $departamento = $sap->getDB()->table('OUDP')->select('Code as value','Remarks as name')->get();
      $requests = new Requests();
      $atendente = User::where('tipo','=','A')->get();
      $solicitante = User::where('tipo','=','S')->get();
      $depositos = $sap->query("select WhsCode as code, WhsName as value from OWHS");
      return view("inventory::transferTaking.report",compact('departamento','requests', 'atendente', 'solicitante','depositos'));
  }

  public function reportGenerate(Request $request){

    $data = [
      'code' => $request->code ?? 'NULL',
      'docStatus' => $request->status ?? 'NULL',
      'initialDate' => $request->data_ini ?? 'NULL',
      'lastDate' => $request->data_fim ?? 'NULL',
      'idUser' => $request->name ?? 'NULL', 
      'whsOrigem' =>  $request->deposito_origem ?? 'NULL',
      'whsDestino' =>  $request->deposito_dest ?? 'NULL',
    ];


    if($request->tipo == 1){
      $report = new JasperReport();
      $relatory_model = storage_path('app/public/relatorios_modelos')."/TransferTaking-sintetico.jasper";
      $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'TransferTaking';
      $output = public_path('/relatorios'.'/'.$file_name);

      if(!file_exists($relatory_model)){
          $relatory_model = storage_path('app/public/relatorios_modelos')."/TransferTaking-sintetico.jrxml";
      }

      $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
      return response()->file($report)->deleteFileAfterSend(true);
      
    }else if($request->tipo == 2){
      $report = new JasperReport();
      $relatory_model = storage_path('app/public/relatorios_modelos')."/TransferTaking-analitico.jasper";
      $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'TransferTaking';
      $output = public_path('/relatorios'.'/'.$file_name);

      if(!file_exists($relatory_model)){
          $relatory_model = storage_path('app/public/relatorios_modelos')."/TransferTaking-analitico.jrxml";
      }

      $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
      return response()->file($report)->deleteFileAfterSend(true);
    }
  }
}
