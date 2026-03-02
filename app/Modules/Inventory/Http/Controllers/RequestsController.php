<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SetInSAPOutputRequest;
use App\Modules\Inventory\Jobs\PurchaseRequestToSAP;
use App\Modules\Inventory\Jobs\OutputToSAP;
use App\LogsError;
use App\User;
use App\Modules\Inventory\Models\Requisicao\Products;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\SapUtilities;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\GrupoWhs;
use App\Jobs\Queue;

use App\JasperReport;
use Exception;

class RequestsController extends Controller
{
    use SapUtilities;

    public function index()
    {
        $busca = Requests::select([
            'requests.id as idRequest', 'requests.codSAP', 'requests.code', 'requests.documentDate',
            'requests.documentDate as requiredDate', 'T1.name', 'T2.name as atendente', 'requests.codStatus'
        ])
            ->leftJoin('users as T1', 'T1.id', '=', 'requests.requesterUser')
            ->leftJoin('users as T2', 'T2.id', '=', 'requests.clerkUser')
            ->orderBy('requests.id', 'desc');

        if (auth()->user()->tipo == 'S') {
            $busca->where('requests.requesterUser', '=', auth()->user()->id);
        } else if (auth()->user()->tipo == 'A') {
            $busca->where('requests.whs', '=', auth()->user()->whsGroup);
        }

        $busca = $busca->paginate(30);
        $requests = new Requests();
        return view("inventory::request.index", compact('busca', 'requests'));
    }

    public function create()
    {
        try {
            $count_request = Requests::join('users', 'users.id', '=', 'requests.requesterUser')
                ->where('requests.codStatus', '=', Requests::STATUS_WAIT_REQUESTER)
                ->where('users.id', '=', auth()->user()->id)
                ->count('requests.id');
            if ($count_request == 0) {
                $sap = new Company(false);
                $date = DATE('Y-m-d');
                $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
                $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
                $projeto = $this->getProjectOptions($sap);
                $warehouses = $sap->query("select WhsCode as code, WhsName as value from OWHS");
                return view("inventory::request.create", compact('centroCusto', 'centroCusto2', 'projeto', 'warehouses'));
            } else {
                return redirect()->route('inventory.request.index')->withErrors("Ops! Não foi possivel cadastrar uma nova Requisição. Pois existem requisições aguardo sua resposta!");
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas233', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('inventory.request.index')->withErrors($e->getMessage());
        }
    }

    public function anyData(Request $request)
    {
        $id = $request->get('myKey');

        $whs = GrupoWhs::where('type', '=', $id)->select('whsCode')->get();

        $aux = [];

        foreach ($whs as $key => $value) {
            array_push($aux, $value['whsCode']);
        }
        $sap = new Company(false);
        $query = $sap->getValidItemQueryBuilder('OITM');
        $query->whereIn(
            'OITM.DfltWH',
            $aux
        );
        $recordsTotal = $query->count();

        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITM.BuyUnitMsr', 'OITM.DfltWH', 'OITW.OnHand'];
        $search = $request->get('search');

        if ($search) {
            if ($search['value']) {
                $query->where(function (Builder $where) use ($search) {
                    $where->orWhere("OITM.ItemCode", "like", "%{$search['value']}%")
                        ->orWhere("OITM.ItemName", "like", "%{$search['value']}%");
                });
            }
        }

        $order = $request->get('order');
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $this->getQtdWhs($query->get($columnsToSelect))
        ]);
    }

    //     public function anyDataWhs(Request $request)
    //   {

    //     // $id = $request->get('myKey');

    //       if (!is_null(auth()->user()->whsDefault)) {
    //           $id = auth()->user()->whsDefault;
    //       }else{
    //           $id = '01';
    //       }

    //       if($request->get('myKey')){
    //         $id = $request->get('myKey');
    //       }

    //       $sap = new Company(false);

    //       if(!$request->get('purchase')){
    //             $query = $sap->getValidItemQueryBuilder('OITM')
    //           ->leftJoin('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode');
    //             $query->where('OITW.WhsCode', '=', $id);

    //             if($request->get('isNFS')){
    //               $isNFS = (int) $request->get('isNFS');
    //               $query->where('OITM.ItmsGrpCod','=','109');
    //             }

    //             if($request->get('isLoan')){
    //             $isLoan = (int) $request->get('isLoan');
    //             $query->where('OITM.ItmsGrpCod','=','119');
    //             }

    //             $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITW.OnHand', 'OITM.InvntryUom'];
    //         }else{
    //             // query("SELECT DISTINCT T0.[ItemCode], T0.[ItemName],T0.[ItmsGrpCod],  T0.[DfltWH], T0.[InvntryUom] FROM OITM T0  INNER JOIN OITW T1 ON T0.[ItemCode] = T1.[ItemCode] WHERE T0.[validFor] ='Y'");
    //             $query = $sap->getDb()->table('OITM')->where('OITM.validFor', '=', 'Y')
    //             ->join('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode');

    //             if($request->get('isNFS')){
    //                 $query->where('OITM.ItmsGrpCod','=','109');
    //             }

    //             $query->distinct();

    //             $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITM.InvntryUom'];
    //         }


    //       $recordsTotal = $query->count();

    //       $query->offset($request->get("start"));
    //       $query->limit($request->get("length"));

    //       $search = $request->get('search');

    //         if($search) {
    //             if($search['value']) {
    //                 $query->where(function (Builder $where) use ($search) {
    //                     $where->orWhere("OITM.ItemCode", "like", "%{$search['value']}%")
    //                         ->orWhere("OITM.ItemName", "like", "%{$search['value']}%");
    //                 });
    //             }
    //         }

    //       $order = $request->get('order');

    //       return response()->json([
    //           "draw" => $request->get("draw"),
    //           "recordsTotal" => $recordsTotal,
    //           "recordsFiltered" => $query->count(),
    //           "data" => $query->get($columnsToSelect)
    //       ]);
    //   }
    public function anyDataWhs(Request $request)
    {

        // $id = $request->get('myKey');

        if (!is_null(auth()->user()->whsDefault)) {
            $id = auth()->user()->whsDefault;
        } else {
            $id = '01';
        }

        if ($request->get('myKey')) {
            $id = $request->get('myKey');
        }

        $sap = new Company(false);
        $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITM.BuyUnitMsr', 'OITM.DfltWH'];

        //   if(!$request->get('purchase')){
        //         $query = $sap->getValidItemQueryBuilder('OITM')
        //       ->join('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode');
        //         $query->where('OITW.WhsCode', '=', $id);

        //         if($request->get('isNFS')){
        //           $isNFS = (int) $request->get('isNFS');
        //           $query->where('OITM.ItmsGrpCod','=','109');
        //         }

        //         if($request->get('isLoan')){
        //         $isLoan = (int) $request->get('isLoan');
        //         $query->where('OITM.ItmsGrpCod','=','119');
        //         }

        //         $columnsToSelect = ['OITM.ItemCode', 'OITM.ItemName', 'OITW.OnHand', 'OITM.InvntryUom'];
        //     }else{
        //   $sap = new Company(false);

        // dd($sap->query("SELECT DISTINCT T0.[ItemCode], T0.[ItemName],T0.[ItmsGrpCod],  T0.[DfltWH], T0.[InvntryUom] FROM OITM T0  INNER JOIN OITW T1 ON T0.[ItemCode] = T1.[ItemCode] WHERE T0.[validFor] ='Y'"));

        // if(!$request->get('purchase')){

        //     $query = $sap->getValidItemQueryBuilder('OITM')
        //     ->join('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode')
        //     ->where('OITM.validFor', '=', 'Y');

        //     $query->where('OITW.WhsCode', '=', $id);
        // }else{
        $query = $sap->getDb()->table('OITM')
            ->join('OITW', 'OITM.ItemCode', '=', 'OITW.ItemCode')
            ->where('OITM.validFor', '=', 'Y');
        // }
        if ($request->get('isNFS')) {
            $query->where('OITM.ItmsGrpCod', '=', '109');
        }

        if ($request->get('isLoan')) {
            $isLoan = (int) $request->get('isLoan');
            $query->where('OITM.ItmsGrpCod', '=', '119');
        }

        if (!empty($request->get('whsCode'))) {
            $query->where('OITW.WhsCode', '=', $request->get('whsCode'));
            array_push($columnsToSelect, 'OITW.OnHand');
        }

        $query->distinct();

        // }




        $query->offset($request->get("start"));
        $query->limit($request->get("length"));

        $search = $request->get('search');

        if ($search) {
            if ($search['value']) {
                $query->where(function (Builder $where) use ($search) {
                    $where->orWhere("OITM.ItemCode", "like", "{$search['value']}%")
                        ->orWhere("OITM.ItemName", "like", "{$search['value']}%");
                });
            }
        }

        $order = $request->get('order');

        $query = $query->get($columnsToSelect);

        $recordsTotal = $query->count();
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query
        ]);
    }

    public function getAccFromWhs($whs)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table('OWHS')->where('WhsCode', '=', $whs)->get();

        return $query;
    }

    public function getQtdWhs($all)
    {
        $newArray = [];
        foreach ($all as $key => $value) {
            $sap = new Company(false);

            $qtd = $sap->query("SELECT T1.[OnHand] as total FROM OITM T0  INNER JOIN OITW T1 ON T0.[ItemCode] = T1.[ItemCode] 
                                  WHERE T0.[DfltWH] = T1.[WhsCode] and T0.[ItemCode] = '{$value->ItemCode}'");
            $newArray[] = [
                'ItemCode' => $value->ItemCode,
                'ItemName' => $value->ItemName,
                'OnHand' => number_format(isset($qtd[0]['total']) ? $qtd[0]['total'] : 0, 2, ',', '.'),
                'BuyUnitMsr' => $value->BuyUnitMsr,
            ];
        }


        return $newArray;
    }


    public function save(Request $request)
    {
        try {
            $requisition = new Requests();
            $requisition->saveInDB($request);
            return redirect()->route('inventory.request.index')->withSuccess("Requisição salva com sucesso!");
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E2F226F', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('inventory.request.index')->withErrors($e->getMessage());
        }
    }

    public function search()
    {
        $query = "select top 100 requests.id as idRequest, A.name,  requests.documentDate,requests.requiredDate, status.value from users A
                          right join requests on requests.clerkUser = A.id
                          join status on requests.codStatus = status.code";

        if (auth()->user()->tipo == 'S') {
            $id = auth()->user()->id;
            $query .= " WHERE requests.requesterUser = '{$id}'";
        }
        $query .= " order by requests.id desc";
        $busca = DB::select($query);
        $productsData = DB::select("select * from request_products");

        return view("inventory::request.search", ['busca' => $busca, 'productsData' => $productsData]);
    }

    public function searching($id)
    {
        $data = Requests::find($id);
        switch ($data->codStatus) {
            case Requests::STATUS_WAIT_REQUESTER:
                if (auth()->user()->tipo == 'S') {
                    return view("inventory::request.answer", $this->option($id));
                } else {
                    return view("inventory::request.view", $this->option($id));
                }
                break;
            case Requests::STATUS_RECEIVED:
                return view("inventory::request.view", $this->option($id));
                break;
            case Requests::STATUS_REFUSED:
                return view("inventory::request.view", $this->option($id));
                break;
            case Requests::STATUS_PARTIAL_ATTENDED:
                if (auth()->user()->tipo == 'A') {
                    return view("inventory::request.answer", $this->option($id));
                } else {
                    return view("inventory::request.view", $this->option($id));
                }
                break;
            case Requests::STATUS_CLERK_LINK:
                if (auth()->user()->tipo == 'A') {
                    return view("inventory::request.answer", $this->option($id));
                } else {
                    return view("inventory::request.view", $this->option($id));
                }
                break;
            case Requests::STATUS_NFS_SAP:
                if (auth()->user()->tipo == 'A') {
                    return view("inventory::request.answer", $this->option($id));
                } else {
                    return view("inventory::request.view", $this->option($id));
                }
                break;
            case Requests::STATUS_WAIT_CLERK:
                if (auth()->user()->tipo == 'A') {
                    return view("inventory::request.answer", $this->option($id));
                } else {
                    return view("inventory::request.answer", $this->option($id));
                }
                break;
            case Requests::STATUS_LINK:
                if (auth()->user()->tipo == 'A') {
                    return view("inventory::request.meet", $this->option($id));
                } else {
                    return view("inventory::request.view", $this->option($id));
                }
                break;

            case Requests::STATUS_CANCELED:
                return view("inventory::request.view", $this->option($id));
                break;
            default:
                break;
        }
    }

    private function option($id)
    {
        try {
            $userData = Requests::where('requests.id', '=', $id)
                ->join('users', 'users.id', '=', 'requests.requesterUser')
                ->select('requests.id', 'requests.code', 'requests.codSAP', 'requests.requesterUser', 'requests.clerkUser', 'requests.whs', 'users.name', 'requests.codStatus', 'requests.description', 'requests.description2', 'requests.documentDate', 'requests.requiredDate', 'requests.requesterUser', 'requests.is_locked', 'requests.message')
                ->get();
            $user = User::find($userData[0]['requesterUser']);
            $requests = new Requests();
            $productsData = Products::where('request_products.idRequest', '=', $id)->get();

            $whsCodes = GrupoWhs::where('idUser', $userData[0]['whs'])->pluck('whsCode')->toArray();

            $whsCodesNames = implode(",", $whsCodes);
            
            $sap = new Company(false);

            foreach ($productsData as $key => $value) {
                if (auth()->user()->tipo == 'S') {
                    $productsData[$key]->inventory = '0.00';
                } else {
                    $productsData[$key]->inventory = $sap->query(
                        "select a.itemcode, a.OnHand, a.WhsCode 
                        from oitw a inner join oitm b on a.ItemCode = b.ItemCode
                        where  a.WhsCode in (" . $whsCodesNames .  ") and b.itemcode = '{$value->codSAP}'"
                    )[0]['OnHand'] ?? '0.00';
                }
                $productsData[$key]->itemname = $sap->getDb()->table('OITM')->select('ItemName')->where('ItemCode', '=', $value->codSAP)->get()[0]->ItemName;
            }

            $warehouses = $sap->getDb()->table('OWHS')->select('WhsCode as code', 'WhsName as value')->get();
            $acct = $sap->getDb()->table('OACT')->select('AcctCode as code', 'AcctName as value')->get();

            return compact('userData', 'productsData', 'warehouses', 'acct', 'requests');
        } catch (\Exception $e) {
            return redirect()->route('home')->withErrors($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {

            $RQ = Requests::find($request->get('id'));
            if (!is_null($RQ) || !empty($RQ)) {
                if ($request->recusar == 'on') {
                    if (auth()->user()->tipo == 'A') {
                        $RQ->description2 = $request->obsevacoes;
                    }
                    if (auth()->user()->tipo == 'S') {
                        $RQ->description = $request->obsevacoes;
                    }
                    $RQ->codStatus = Requests::STATUS_REFUSED;
                    $RQ->save();
                    $obj = new Requests();
                    $obj->saveInputInSAP(new Company(true), $RQ);
                }
                if ($request->receber == 'on') {
                    if ($this->checkProduct($RQ->code)) {
                        $RQ->codStatus = Requests::STATUS_RECEIVED;
                    } else {
                        $RQ->codStatus = Requests::STATUS_PARTIAL_ATTENDED;
                    }
                    $RQ->description = $request->obsSolicitante;

                    $obj = new Requests();
                    $RQ->save();
                    #$obj->saveOutputInSAP(new Company(true), $RQ);
                    #OutputToSAP::dispatch($RQ);
                }
                if (($request->recusar != 'on') && ($request->receber != 'on') && ($request->solicitarCompra != 'on')) {
                    $RQ->codStatus = Requests::STATUS_WAIT_REQUESTER;
                    $RQ->save();
                }
                if (isset($request->item)) {
                    foreach ($request->item as $keys => $values) {
                        foreach ($values as $key => $value) {
                            if (is_int($key)) {
                                $product = Products::find($key);
                                $value['quantityServed'] = min(
                                    clearNumberDouble($value['quantityServed']) + $product->quantityServed,
                                    $product->quantityRequest
                                );
                                $product->quantityServed = $value['quantityServed'];

                                if ($product->pendingAmount > 0) {
                                    $product->pendingAmount = ($product->pendingAmount - $value['quantityServed']);
                                } else {
                                    $product->pendingAmount = ($product->quantityRequest - $value['quantityServed']);
                                }
                                $product->status = $RQ->codStatus;
                                $product->save();
                            }
                            if (((isset($value['solicitarCompra'])) && ($value['solicitarCompra'] == 'on'))) {
                                $RQ->codStatus = Requests::STATUS_WAIT_CLERK;
                                $RQ->description2 = $request->obsAtentente;
                                $RQ->save();
                                
                                $pr = new PurchaseRequest();
                                $pr->saveInDBFromInternalRequest($RQ);
                                PurchaseRequestToSAP::dispatch($pr)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                            }
                        }
                    }
                }
                $RQ->save();

                $obj = new Requests();
                // $obj->saveOutputInSAP($RQ);
                SetInSAPOutputRequest::dispatch($RQ);

                Products::where('request_products.idRequest', '=', $RQ->id)->update(['status' => $RQ->codStatus]);
                return redirect()->route('inventory.request.search')->withSuccess('Operação realizada com sucesso!');
            } else {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('Fas237', 'Documento Base Desconhecido', $request->id);
                return redirect()->route('inventory.request.search')->withErrors('Documento base desconhecido');
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('Fas232', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('inventory.request.search')->withErrors($e->getMessage());
        }
    }

    private function checkProduct($code)
    {
        $busca = Products::select('request_products.quantityRequest', 'request_products.quantityServed')
            ->join('requests', 'requests.id', '=', 'request_products.idRequest')
            ->where('requests.code', '=', $code)->get();
        $check = true;
        foreach ($busca as $key => $value) {
            if ($value->quantityRequest > $value->quantityServed) {
                $check = false;
            }
        }
        return $check;
    }

    public function anyDataRequest($id)
    {
        $producs = DB::SELECT("SELECT * FROM request_products where request_products.idRequest = '{$id}'");
        return response()->json([
            "data" => $this->getNameItem($producs)
        ]);
    }

    private function getNameItem($producs)
    {
        $newArray = [];
        foreach ($producs as $key => $value) {
            $sap = new Company(false);
            $newArray[$key]['id'] = $value->id;
            $newArray[$key]['codSAP'] = $value->codSAP;
            $newArray[$key]['idRequest'] = $value->idRequest;
            $newArray[$key]['quantityRequest'] = $value->quantityRequest;
            $newArray[$key]['qtdInventory'] = $sap->query("SELECT  B.ONHAND  FROM OITW B WHERE '$value->codSAP' = B.ItemCode")[0]['ONHAND'];
            $newArray[$key]['itemName'] = $sap->query("SELECT T0.ItemName FROM OITM T0 WHERE T0.ItemCode = '{$value->codSAP}' ")[0]['ItemName'];
        }
        return $newArray;
    }

    public function connectar(Request $request)
    {
        try {
            if ($request->get('vincular') == 'on') {
                $reqDB = Requests::find($request->get('id'));
                $reqDB->clerkUser = auth()->user()->id;
                $reqDB->codStatus = Requests::STATUS_CLERK_LINK;
                $reqDB->save();
                return redirect()->back()->withSuccess("Vinculado com sucesso!");
            } else {
                return redirect()->back()->withErrors('Não foi possível vincular  a solicitação');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function rejectRequest($code)
    {
        try {
            $resq = DB::select("select * from requests where requests.code = '$code'");
            $request = Requests::find($resq[0]->id);
            $request->codStatus = 4;
            $request->save();
            return redirect()->route('inventory.request.index')->withSuccess("Requisição recusada com sucesso!");
        } catch (\Exception $e) {
            return redirect()->route('inventory.request.index')->withErrors($e->getMessage());
        }
    }

    public function report()
    {
        $sap = new Company(false);
        $departamento = $sap->getDB()->table('OUDP')->select('Code as value', 'Remarks as name')->get();
        $requests = new Requests();
        $atendente = User::where('tipo', '=', 'A')->get();
        $solicitante = User::where('tipo', '=', 'S')->get();
        return view("inventory::request.report", compact('departamento', 'requests', 'atendente', 'solicitante'));
    }

    public function print($code)
    {
        $companies = DB::SELECT("SELECT * FROM companies");
        $id = $companies[0]->id;
        $img = DB::SELECT("SELECT TOP 1 diretory FROM uploads WHERE idReference = '{$id}' order by id desc");
        $user = DB::SELECT("SELECT T1.name as solicitante, T2.name as atendente, T0.requiredDate, T0.documentDate, T0.description, T0.description2 FROM requests T0
                      JOIN users T1 on T1.id = T0.requesterUser JOIN users T2 on T2.id = T0.clerkUser");
        $producs = DB::SELECT("SELECT T0.id,T0.codSAP,T0.idRequest,T0.quantityRequest FROM request_products T0 JOIN requests T1 on T0.idRequest = T1.id where T1.code = '{$code}'");
        $items = $this->getNameItem($producs);
        $pdf = \PDF1::setOptions([
            'uplouds' => true
        ])
            ->loadView('relatorios.request', compact('img', 'code', 'items', 'user'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('pdf.pdf');
    }
    public function reportGenerate(Request $request)
    {

        $data = [
            'code' => $request->code ?? 'NULL',
            'requester' => $request->solicitante ?? 'NULL',
            'clerk' => $request->atendente ?? 'NULL',
            'initialDate' => $request->data_ini ?? '2015-01-01',
            'lastDate' => $request->data_fim ?? date('Y-m-d'),
            'docStatus' => $request->status ?? 'NULL'
        ];

        if($request->tipo == 1){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryRequests-Sintetico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_order';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryRequests-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }else if($request->tipo == 2){

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryRequests-Analitico.jasper";
            $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'purchase_order';
            $output = public_path('/relatorios'.'/'.$file_name);
    
            if(!file_exists($relatory_model)){
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryRequests-Analitico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);

        }

        ini_set('max_execution_time', 0);
        $requisition = Requests::orderBy('id', 'desc');
        $req = new Requests();
        if (!is_null($request->get('status'))) {
            $requisition->where('codStatus', '=', $request->get('status'));
        }
        if (!is_null($request->get('code'))) {
            $requisition->where('code', '=', $request->get('code'));
        }
        if (!is_null($request->get('atendente'))) {
            $requisition->where('clerkUser', '=', $request->get('atendente'));
        }
        if (!is_null($request->get('solicitante'))) {
            $requisition->where('requesterUser', '=', $request->get('solicitante'));
        }

        if (!is_null($request->get('data_ini')) && !is_null($request->get('data_fim'))) {
            $requisition->where('requiredDate', '>=', $request->get('data_ini'));
            $requisition->where('requiredDate', '<=', $request->get('data_fim'));
        }
        $tipo = $request->get('tipo');

        $items = $this->getComplementarSAP($requisition->get(), new Requests());

        /*$pdf = \PDF1::setOptions([
            'uplouds'=>true ])->loadView('relatorios.internalRequisition',compact('items', 'tipo', 'req'))
            ->setPaper('a4','portrait');
        return  $pdf->stream('pdf.pdf');*/

        /** @var PdfWrapper $pdf */
        $pdf = SnappyPdf::loadView('relatorios.internalRequisition', compact('items', 'tipo', 'req'));
        //$pdf->setOption('footer-html', route('report.footer'));
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-top', 10);
        return $pdf->inline('report.pdf');
    }

    private function getComplementarSAP($items, $status)
    {
        $newArray = [];
        try {
            foreach ($items as $key => $value) {
                $sap = new Company(false);
                $requesterUser = User::find($value->requesterUser)->userClerk;
                $newArray[$key]['id'] = $value->id;
                $newArray[$key]['code'] = $value->code;
                $newArray[$key]['requiredDate'] = $value->requiredDate;
                $newArray[$key]['status'] = $status::TEXT_STATUS[$value->codStatus];
                $name = $sap->query("SELECT (T0.[firstName]+''+T0.[middleName]+' '+T0.[lastName]) as solicitante FROM OHEM T0 WHERE T0.[empID]  = '{$requesterUser}'");
                $newArray[$key]['solicitante'] = isset($name[0]['solicitante']) ? $name[0]['solicitante'] : '';
                #$name2 = $sap->query("SELECT T1.[Remarks] FROM OHEM T0  INNER JOIN OUDP T1 ON T0.[dept] = T1.[Code] WHERE T0.[empID]  = '{$requesterUser}'");
                #$newArray[$key]['solicitDep'] = isset($name1[0]['Remarks']) ? $name1[0]['Remarks']: '';

                if (isset(User::find($value->clerkUser)->userClerk)) {
                    $clerkUser = User::find($value->clerkUser)->userClerk;
                    $name2 = $sap->query("SELECT (T0.[firstName]+' '+T0.[middleName]+' '+T0.[lastName]) as atendente FROM OHEM T0 WHERE T0.[empID]  = '{$clerkUser}'");
                    $newArray[$key]['atendente'] = isset($name[0]['atendente']) ? $name[0]['atendente'] : '';
                    #$newArray[$key]['atendenteDep'] = $sap->query("SELECT T1.[Remarks] FROM OHEM T0  INNER JOIN OUDP T1 ON T0.[dept] = T1.[Code] WHERE T0.[empID]  = '{$clerkUser}'")[0]['Remarks'];
                } else {
                    $newArray[$key]['atendente'] = '';
                    #$newArray[$key]['atendenteDep'] ='';
                }
            }
        } catch (\Throwable $th) {
            $newArray = [];
        }

        return $newArray;
    }

    public function filter(Request $request)
    {
        try {
            $request->flash();

            $data = Requests::leftJoin('users as T1', 'T1.id', '=', 'requests.requesterUser')
                ->leftJoin('users as T2', 'T2.id', '=', 'requests.clerkUser')
                ->select('requests.id as idRequest', 'requests.codSAP', 'requests.codStatus', 'requests.code', 'requests.documentDate', 'requests.documentDate as requiredDate', 'T1.name', 'T2.name as atendente');

            if (!is_null($request->codWeb)) {
                $data->where('requests.code', 'like', "%{$request->codWeb}%");
            }
            if (!is_null($request->name)) {
                $data->where('T1.id', '=', "{$request->name}");
            }
            if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
                $data->where('requests.documentDate', '>=', "{$request->data_fist}");
                $data->where('requests.documentDate', '<=', "{$request->data_last}");
            }
            if (!is_null($request->status) && ($request->status != '-1')) {
                $data->where('requests.codStatus', '=', "{$request->status}");
            }
            if (auth()->user()->tipo == 'S') {
                $data->where('requests.requesterUser', '=', auth()->user()->id);
            }

            $requests = new Requests();
            $data = $data->orderBy('requests.id', 'desc')->paginate(30)->appends(request()->query());
            return view("inventory::request.index", ['busca' => $data, 'requests' => $requests]);
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0009', 'Listando a entrada de mercadoria', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    private function saveProducts($request, $cod)
    {
        //dd($request->requiredProducts);
        foreach ($request->requiredProducts as $keys => $value) {
            if (isset($value['codSAP'])) {
                $products = new Products();
                $products->codSAP = $value['codSAP'];
                $products->idRequest = $cod;
                $products->quantityRequest = $value['qtd'];
                $products->quantityServed = 0;
                $products->costCenter = $value['centroCusto'];
                $products->deposit = $value['deposit'];
                $products->project = $value['projeto'];
                $products->status = 0;
                $products->save();
            }
        }
    }

    public function cancel($id)
    {
        try {
            $request = Requests::find($id);
            if($request->cancel()){
                return view("inventory::request.view", $this->option($id));
            }
            return redirect()->back()->withErrors("Não foi possível cancelar a requisição interna!");
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0988', 'Erro ao cancelar requisição interna', $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }


    public function forceExit($id)
    {
        try {

            $rq = Requests::find($id);
            if(!empty($rq->id)){
                #OutputToSAP::dispatch($rq);
                $obj = new Requests();
                $obj->saveOutputInSAP($rq);
                // SetInSAPOutputRequest::dispatch($rq);
                return redirect()->back()->withSuccess('PASSOU');
            }
            throw new Exception("Documento inexistente.");

        } catch (\Exception $e) {
            $rq->message = $e->getMessage();
            $rq->is_locked = true;
            $rq->save();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
