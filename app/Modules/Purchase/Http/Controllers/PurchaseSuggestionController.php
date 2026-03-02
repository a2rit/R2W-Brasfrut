<?php

namespace App\Modules\Purchase\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;
use App\SapUtilities;
use App\ConfigSAP;
use App\LogsError;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Inventory\Models\item\Item;


class PurchaseSuggestionController extends Controller
{   
    use SapUtilities;


    public function index(){
        
        $sap = new Company(false);
        $items = $sap->getDb()
            ->table("SUGESTAO_COMPRA AS T0")
            ->select("T0.ItemCode", 
                        "T0.ItemName", 
                        "T0.BuyUnitMsr",
                        "T0.InvntryUom",
                        "T0.NumInBuy",
                        "T0.CardCode", 
                        "T0.ItmsGrpNam",
                        "T0.FirmCode",
                        "T0.WhsCode",
                        "T0.OnHand",
                        "T0.MinLevel",
                        "T0.MaxLevel",
                        "T0.Sugestao_Compra",
                        "T0.IsCommited",
                        "T0.LastPurPrc",
                        "T0.CardName",
                        "T0.WhsName")
            ->whereRaw("T0.DfltWH = T0.WhsCode")
            ->orderBy('T0.ItemName', 'ASC')
            ->paginate(30);
    
        return view('purchase::purchaseSuggestion.index', compact('items'), $this->options());
    }

    public function filter(Request $request){

        $sap = new Company(false);
        $items = $sap->getDb()
            ->table("SUGESTAO_COMPRA AS T0")
            ->select("T0.ItemCode", 
                        "T0.ItemName", 
                        "T0.BuyUnitMsr",
                        "T0.InvntryUom",
                        "T0.NumInBuy",
                        "T0.CardCode", 
                        "T0.ItmsGrpNam", 
                        "T0.FirmCode",
                        "T0.WhsCode",
                        "T0.OnHand",
                        "T0.MinLevel",
                        "T0.MaxLevel",
                        "T0.Sugestao_Compra",
                        "T0.IsCommited",
                        "T0.LastPurPrc",
                        "T0.CardName",
                        "T0.WhsName");

        if(!empty($request->itemCode)){
            // dd(explode(",", implode(",", $request->itemCode)));
            $items->whereIn("T0.ItemCode", $request->itemCode);
        }

        if(!empty($request->group)){
            $items->where("T0.ItmsGrpCod", "=", "{$request->group}");
        }

        if(!empty($request->subGroup)){
            $items->where("T0.FirmCode", "=", "{$request->subGroup}");
        }

        if(!empty($request->warehouse)){
            $items->where("T0.WhsCode", "=", "{$request->warehouse}");
        }else{
            $items->whereRaw("T0.DfltWH = T0.WhsCode");
        }

        if(!empty($request->cardCode)){
            $items->where("T0.CardCode", "=", "{$request->cardCode}");
        }
        
        $items = $items->orderBy('T0.ItemName', 'ASC')->paginate(30)->appends(request()->query());
        $request->flash();
        return view('purchase::purchaseSuggestion.index', compact('items'), $this->options());
    }

    protected function options(){
        $sap = new Company(false);

        $itemGroups = $sap->getDb()->table('OITB')->select('ItmsGrpCod as value', 'ItmsGrpNam as name')->get();
        $subGroups = $sap->getDb()->table('OMRC')->select('FirmCode as value', 'FirmName as name')->get();
        $warehouses = $sap->getDb()->table('OWHS')->select('WhsCode as value', 'WhsName as name')->get();
        $projeto = $this->getProjectOptions($sap);
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $itemModel = new Item;
        $budgetAccountingAccounts = $sap->query("SELECT DISTINCT a.name as value, b.AcctName as name FROM [@A2RORCPC] a INNER JOIN OACT b ON a.Name = b.AcctCode");

        return compact("itemGroups", "subGroups", "warehouses", "projeto", "centroCusto", "centroCusto2", "itemModel", "budgetAccountingAccounts");
    }

    public function save(Request $request){

        try {
            DB::beginTransaction();
                $contador = 0;
                $jump_pointer_projeto = false;
                $jump_pointer_centroCusto = false;
                $jump_pointer_cardCode = false;
                $requester = $request->requester;
                $requriedDate = $request->data;
                $dataLancamento = $request->dataLancamento;

                foreach (array_except(collect($request->requiredProducts)->groupBy(['projeto']), array('')) as $key => $groupByProject) {
                    
                    $jump_pointer_projeto = false;
                    foreach (array_except(collect($groupByProject)->groupBy(['centroCusto']), array('')) as $key => $groupByCentroCusto) {
                        
                        $jump_pointer_centroCusto = false;
                        if($request->documentType == 2){
                            foreach (array_except(collect($groupByCentroCusto)->groupBy(['cardCode']), array('')) as $key => $groupByCardCode) {
                                $jump_pointer_cardCode = false;
                                $purchase_order = new PurchaseOrder;

                                foreach (array_except(collect($groupByCardCode)->groupBy(['centroCusto2']), array('')) as $key => $groupByCentroCusto2) {
                                    $purchase_order->saveInDBSuggestion($this->reorderRequestArray($groupByCentroCusto2, ['cardCode' => $groupByCentroCusto2[0]['cardCode'], 'dataLancamento' => $dataLancamento]));
                                    
                                    $jump_pointer_cardCode = true;
                                    $jump_pointer_centroCusto = true;
                                    $jump_pointer_projeto = true;
                                    $contador++;
                                }

                                if($jump_pointer_cardCode){
                                    continue;
                                }

                                $purchase_order = new PurchaseOrder;
                                $purchase_order->saveInDBSuggestion($this->reorderRequestArray($groupByCardCode, ['cardCode' => $groupByCardCode[0]['cardCode'], 'dataLancamento' => $dataLancamento]));
                                
                                $jump_pointer_centroCusto = true;
                                $jump_pointer_projeto = true;
                                $contador++;
                            }
                        }else{
                            foreach (array_except(collect($groupByCentroCusto)->groupBy(['centroCusto2']), array('')) as $key => $groupByCentroCusto2) {
                                $purchase_request = new PurchaseRequest;
                                $purchase_request->saveInDBSuggestion($this->reorderRequestArray($groupByCentroCusto2, ["data" => $requriedDate, "requester" => $requester]));
                                $jump_pointer_centroCusto = true;
                                $jump_pointer_projeto = true;
                                $contador++;
                            }
                        }

                        if($jump_pointer_centroCusto){
                            continue;
                        }

                        $purchase_request = new PurchaseRequest;
                        $purchase_request->saveInDBSuggestion($this->reorderRequestArray($groupByCentroCusto, ["data" => $requriedDate, "requester" => $requester]));
                        
                        $jump_pointer_projeto = true;
                        $contador++;
                    }

                    if($jump_pointer_projeto){
                        continue;
                    }

                    $purchase_request = new PurchaseRequest;
                    $purchase_request->saveInDBSuggestion($this->reorderRequestArray($groupByProject, ["data" => $requriedDate, "requester" => $requester]));
                }
            DB::commit();
            
            return response()->json(["status" => "success","message" => "Foram criados {$contador} documentos com sucesso!"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["status" => "error", "message" => "{$e->getLine()} - {$e->getMessage()}"], 200);
        }
    }

    public function salesHistory($itemCode){
        try {
            $sap = new Company();
            $query = $sap->query("SELECT
                                    CONCAT(DATEPART(MONTH, T0.TaxDate), '-', DATEPART(YEAR, T0.TaxDate)) AS date,
                                    COUNT(T0.DocNum) AS totalSales,
                                    SUM(T1.Price) / COUNT(T0.DocNum) AS averagePrice,
                                    SUM(T1.Quantity) AS quantityTotal
                                FROM OINV T0
                                JOIN INV1 T1 ON T1.DocEntry = T0.DocNum
                                WHERE T0.TaxDate >= DATEADD(MONTH, DATEDIFF(MONTH, 0, GETDATE())-3, 0)
                                AND T0.TaxDate <  DATEADD(MONTH, DATEDIFF(MONTH, 0, GETDATE()), 0)
                                AND T1.ItemCode = :itemCode
                                GROUP BY
                                    DATEPART(MONTH, T0.TaxDate),
                                    DATEPART(YEAR, T0.TaxDate)", ["itemCode" => $itemCode]);
            return response()->json($query);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    private function reorderRequestArray($array, $extraValues){
        $data = (Array)[
            "requiredProducts" => []
        ];

        foreach($extraValues as $key => $value){
            $data[$key] = $value;
        }


        foreach($array as $key => $value){
            array_push($data["requiredProducts"], [
                "codSAP" => $value["codSAP"],
                "itemName" =>  $value["itemName"],
                "itemUnd" => $value["itemUnd"],
                "wareHouseCode" => $value["wareHouseCode"],
                "projeto" => $value["projeto"],
                "centroCusto" => $value["centroCusto"],
                "centroCusto2" => $value["centroCusto2"],
                "qtd" => $value["qtd"],
                "price" => $value["price"] ?? 0,
            ]);
        }

        return (Object)$data;
    }
}
