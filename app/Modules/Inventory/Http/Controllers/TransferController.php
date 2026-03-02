<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\LogsError;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\Transfer\Transfer;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Modules\Inventory\Models\Transfer\Item;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\User;

use Illuminate\Support\Facades\DB;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
#use App\Jobs\Set\TrasnferWhs;
use App\Modules\Inventory\Jobs\TransferToSAP;
use Barryvdh\Snappy\Facades\SnappyPdf;

use App\JasperReport;

class TransferController extends Controller{

  
    public function index(){

      $items = Transfer::join('users', 'users.id', '=', 'transfers.idUser')
                ->join('SAPHOMOLOGACAO.dbo.OWHS as T1', 'T1.WhsCode', '=', 'transfers.fromWarehouse')
                ->join('SAPHOMOLOGACAO.dbo.OWHS as T2', 'T2.WhsCode', '=', 'transfers.toWarehouse')
                ->select('transfers.id','users.name','docDate','taxDate','code','codSAP','T1.WhsName as fromWarehouse', 'T2.WhsName as toWarehouse','comments','is_locked','message', 'status')
                ->orderBy('transfers.id', 'desc')
                ->paginate(30);

      return view("inventory::transfer.index", compact('items'));
    }

    public function create(Request $request){
      return view("inventory::transfer.create", $this->getOption());
    }

    public function check(Request $request){
      Session::put('toWarehouse',$request->warehouse);
      Session::put('fomWarehouse',$request->fromWarehouse);

      $sap = new Company(false);
      $centroCusto = $sap->query("SELECT A.PrcCode as code, A.PrcName as value FROM OPRC A WHERE A.Active = 'Y'");
      $projeto = $sap->query("select OPRJ.PrjCode as code, OPRJ.PrjName as value from OPRJ where OPRJ.Active = 'Y'");
      $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
      $rule = $sap->query("SELECT OOCR.OcrCode as code, OOCR.OcrName as value FROM oocr");

      return view("inventory::transfer.create", ['dt'=> $request->data,'whs'=>$request->fromWarehouse,'wh'=>$request->warehouse,'check'=>true,'role'=>$rule,'projeto'=> $projeto,'warehouses'=> $warehouses, 'centroCusto' => $centroCusto]);
    }

    public function save(Request $request){
      try {
        DB::beginTransaction();
        $transfer = new Transfer();
        $transfer->saveInDB($request);
        saveUpload($request,'transfers',$transfer->id);
        DB::commit();
        #$transfer->saveInSAP($transfer);
        TransferToSAP::dispatch($transfer);
        return redirect()->route('inventory.transfer.edit', $transfer->id)->withSuccess('Salvo com sucesso');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.transfer.index')->withErrors($e->getMessage());
      }
    }
    
    public function search(){
      return view('inventory::transfer.search');
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

        $items = Transfer::join('users', 'users.id', '=', 'transfers.idUser')
                  ->join('SAPHOMOLOGACAO.dbo.OWHS as T1', 'T1.WhsCode', '=', 'transfers.fromWarehouse')
                  ->join('SAPHOMOLOGACAO.dbo.OWHS as T2', 'T2.WhsCode', '=', 'transfers.toWarehouse')
                  ->select('transfers.id','users.name','docDate','taxDate','code','codSAP','T1.WhsName as fromWarehouse', 'T2.WhsName as toWarehouse','comments','is_locked','message', 'status');
                  
        if (!is_null($request->codSAP)) {
          $items->where('codSAP', 'like', "%{$request->codSAP}%");
        }
        if (!is_null($request->codWEB)) {
          $items->where('code', 'like', "%{$request->codWEB}%");
        }
        if (!is_null($request->name)) {
          $items->where('users.id', '=', "{$request->name}");
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
              $items->where('is_locked', 0)->whereNull('message');
              break;
            case 2:
              $items->where('is_locked', 0)->whereNotNull('message');
              break;
            case 3:
              $items->where('is_locked', 1);
              break;
            case 4:
              $items->where('status', '4');
              break;
            case 5:
              $items->where('is_locked', 1)->whereNotNull('message');
              break;
            default:
              die('Houve um erro durante o processo, atualize a pagina e tente novamente!');
              break;
          }
        }
        $items = $items->orderBy('transfers.id', 'desc')->paginate(30)->appends(request()->query());
        return view('inventory::transfer.index', compact('items'));
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0008', 'Listando o adiantamentos ao fornecedor',$e->getMessage());
         return view('inventory::transfer.index')->withErrors($e->getMessage());
      }
  }
  
    public function edit($id){
      $head = DB::SELECT("SELECT T0.id,T1.name,T0.docDate,T0.taxDate,T0.code,T0.codSAP,T0.fromWarehouse,T0.toWarehouse,T0.comments,T0.is_locked,T0.message, T0.status FROM transfers as T0 JOIN users T1 on T0.idUser = T1.id where T0.id = '{$id}'")[0];
      $body =  DB::SELECT("SELECT * from transfer_items WHERE transfer_items.idTransfer = '{$id}'");
      $request = TransferTaking::where('codWEBTransf', $head->code)->first();
      $upload = Upload::where('reference','transfers')->where('idReference',$id)->get();
      return view("inventory::transfer.edit", array_merge($this->getOption(),['head'=>$head,'body'=>$this->geDiscribre($body),'upload' => $upload, 'request' => $request]));
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
            $transfer = Transfer::find($idReference);
            DB::commit();
           
            return redirect()->route('inventory.transfer.edit', $transfer->id)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function cancel($id){
      try {
        DB::beginTransaction();
        
        $transfer = Transfer::find((int)$id);
        if($transfer){
          $transfer->status = 4;
          $transfer->message = "Transferencia cancelada";
          $transfer->save();

          DB::commit();

          DB::beginTransaction();

          $transferTanking = TransferTaking::where("codWEBTransf", '=', $transfer->code)->first();
          if($transferTanking){
            $transferTanking->status = 3;
            $transferTanking->message = "Transferencia cancelada";
            $transferTanking->save();
          }

          DB::commit();
  
          return redirect()->route('inventory.transfer.edit', $transfer->id)->withSuccess("Transferência cancelada com sucesso!");
        }else{
          return redirect()->route('inventory.transfer.edit', $transfer->id)->withErrors("Não foi possivel concluir o processo, atualize a página e tente novamente!");
        }
        
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.transfer.edit', $transfer->id)->withErrors($e->getMessage());
      }
    }

    public function geDiscribre($body){
      $new = [];
      foreach ($body as $key => $value) {
        $sap = new Company(false);
        $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];
        $sap = new Company(false);
        $itemName = $sap->query("SELECT ItemName From OITM WHERE OITM.ItemCode = '{$value->itemCode}'")[0]['ItemName'];
        $new[] = [
          'id' => $value->id,
          'idTransfer' => $value->idTransfer,
          'itemCode' => $value->itemCode,
          'quantity' => $value->quantity,
          'projectCode' => $value->projectCode,
          'distributionRule' => $value->distributionRule,
          'costCenter' => $value->costCenter,
          'costCenter2' => $value->costCenter2,
          'itemName' => $itemName,
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

        $check = false;

        foreach ($request->items as $key => $value) {
          $item = Item::find($key);
          $item->idTransfer = $value['id'];
          $item->itemCode = $value['itemCode'];
          $item->quantity = $value['quantity'];
          $item->projectCode = $value['projectCode'];
          $item->distributionRule = $value['centroCusto'];
          $item->costCenter = $value['centroCusto'];
          $item->costCenter2 = $value['centroCusto2'];
          $item->save();
        
          $trans = Transfer::find($value['id']);
          $trans->toWarehouse = $request->toWarehouse;
          $trans->fromWarehouse = $request->fromWarehouse;
          $trans->taxDate = $request->taxDate;
          $trans->dbUpdate = true;
          $trans->is_locked = false;
          $trans->save();
          #$trans->saveInSAP($trans);

          if($check === false){
            saveUpload($request,'transfers',$trans->id);
          }
          
          TransferToSAP::dispatch($trans);
          return redirect()->route('inventory.transfer.edit', $trans->id)->withSuccess("Transferência atualizada com sucesso!");
        }
      } catch (\Exception $e) {
        $LogsError = new LogsError();
        $LogsError->saveInDB('E0102', 'Atualização de Transferencia de Estoque',$e->getMessage());
        return redirect()->back()->withErrors($e->getMessage());
      }

    }

    public function print($id){
      try {
        $transfer = Transfer::find($id);
        if(!empty($transfer)){
            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer.jasper";
            
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer.jrxml";
            }

            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'transfer';
            $output = public_path('/relatorios'.'/'.$file_name);
            $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id'=>$id], 'pt_BR', 'r2w');
            
            return response()->file($report)->deleteFileAfterSend(true);
        }
      } catch (\Throwable $e) {
          $logsError = new logsError();
          $logsError->saveInDB('E0013kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
          return redirect()->back()->withErrors($e->getMessage());
      }
    }

    private function getItemName($array){
      $newArray = [];
      
      foreach ($array as $key => $value) {
        $sap = new Company(false);
       
        $codProject = $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->projectCode}'");
        $codCost = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter}'");
        $codCost2 = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter2}'");
         $newArray[] = [
          'id'=> $value->id,
          'itemCode'=> $value->itemCode,
          'itemName'=> $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
          'quantity'=> $value->quantity,
          'projectCode' => (!empty($codProject) ? $codProject[0]['PrjName'] : ''),
          'distributionRule' => (!empty($codCost) ? $codCost[0]['OcrName'] : ''),
          'distributionRule2' => (!empty($codCost2) ? $codCost2[0]['OcrName'] : '')
          // 'distributionRule'=> $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->distributionRule}'")[0]['OcrName']
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
   
      return view("inventory::transfer.report",compact('departamento','requests', 'atendente', 'solicitante','depositos'));
  }

  public function reportGenerate(Request $request){

    $data = [
      'code' => $request->code ?? 'NULL',
      'idUser' => $request->name ?? 'NULL',
      'whsOrigem' => $request->deposito_origem ?? 'NULL',
      'whsDestino' => $request->deposito_dest ?? 'NULL',
      'initialDate' => $request->data_ini ?? '2015-01-01',
      'lastDate' => $request->data_fim ?? date('Y-m-d'),
      'docStatus' => $request->status ?? 'NULL',
    ];

    if($request->tipo == 2){

      $report = new JasperReport();
      $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer-Analitico.jasper";
      $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'inventory_transfer';
      $output = public_path('/relatorios'.'/'.$file_name);

      if(!file_exists($relatory_model)){
          $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer-Analitico.jrxml";
      }

      $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
      return response()->file($report)->deleteFileAfterSend(true);

    }else if($request->tipo == 1){
        
      $report = new JasperReport();
      $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer-Sintetico.jasper";
      $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'inventory_transfer';
      $output = public_path('/relatorios'.'/'.$file_name);

      if(!file_exists($relatory_model)){
          $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryTransfer-Sintetico.jrxml";
      }

      $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
      return response()->file($report)->deleteFileAfterSend(true);
    }
  }
}
