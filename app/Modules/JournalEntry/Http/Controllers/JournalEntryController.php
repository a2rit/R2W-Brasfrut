<?php

namespace App\Modules\JournalEntry\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Modules\JournalEntry\Models\JournalEntry;
use App\Modules\JournalEntry\Models\JournalEntry\Item;
use App\SapUtilities;
use Carbon\Carbon;
use App\CFItems;
use App\LogsError;
use Illuminate\Http\Request;
use App\Modules\JournalEntry\Jobs\JournalEntryToSAP;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
#use App\Jobs\SetInSAPJE;

class JournalEntryController extends Controller
{
    use SapUtilities;
    
  /*  public function __construct(){
      $this->middleware(function ($request, $next){
          if(!checkAccess('jornal_entry')){
              return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
          }else{
              return $next($request);
          }
      });
    }*/
    public function index(){
      
      $items = JournalEntry::join('users', 'users.id', '=', 'journal_entries.idUser')
                ->select("journal_entries.id","users.name","codSAP","code","doc_date","due_date","posting_date","project","distribution_rule","is_locked",
                  (DB::raw("CASE
                              WHEN codStatus = '4' THEN 'CANCELADO'
                              WHEN is_locked ='0' AND message is null THEN 'AGUARDANDO'
                              WHEN dbUpdate = '1' AND is_locked ='0' THEN 'ATUALIZANDO'
                              WHEN is_locked ='0' AND message is not null THEN 'SINCRONIZADO'
                              WHEN is_locked = '1' THEN 'ERROR'
                            END  AS docStatus")))
                ->orderBy('id', 'desc')
                ->paginate(30);
      return view("journal-entry::index", compact('items'), $this->getOption());
    }
    public function search(){
      return view("journal-entry::search", $this->getOption());
    }
    public function reports(){
        return view("journal-entry::report");
    }
    protected function getOption(){
      $sap = new Company(false);
      $distributionRules = $this->getDistributionRulesOptions($sap);
      $projects = $this->getProjectOptions($sap);
      $accounts = $this->getAccountOptions($sap);
      $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
      $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
      $partner =  $sap->query("SELECT T0.CardCode as value, T0.CardName as name FROM OCRD as T0");
      $cashFlow = DB::SELECT("SELECT T0.id as value, T0.description as name from cash_flows as T0 WHERE T0.status = '1'");
      $types = [
          ['name' => 'Despesa', 'value' => 0],
          ['name' => 'Receita', 'value' => 1]
      ];
      return compact('distributionRules', 'projects','centroCusto','centroCusto2', 'accounts', 'partner', 'cashFlow', 'types');
    }
    public function filter(Request $request){
      try{
        $items = JournalEntry::join('users', 'users.id', '=', 'journal_entries.idUser')
                  ->select("journal_entries.id","users.name","codSAP","code","doc_date","due_date","posting_date","project","distribution_rule","is_locked",
                    (DB::raw("CASE
                                WHEN codStatus = '4' THEN 'CANCELADO'
                                WHEN is_locked ='0' AND message is null THEN 'AGUARDANDO'
                                WHEN dbUpdate = '1' AND is_locked ='0' THEN 'ATUALIZANDO'
                                WHEN is_locked ='0' AND message is not null THEN 'SINCRONIZADO'
                                WHEN is_locked = '1' THEN 'ERROR'
                              END  AS docStatus")));


        if (!is_null($request->codSAP)) {
          $items->where('codSAP', 'like', "%{$request->codSAP}%");
        }
        if (!is_null($request->codWEB)) {
          $items->where('code', 'like', "%{$request->codWEB}%");
        }
        if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
          $items->whereBetween('posting_date', [formatDateUSA($request->data_fist), formatDateUSA($request->data_last)]);
        }
        
        if (!is_null($request->project)) {
          $items->where('project', 'like', "%{$request->project}%");
        }
        if (!is_null($request->distribution_rule)) {
          $items->where('distribution_rule', 'like', "%{$request->distribution_rule}%");
        }
        //nome de usuário
        if (!is_null($request->name)) {
          $items->where('users.name', 'like', "%{$request->name}%");
        }
        if((!is_null($request->status)) && ($request->status > 0)){
            switch ($request->status) {
              case 2:
                $items->where("is_locked", "0")->where('message', '!=', null);
                break;
              case 4:
                $items->where("codStatus", "4");
                break;
              default:
                die('Não foi possivel concluir a operação, atualize a página e tente novamente.');
                break;
            }
        }
        $items = $items->orderBy('journal_entries.id', 'desc')->paginate(30)->appends(request()->query());
        return view("journal-entry::index", compact('items'), $this->getOption());
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0065', $e->getFile(). ' | '.$e->getLine(),$e->getMessage());
         return view('journal-entry::index', $this->getOption())->withErrors($e->getMessage());
      }
    }

    public function canceled($id)
    {
        try {
            DB::beginTransaction();
            $oJE = JournalEntry::find($id);
            $obj = new JournalEntry();
            $obj->cenceledInSAP($oJE);
            DB::commit();
            if ($oJE->is_locked) {
                return redirect()->route('journal-entry.index')->withErrors($oJE->message);
            } else {
                return redirect()->route('journal-entry.index')->withSuccess("Cancelado com sucesso!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }



    public function searchTable(Request $request){
      $sap = new Company(false);
      $query = $sap->getDb()->table("OJDT");
      $recordsTotal = $query->count();
      $query->offset($request->get("start"));
      $query->limit($request->get("length"));
      $columns = $request->get("columns");
      $columnsToSelect = ['TransId', 'RefDate','LocTotal','Memo'];

      $search = $request->get('search');
      if ($search['value']) {
          $query->orWhere("TransId", "like", "%{$search['value']}%")
                ->orWhere("RefDate", "like", "%{$search['value']}%")
               ->orWhere("LocTotal", "like", "%{$search['value']}%")
              ->orWhere("Memo", "like", "%{$search['value']}%");
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
    public function edit($id){
      $head = DB::SELECT("SELECT TOP 50 T0.id,T1.name,T0.idUser,T0.nameUser, T0.codStatus,T0.idCancel,T0.nameCancel,T0.codSAP,T0.code,T0.doc_date,T0.due_date,T0.posting_date,T0.comments,T0.coin,
                        T0.project,T0.distribution_rule,T0.is_locked,T0.message, T0.currencyValue,T0.type, T3.description as cashFlow
                        FROM journal_entries as T0
                        JOIN users T1 on T0.idUser = T1.id
                        LEFT JOIN cash_flow_items T2 on T0.id =  T2.idTransation and T2.transation = 'journal_entries'
                        LEFT JOIN cash_flows T3 on T2.idCashFlow = T3.id WHERE T0.id = '{$id}'")[0];

      $body =  DB::SELECT("SELECT * from journal_entry_items WHERE journal_entry_items.je_id = '{$id}'");

     
      
      
      return view("journal-entry::edit", array_merge($this->getOption(),['head'=>$head, 'body' =>$body]));
    }

    public function geDiscribre($body){
      $new = [];
      foreach ($body as $key => $value) {
        $sap = new Company(false);
        $itemName = $sap->query("SELECT ItemName From OITM WHERE OITM.ItemCode = '{$value->itemCode}'")[0]['ItemName'];
        $new[] = [
          'id' => $value->id,
          'idInputs' => $value->idInputs,
          'itemCode' => $value->itemCode,
          'quantity' => $value->quantity,
          'price' => $value->price,
          'projectCode' => $value->projectCode,
          'costingCode' => $value->costingCode,
          'costingCode2' => $value->costingCode2,
          'accountCode' => $value->accountCode,
          'itemName' => $itemName,
        ];
      }
      return $new;
    }
    public function create(){
        $sap = new Company(false);
        $distributionRules = $this->getDistributionRulesOptions($sap);
        $projects = $this->getProjectOptions($sap);
        $accounts = $this->getAccountOptions($sap);
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $partner =  $sap->query("SELECT T0.CardCode as value, T0.CardName as name FROM OCRD as T0");
        $types = [
            ['name' => 'Despesa', 'value' => 0],
            ['name' => 'Receita', 'value' => 1]
        ];
        return view("journal-entry::create", compact('distributionRules', 'projects','centroCusto','centroCusto2', 'accounts', 'partner', 'types'));
    }
    public function save(Request $request){
        try {
            DB::beginTransaction();
            $all = $request->all();
           
            $all['due_date'] = Carbon::createFromFormat("d/m/Y", $all['due_date']);
            $all['doc_date'] = Carbon::createFromFormat("d/m/Y", $all['doc_date']);
            $all['posting_date'] = Carbon::createFromFormat("d/m/Y", $all['posting_date']);
            $all['idUser'] = auth()->user()->id;
            $all['nameUser'] = auth()->user()->name;
           
            $all['code'] = $this->createCode();
            $all['distribution_rule'] = $request->costCenter;
            $all['costCenter'] = $request->costCenter;
            $all['costCenter2'] = $request->costCenter2;
            if(workCoin())
            $all['coin'] = $request->coin;
            $journalEntry = JournalEntry::create($all);
            $lines = $request->get('lines');
           
            if(workCashFlow()){
              $CFItems =  new CFItems(); //fluxo de caixa;
              $CFItems->saveInDB($request->cashFlow,$journalEntry->id,'journal_entries');
             
            }

            foreach ($lines as $line) {
              
                // remove line index=index and credit e debit = 0;
                if (!isset($line['credit']) && !isset($line['debit'])) {
                    if ((int)$line['credit'] === 0 && (int)$line['debit'] === 0) {
                        continue;
                    }
                }
                $line['je_id'] = $journalEntry->id;
                $line['distribution_rule'] = $line['costCenter'];
                JournalEntry\Item::create($line);
            }
            #$t = new JournalEntry();
            #$t->saveInSAP($journalEntry);
            
            DB::commit();
            JournalEntryToSAP::dispatch($journalEntry);

            if($journalEntry->is_locked){
              return redirect()->route('journal-entry.index')->withErrors($journalEntry->message);
            }else {
              return redirect()->route('journal-entry.index')->withSuccess("Lançamento adicionado com sucesso!");
            }
        } catch (\Exception $e) {
          DB::rollBack();
          $logsError = new LogsError();
          $logsError->saveInDB('E98FB', $e->getFile().' | '.$e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    private function createCode(){
          $busca = DB::select("select top 1 journal_entries.code from journal_entries order by journal_entries.id desc");
            $codigo = '';
            if (empty($busca) || is_null($busca) || $busca == '') {
                  $codigo = 'JE00001';
            } else {
                $codigo = $busca[0]->code;
                $codigo++;
            }
            return $codigo;
    }
    public function store(Request $request){
       try {
         $je = JournalEntry::find($request->id);
         $je->idUser = auth()->user()->id;
         $je->nameUser = auth()->user()->name;
         $je->doc_date = Carbon::createFromFormat("d/m/Y", ($request->doc_date));
         $je->due_date = Carbon::createFromFormat("d/m/Y", ($request->due_date));
         $je->posting_date = Carbon::createFromFormat("d/m/Y", ($request->posting_date));
         $je->comments = $request->comments;
        //  $je->project = $request->project;
        //  $je->distribution_rule = $request->distribution_rule;
         $je->is_locked = false;
         $je->dbUpdate = 1;
         $je->currencyValue = $request->currency;
         $je->save();
         
         foreach ($request->lines as $key => $value) {
            $item = Item::find($key);
            if(isset($value['account']) && !empty($value['account']))
            $item->account = $value['account'];
            if(isset($value['cardCode']) && !empty($value['cardCode']))
            $item->cardCode = $value['cardCode'];
            if(isset($value['credit']) && !empty($value['credit']))
            $item->credit = $value['credit'];
            if(isset($value['debit']) && !empty($value['debit']))
            $item->debit = $value['debit'];
            $item->project = $value['project'];
            // $item->distribution_rule = $value['distribution_rule'];
            $item->costCenter = $value['costCenter'];
            $item->costCenter2 = $value['costCenter2'];
            $item->save();
         }
         $obj = new JournalEntry();
         $obj->saveInSap($je);
         return redirect()->route('journal-entry.index')->withSuccess('Operação realizada com sucesso!');
       } catch (\Exception $e) {
         $LogsError = new LogsError();
         $LogsError->saveInDB('E0103', $e->getLine().' | '.$e->getFile(), $e->getMessage());
         return redirect()->route('journal-entry.index')->withErrors($e->getMessage());
       }


    }
}
