<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\LogsError;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\StockLoan\StockLoan;
use App\Modules\Inventory\Models\StockLoan\Item;
use App\Modules\Inventory\Models\StockLoan\Historic;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Modules\Inventory\Models\Requisicao\Requests;

use Illuminate\Support\Facades\DB;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
#use App\Jobs\Set\TrasnferWhs;
use App\JasperReport;
use App\Modules\Inventory\Jobs\StockLoanToSAP;
use App\User;

class StockLoanController extends Controller{

    public function index(){

      $items = StockLoan::select('stock_loans.id', 'users.name','status','docDate','parcial','devolution',
                  'id_stockLoan','requester','returner','taxDate','code','codSAP',
                  'T1.WhsName as fromWarehouse', 'T2.WhsName as toWarehouse', 'comments','is_locked','message')
              ->join('users', 'users.id', '=', 'stock_loans.idUser')
              ->join('SAPHOMOLOGACAO.dbo.OWHS as T1', 'T1.WhsCode', '=', 'stock_loans.fromWarehouse')
              ->join('SAPHOMOLOGACAO.dbo.OWHS as T2', 'T2.WhsCode', '=', 'stock_loans.toWarehouse')
              ->orderBy('stock_loans.id', 'desc')
              ->paginate(30);

      return view("inventory::stockLoan.index", compact('items'));
    }
    public function create(Request $request){
      return view("inventory::stockLoan.create", $this->getOption());
    }
    public function check(Request $request){
      Session::put('toWarehouse',$request->warehouse);
      Session::put('fomWarehouse',$request->fromWarehouse);

      $sap = new Company(false);
      $centroCusto = $sap->query("SELECT A.PrcCode as code, A.PrcName as value FROM OPRC A WHERE A.Active = 'Y'");
      $projeto = $sap->query("select OPRJ.PrjCode as code, OPRJ.PrjName as value from OPRJ where OPRJ.Active = 'Y'");
      $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
      $rule = $sap->query("SELECT OOCR.OcrCode as code, OOCR.OcrName as value FROM oocr");
      

      return view("inventory::stockLoan.create", ['dt'=> $request->data,'whs'=>$request->fromWarehouse,'wh'=>$request->warehouse,'check'=>true,'role'=>$rule,'projeto'=> $projeto,'warehouses'=> $warehouses, 'centroCusto' => $centroCusto]);
    }
    public function save(Request $request){
      try {
        
        DB::beginTransaction();
          $stockLoan = new StockLoan();
          $stockLoan->saveInDB($request);
          saveUpload($request,'stockLoans',$stockLoan->id);
        DB::commit();

        return redirect()->route('inventory.stockloan.edit', $stockLoan->id)->withSuccess('Salvo com sucesso');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.stockloan.index')->withErrors($e->getMessage());
      }

    }
    
    public function devolution(Request $request){
      try {
        DB::beginTransaction();
        
        $stockLoan = new StockLoan();
        $stockLoan->devolutionInDB($request);
        
        //saveUpload(new Upload, $request,'trasnfers',$stockLoan->id);
        
        DB::commit();
        
        #$stockLoan->saveInSAP($stockLoan);
        $stockLoan = StockLoan::find($request->stockLoan_id);
        StockLoanToSAP::dispatch($stockLoan);

        return redirect()->route('inventory.stockloan.index')->withSuccess('Salvo com sucesso');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        return redirect()->route('inventory.stockloan.index')->withErrors($e->getMessage());
      }

    }
    public function search(){
      return view('inventory::stockLoan.search');
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
    public function filter(Request $request)
  {
      try {
          $request->flash();

          $items = StockLoan::select('stock_loans.id', 'users.name as user','stock_loans.status','docDate','parcial','devolution',
              'id_stockLoan','requester.name as requester','returner','taxDate','stock_loans.code','codSAP',
              'fromWarehouse','toWarehouse','comments','is_locked','message')
              ->leftJoin('users', 'users.id', '=', 'stock_loans.idUser')
              ->leftJoin('users as requester', 'requester.userClerk', '=', 'stock_loans.requester');

          if (!is_null($request->codSAP)) {
              $items->where('codSAP', 'like', "%$request->codSAP%");
          }
          if (!is_null($request->codWEB)) {
              $items->where('code', 'like', "%$request->codWEB%");
          }
          if (!is_null($request->name)) {
            $items->where('users.id', '=', "$request->name");
        }
        if (!is_null($request->nameRequester)) {
          $items->where('requester.id', '=', "$request->nameRequester");
        }
        if ((!is_null($request->data_fist))) {
          $items->where('taxDate', '>=', "%$request->data_fist%");
        }
        if ((!is_null($request->data_last))) {
          $items->where('taxDate', '<=', "%$request->data_last%");
        }
        if((!is_null($request->status))){
          $items->where('status', '=', "$request->status");
        }
        
        $items = $items->orderBy('stock_loans.id', 'desc')->paginate(30)->appends(request()->query());

          return view('inventory::stockLoan.index', compact('items'));
      } catch (\Throwable $e) {
          $logsErrors = new LogsError();
          $logsErrors->saveInDB('E0008', 'Listando o adiantamentos ao fornecedor', $e->getMessage());
          return view('inventory::stockLoan.index')->withErrors($e->getMessage());
      }
  }

    public function edit($id){
      $head = DB::SELECT("SELECT T0.id,T1.name,T0.status,T0.parcial,T0.docDate,T0.taxDate,T0.devolution,T0.devolved,T0.requester,T0.returner,T0.code,T0.codSAP,T0.fromWarehouse,T0.toWarehouse,T0.comments,T0.is_locked,T0.message FROM stock_loans as T0 JOIN users T1 on T0.idUser = T1.id where T0.id = :id", ['id' => $id])[0];
      $body =  DB::SELECT("SELECT * from stock_loans_items WHERE stock_loans_items.idStockLoan = :id", ['id' => $id]);
      
      $historic = DB::SELECT("SELECT T0.*, T1.itemCode, T3.ItemName
                              FROM stock_loans_historics T0
                              INNER JOIN stock_loans_items T1 ON T1.id = T0.idItem
                              INNER JOIN [SAPHOMOLOGACAO].[dbo].[OITM] T3 ON T3.ItemCode = T1.itemCode
                              WHERE T0.idStockLoan = :id
                              ORDER BY T0.created_at", ['id' => $id]);
    
      //$upload = Upload::where('reference','stockLoans')->where('idReference',$id)->get();
      $stockLoan = new StockLoan;
      return view("inventory::stockLoan.edit", array_merge($this->getOption(),[
        'head'=>$head,
        'body'=>$body, 
        'historic' => $historic, 
        'stockLoan' => $stockLoan]));
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
            $stockloan = StockLoan::find($idReference);
            DB::commit();
           
            return redirect()->route('inventory.stockloan.edit', $stockloan->id)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
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
          'idStockLoan' => $value->idStockLoan,
          'itemCode' => $value->itemCode,
          'quantity' => $value->quantity,
          'quantityPending' => $value->quantityPending,
          'quantityDevolved' => $value->quantityDevolved,
          'projectCode' => $value->projectCode,
          #'distributionRule' => $value->distributionRule,
          'costCenter' => $value->costCenter,
          'costCenter2' => $value->costCenter2,
          'itemName' => $itemName,
          'parcial' => $value->parcial,
          'itemUnd' => ((!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom']) ) ,

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
      $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);

      return compact('centroCusto','projeto','warehouses','role','centroCusto','centroCusto2','requesters');
    }


    public function store(Request $request){
      try {
        DB::beginTransaction();
          $trans = StockLoan::find($request->id);
          $trans->dbUpdate = true;
          $trans->fromWarehouse = $request->fromWarehouse;
          $trans->toWarehouse = $request->toWarehouse;
          $trans->devolved = $request->transType == 2 ? 1 : 0; // devolucao
          $trans->returner = $request->transType == 1 ? 1 : 0; // recebimento

          $pending = false;
          $closed_quantities = 0;
          foreach ($request->items as $key => $value) {
            $item = Item::find($key);

            if(abs(((Double)$item->quantityPending - clearNumberDouble($value['quantityPending']))) > 0 || abs(((Double)$item->quantityDevolved - clearNumberDouble($value['quantityDevolved']))) > 0){
              $historic = new Historic;
              $value['idItem'] = $key;
              $historic->saveInDB($value, $request);
            }

            $item->quantityPending = clearNumberDouble($value['quantityPending']);
            $item->quantityDevolved = clearNumberDouble($value['quantityDevolved']);

            if($item->quantityPending != $item->quantity || $item->quantityDevolved != $item->quantity){
              $pending = true;
            }elseif($item->quantityPending == $item->quantity && $item->quantityDevolved == $item->quantity){
              $closed_quantities++;
            }

            $item->save();
          }

          if($pending){
            $trans->status = 3;
          }elseif($closed_quantities > 0){
            if(count(Item::where('idStockLoan', $request->id)->get()) == $closed_quantities){
              $trans->status = 4;
            }
          }

          $trans->save();

          saveUpload($request,'stockLoans',$trans->id);
        DB::commit();
        StockLoanToSAP::dispatch($trans);
        return redirect()->back()->withSuccess('Operação realizada com sucesso!');
      } catch (\Exception $e) {
        DB::rollback();
        $LogsError = new LogsError();
        $LogsError->saveInDB('E0102', 'Atualização de Emprestimo de Estoque',$e->getMessage());
        return redirect()->back()->withErrors($e->getMessage());
      }

    }

    public function print($id){
      try {

          $report = new JasperReport();
          $relatory_model = storage_path('app/public/relatorios_modelos')."/StockLoan.jasper";
          $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'emprestimo_de_ferramentas';
          $output = public_path('/relatorios'.'/'.$file_name);

          if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/StockLoan.jrxml";
          }

          $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id'=>$id], 'pt_BR', 'r2w');
          return response()->file($report)->deleteFileAfterSend(true);


      } catch (\Exception $e) {
          $logsError = new logsError();
          $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
          return redirect()->back()->withErrors($e->getMessage());
      }
    }

    public function reportGenerate(Request $request){
      ini_set('max_execution_time', 0);
      
      try{
        $sql = "SELECT TOP 50 T0.id,
                T1.name,
                T0.docDate,
                T0.nameDevolved,
                T0.nameRequester,
                T0.id_stockLoan,
                T0.taxDate,
                T0.code,
                T0.requester,
                T0.returner,
                T0.codSAP,
                T0.fromWarehouse,
                T0.toWarehouse,
                T0.comments,
                T0.is_locked,
                T0.message, 
                T2.firstName as solicitante_firstName, 
                T2.middleName as solicitante_middleName,
                T2.lastName as solicitante_lastName,
                T3.firstName as devolvido_firstName, 
                T3.middleName as devolvido_middleName,
                T3.lastName as devolvido_lastName,
                CASE WHEN T0.devolution = '1'  THEN 'DEVOLVIDO'
                     WHEN T0.is_locked ='0' AND T0.devolution = '0' THEN 'SINCRONIZADO'
                END  AS docStatus
                FROM stock_loans as T0
                JOIN users T1 on T0.idUser = T1.id
                LEFT JOIN [SAPHOMOLOGACAO].[dbo].[OHEM] T2 on T0.requester = T2.empID
                LEFT JOIN [SAPHOMOLOGACAO].[dbo].[OHEM] T3 on T0.returner = T3.empID
                where T0.id != '-1'";
                
                if (!is_null($request->code)) {
                    $sql .= " and T0.code like '%{$request->code}%'";
                }
                if (!is_null($request->atendente)) {
                   $sql .= " and T1.name like '%{$request->atendente}%'";
                }
                if (!is_null($request->solicitante)) {
                    $sql .= " and T2.empID = {$request->solicitante}";
                }
                if (!is_null($request->devolvido)) {
                    $sql .= " and T3.empID = {$request->devolvido}";
                }
                if ((!is_null($request->data_ini))) {
                  $sql .= "and T0.TaxDate >= '{$request->data_ini}' ";
                }
                if ((!is_null($request->data_fim))) {
                  $sql .= "and T0.TaxDate <= '{$request->data_fim}' ";
                }
                if((!is_null($request->status)) && ($request->status > 0)){
                    switch ($request->status) {
                      
                      case 2:
                        $sql .= " and T0.is_locked ='0'";
                        $sql .= " and T0.devolution = '0'";
                        break;
                      
                      case 4:
                        $sql .= " and T0.devolution = '1' AND T0.is_locked ='0' ";
                        break;
                      default:
                        die('Houve um problema de edição de codigo HTML, por favor volte a pagina!');
                        break;
                    }
                }

        $sql.= " order by  T0.id desc ";
        $query = DB::select($sql);

        $tipo = $request->tipo;
        $requests = new Requests();

        $items = $this->getComplementarSAP($query );

        $body = [];
        $count = 0;

        foreach($items as $value){

          $body[$count] =  DB::SELECT("SELECT * from stock_loans_items WHERE stock_loans_items.idStockLoan = '{$value['id']}'");
          $body[$count] = $this->geDiscribre($body[$count]);
          $count++;
        }

        /** @var PdfWrapper $pdf */
        $pdf = SnappyPdf::loadView('inventory::stockLoan.reportview',compact('items', 'tipo', 'body', 'request'));
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-top', 10);
        return $pdf->inline('report.pdf');
        
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0008', 'Listando o adiantamentos ao fornecedor',$e->getMessage());
         return view('inventory::stockLoan.index')->withErrors($e->getMessage());
      }

  }

  private function getComplementarSAP($items){
    $newArray = [];

    try {
        foreach ($items as $key => $value) {
            $sap = new Company(false);

            //dd($value);
            
            $newArray[$key]['id'] = $value->id;
            $newArray[$key]['name'] = $value->name;
            $newArray[$key]['docDate'] = $value->docDate;
            $newArray[$key]['nameDevolved'] = $value->nameDevolved;
            $newArray[$key]['nameRequester'] = $value->nameRequester;
            $newArray[$key]['id_stockLoan'] = $value->id_stockLoan;
            $newArray[$key]['taxDate'] = $value->taxDate;
            $newArray[$key]['code'] = $value->code;
            $newArray[$key]['requester'] = $value->requester;
            $newArray[$key]['returner'] = $value->returner;
            $newArray[$key]['codSAP'] = $value->codSAP;
            $newArray[$key]['fromWarehouse'] = $value->fromWarehouse;
            $newArray[$key]['toWarehouse'] = $value->toWarehouse;
            $newArray[$key]['comments'] = $value->comments;
            $newArray[$key]['is_locked'] = $value->is_locked;
            $newArray[$key]['message'] = $value->message;
            $newArray[$key]['docStatus'] = $value->docStatus;
            $newArray[$key]['solicitante_name'] = $value->solicitante_firstName.' '.$value->solicitante_middleName.' '.$value->solicitante_lastName;
            $newArray[$key]['devolvido_name'] = $value->devolvido_firstName.' '.$value->devolvido_middleName.' '.$value->devolvido_lastName;

        }
        //dd($newArray);
    } catch (\Throwable $th) {
        $newArray = [];
    }

    return $newArray;
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

    public function report(){
      $sap = new Company(false);
      $departamento = $sap->getDB()->table('OUDP')->select('Code as value','Remarks as name')->get();
      $requests = new StockLoan();

      $sql = "SELECT TOP 50  T0.id,T1.name,
                T0.docDate,
                T0.nameDevolved,
                T0.nameRequester,
                T0.id_stockLoan,
                T0.taxDate,
                T0.code,
                T0.requester,
                T0.returner,
                T0.codSAP,
                T0.fromWarehouse,
                T0.toWarehouse,
                T0.comments,
                T0.is_locked,
                T0.message, 
                T2.firstName as solicitante_firstName, 
                T2.middleName as solicitante_middleName,
                T2.lastName as solicitante_lastName,
                T3.firstName as devolvido_firstName, 
                T3.middleName as devolvido_middleName,
                T3.lastName as devolvido_lastName,
                CASE WHEN T0.devolution = '1'  THEN 'DEVOLVIDO'
                     WHEN T0.is_locked ='0' AND T0.devolution = '0' THEN 'SINCRONIZADO'
                END  AS docStatus
                FROM stock_loans as T0
                JOIN users T1 on T0.idUser = T1.id
                LEFT JOIN [SAPHOMOLOGACAO].[dbo].[OHEM] T2 on T0.requester = T2.empID
                LEFT JOIN [SAPHOMOLOGACAO].[dbo].[OHEM] T3 on T0.returner = T3.empID
                where T0.id != '-1'";

        $sql.= " order by solicitante_firstName ";
        $emprestimos = DB::select($sql);

        $devolvidos = collect($emprestimos)->sortBy('devolvido_firstName')->groupBy('returner');
        $solicitantes = collect($emprestimos)->sortBy('solicitante_firstName')->groupBy('requester');
        $atendentes = collect($emprestimos)->sortBy('name')->groupBy('name');
      
      return view("inventory::stockLoan.report", 
      compact(
        'departamento',
        'requests',
        'atendentes', 
        'solicitantes',
        'devolvidos',
      ));
  }

  public function updateUploads(Request $request){
    saveUpload($request, $request->table, $request->id);
    // $stockLoan = StockLoan::find($request->id);
    // $stockLoan->updateUpload();

  }
}
