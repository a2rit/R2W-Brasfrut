<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Upload;
use App\Jobs\Queue;
use App\Jobs\UploadsToSAP;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SapUtilities;
use App\ConfigSAP;
use App\LogsError;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use App\Modules\Purchase\Models\AdvanceProvider\Items;
use App\Modules\Purchase\Models\AdvanceProvider\Payments;
use App\Modules\Purchase\Jobs\AdvanceProviderToSAP;
use App\JasperReport;


class AdvanceProviderController extends Controller
{   
    use SapUtilities;


    public function index(){

        $items = AdvanceProvider::select(
            "advance_provider.id",
            "codSAP",
            "advance_provider.cardCode",
            "OCRD.CardName",
            "taxDate",
            "status",
            "message",
            "is_locked",
            "code",
            "users.name",
            "docTotal"
        )
        ->join('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'advance_provider.cardCode')
        ->join('users', 'advance_provider.idUser', '=', 'users.id');
        
        $buscaGraph = AdvanceProvider::select('status')
            ->join('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'advance_provider.cardCode')
            ->join('users', 'advance_provider.idUser', '=', 'users.id')
            ->whereBetween('taxDate', [Carbon::now()->subYear(), Carbon::now()])->get();
        $ODPO = new AdvanceProvider;
        $items = $items->orderBy('advance_provider.id', 'desc')->paginate(30);
    
        return view('purchase::advanceProvider.index', compact('items', 'buscaGraph', 'ODPO'));
    }

    public function search(Request $request){

        try{
            $items = AdvanceProvider::select("advance_provider.id",
                    'codSAP',
                    'advance_provider.cardCode',
                    'OCRD.CardName',
                    'taxDate',
                    'status',
                    'message',
                    'is_locked',
                    'code',
                    'users.name')
                ->join('SAPHOMOLOGACAO.dbo.OCRD', 'OCRD.CardCode', '=', 'advance_provider.cardCode')
                ->join('users', 'advance_provider.idUser', '=', 'users.id');

          if($request->get('cpf_cnpj')) {
              $aux = preg_replace('/[^0-9]/', '', $request->get('cpf_cnpj'));
              $items->where('CRD7.TaxId4', 'like', "%{$aux}%")->orWhere('CRD7.TaxId0', 'like', "%{$aux}%");
          }

          if (!is_null($request->codSAP)) {
              $items->where('codSAP', 'like', "%{$request->codSAP}%");
          }
          if (!is_null($request->codWEB)) {
                $items->where('code', 'like', "%{$request->codWEB}%");
          }
          if (!is_null($request->nameParceiro)) {
            $items->where('OCRD.CardCode', 'like', "%{$request->nameParceiro}%");
          }

          if (!is_null($request->data_fist)){
            $items->whereDate("taxDate", '>=', "$request->data_fist");
          }

          if (!is_null($request->data_last)){
            $items->whereDate("taxDate", '<=', "$request->data_last");
          }

          if(!is_null($request->status)){
            $items->where('status', $request->status);
          }

          $buscaGraph = $items->get();
          $ODPO = new AdvanceProvider;

          $items = $items->orderBy('taxDate', 'desc')->paginate(30)->appends(request()->query());
          $request->flash();
          
          return view('purchase::advanceProvider.index', compact('items', 'buscaGraph', 'ODPO'));
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('E0003', 'Listando o adiantamentos ao fornecedor',$e->getMessage());
           return view('purchase::advanceProvider.index')->withErrors($e->getMessage());
        }
    }

    public function anyData($id){
        $sap = new Company(false);
        $advances = $sap->query("SELECT T0.ItemCode, T0.Dscription, T0.Quantity, T0.Price, T0.LineTotal, 
                                            T0.Project, T0.OcrCode, T0.OcrCode2
                                        FROM DPO1 T0
                                        WHERE T0.DocEntry = '{$id}'");

        return json_encode([
            'data' => $advances
        ]);

    }

    public function read($id)
    {
        
        $head = AdvanceProvider::select(
                "advance_provider.id",
                'codSAP',
                'advance_provider.cardCode',
                'code',
                'docDueDate',
                'taxDate',
                'docDate',
                'idUser',
                'users.name',
                'status',
                'paymentCondition',
                'message',
                'is_locked',
                'dpmPrcnt',
                'T2.CardName',
                'docTotal',
                'veiculo',
                'ticket'
            )
            ->leftJoin('SAPHOMOLOGACAO.dbo.OCRD as T2', 'T2.CardCode', '=', 'advance_provider.cardCode')
            ->join('users', 'advance_provider.idUser', '=', 'users.id')
            ->find($id);

        $body = Items::where('idAdvanceProvider', $id)->get();
        $payment = $head->payment()->first();
        $upload = Upload::where('reference','advance_provider')->where('idReference',$id)->get();
        
        return view('purchase::advanceProvider.create', compact('head', 'body', 'payment', 'upload'), $this->options());
    }

    public function create()
    {
        return view("purchase::advanceProvider.create", $this->options());
    }

    public function save(Request $request){
        try {
            DB::beginTransaction();
                if(empty($request->id)){
                    $advance_provider = new AdvanceProvider();
                    $advance_provider->saveInDB($request->all());
                }else{
                    $advance_provider = AdvanceProvider::find($request->id);
                    $advance_provider->updateInDB($request, $advance_provider);
                }

                saveUpload($request, 'advance_provider', $advance_provider->id);
            DB::commit();

            $uploads = Upload::where('idReference', $advance_provider->id)->where('reference', 'advance_provider')->first();
            if(!empty($uploads)){
                UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }

            AdvanceProviderToSAP::dispatch($advance_provider);

            return redirect()->route('purchase.advance.provider.read', $advance_provider->id)
                    ->withSuccess("Adiantamento salvo com sucesso. Documento sendo enviado para o SAP!");
        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->route('purchase.advance.provider.index')
                    ->withErrors($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    public function copyFromPurchaseOrder($id){
        try {
            DB::beginTransaction();
                $advance_provider = new AdvanceProvider();
                $advance_provider->copyFromPurchaseOrder($id);
            DB::commit();
            return redirect()->route('purchase.advance.provider.read', $advance_provider->id)
                    ->withSuccess("Adiantamento para Fornecedores salvo com sucesso!");
        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->route('purchase.advance.provider.index')
                    ->withErrors($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    public function refund($id){
        try {
            $payment = Payments::find($id);
            $paymentResult = $payment->refund();
            if($paymentResult['status'] === 'success'){
                return redirect()->route('purchase.advance.provider.read', $payment->advanced_provider->id)
                    ->withSuccess($paymentResult['message']);
            }
            return redirect()->route('purchase.advance.provider.read', $payment->advanced_provider->id)
                ->withErrors($paymentResult['message']);
        } catch (\Exception $e) {
            return redirect()->route('purchase.advance.provider.read', $payment->advanced_provider->id)
                ->withErrors($e->getMessage());
        }
    }

    public function print($id){

        try {
            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/AdvanceProvider.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'advance_provider';
            $output = public_path('/relatorios'.'/'.$file_name);

            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/AdvanceProvider.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], ['DocNum'=>$id], 'pt_BR', 'sap');
            return response()->file($report)->deleteFileAfterSend(true);
        

        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E001kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    public function updateUploads(Request $request){
        saveUpload($request, $request->table, $request->id);
        $advance_provider = AdvanceProvider::find($request->id);
        $advance_provider->updateUpload();
    }

    public function listAdvancesTopNav(Request $request)
    {        
        $columns = [];

        foreach($request->fields as $index => $value){
            $columns[$index] = $value['fieldName'];
        }

        $columns = implode(',', $columns);
        $sql = "SELECT DISTINCT TOP 10 $columns FROM [VW_R2W_ADVANCE_PROVIDER_TOP_NAV]";
        
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

    private function options(){
        $sap = new Company(false);
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $paymentForms = $sap->query("SELECT T0.[PayMethCod] as value, T0.[Descript] as name, T0.[Type]  FROM OPYM T0 WHERE T0.[Type] = 'O'");
        $accounts = $this->getAccountOptions($sap);
        $advance_provider_model = new AdvanceProvider;
        return compact('paymentConditions', 'accounts', 'centroCusto', 'centroCusto2', 'projeto', 'paymentForms', 'advance_provider_model');
    }

}
