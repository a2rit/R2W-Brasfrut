<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\Item\Item;
use App\Upload;
use Carbon\Carbon;
use App\logsError;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\Remove\rItem;
use App\Jobs\Set\SAPItem;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;

use App\Modules\Inventory\Models\Item\Approve;

use App\JasperReport;

class ItemsController extends Controller
{

    public function index()
    {
        $sap = new Company(false);

        $items = $sap->getDb()
            ->table('OITM')
            ->select('OITM.ItemCode', 'ItemName', DB::raw("CASE WHEN validFor = 'Y' THEN 'ATIVO' WHEN validFor = 'N' THEN 'INATIVO' END as Status"))
            ->orderBy('OITM.ItemCode', 'desc')
            ->paginate(30);
        $itemGroups = $sap->getDb()
            ->table('OITB')
            ->select('ItmsGrpCod as value', 'ItmsGrpNam as name')
            ->get();
        $subGroups = $sap->getDb()->table('OMRC')->select('FirmCode as value', 'FirmName as name')->get();
        $warehouses = $sap->getDb()
            ->table('OWHS')
            ->select('WhsCode as value', 'WhsName as name')
            ->get();
        return view('inventory::items.index', compact('items', 'itemGroups', 'warehouses', 'subGroups'));
    }

    public function create()
    {
        return view("inventory::items.create", $this->getFormOptions());
    }

    public function store(Request $request)
    {
        try {
            $item = new Item();
            return Response()->json($item->saveInSAP($request));
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('ITM002', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return Response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $sap = new Company(false);
            $itemInstance = new Item();

            $item = $sap->getDb()->table('OITM')->select(
                "ItemCode",
                "NCMCode",
                "CardCode",
                "ItemName",
                "ItmsGrpCod",
                "FirmCode",
                "ItemType",
                "InvntItem",
                "SellItem",
                "PrchseItem",
                "ItemClass",
                "ProductSrc",
                "MatGrp",
                "MatType",
                "OSvcCode",
                "ISvcCode",
                "BuyUnitMsr",
                "NumInBuy",
                "PurPackMsr",
                "PurPackUn",
                "SalUnitMsr",
                "NumInSale",
                "SalPackMsr",
                "SalPackUn",
                "GLMethod",
                "DfltWH",
                "InvntryUom",
                "UserText",
                'PlaningSys',
                'PrcrmntMtd',
                'CompoWH',
                'Series',
                "validFor",
                "QryGroup1",
                "QryGroup2",
                "QryGroup3",
                "QryGroup4",
                "QryGroup5",
                "QryGroup6",
                "QryGroup7",
                "QryGroup8",
                "QryGroup9",
                "QryGroup10",
                "QryGroup11",
                "QryGroup12",
                "QryGroup13",
                "QryGroup14",
                "QryGroup15",
                "QryGroup16",
                "QryGroup17",
                "QryGroup18",
                "QryGroup19",
                "QryGroup20",
                "QryGroup21",
                "QryGroup22",
                "QryGroup23",
                "QryGroup24",
                "QryGroup25",
                "QryGroup26",
                "QryGroup27",
                "QryGroup28",
                "QryGroup29",
                "QryGroup30",
                "QryGroup31",
                "QryGroup32",
                "QryGroup33",
                "QryGroup34",
                "QryGroup35",
                "QryGroup36",
                "QryGroup37",
                "QryGroup38",
                "QryGroup39",
                "QryGroup40",
                "QryGroup41",
                "QryGroup42",
                "QryGroup43",
                "QryGroup44",
                "QryGroup45",
                "QryGroup46",
                "QryGroup47",
                "QryGroup48",
                "QryGroup49",
                "QryGroup50",
                "QryGroup51",
                "QryGroup52",
                "QryGroup53",
                "QryGroup54",
                "QryGroup55",
                "QryGroup56",
                "QryGroup57",
                "QryGroup58",
                "QryGroup59",
                "QryGroup60",
                "QryGroup61",
                "QryGroup62",
                "QryGroup63",
                "QryGroup64"
            )
                ->where('ItemCode', "=", $id)->first();

            $ncm = $this->getNCM($item->NCMCode);
            //$cest = $this->getCEST($item->U_SKILL_CEST);
            $cest = null;

            $lastProviders = $sap->query("SELECT TOP 10  T0.[CardName], T0.[DocEntry], T0.[CardCode], T0.[TaxDate], T1.[Price]  FROM OPCH T0  
            INNER JOIN PCH1 T1 ON T0.DocEntry = T1.DocEntry 
            WHERE  
            T1.[ItemCode] = '{$id}' and 
            T0.DocEntry NOT IN (SELECT T4.BaseEntry FROM ORPC T3 INNER JOIN RPC1 T4 ON T3.DocEntry = T4.DocEntry
            WHERE T4.BaseEntry IS NOT NULL and  T3.SeqCode = 1) ORDER BY T0.[DocDate] desc
            ");

            $preferenceSuplier = !empty($item->CardCode) ? $itemInstance->getSupplierLabel($item->CardCode) : null;
            $warehouse = $sap->getDb()->table('OITW')->select(
                'OITW.WhsCode',
                'OWHS.WhsName',
                'OITW.AvgPrice',
                'OITW.Locked',
                'OITW.OnHand',
                'OITW.IsCommited',
                'OITW.OnOrder',
                'OITW.MinStock',
                'OITW.MaxStock',
                'OITW.ExpensesAc',
                'OITW.ReturnAc'
            )
                ->leftJoin('OWHS', 'OWHS.WhsCode', '=', 'OITW.WhsCode')
                ->where('ItemCode', "=", $id)->get();

            $prices = $itemInstance->getPriceList($item->ItemCode);
            return view("inventory::items.edit", array_merge(compact("item", "prices", "ncm", "cest", "lastProviders", "preferenceSuplier", "warehouse"), $this->getFormOptions()));
        } catch (\Exception $e) {
            return redirect()->route('inventory.items.index')->withErrors($e->getMessage());
        }
    }

    public function printMovement(Request $request)
    {
        $report = new JasperReport();
        $relatory_model = storage_path('app/public/relatorios_modelos') . "/ItemMovement.jasper";

        if (!file_exists($relatory_model)) {
            $relatory_model = storage_path('app/public/relatorios_modelos') . "/ItemMovement.jrxml";
        }

        $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'item-movement';
        $output = public_path('/relatorios' . '/' . $file_name);
        $report = $report->generateReport($relatory_model, $output, ['pdf'], ['ItemCode' => $request->itemCode, 'initialDate' => $request->initialDate, 'lastDate' => $request->lastDate], 'pt_BR', 'sap');

        return response()->file($report)->deleteFileAfterSend(true);
    }

    public function anyData(Request $request)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OITM");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit(10);
        $columns = $request->get("columns");
        $columnsToSelect = ['ItemCode', 'ItemName', 'U_R2W_CODE'];

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("ItemCode", "like", "%{$search['value']}%")
                ->orWhere("U_R2W_CODE", "like", "%{$search['value']}%")
                ->orWhere("ItemName", "like", "%{$search['value']}%");
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

    public function getNCM($ncm)
    {
        $sap = new Company(false);
        return $sap->getDb()->table('ONCM')->select('AbsEntry as value', DB::raw("concat(NcmCode, ' - ', Descrip) as name"))
            ->where('AbsEntry', "=", $ncm)->first();
    }

    public function getCEST($cest)
    {
        $sap = new Company(false);
        return $sap->query("SELECT T0.[U_SKILL_COD_CEST], T0.[U_SKILL_DESC_CEST] FROM [@SKILL_CEST] T0 WHERE T0.[U_SKILL_COD_CEST] = '$cest'");
    }

    public function ncmSearch(Request $request)
    {
        $sap = new Company(false);
        $ncmCodes = $sap->getDb()->table('ONCM')->select('AbsEntry as value', DB::raw("concat(NcmCode, ' - ', Descrip) as name"))
            ->orWhere('Descrip', 'like', "%{$request->get("q")}%")
            ->orWhere('NcmCode', 'like', "%{$request->get("q")}%")
            ->limit(7)->get();
        return response()->json($ncmCodes);
    }

    public function cestSearch(Request $request)
    {
        $sap = new Company(false);
        $cestCodes = $sap->getDb()->table('OCEST')->select('AbsId as value', DB::raw("concat(CEST, ' - ', Descr) as name"))
            ->orWhere('Descr', 'like', "%{$request->get("q")}%")
            ->orWhere('AbsId', 'like', "%{$request->get("q")}%")
            ->limit(7)->get();
        return response()->json($cestCodes);
    }

    public function suppliersSearch(Request $request)
    {
        $sap = new Company(false);
        $suppliers = $sap->getValidItemQueryBuilder('OCRD')
            ->where('CardType', '=', 'S')
            ->where(\DB::raw("concat(CardCode, ' - ', CardName)"), 'like', "%{$request->get('q')}%")
            ->limit(7)
            ->orderBy('CardCode')
            ->get(['CardCode as value', \DB::raw("concat(CardCode, ' - ', CardName) as name")]);
        return response()->json($suppliers);
    }

    protected function getFormOptions()
    {
        $sap = new Company(false);
        $itemGroups = $sap->getDb()->table('OITB')->select('ItmsGrpCod as value', 'ItmsGrpNam as name')->get();
        $subGroups = $sap->getDb()->table('OMRC')->select('FirmCode as value', 'FirmName as name')->get();
        $priceList = $sap->getDb()->table('OPLN')->select('ListNum as value', 'ListName as name')->get();
        $manufacturers = $sap->getDb()->table('OMRC')->select('FirmCode as value', 'FirmName as name')->get();
        $ncmCodes = $sap->getDb()->table('ONCM')->select('AbsEntry as value', DB::raw("concat(NcmCode, ' - ', Descrip) as name"))->get();
        $dnfCodes = $sap->getDb()->table('ODNF')->select('AbsEntry as value', 'DNFCode as name')->get();
        $materialGroups = $sap->getDb()->table('OMGP')->select('AbsEntry as value', DB::raw("concat(MatGrp, ' - ', Descrip) as name"))->get();
        $productSources = $sap->getDb()->table('OPSC')->select('Code as value', DB::raw("concat(Code, ' - ', [Desc]) as name"))->get();
        $adm1 = $sap->getDb()->table('ADM1')->select('County')->first();
        $serviceCodesContrated = $sap->getDb()->table('OSCD')
            ->select('AbsEntry as value', DB::raw("concat(ServiceCD, ' - ', Descrip) as name"))
            ->where('Incomimg', 'Y')->get();
        $serviceCodes = $sap->getDb()->table('OSCD')
            ->select('AbsEntry as value', DB::raw("concat(ServiceCD, ' - ', Descrip) as name"))
            ->where('Incomimg', 'N')->get();
        //$serviceGroups = $sap->getDb()->table('OSGP')->select('AbsEntry as value', DB::raw("concat(ServiceGrp, ' - ',Descrip) as name"))->get();
        $warehouses = $sap->getDb()->table('OWHS')->select('WhsCode as value', 'WhsName as name')->get();
        $series = $sap->getDb()->table('NNM1')->select('Series as value', 'SeriesName as name')->where(['ObjectCode' => '4'])->get();
        $itemProperties = $sap->getDb()->table('OITG')->select('ItmsTypCod as value', 'ItmsGrpNam as name')->get();
        $cests = $sap->query("SELECT T0.[U_SKILL_COD_CEST], T0.[U_SKILL_DESC_CEST] FROM [@SKILL_CEST] T0");

        $itemTypes = [
            ["value" => 0, "name" => "Item"], ["value" => 1, "name" => "Mão de obra"], ["value" => 2, "name" => "Viagens"], /*["value" => 3, "name" => "Fixed"]*/
        ];
        $materialTypes = [
            ["value" => 0, "name" => "0 - Mercadoria para revenda"],
            ["value" => 1, "name" => "1 - Matéria-prima"],
            ["value" => 2, "name" => "2 - Embalagem"],
            ["value" => 3, "name" => "3 - Mercadorias em processo"],
            ["value" => 4, "name" => "4 - Produtos acabados"],
            ["value" => 5, "name" => "5 - Subproduto"],
            ["value" => 6, "name" => "6 - Produto intermediário"],
            ["value" => 7, "name" => "7 - Material de uso e consumo"],
            ["value" => 8, "name" => "8 - Ativo imobilizado"],
            ["value" => 9, "name" => "9 - Serviços"],
            ["value" => 10, "name" => "10 - Outros insumos"],
            ["value" => 99, "name" => "99 - Outras"],
        ];
        $glMethods = [
            ["value" => 0, "name" => "Depósito"],
            ["value" => 1, "name" => "Grupo de itens"],
            ["value" => 2, "name" => "Nível do item"]
        ];
        $unitsMeasurement = [
            ["value" => 'AMPOLA', "name" => "AMPOLA"],
            ["value" => 'BALDE', "name" => "BALDE"],
            ["value" => 'BANDEJ', "name" => "BANDEJ"],
            ["value" => 'BARRA', "name" => "BARRA"],
            ["value" => 'BISNAG', "name" => "BISNAG"],
            ["value" => 'BLOCO', "name" => "BLOCO"],
            ["value" => 'BOBINA', "name" => "BOBINA"],
            ["value" => 'BOMB', "name" => "BOMB"],
            ["value" => 'CAPS', "name" => "CAPS"],
            ["value" => 'CART', "name" => "CART"],
            ["value" => 'CENTO', "name" => "CENTO"],
            ["value" => 'CJ', "name" => "CJ"],
            ["value" => 'CM', "name" => "CM"],
            ["value" => 'CM2', "name" => "CM2"],
            ["value" => 'CX', "name" => "CX"],
            ["value" => 'CX2', "name" => "CX2"],
            ["value" => 'CX3', "name" => "CX3"],
            ["value" => 'CX5', "name" => "CX5"],
            ["value" => 'CX10', "name" => "CX10"],
            ["value" => 'CX15', "name" => "CX15"],
            ["value" => 'CX20', "name" => "CX20"],
            ["value" => 'CX25', "name" => "CX25"],
            ["value" => 'CX50', "name" => "CX50"],
            ["value" => 'CX100', "name" => "CX100"],
            ["value" => 'DISP', "name" => "DISP"],
            ["value" => 'DUZIA', "name" => "DUZIA"],
            ["value" => 'EMBAL', "name" => "EMBAL"],
            ["value" => 'FARDO', "name" => "FARDO"],
            ["value" => 'FOLHA', "name" => "FOLHA"],
            ["value" => 'FRASCO', "name" => "FRASCO"],
            ["value" => 'GALAO', "name" => "GALAO"],
            ["value" => 'GF', "name" => "GF"],
            ["value" => 'GRAMAS', "name" => "GRAMAS"],
            ["value" => 'JOGO', "name" => "JOGO"],
            ["value" => 'KG', "name" => "KG"],
            ["value" => 'KIT', "name" => "KIT"],
            ["value" => 'LATA', "name" => "LATA"],
            ["value" => 'LITRO', "name" => "LITRO"],
            ["value" => 'M', "name" => "M"],
            ["value" => 'M2', "name" => "M2"],
            ["value" => 'M3', "name" => "M3"],
            ["value" => 'MILHEI', "name" => "MILHEI"],
            ["value" => 'ML', "name" => "ML"],
            ["value" => 'MWH', "name" => "MWH"],
            ["value" => 'PACOTE', "name" => "PACOTE"],
            ["value" => 'PALETE', "name" => "PALETE"],
            ["value" => 'PARES', "name" => "PARES"],
            ["value" => 'PC', "name" => "PC"],
            ["value" => 'POTE', "name" => "POTE"],
            ["value" => 'K', "name" => "K"],
            ["value" => 'RESMA', "name" => "RESMA"],
            ["value" => 'ROLO', "name" => "ROLO"],
            ["value" => 'SACO', "name" => "SACO"],
            ["value" => 'SACOLA', "name" => "SACOLA"],
            ["value" => 'TAMBOR', "name" => "TAMBOR"],
            ["value" => 'TANQUE', "name" => "TANQUE"],
            ["value" => 'TON', "name" => "TON"],
            ["value" => 'TUBO', "name" => "TUBO"],
            ["value" => 'UNID', "name" => "UNID"],
            ["value" => 'UND', "name" => "UND"],
            ["value" => 'VASIL', "name" => "VASIL"],
            ["value" => 'VIDRO', "name" => "VIDRO"],
        ];

        return compact(
            "itemGroups",
            "subGroups",
            "itemTypes",
            "priceList",
            "series",
            "itemProperties",
            "manufacturers",
            "ncmCodes",
            "dnfCodes",
            "materialGroups",
            "productSources",
            "materialTypes",
            "serviceCodesContrated",
            "serviceCodes",
            "warehouses",
            "glMethods",
            "unitsMeasurement",
            'cests'
        );
    }

    public function getWHS($itemCode)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OITW")
            ->join("OWHS", "OITW.WhsCode", "=", "OWHS.WhsCode")
            ->orWhere("OITW.ItemCode", "=", "{$itemCode}");
        $recordsTotal = $query->count();
        $columnsToSelect = ['OITW.WhsCode', 'OITW.OnHand', 'OWHS.WhsName'];

        return response()->json([
            "draw" => $recordsTotal,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->get($columnsToSelect)
        ]);
    }
    public function getWHSEmprestimo($itemCode)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OITW")
            ->join("OWHS", "OITW.WhsCode", "=", "OWHS.WhsCode")
            ->orWhere("OITW.ItemCode", "=", "{$itemCode}")
            ->where("OWHS.WhsCode", '06');
        $recordsTotal = $query->count();
        $columnsToSelect = ['OITW.WhsCode', 'OITW.OnHand', 'OWHS.WhsName'];

        return response()->json([
            "draw" => $recordsTotal,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->get($columnsToSelect)
        ]);
    }

    public function remove($id)
    {
        try {
            $item = Item::find($id);
            if (is_null($item->codSAP)) {
                $item->delete()->where('id', $item->id);
                return redirect()->route('inventory.items.index')->withSuccess('Item Removido com Sucesso!');
            } else {
                rItem::dispatch($item);
                return redirect()->route('inventory.items.index')->withSuccess('Processando Remoção do Item no SAP!');
            }
        } catch (\Exception $e) {
            return redirect()->route('inventory.items.index')->withErrors($e->getMessage());
        }
    }

    public function relatory()
    {
        return view('inventory::items.relatory');
    }

    public function relatoryFilter(Request $request)
    {
        try {
            $sap = new Company(false);
            if ($request->get('type') == '1') {
                $sql = $sap->getDb()->table('OITW')
                    ->join('OITM', 'OITW.ItemCode', '=', 'OITM.ItemCode')
                    ->join('OWHS', 'OWHS.WhsCode', '=', 'OITW.WhsCode')
                    ->select('OITW.ItemCode', 'OITM.ItemName', DB::raw('sum(OITW.OnHand)as OnHand'));

                if (!is_null($request->get('item'))) {
                    $sql->whereIn('OITW.ITEMCODE', $request->get('item'));
                }
                $sql->groupBy('OITW.ItemCode', 'OITM.ItemName');
            } else if ($request->get('type') == '2') {
                $sql = $sap->getDb()->table('OITW')
                    ->join('OITM', 'OITW.ItemCode', '=', 'OITM.ItemCode')
                    ->join('OWHS', 'OWHS.WhsCode', '=', 'OITW.WhsCode')
                    ->select('OITW.ItemCode', 'OITM.ItemName', DB::raw("CONCAT(OITW.WhsCode, ' - ', OWHS.WhsName) as WhsCode"), 'OITW.OnHand');
                if (!is_null($request->get('item'))) {
                    $sql->whereIn('OITW.ITEMCODE', $request->get('item'));
                }
            }
            $tipo = $request->type;
            $body = $sql->get();
            $company = \App\Modules\Settings\Models\Company::orderBy('id', 'desc')->first();
            $img = Upload::where(['reference' => 'companies', 'idReference' => $company->id])->orderBy('id', 'desc')->first();
            return \PDF1::setOptions(['uplouds' => true])->loadView('relatory.layouts.items', compact('tipo', 'body', 'img', 'company'))->setPaper('a4', 'portrait')->stream('pdf.pdf');
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0007', 'Listando o entrada de mercadoria', $e->getMessage());
            return view('inventory::items.index')->withErrors($e->getMessage());
        }
    }

    public function relatoryAll()
    {
        try {
            $sap = new Company(false);

            $sql = $sap->getDb()->table('OITW')
                ->join('OITM', 'OITW.ItemCode', '=', 'OITM.ItemCode')
                ->join('OWHS', 'OWHS.WhsCode', '=', 'OITW.WhsCode')
                ->select('OITW.ItemCode', 'OITM.ItemName', DB::raw("CONCAT(OITW.WhsCode, ' - ', OWHS.WhsName) as WhsCode"), 'OITW.OnHand');
            $body = $sql->get();
            $company = \App\Modules\Settings\Models\Company::orderBy('id', 'desc')->first();
            $img = Upload::where(['reference' => 'companies', 'idReference' => $company->id])->orderBy('id', 'desc')->first();
            return \PDF1::setOptions(['uplouds' => true])->loadView('relatory.layouts.items', compact('body', 'img', 'company'))->setPaper('a4', 'portrait')->stream('pdf.pdf');
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E9+06FA', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return view('inventory::items.index')->withErrors($e->getMessage());
        }
    }

    public function exportFilterExcel(Request $request){

        $data = [
            "itemCode" => $request->codSAP,
            "itemName" => $request->name,
            "ItmsGrpCod" => $request->group,
            "whsCode" => $request->warehouse,
            "validFor" => $request->status,
        ];

        $report = new JasperReport();
        $relatory_model = storage_path('app/public/relatorios_modelos')."/ItemsExcel.jasper";
        
        if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/ItemsExcel.jrxml";
        }

        $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'items';
        $output = public_path('/relatorios'.'/'.$file_name);
        $report = $report->generateReport($relatory_model, $output, ['xls'], $data, 'pt_BR', 'sap');
        
        return response()->download($report)->deleteFileAfterSend(true);
    }

    public function itemsSearch(Request $request)
    {
        $sap = new Company(false);
        if(!empty($request->get('query'))){ //autocomplete plugin
          $name = $request->get("query");
          $items = $sap->getDb()->table('OITM')
            ->select('ItemName as value', 'ItemName as data')
            ->where('ItemCode', 'like', "%{$name}%")
            ->orWhere('ItemName', 'like', "%{$name}%")
            ->limit(50)
            ->get();

           return response()->json(["query" => $name, "suggestions" => $items]);
        }

        $items = $sap->getDb()->table('OITM')
            ->select('ItemCode as value', DB::raw("concat(ItemCode, ' - ', ItemName) as name"))
            ->where('ItemCode', 'like', "%{$request->get("q")}%")
            ->orWhere('ItemName', 'like', "%{$request->get("q")}%")
            ->limit(50)
            ->get();

        return response()->json($items);
    }

    public function filter(Request $request)
    {
        try {
            $sap = new Company(false);
            $request->flash();

            $items = $sap->getDb()
                ->table('OITM')
                ->select('OITM.ItemCode', 'OITM.ItemName', DB::raw("CASE WHEN validFor = 'Y' THEN 'ATIVO' WHEN validFor = 'N' THEN 'INATIVO' END as Status"));
            $itemGroups = $sap->getDb()->table('OITB')->select('ItmsGrpCod as value', 'ItmsGrpNam as name')->get();
            $warehouses = $sap->getDb()->table('OWHS')->select('WhsCode as value', 'WhsName as name')->get();
            $subGroups = $sap->getDb()->table('OMRC')->select('FirmCode as value', 'FirmName as name')->get();

            if (!is_null($request->codSAP)) {
                $items->where('OITM.ItemCode', 'like', "%$request->codSAP%");
            }
            if (!is_null($request->name)) {
                $items->where('OITM.ItemName', 'like', "%$request->name%");
            }
            if (!is_null($request->group)) {
                $items->where('OITM.ItmsGrpCod', 'like', "%$request->group%");
            }
            if (!is_null($request->subGroup)) {
                $items->where('OITM.FirmCode', '=', "{$request->subGroup}");
            }
            if (!is_null($request->warehouse)) {
                $items->rightJoin('OITW', 'OITW.ItemCode', '=', 'OITM.ItemCode')
                ->where('OITW.WhsCode', '=', $request->warehouse);
            }
            if (!is_null($request->status)) {
                $items->where('OITM.validFor', '=', $request->status);
            }
            
            $items = $items->orderBy('OITM.ItemCode', 'desc')->distinct(['OITM.ItemCode']);
            $items = $items->paginate(30)->appends(request()->query());
            return view('inventory::items.index', compact('items', 'itemGroups', 'subGroups', 'warehouses'));
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0008', 'Listando a entrada de mercadoria', $e->getMessage());
            return view('inventory::items.index')->withErrors($e->getMessage());
        }
    }

    public function duplicate(Request $request)
    {
    }
}
