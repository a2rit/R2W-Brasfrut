<?php

namespace App\Modules\Partners\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Partners\Models\Partner;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use App\SapUtilities;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use App\JasperReport;
use App\Jobs\Queue;
use App\Jobs\UploadsToSAP;
use App\logsError;
use App\Modules\Partners\Models\Partner\Contract;
use App\Upload;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PartnersController extends Controller
{   
    use SapUtilities;

   /* public function __construct(){
        $this->middleware(function ($request, $next){
            if(!checkAccess('par_cad')){
                return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
            }else{
                return $next($request);
            }
        });
    } */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index(){
        $sap = new Company(false);
         
        $query = $sap->query("SELECT DISTINCT T1.[CardCode], T1.[CardName], T1.[CardFName], T0.[TaxId0] as CNPJ,T0.[TaxId4] as CPF, 
                                    T1.[CardType] FROM CRD7 T0 
                                INNER JOIN OCRD T1 ON T0.[CardCode] = T1.[CardCode] 
                                WHERE T0.[TaxId0] is not null AND T1.[CardType] = 'S' AND T0.[AddrType] = 'S'");


       $current_page = LengthAwarePaginator::resolveCurrentPage();
       $current_page_path = LengthAwarePaginator::resolveCurrentPath();
       $currentItems = array_slice($query, 30 * ($current_page - 1), 30);
       $paginator = new LengthAwarePaginator(
           $currentItems, count($query), 30, LengthAwarePaginator::resolveCurrentPage(), 
           ['path' => $current_page_path]);
        
        return view('partners::index',['items' => $paginator], $this->getFormOptions());
    }

    public function anyData(Request $request)
    {
        $sap = new Company(false);
        $columnsToSelect = ['CardCode', 'CardName']; 
        $query = $sap->getDb()->table("OCRD");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");

        $search = $request->get('search');
        if($search['value']) {
            $query->orWhere("CardCode", "like", "%{$search['value']}%")
                ->orWhere("CardName", "like", "%{$search['value']}%");
        }
        $order = $request->get('order');
        $query->orderBy($columns[$order[0]['column']]['name'], $order[0]['dir']);

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->get($columnsToSelect)
        ]);
    }

    public function getPartnersSAP(Request $request){
        $sap = new Company(false);
        $busca = $sap->query("SELECT T1.[Name] as value, T1.[Name] as name FROM OCRD T0  INNER JOIN OCPR T1 ON T0.[CardCode] = T1.[CardCode] WHERE T0.[CardCode] like '%{$request->get("c")}%' and T1.[Name] like '%{$request->get("q")}%' ");
        return response()->json($busca);
    }
  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('partners::create', $this->getFormOptions());
    }

    public function getGroups(Request $request)
    {
        $sap = new Company(false);
        $groups = $sap->query("select GroupCode as value, GroupName as name from OCRG where GroupType = :type",
            ['type' => $request->get('type')]);
        
        

        return response()->json($groups);
    }

    public function getPartner($cardCode){
        $sap = new Company(false);
        $busca = $sap->getDb()->table('OCRD')
                    ->join('CRD7', 'CRD7.CardCode', '=', 'OCRD.CardCode')
                    ->select("OCRD.CardCode", 
                                "OCRD.CardName", 
                                "CRD7.TaxId0", 
                                "CRD7.TaxId4")
                    ->where('OCRD.CardCode', '=', $cardCode)
                    ->first();
        return response()->json($busca);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            
            $id = $request->get('id', false);
            if($id) {
                $partner = Partner::where('id', $id)->first();
                $atributes = $request->all();
                $atributes['idUser'] = auth()->user()->id;
                $atributes['contaContabil'] = $request->contaContabils;
                $atributes['contaControle'] = $request->contaControles;
                $atributes['telephone'] = preg_replace('/[^0-9]/', '', $request->telephone);
                $partner->update($atributes);
            } else {
                $atributes = $request->all();
                $atributes['idUser'] = auth()->user()->id;
                $atributes['telephone'] = preg_replace('/[^0-9]/', '', $request->telephone);
                $partner = Partner::create($atributes);
            }

            $partner->addresses()->delete();
            $line = 0;
            foreach ($request->get('addresses', []) as $item) {
                $item['partner_id'] = $partner->id;
                $item['U_SKILL_IE'] = $partner->ie;
                $item['line'] = $line;
                Partner\Address::create($item);
                $line++;
            }

            $partner->contacts()->delete();
            foreach ($request->get('contacts', []) as $item) {
                $item['partner_id'] = $partner->id;
                Partner\Contact::create($item);
            }

            $partner->payments()->delete();
            foreach ($request->get('paymentForms', []) as $key => $item) {
                $payment = new  Partner\Payments();
                $item['partner_id'] = $partner->id;
                $item['description'] = $key;
                $payment->fill($item);
                $payment->save();
            }

            $partner->payments()->delete();
            foreach ($request->get('paymentForms', []) as $key => $item) {
                $payment = new  Partner\Payments();
                $item['partner_id'] = $partner->id;
                $item['description'] = $key;
                $payment->fill($item);
                $payment->save();
            }

            $partner->bankaccounts()->delete();
            foreach ($request->get('bankaccount', []) as $key => $item) {
               
                if(!array_key_exists("delete",$item)){
                    $payment = new  Partner\BankAccounts();
                    $item['partner_id'] = $partner->id;
                    $payment->fill($item);
                    $payment->save();
                }
            }

            // $partner->contracts()->delete();
            
            foreach ($request->get('contracts', []) as $key => $item) {
                if(!empty($item['id'])){
                    $contract = Partner\Contract::find($item['id']);
                    $contract->contractNumber = $item['contractNumber'];
                    $contract->startDate = $item['startDate'];
                    $contract->endDate = $item['endDate'];
                    // $contract->amount = clearNumberDouble($item['amount']);
                    // $contract->residualAmount = clearNumberDouble($item['residualAmount']);
                }else{
                    $contract = new Partner\Contract;
                    $item['partner_id'] = $partner->id;
                    $item['cardCode'] = $partner->code;
                    $item['code'] = $contract->createCode();
                    $item['amount'] = clearNumberDouble($item['amount']);
                    $item['residualAmount'] = clearNumberDouble($item['amount']);
                    $contract->fill($item);
                }
                $contract->save();
                $contract->updateResidualValue(Carbon::now()->format('Y-m-d'));
            }

            saveUpload($request, 'partners', $partner->id);

            $uploads = Upload::where('idReference', $partner->id)->where('reference', 'partners')->first();
            if(!empty($uploads)){
                UploadsToSAP::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
            }

            $partner->saveInSap();
            DB::commit();
            return response()->json(['status' => 'success', 'code' => $partner->code]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            if(isset($partner)) {
                $partner->is_locked = true;
                $partner->message = $e->getMessage();
                $partner->save();
            }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $code
     * @return \Illuminate\Http\Response
     */
    public function edit($code)
    {
        try {
            $sap = new Company(false);
            $partner = Partner::getUpdated($sap, $code);
          
        } catch (\Exception $e) {
            return redirect()->route('partners.index')->withErrors($e->getMessage());
        }
        return view("partners::edit", array_merge(compact("partner"), $this->getFormOptions()));
    }


    protected function getFormOptions()
    {
        $sap = new Company(false);
        $groups = $sap->query("SELECT GroupCode AS value, GroupName AS name, GroupType AS type FROM OCRG WHERE GroupType = 'S'");
        $paymentForms = $sap->query("SELECT T0.[PayMethCod] AS value, T0.[Descript] AS name, T0.[Type] FROM OPYM T0 WHERE T0.[Type] = 'O'");
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $types = [
            // ['name' => 'Cliente', 'value' => 0],
            ['name' => 'Fornecedor', 'value' => 1],
            // ['name' => 'Cliente Potencial', 'value' => 2],
        ];
        $cnae = $sap->query("SELECT * FROM OCNA");

        $states = $sap->query("SELECT Code AS value, Name AS name FROM OCST WHERE Country = 'BR'");
        $countries = $sap->query("SELECT Code AS value, Name AS label FROM OCRY ORDER BY label");
       
        $acct = $this->getAccountOptions($sap);
        $acctControl = $this->getAccountOptionsControle($sap);
        $bankaccounts = $sap->query("SELECT T0.[BankCode], T0.[BankName] FROM ODSC T0");
        $properties = $sap->query("SELECT T0.[GroupCode] AS value, T0.[GroupName] AS name FROM OCQG T0");
        
        return compact('groups', 'types','cnae','acct','acctControl' ,'states','countries','paymentForms','paymentConditions', 'bankaccounts', 'properties');
    }

    public function getTypeOfAddress($ibge){
        $sap = new Company(false);
        return $sap->query("select AbsId as code,Name as name from OCNT where IbgeCode = '{$ibge}'");
    }

    public function getContractUsageHistory(Request $request){
        $columnsToSelect = ['incoing_invoices.id', 'codSAP', 'incoing_invoices.code', 'users.name', 'incoing_invoices.taxDate',
                             'docTotal', 'incoing_invoice_taxes.sequenceSerial'];
        $query = IncoingInvoice::join('users', 'users.id', '=', 'incoing_invoices.idUser')
                    ->join('incoing_invoice_taxes', 'incoing_invoice_taxes.idIncoingInvoice', '=', 'incoing_invoices.id')
                    ->where('contract', '=', "{$request->contract}");

        $query->offset($request->get("start"));
        $query->limit($request->get("length"));

        $search = $request->get('search');
      
        if($search) {
            if($search['value']) {
                $query->where(function (Builder $where) use ($search) {
                    $where->orWhere("codSAP", "like", "%{$search['value']}%")
                        ->orWhere("code", "like", "%{$search['value']}%");
                });
            }
        }


        $query = $query->get($columnsToSelect);
        $recordsTotal = $query->count();
        ;
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query
        ]);
    }


    public function filter(Request $request){
        try {
          $request->flash();
          $sap = new Company(false);
          $sql = "SELECT DISTINCT T1.[CardCode], T1.[CardName], T1.[CardFName], T0.[TaxId0] as CNPJ,T0.[TaxId4] as CPF, T1.[CardType]
                    FROM CRD7 T0  INNER JOIN OCRD T1 ON T0.[CardCode] = T1.[CardCode] WHERE T0.[TaxId0] is not null AND T1.[CardType] = 'S' ";

          if(isset($request->codSAP) && !is_null($request->codSAP)){
             $sql .= " and T1.[CardCode] = '{$request->codSAP}'";
          }
          if(isset($request->name) && !is_null($request->name)){
             $sql .= " and (T1.[CardName] like '%{$request->name}%' or T1.[CardFName] like '%{$request->name}%')";
          }

          if (!is_null($request->cnpj_cpf)) {
              $sql .= " and T0.[TaxId4] like '%{$request->cnpj_cpf}%'";
              $sql .= " or T0.[TaxId0] like '%{$request->cnpj_cpf}%'";
          }
          if (isset($request->type) && !is_null($request->type)){
              switch ($request->type) {
                case '0':
                 $sql .= " and  T1.[CardType] = 'C'";
                  break;
                case '1':
                 $sql .= " and  T1.[CardType] like 'S'";
                  break;
                case '2':
                 $sql .= " and  T1.[CardType] like 'L'";
                  break;
              }
          }
          $sql .= ' order by  T1.[CardCode] desc ';
          $query = $sap->query($sql);

          $current_page = LengthAwarePaginator::resolveCurrentPage();
          $current_page_path = LengthAwarePaginator::resolveCurrentPath();
          $currentItems = array_slice($query, 30 * ($current_page - 1), 30);
          $paginator = new LengthAwarePaginator(
              $currentItems, count($query), 30, LengthAwarePaginator::resolveCurrentPage(), 
              ['path' => $current_page_path, 
               'query' => $request->query()]);

          return view('partners::index',['items' => $paginator], $this->getFormOptions());
        } catch (\Exception $e) {
          return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function getPartnerContracts($codPN){
        return response()->json(Partner::partnerContracts($codPN));
    }

    public function relatory(Request $request){

        if(empty($request->tipo)){
            return view('partners::report', $this->getFormOptions());
        }

        $data = [
            'cardCode' => $request->cardCode ?? 'NULL',
            'contractNumber' => $request->contractNumber ?? 'NULL',
            'initialDate' => $request->initialDate ?? '2000-12-31',
            'lastDate' => $request->lastDate ?? date('Y-m-d'),
            'partnerStatus' => $request->partnerStatus ?? 'NULL',
            'partnerGroup' => $request->partnerGroup ?? 'NULL',
            'partnerCharacteristic' => $request->partnerCharacteristic ?? 'NULL',
        ];
        
        if($request->tipo == 1){
    
            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/PartnersContract-Sintetico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'contract';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PartnersContract-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }else if($request->tipo == 2){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/PartnersContract-Analitico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'contract';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/PartnersContract-Analitico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        }

    }

    public function removeContract(Request $request)
    {
        try {
            DB::beginTransaction();
            $contract = new Contract;
            $contract->removeInSAP($request->input('contract_code', null));
            DB::commit();
            return response()->json(
                [
                    'code' => Response::HTTP_OK
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function updateUploads(Request $request){
        saveUpload($request, $request->table, $request->id);
        $partner = Partner::find($request->id);
        $partner->updateUpload();
    }

    public function removeUploads($id,$idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::find($id);
            $diretory = public_path($upload->get()->first()->diretory);
            if(file_exists($diretory)){
                unlink($diretory);
            };
            $upload->delete();
            $partner = Partner::find($idReference);
            DB::commit();
           
            return redirect()->route('partners.edit', $partner->code)->withSuccess("Anexo excluido com sucesso!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
