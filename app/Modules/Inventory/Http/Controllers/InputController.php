<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\LogsError;
use App\Jobs\SetInSAPInput;

use Illuminate\Validation\Rules\In;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\Input\Input;
use App\Modules\Inventory\Models\Input\Item;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
use App\SapUtilities;
use App\Modules\Inventory\Jobs\InputToSAP;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Litiano\Sap\NewCompany;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\User;

use App\JasperReport;

class InputController extends Controller
{

    use SapUtilities;


    public function index()
    {
        $items = Input::join('users','users.id','=','inputs.idUser')->select('inputs.*', 'users.name')->orderBy('inputs.id', 'desc')->paginate(30);
        return view("inventory::input.index", compact('items'));
    }

    public function create()
    {
        return view("inventory::input.create", $this->getOption());
    }

    private function getOption()
    {
        $sap = new Company(false);
        
        //$centroCusto = $this->getCostCenterOptions($sap);
        $projeto = $this->getProjectOptions($sap);
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $warehouses = $this->getWHSOptions($sap);
        $role = $this->getDistributionRulesOptions($sap);
        $acct = $this->getAccountOptions($sap);
        
        return compact('centroCusto', 'projeto', 'warehouses', 'role', 'acct','centroCusto2');

    }

    public function check(Request $request)
    {
        Session::put('warehouse', $request->warehouse);
        return view("inventory::input.create", array_merge(['dt' => $request->data, 'wh' => $request->warehouse, 'check' => true], $this->getOption()));

    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
                $input = new Input();
                $input->saveInDB($request);

                saveUpload($request,'inputs',$input->id);
            DB::commit();

            InputToSAP::dispatch($input);

            if ($input->is_locked) {
                return redirect()->route('inventory.input.index')->withErrors($input->message);
            } else {
                return redirect()->route('inventory.input.edit', $input->id)->withSuccess('Salvo com sucesso!');
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('E0024', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('inventory.input.index')->withErrors($e->getMessage());
        }
    }

    private function saveItensInput($request, $id, $code)
    {
        $save = true;
        if (isset($request->requiredProducts)) {
            try {
                $sap = NewCompany::getInstance()->getCompany();
                $item = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenEntry);
                $item->DocDate = DATE('d-m-Y h:i:s');
                $item->TaxDate = $request->data;
                $item->Comments = is_null($request->obsevacoes) ? '' : $request->obsevacoes;
                $item->UserFields->fields->Item("U_R2W_CODE")->value = $code;
                $item->UserFields->fields->Item("U_R2W_USERNAME")->value = auth()->user()->name;

                foreach ($request->requiredProducts as $key => $value) {
                    $in = new ItemsInput();
                    $in->idInputs = $id;
                    $in->codSap = $value['codSAP'];
                    $in->qtd = $value['qtd'];
                    $in->acctCode = $value['conta'];
                    $in->save();

                    $item->Lines->ItemCode = $value['codSAP'];
                    $item->Lines->Quantity = $value['qtd'];
                    $item->Lines->Price = $value['price'];
                    $item->Lines->ProjectCode = $value['projeto'];
                    $item->Lines->CostingCode = $value['role'];
                    $item->Lines->AccountCode = $value['conta'];
                    $item->Lines->Add();
                }

                if ($item->Add() !== 0) {
                    return ['type' => 'error', 'messager' => $sap->GetLastErrorDescription()];
                } else {
                    return ['type' => 'succes'];
                }
            } catch (\Throwable $e) {
                return ['type' => 'error', 'messager' => $e->getMessage()];
            }
        }
        return $save;
    }

    public function search()
    {
        $mult = DB::select("SELECT T0.value FROM settings T0 WHERE T0.code = 'mlt-branch'");
        return view('inventory::input.search', ['mult' => $mult]);
    }

    public function edit($id)
    {
        $head = Input::find($id);
        $body = Input::join('input_items', 'input_items.idInputs', '=', 'inputs.id')
            ->where('input_items.idInputs', '=', $id)->select('input_items.*')->get();
            
        $upload = Upload::where('reference','inputs')->where('idReference',$id)->get();

        return view("inventory::input.edit", array_merge($this->getOption(), ['head' => $head, 'obj' => new Input(), 'body' => $this->geDiscribre($body) , 'upload' => $upload]));
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
            $input = Input::find($idReference);
            DB::commit();
           
            return redirect()->route('inventory.input.edit', $input->id)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function geDiscribre($body)
    {
        $new = [];
        foreach ($body as $key => $value) {

            $sap = new Company(false);

            $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];

            
            $sap = new Company(false);
            $itemName = $sap->getDb()->table('OITM')->where('ItemCode', $value->itemCode)->first()->ItemName;
            $new[] = [
                'id' => $value->id,
                'idInputs' => $value->idInputs,
                'itemCode' => $value->itemCode,
                'quantity' => str_replace(',','.',$value->quantity),
                'price' => str_replace(',','.',$value->price),
                'projectCode' => $value->projectCode,
                'costCenter' => $value->costCenter,
                'costCenter2' => $value->costCenter2,
                'accountCode' => $value->accountCode,
                'accountName' => $sap->getDb()->table('OACT')->where('OACT.AcctCode', $value->accountCode)->first()->AcctName,
                'itemName' => $itemName,
                'wareHouseCode' => $value->wareHouseCode,
                'wareHouseName' => $sap->getDb()->table('OWHS')->where('OWHS.WhsCode', $value->wareHouseCode)->first()->WhsName,
                'itemUnd' => ((!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom']) ),                
            ];
        }
        
        return $new;
    }

    public function store(Request $request)
    {
    
        try {
            DB::beginTransaction();
            Item::where('idInputs','=', $request->get('id'))->delete();
            foreach ($request->items as $key => $value) {
                $item = new Item();
                $value['idInputs'] = $request->get('id');
                $item->updateInDB($value);
            }
            $input = Input::find($request->get('id'));
            $input->dbUpdate = true;
            $input->is_locked = false;
            $input->save();
            saveUpload($request,'inputs',$input->id);
            $check = true;

            DB::commit();
            InputToSAP::dispatch($input);
            return redirect()->route('inventory.input.edit', $input->id)->withSuccess('Operacao realizada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('E0aAF', $e->getFile(), $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updateUploads(Request $request){
        saveUpload($request, $request->table, $request->id);
        $input = Input::find($request->id);
        $input->updateUpload();
    }


    public function anyData(Request $request)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OIGN");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");
        $columnsToSelect = ['DocNum', 'DocDate', 'TaxDate', 'Comments', 'U_R2W_USERNAME', 'U_R2W_CODE'];

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

    public function searchData($code)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table('OIGN')
            ->leftJoin('IGN1', 'IGN1.DocEntry', '=', 'OIGN.DocEntry')
            ->join('OPRJ', 'OPRJ.PrjCode', '=', 'IGN1.Project')
            ->join('OOCR', 'OOCR.OcrCode', '=', 'IGN1.OcrCode')
            ->where('OIGN.DocNum', $code)
            ->select('IGN1.ItemCode','IGN1.Dscription','IGN1.Quantity','IGN1.Price', 'OPRJ.PrjName', 'OOCR.OcrName', 'OIGN.U_R2W_CODE')
            ->get();
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
    
            $items = Input::join('users','users.id','=','inputs.idUser')
                        ->select(['inputs.*','users.name']);
    
            if (!is_null($request->codSAP)) {
                $items->where('codSAP','like', "%{$request->codSAP}%");
            }
            if (!is_null($request->codWEB)) {
                $items->where('code','like', "%{$request->codWEB}%");
            }
            if (!is_null($request->usuario)) {
                $items->where('idUser','=', "{$request->usuario}");
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
                        $items->where('is_locked', 0);
                        $items->whereNotNull('message');
                        break;
                    case 3:
                        $items->where('is_locked', 1);
                        break;
                    case 4:
                        $items->where(['dbUpdate' => 1, 'is_locked' => 0]);
                        break;
                    default:
                        die('Houve um problema na edição do código HTML, por favor volte à página!');
                        break;
                }
            }
    
            $query = $items->orderBy('inputs.id', 'desc')->paginate(30)->appends(request()->query());
    
            return view('inventory::input.index', array_merge(['items' => $query], $this->getOption()));
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0006', 'Listando a entrada de mercadoria', $e->getMessage());
            return view('inventory::input.index')->withErrors($e->getMessage());
        }
    }
    

    public function print($id)
    {
        try {
            $input = Input::find($id);
            if(!empty($input)){
                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory.jasper";
                
                if(!file_exists($relatory_model)){
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory.jrxml";
                }

                $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'input';
                $output = public_path('/relatorios'.'/'.$file_name);
                $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id'=>$id], 'pt_BR', 'r2w');
                
                return response()->file($report)->deleteFileAfterSend(true);
            }
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E0012kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    private function getItemName($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            $sap = new Company(false);
            $codProject = $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->projectCode}'");

            $codWhs = $sap->query("SELECT concat(T0.[WhsCode],'-', T0.[WhsName]) as [WhsName]  FROM OWHS T0 WHERE T0.[WhsCode]= '{$value->wareHouseCode}'");

            $codAcct = $sap->query("SELECT concat(T0.[AcctCode],'-', T0.[AcctName]) as [AcctName] FROM OACT T0 WHERE T0.[AcctCode] = '{$value->accountCode}'");

            $codCost = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter}'");
            $codCost2 = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter2}'");

            $newArray[] = [
                'id' => $value->id,
                'idInputs' => $value->idInputs,
                'itemCode' => $value->itemCode,
                'itemName' => $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
                'quantity' => $value->quantity,
                'price' => $value->price,
                'accountCode' => (!empty($codAcct) ? $codAcct[0]['AcctName'] : ''),
                'wareHouseCode' => (!empty($codWhs) ? $codWhs[0]['WhsName'] : ''),
                'projectCode' => (!empty($codProject) ? $codProject[0]['PrjName'] : ''),
                'costingCode' => (!empty($codCost) ? $codCost[0]['OcrName'] : ''),
                'costingCode2' => (!empty($codCost2) ? $codCost2[0]['OcrName'] : '')
            ];
        }

        return $newArray;

    }

    public function report(Request $request){
        
        $requests = new Requests();
        $usuarios = User::where('ativo','=','1')->select('id','name')->get();
     
        return view("inventory::input.report",compact('requests',  'usuarios'));
    }

    public function gerarReport(Request $request){

        $data = [
            'code' => $request->code ?? 'NULL',
            'idUser' => $request->name ?? 'NULL',
            'initialDate' => $request->data_ini ?? '2015-01-01',
            'lastDate' => $request->data_fim ?? date('Y-m-d'),
            'docStatus' => $request->status ?? 'NULL'
        ];

        if($request->tipo == 1){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory-Sintetico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_order';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }else if($request->tipo == 2){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory-Analitico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_order';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InputInventory-Analitico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }
       
    }
}
