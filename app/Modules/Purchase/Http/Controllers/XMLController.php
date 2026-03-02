<?php

namespace App\Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;

use App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Partners\Models\Partner;
use App\Modules\Partners\Models\Partner\Catalog;
use App\Modules\Purchase\Models\XML\Import;
use App\Modules\Purchase\Models\XML\Items;
use App\Modules\Inventory\Models\Item;
use App\Jobs\SetInSAPPartners;
use App\Jobs\Set\SAPItem;
use App\Jobs\Set\SAPCatalogPartners;
use App\Jobs\SetInSAPReceiptGoods;
use App\SapUtilities;
use App\logsError;
use App\Upload;
use App\Modules\Settings\Models\Company as DBCompany;

class XMLController extends Controller
{
    use SapUtilities;
/*
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!checkAccess('import_xml')) {
                return redirect()->route('home')->withErrors(auth()->user()->name . ' você não possui acesso! consulte o Admin do Sistema');
            } else {
                return $next($request);
            }
        });
    } */

    public function import()
    {
        $query = Import::join('xml_partners', 'xml_imports.id', '=', 'xml_partners.idImportXML')
            ->select('xml_imports.codSAP', 'xml_imports.id', 'xml_imports.nNF', 'xml_imports.taxDate', 'xml_partners.name', 'xml_partners.cnpj', 'xml_imports.created_at', 'xml_imports.docTotal')
            ->orderBy('xml_imports.id', 'desc')
            ->where('xml_imports.status', '=', Import::STATUS_CLOSE)
            ->get();
        return view('purchase::XML.import', ['items' => $query]);
    }

    private function saveXML($xml)
    {
        $search = Import::where('chNFe', 'like', $xml->protNFe->infProt->chNFe)->get();
        if (!isset($search[0]->chNFe)) {
            $import = new Import();
            $import->idUser = auth()->user()->id;
            $import->chNFe = $xml->protNFe->infProt->chNFe;
            $import->comments = $xml->NFe->infNFe->infAdic;
            $import->save();
            return true;
        } else {
            return false;
        }

    }

    public function load(Request $request)
    {
        try {
            $xml = simplexml_load_file($request->file('Fichier1'));
            $sCompany = DBCompany::orderBy('id', 'desc')->get();
            $fornecedor = $xml->NFe->infNFe->emit->xNome[0];
            $cnpj = $xml->NFe->infNFe->emit->CNPJ[0];
            if (formatCNPJ($xml->NFe->infNFe->dest->CNPJ) == $sCompany[0]->cnpj) {
                $importXML = new Import();
                if ($importXML->saveInDB($xml)) {
                    $partners = new Partner();
                    if (!Partner::getCardCode(new Company(false), formatCNPJ($cnpj[0]))) {
                        DB::beginTransaction();
                        $sap = new Company(false);
                        $groups = $this->getGroupOptions($sap, 'S');
                        $partners->idUser = auth()->user()->id;
                        $partners->name = $fornecedor[0];
                        $partners->fantasy_name = $fornecedor[0];
                        #$partners->code = $partners->createCode();
                        $partners->cnpj = formatCNPJ($cnpj[0]);
                        $partners->type = 1;
                        $partners->group = $groups[0]['value'];
                        $partners->save();

                        $pa = new Partner\Address();
                        $pa->saveFromXML($partners->id, $xml->NFe->infNFe->emit->enderEmit, 0);
                        $npa = new Partner\Address();
                        $npa->saveFromXML($partners->id, $xml->NFe->infNFe->emit->enderEmit, 1);
                        DB::commit();
                        $partners->saveInSap();
                        #SetInSAPPartners::dispatch($partners);
                    }
                    return view('purchase::XML.create', $this->getFormOptions());
                } else {
                    return redirect()->route('purchase.order.import.xml')->withErrors('Atenção! esse XML já encontra-se salvo, por favor utilize outro!');
                }
            } else {
                return redirect()->route('purchase.order.import.xml')->withErrors('Atenção! esse XML não pertence a empresa registrada, por favor utilize outro!');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new logsError();
            $logsError->saveInDB('E0083I', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->assoc as $key => $value) {
                if (is_null($value)) {
                    $item = new Item();
                    $item->saveInDB($request, $key);
                    SAPItem::dispatch($item, $key);
                } else {
                    $catalog = new Catalog();
                    $catalog->saveInDB($value, $request->cnpj, $key);
                    SAPCatalogPartners::dispatch($catalog);
                }
            }
            DB::commit();

            $id = Session::get('idImportXML');
            $head = Import::join('users as T1', 'xml_imports.idUser', '=', 'T1.id')
                ->join('xml_partners as T2', 'xml_imports.id', '=', 'T2.idImportXML')
                ->select('T1.name', 'xml_imports.chNFe', 'xml_imports.comments', 'T2.name', 'T2.cnpj')
                ->where('xml_imports.id', '=', $id)
                ->get();

            $cnpj = $head[0]->cnpj;
            $cardCode = Partner::getCardCode(new Company(false), $cnpj);
            $item = Items::where('idImportXML', '=', $id)->get();
            $OPOR = PurchaseOrder::JOIN('users as T1', 'T1.id', '=', 'purchase_orders.idUser')
                ->select('purchase_orders.id', 'purchase_orders.code', 'purchase_orders.codSAP', 'purchase_orders.taxDate', 'purchase_orders.docTotal', 'purchase_orders.comments', 'T1.name')
                ->where('purchase_orders.CardCode', '=', $cardCode)
                ->whereNotNull('purchase_orders.codSAP')
                ->where('purchase_orders.status', PurchaseOrder::STATUS_OPEN)
                ->get();

            return view('purchase::XML.join', compact('OPOR', 'head', 'item'));
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new logsError();
            $logsError->saveInDB('E0084I', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return redirect()->route('purchase.order.import.xml')->withErrors($e->getMessage());
        }

    }

    public function store(Request $request)
    {
        try {
            $id = Session::get('idImportXML');
            $sap = new Company(false);
            if (!is_null($request->get('assoc')) && !empty($request->get('assoc'))) {
                $idPurchaseOrder = $request->get('assoc');
            } else {
                $idPurchaseOrder = false;
            }
            $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
            $obpl = $sap->query("SELECT T0.BPLId as code, T0.BPLName as value FROM OBPL T0");
            $centroCusto = $this->getDistributionRulesOptions($sap);
            $projeto = $this->getProjectOptions($sap);
            $typeOut = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
            $use = $sap->query('SELECT T0.ID as code, T0.Descr as value FROM OUSG T0');
            $accounts = $this->getAccountOptions($sap);
            $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
            $cashFlow = DB::SELECT("SELECT T0.id, T0.description as value FROM cash_flows as T0 WHERE T0.module = 'C' and T0.status = '1'");
            $model = $this->getModelOptions($sap);
            $tax = $this->getTaxOptions($sap);
            $cfop = $this->getCFOPOptions($sap);
            $types = [
                ['name' => 'Moeda Corrente', 'value' => 'L'],
                ['name' => 'Moeda do Sistema', 'value' => 'S'],
                ['name' => 'Moeda do Parceiro', 'value' => 'C'],
            ];
            $head = Import::join('xml_partners', 'xml_partners.idImportXML', '=', 'xml_imports.id')
                ->select('xml_partners.name', 'xml_imports.taxDate', 'xml_imports.docDueDate', 'xml_imports.nNF', 'xml_imports.serie', 'xml_partners.cnpj', 'xml_imports.docTotal', 'xml_imports.created_at', 'xml_imports.comments', 'xml_imports.chNFe')
                ->where('xml_imports.id', '=', $id)
                ->get();
            $body = Items::where('idImportXML', '=', $id)
                ->select('codPartners', 'name', 'qCom', 'vUnCom', 'vProd')
                ->get();
            $cardCode = Partner::getCardCode(new Company(false), $head[0]->cnpj);

            return view("purchase::XML.receiptGoods", compact('idPurchaseOrder', 'cardCode', 'head', 'body', 'cfop', 'cashFlow', 'cartao', 'accounts', 'tax', 'paymentConditions', 'obpl', 'centroCusto', 'projeto', 'typeOut', 'use', 'types', 'model', 'id'));

        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E3012', $e->getLine() . '|' . $e->getFile(), $e->getMessage());
            return redirect()->route('purchase.order.import.xml')->withErrors($e->getMessage());
        }

    }

    public function getItems($id)
    {
        $sap = new Company(false);
        $POR1 = $sap->query("SELECT T0.[DocNum],T1.[ItemCode], T1.[Dscription], T1.[Quantity], T1.[Price] FROM OPOR T0  INNER JOIN POR1 T1 ON T0.[DocEntry] = T1.[DocEntry] WHERE T0.[DocNum]  = '{$id}'");
        return response()->json([
            "data" => $POR1
        ]);
    }

    protected function getFormOptions()
    {
        $sap = new Company(false);
        $itemGroups = $sap->query("select ItmsGrpCod as [value], ItmsGrpNam as [name] from OITB");
        $priceList = $sap->query("select ListNum as value, ListName as name from OPLN");
        $manufacturers = $sap->query("select FirmCode as value, FirmName as name from OMRC");
        $ncmCodes = $sap->query("select AbsEntry as value, concat(NcmCode, ' - ', Descrip) as name from ONCM");
        $dnfCodes = $sap->query("select AbsEntry as value, DNFCode as name from ODNF");
        $materialGroups = $sap->query("select AbsEntry as value, concat(MatGrp, ' - ', Descrip) as name from OMGP");
        $productSources = $sap->query("select Code as value, concat(Code, ' - ', [Desc]) as name from OPSC");

        $adm1 = $sap->getDb()->selectOne("select top 1 County from ADM1");
        $serviceCodesContrated = $sap->query("select AbsEntry as value, CONCAT(ServiceCD, ' - ', Descrip) as name from OSCD
        where County = :county and Incomimg = 'Y'", ["county" => $adm1->County]);
        $serviceCodes = $sap->query("select AbsEntry as value, CONCAT(ServiceCD, ' - ', Descrip) as name from OSCD
        where County = :county and Incomimg = 'N'", ["county" => $adm1->County]);

        $serviceGroups = $sap->query("select AbsEntry as value, CONCAT(ServiceGrp, ' - ', Descrip) as name from OSGP");
        $warehouses = $sap->query("select WhsCode as value, WhsName as name from OWHS");

        $itemTypes = [
            ["value" => 0, "name" => "Item"], ["value" => 1, "name" => "Mão de obra"]
            , ["value" => 2, "name" => "Viagens"], /*["value" => 3, "name" => "Fixed"]*/
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
            ["value" => 0, "name" => "AMPOLA"],
            ["value" => 1, "name" => "BALDE"],
            ["value" => 2, "name" => "BANDEJ"],
            ["value" => 3, "name" => "BARRA"],
            ["value" => 4, "name" => "BISNAG"],
            ["value" => 5, "name" => "BLOCO"],
            ["value" => 6, "name" => "BOBINA"],
            ["value" => 7, "name" => "BOMB"],
            ["value" => 8, "name" => "CAPS"],
            ["value" => 9, "name" => "CART"],
            ["value" => 10, "name" => "CENTO"],
            ["value" => 11, "name" => "CJ"],
            ["value" => 12, "name" => "CM"],
            ["value" => 13, "name" => "CM2"],
            ["value" => 14, "name" => "CX"],
            ["value" => 15, "name" => "CX2"],
            ["value" => 16, "name" => "CX3"],
            ["value" => 17, "name" => "CX5"],
            ["value" => 18, "name" => "CX10"],
            ["value" => 19, "name" => "CX15"],
            ["value" => 20, "name" => "CX20"],
            ["value" => 21, "name" => "CX25"],
            ["value" => 22, "name" => "CX50"],
            ["value" => 23, "name" => "CX100"],
            ["value" => 24, "name" => "DISP"],
            ["value" => 25, "name" => "DUZIA"],
            ["value" => 26, "name" => "EMBAL"],
            ["value" => 27, "name" => "FARDO"],
            ["value" => 28, "name" => "FOLHA"],
            ["value" => 29, "name" => "FRASCO"],
            ["value" => 30, "name" => "GALAO"],
            ["value" => 31, "name" => "GF"],
            ["value" => 32, "name" => "GRAMAS"],
            ["value" => 33, "name" => "JOGO"],
            ["value" => 34, "name" => "KG"],
            ["value" => 35, "name" => "KIT"],
            ["value" => 36, "name" => "LATA"],
            ["value" => 37, "name" => "LITRO"],
            ["value" => 38, "name" => "M"],
            ["value" => 39, "name" => "M2"],
            ["value" => 40, "name" => "M3"],
            ["value" => 41, "name" => "MILHEI"],
            ["value" => 42, "name" => "ML"],
            ["value" => 43, "name" => "MWH"],
            ["value" => 44, "name" => "PACOTE"],
            ["value" => 45, "name" => "PALETE"],
            ["value" => 46, "name" => "PARES"],
            ["value" => 47, "name" => "PC"],
            ["value" => 48, "name" => "POTE"],
            ["value" => 49, "name" => "K"],
            ["value" => 50, "name" => "RESMA"],
            ["value" => 51, "name" => "ROLO"],
            ["value" => 52, "name" => "SACO"],
            ["value" => 53, "name" => "SACOLA"],
            ["value" => 54, "name" => "TAMBOR"],
            ["value" => 55, "name" => "TANQUE"],
            ["value" => 56, "name" => "TON"],
            ["value" => 57, "name" => "TUBO"],
            ["value" => 58, "name" => "UNID"],
            ["value" => 59, "name" => "VASIL"],
            ["value" => 60, "name" => "VIDRO"],
        ];

        $id = Session::get('idImportXML');
        $importXML = new Import();

        $xItem = Items::join('xml_imports', 'xml_imports.id', '=', 'xml_items.idImportXML')
            ->where([
                'xml_imports.id' => $id,
                'xml_imports.status' => $importXML::STATUS_OPEN
            ])
            ->select('xml_items.id', 'xml_items.codPartners', 'xml_items.itemCode', 'xml_items.name')
            ->get();
        $xHead = Import::join('xml_partners', 'xml_partners.idImportXML', '=', 'xml_imports.id')
            ->where('xml_imports.id', '=', $id)
            ->select('xml_imports.docTotal', 'xml_partners.name', 'xml_partners.cnpj')
            ->get();

        return compact("itemGroups", "itemTypes", "priceList", "xItem", "xHead",
            "manufacturers", "ncmCodes", "dnfCodes", "materialGroups", "productSources", "materialTypes",
            "serviceCodesContrated", "serviceCodes", "serviceGroups", "warehouses", "glMethods", "unitsMeasurement");
    }

    public function getItemsXML($id)
    {
        $query = DB::SELECT("SELECT name,qCom,vUnCom,vProd from xml_items where idImportXML = '{$id}'");

        return response()->json([
            "recordsTotal" => count($query),
            "recordsFiltered" => count($query),
            "data" => $query
        ]);
    }

    public function filter(Request $request)
    {
        try {
            $sql = "SELECT T2.id, T0.codSAP, T3.name,T3.cnpj,T2.nNF,T2.taxDate,T2.created_at,T2.docTotal from receipt_goods as T0
                  JOIN receipt_goods_taxes T1 on T0.id = T1.idReceiptGoods
                  JOIN xml_imports T2 on T1.sequenceSerial = T2.nNf
                  JOIN xml_partners T3 on T2.id = T3.idImportXML
                  where T0.id != '-1'";

            if (!is_null($request->nf)) {
                $sql .= " and T2.nNF = '{$request->nf}'";
            }
            if (!is_null($request->name)) {
                $sql .= " and T3.name like '%{$request->name}%'";
            }
            if (!is_null($request->cnpj)) {
                $sql .= " and T3.cnpj like '{$request->cnpj}'";
            }

            $sql .= " order by T0.codSAP desc ";
            $query = DB::select($sql);
            return view('purchase::XML.import', ['items' => $query]);
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EXML12', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return view('purchase::XML.import')->withErrors($e->getMessage());
        }
    }
}
