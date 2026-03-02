<?php

namespace App\Modules\Inventory\Models\item;

use App\Modules\Inventory\Models\Item\Price;
use App\Modules\Inventory\Models\Item\Approve;
use App\Modules\Settings\Models\Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoYesNoEnum;
use Litiano\Sap\Enum\ItemClassEnum;
use Litiano\Sap\IdeHelper\IItems;
use Illuminate\Support\Facades\DB;
use App\Modules\Partners\Models\Partner\Catalog;
use App\LogsError;
use App\Jobs\Set\SAPCatalogPartners;
use App\Modules\Purchase\Models\XML\Import;
use App\Modules\Purchase\Models\XML\Items;
use App\Modules\Purchase\Models\XML\Partner as xPartner;
use App\Modules\Partners\Models\Partner;
use Litiano\Sap\NewCompany;

/**
 * App\Modules\Inventory\Models\Item
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property int $group
 * @property int $type
 * @property string|null $barcode
 * @property bool $is_inventory_item
 * @property bool $is_sales_item
 * @property bool $is_purchase_item
 * @property string $classification
 * @property int $manufacturer
 * @property int|null $ncm
 * @property int|null $materials_group
 * @property int|null $material_type
 * @property string|null $dnf_code
 * @property string|null $contracted_service_code
 * @property string|null $service_code
 * @property string|null $service_group
 * @property string|null $purchase_um
 * @property string|null $preferred_supplier
 * @property string|null $sales_um
 * @property string|null $gl_method
 * @property string|null $default_warehouse
 * @property string|null $comments
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereContractedServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereDefaultWarehouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereDnfCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereGlMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereIsInventoryItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereIsPurchaseItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereIsSalesItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereMaterialType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereMaterialsGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereNcm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item wherePreferredSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item wherePurchaseUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereSalesUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereServiceGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereUpdatedAt($value)
 * @property string|null $code
 * @property int|null $source
 * @property bool $is_locked
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereSource($value)
 * @property string|null $inventory_um
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereInventoryUm($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Inventory\Models\Item\Price[] $prices
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereMessage($value)
 * @property string|null $codSAP
 * @property string|null $codSupllier
 * @property string|null $CFOP
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereCFOP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereCodSupllier($value)
 * @property string|null $idUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereIdUser($value)
 * @property string|null $dbUpdate
 * @property float|null $min_whs
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Item whereMinWhs($value)
 */
class Item extends Model
{
    protected $table = 'items';

    protected $guarded = ['prices'];

    public function saveInDB($request, $key)
    {
        try {
            $this->codSupllier = $this->getItemXML($key)->codPartners;
            $this->name = clearString($this->getItemXML($key)->name);
            $this->idUser = auth()->user()->id;
            $this->group = 100;
            $this->type = 0;
            $this->code = $this->createCode();
            $this->barcode = $this->getItemXML($key)->EAN;
            $this->is_inventory_item = $request->is_inventory_item;
            $this->is_sales_item = $request->is_sales_item;
            $this->is_purchase_item = $request->is_purchase_item;
            $this->classification = 2;
            $this->manufacturer = '-1';
            $this->source = 0;
            if ($this->validNCMSAP(mask($this->getItemXML($key)->NCM, '####.##.##')))
                $this->ncm = $this->validNCMSAP(mask($this->getItemXML($key)->NCM, '####.##.##'));

            $this->CFOP = $this->getItemXML($key)->CFOP;
            $this->materials_group = '-1';
            $this->material_type = 0;
            $this->dnf_code = '-1';
            $this->contracted_service_code = '-1';
            $this->service_code = '-1';
            $this->service_group = '-1';
            $this->gl_method = $request->gl_method;
            $this->default_warehouse = $request->default_warehouse;
            $this->inventory_um = $this->getItemXML($key)->uCom;
            $this->comments = 'VIA IMPORTADOR XML';
            $this->is_locked = false;
            $this->save();

            $price = new Price();
            $price->item_id = $this->id;
            $price->list_id = 1;
            $price->price = $this->getItemXML($key)->vUnCom;
            $price->save();
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EI084I', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        }

    }

    public function saveFromSAP($sap_item, $key = null){
        try {
          
            $this->codSAP = $sap_item['ItemCode'];
            $this->message = "Item salvo no SAP com sucesso.";
            $this->is_locked = false;
            $this->codSupllier = (isset($sap_item['CardCode'])) ? : '';
            $this->name = (isset($sap_item['ItemName'])) ? clearString($sap_item['ItemName']) : '';
            $this->idUser = auth()->user()->id;
            $this->group = (isset($sap_item['ItmsGrpCod'])) ? $sap_item['ItmsGrpCod'] : '';
            if(isset($sap_item['ItemType'])){
                if( $sap_item['ItemType'] === 'I' ){
                    $this->type = 0;
                } elseif ( $sap_item['ItemType'] === 'L' ){
                    $this->type = 1;
                } elseif ( $sap_item['ItemType'] === 'T' ){
                    $this->type = 2;
                } else {
                    $this->type = 3;
                }

            }
            $this->code = $this->createCode();
            $this->barcode = (isset($sap_item['CodeBars'])) ? $sap_item['CodeBars'] : '';
            $this->is_inventory_item = (isset($sap_item['InvntItem']) && $sap_item['InvntItem'] === 'Y') ? true : false;
            $this->is_sales_item = (isset($sap_item['SellItem']) && $sap_item['SellItem']) ? true : false;
            $this->is_purchase_item = (isset($sap_item['PrchseItem']) && $sap_item['PrchseItem'] === 'Y' ) ? true : false;
            $this->classification = (isset($sap_item['ItemClass'])) ? $sap_item['ItemClass'] : '';
            $this->manufacturer = (isset($sap_item['FirmCode'])) ? $sap_item['FirmCode'] : '';
            $this->min_whs = (isset($sap_item['MinLevel'])) ? $sap_item['MinLevel'] : '';
            $this->source = (isset($sap_item['ProductSrc'])) ? $sap_item['ProductSrc'] : '';
            $this->material_type = (isset($sap_item['MatType'])) ? $sap_item['MatType'] : '';
            $this->ncm = (isset($sap_item['NCMCode'])) ? $sap_item['NCMCode'] : '';
           
            if ($this->validNCMSAP(mask($sap_item['NCMCode'], '####.##.##')))
            $this->ncm = $this->validNCMSAP(mask($sap_item['NCMCode'], '####.##.##'));
            
            $this->materials_group = $this->material_type = (isset($sap_item['MatGrp'])) ? $sap_item['MatGrp'] : '';
            $this->dnf_code = $this->material_type = (isset($sap_item['DNFEntry'])) ? $sap_item['DNFEntry'] : '';
            $this->service_code = $this->material_type = (isset($sap_item['OSvcCode'])) ? $sap_item['OSvcCode'] : '';
            $this->contracted_service_code = $this->material_type = (isset($sap_item['ISvcCode'])) ? $sap_item['ISvcCode'] : '';
            $this->service_group = $this->material_type = (isset($sap_item['ServiceGrp'])) ? $sap_item['ServiceGrp'] : '';
            $this->purchase_um = $this->material_type = (isset($sap_item['PUoMEntry'])) ? $sap_item['PUoMEntry'] : '';
            
            $this->preferred_supplier = $this->material_type = (isset($sap_item['CardCode'])) ? $sap_item['CardCode'] : '';
            $this->sales_um = $this->material_type = (isset($sap_item['SalUnitMsr'])) ? $sap_item['SalUnitMsr'] : '';
            
            if(isset($sap_item['GLMethod'])){
                if($sap_item['GLMethod'] === 'W'){
                    $this->gl_method = 0;
                } elseif ($sap_item['GLMethod'] === 'C'){
                    $this->gl_method = 1;
                } else {
                    $this->gl_method = 2;
                }
            }

            $this->default_warehouse = (isset($sap_item['DfltWH'])) ? $sap_item['DfltWH'] : '';
            $this->inventory_um = (isset($sap_item['InvntryUom'])) ? $sap_item['InvntryUom'] : '';
            $this->comments =  (isset($sap_item['UserText'])) ? $sap_item['UserText'] : '';
            $this->CFOP = (isset($sap_item['CFOP'])) ? $sap_item['CFOP'] : '';
            $this->is_locked = false;
            $this->save();

            // $price = new Price();
            // $price->item_id = $this->id;
            // $price->list_id = (isset($sap_item['PriceList'])) ? $sap_item['PriceList'] : '';
            // $price->price = (isset($sap_item['Price'])) ? $sap_item['Price'] : '';
            // $price->save();
            $sap = new Company(false);
            $prices = $sap->query("select PriceList, Price from ITM1 where ItemCode = :itemCode", ["itemCode" => $sap_item['ItemCode']]);
            foreach ($prices as $price) {
                Price::updateOrInsert(["item_id" => $this->id, "list_id" => $price['PriceList']], ["price" => $price['Price']]);
            }

            return $this;

        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('EI084I', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        }
    }

    public function updateInDB($obj, $item)
    {
        $obj->name = $item['name'];
        $obj->idUser = auth()->user()->id;
        $obj->group = $item['group'];
        $obj->type = $item['type'];
        $obj->barcode = $item['barcode'];
        $obj->is_inventory_item = $item['is_inventory_item'];
        $obj->is_sales_item = $item['is_sales_item'];
        $obj->is_purchase_item = $item['is_purchase_item'];
        $obj->classification = $item['classification'];
        $obj->manufacturer = $item['manufacturer'];
        $obj->source = $item['source'];
        $obj->ncm = $item['ncm'];
        #$obj->CFOP = $item['CFOP'];
        $obj->materials_group = $item['materials_group'];
        $obj->material_type = $item['material_type'];
        $obj->dnf_code = $item['dnf_code'];
        $obj->contracted_service_code = $item['contracted_service_code'];
        $obj->service_code = $item['service_code'];
        $obj->service_group = $item['service_group'];
        $obj->gl_method = $item['gl_method'];
        $obj->default_warehouse = $item['default_warehouse'];
        $obj->inventory_um = $item['inventory_um'];
        $obj->comments = $item['comments'];
        $obj->is_locked = false;
        $obj->save();
    }

    public function createCode()
    {
        $busca = DB::select("select top 1 items.code from items order by items.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'I00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    private function getItemXML($id)
    {
        return Items::find($id);
    }

    private function validNCMSAP($ncm)
    {
        $sap = new Company(false);
        $busca = $sap->query("SELECT T0.AbsEntry from ONCM as T0 where T0.NcmCode = '{$ncm}'");
        if (empty($busca)) {
            return false;
        } else {
            return $busca[0]['AbsEntry'];
        }
    }

    public function prices()
    {
        return $this->hasMany(Price::class, 'item_id', 'id');
    }

    public function getPrice($id = 1)
    {
        $price = Price::where('item_id', '=', $this->id)
            ->where('list_id', '=', $id)->first();
        if ($price) {
            return $price->price;
        }
        return null;
    }

    /**
     * @param Company $sap
     * @throws \Exception
     */
    public function saveInSap($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            /** @var IItems $item */
            $item = $sap->GetBusinessObject(BoObjectTypes::oItems);
           
            $update = false;
            if (!empty($obj->itemCode)) {
                $item->GetByKey($obj->itemCode);
                $update = true;
            } else {
                $item->Series = $obj->numberSeries;
            }

            $item->ItemName = (string)$obj->name;
            $item->ItemsGroupCode = (int)$obj->group;
            $item->Manufacturer = $obj->subGroup;
            $item->ItemType = (int)$obj->type;
            $item->BarCode = (string)$obj->barcode;
            $item->InventoryItem = (int)$obj->is_inventory_item;
            $item->SalesItem = (int)$obj->is_sales_item;
            $item->PurchaseItem = (int)$obj->is_purchase_item;
            $item->ItemClass = (int)$obj->classification;
            //$item->Manufacturer = (int)$obj->manufacturer;
            /*
            !empty($obj->needApproval) ? $item->UserFields->fields->Item("U_R2W_APROVAITEM")->value = $obj->needApproval : null;
            !empty($obj->item_genre) ? $item->UserFields->fields->Item("U_GeneroItem")->value = $obj->item_genre : null;
            !empty($obj->item_type) ? $item->UserFields->fields->Item("U_TipoItem")->value = $obj->item_type : null;
            !empty($obj->cfop) ? $item->UserFields->fields->Item("U_CFOPCode")->value = $obj->cfop : null;
            !empty($obj->revised) ? $item->UserFields->fields->Item("U_ICB_REV")->value = $obj->revised : null;
            !empty($obj->cfop_inside_state) ? $item->UserFields->fields->Item("U_A2R_CFOPDUF")->value = $obj->cfop_inside_state : null;
            !empty($obj->cfop_outside_state) ? $item->UserFields->fields->Item("U_A2R_CFOPFUF")->value = $obj->cfop_outside_state : null;
            !empty($obj->cst_icms) ? $item->UserFields->fields->Item("U_A2R_CSTICMS")->value = $obj->cst_icms : null;
            !empty($obj->cst_pis) ? $item->UserFields->fields->Item("U_A2R_CSTPIS")->value = $obj->cst_pis : null;
            !empty($obj->cst_cofins) ? $item->UserFields->fields->Item("U_A2R_CSTCOF")->value = $obj->cst_cofins : null;
            !empty($obj->cst_icms_output) ? $item->UserFields->fields->Item("U_A2R_CSTICMSS")->value = $obj->cst_icms_output : null;
            !empty($obj->cst_pis_output) ? $item->UserFields->fields->Item("U_A2R_CSTPISS")->value = $obj->cst_pis_output : null;
            !empty($obj->cst_cofins_output) ? $item->UserFields->fields->Item("U_A2R_CSTCOFS")->value = $obj->cst_cofins_output : null;
            !empty($obj->monofasico) ? $item->UserFields->fields->Item("U_A2R_MON")->value = $obj->monofasico : null;
            !empty($obj->as_integrado) ? $item->UserFields->fields->Item("U_AS_INTEGRADO")->value = $obj->as_integrado : null;
            */
            !empty($obj->item_genre) ? $item->MinInventory = (double)$obj->min_whs : null;

            if ((Int)$obj->classification === (Int)ItemClassEnum::itcMaterial) {
                $item->ProductSource = (int)$obj->source;
                $item->MaterialType = (int)$obj->material_type;
                

                if (!is_null($obj->ncm)) {
                    $item->NCMCode = (int)$obj->ncm;
                }

                if (!is_null($obj->cest)) {
                    //$item->CESTCode = (int)$obj->cest;
                    $item->UserFields->fields->Item("U_SKILL_CEST")->value = $obj->cest;
                }
                
                if ($obj->materials_group) {
                    $item->MaterialGroup = (int)$obj->materials_group;
                }
                if ($obj->dnf_code) {
                    $item->DNFEntry = (int)$obj->dnf_code;
                }
            }

            if ((Int)$obj->classification === (Int)ItemClassEnum::itcService) {
                if ($obj->service_code) {
                    $item->OutgoingServiceCode = (int)$obj->service_code;
                }
                if ($obj->contracted_service_code) {
                    $item->IncomingServiceCode = (int)$obj->contracted_service_code;
                }
                if ($obj->service_group) {
                    $item->ServiceGroup = (int)$obj->service_group;
                }
            }

            for ($i=1; $i < 64; $i++) { 
                $item->Properties[$i] = isset($obj->itemProperties[$i])? BoYesNoEnum::tYES : BoYesNoEnum::tNO;
            }
            
            $item->PurchaseUnit = (string)$obj->purchase_um;
            $item->PurchasePackagingUnit = (string)$obj->purchase_um;
            clearNumberDouble($obj->numInBuy) > 0 ? $item->PurchaseItemsPerUnit = (Double)clearNumberDouble($obj->numInBuy): null;
            clearNumberDouble($obj->purPackUn) > 0 ? $item->PurchaseQtyPerPackUnit = (Double)clearNumberDouble($obj->purPackUn): null;
            $item->Mainsupplier = (string)$obj->preferred_supplier;

            $item->SalesUnit = (string)$obj->sales_um;
            $item->SalesPackagingUnit = (string)$obj->sales_um;
            clearNumberDouble($obj->numInSale) > 0 ? $item->SalesItemsPerUnit = (Double)clearNumberDouble($obj->numInSale): null;
            clearNumberDouble($obj->salPackUn) > 0 ? $item->SalesQtyPerPackUnit = (Double)clearNumberDouble($obj->salPackUn): null;

            
            $item->GLMethod = (int)$obj->gl_method;
            $item->DefaultWarehouse = (string)$obj->default_warehouse;
            $item->InventoryUOM = (string)$obj->inventory_um;
            $item->User_Text = (string)$obj->comments;
            $item->PlanningSystem = (Int)$obj->planingSys;
            $item->ProcurementMethod = (Int)$obj->prcrmntMtd;
            $item->ComponentWarehouse = (Int)$obj->compoWH;

            if(isset($obj->priceList) && $obj->price){
                $item->PriceList->SetCurrentLine($obj->priceList - 1);
                $item->PriceList->Price = (Double)clearNumberDouble($obj->price);
            }
            
            if ($update) {
                $ret = $item->Update();

                // $itemApproval = new Approve;
                // $itemApproval->saveInDB($obj);
            } else {
                $ret = $item->Add();
            }

            if ($ret !== 0) {
                $logsError = new logsError();
                $logsError->saveInDB('ITM001', "SaveInSAP Error, Update: $update", $sap->GetLastErrorDescription());
                return ["status" => "error", "message" => $sap->GetLastErrorDescription()];
                
            } else {
                $itemCode = $sap->GetNewObjectKey();
                return ["status" => "success", "key" => $itemCode];
                
                // if (!is_null($key)) {
                //     $obj->saveInCatalog($obj, $key);
                // }
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E0083', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return ["status" => "error", "message" => $e->getMessage()];
        }

    }

    public function removeInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $item = $sap->GetBusinessObject(BoObjectTypes::oItems);
            if ($item->GetByKey((string)$obj->codSAP)) {
                $val = $item->Remove;
                if ($val !== 0) {
                    $obj->message = $sap->GetLastErrorDescription();
                    $obj->is_locked = true;
                    $obj->save();
                    return false;
                } else {
                    Item::find($obj->id)->delete();
                    return true;
                }
            }

        } catch (\Exception $e) {
            $obj->is_locked = true;
            $obj->message = $e->getMessage();
            $obj->dbUpdate = true;
            $obj->save();
        }

    }

    public function getNameUser($id)
    {
        return DB::SELECT("SELECT T0.name FROM Users T0 WHERE T0.id = '{$id}'")[0]->name;
    }

    /**
     * @param Company $sap
     * @param $itemCode
     * @return $this|Model|null|static
     * @throws \Exception
     */
    public static function getUpdated(Company $sap, $itemCode)
    {
        $query = "select ItemCode as code, ItemName as name, ItmsGrpCod as [group], ItemType as type, CodeBars as barcode, InvntItem as is_inventory_item,
                PrchseItem as is_purchase_item, SellItem as is_sales_item, ItemClass as classification, FirmCode as manufacturer,
                -------- Material-----
                ProductSrc as source, NCMCode as ncm, MatGrp as materials_group, MatType as material_type, DNFEntry as dnf_code,
                ------- Service ------
                OSvcCode as contracted_service_code, ISvcCode as service_code, ServiceGrp as service_group,
                ----------------------
                BuyUnitMsr as purchase_um, SalUnitMsr as sales_um, CardCode as preferred_supplier, DfltWH as default_warehouse,
                GLMethod gl_method, InvntryUom as inventory_um, UserText as comments from OITM where ItemCode = :itemCode";

        $attributes = $sap->query($query, ["itemCode" => $itemCode]);
        $attributes = $attributes[0];
        $attributes['is_inventory_item'] = $attributes['is_inventory_item'] === 'Y' ? true : false;
        $attributes['is_purchase_item'] = $attributes['is_purchase_item'] === 'Y' ? true : false;
        $attributes['is_sales_item'] = $attributes['is_sales_item'] === 'Y' ? true : false;

        if ($attributes['gl_method'] === 'W') {
            $attributes['gl_method'] = 0;
        } elseif ($attributes['gl_method'] === 'C') {
            $attributes['gl_method'] = 1;
        } else {
            $attributes['gl_method'] = 2;
        }

        if ($attributes['type'] === 'I') {
            $attributes['type'] = 0;
        } elseif ($attributes['type'] === 'L') {
            $attributes['type'] = 1;
        } elseif ($attributes['type'] === 'T') {
            $attributes['type'] = 2;
        } else {
            $attributes['type'] = 3;
        }

        $item = Item::where("code", "=", $itemCode)->first();

        if ($item) {
            if ($item->is_locked) {
                throw new \Exception('Item aguardando sincronização com SAP!');
            }
            $item->fill($attributes);
            $item->save();
        } else {
            $attributes['is_locked'] = false;
            $item = Item::create($attributes);
        }

        $prices = $sap->query("select PriceList, Price from ITM1 where ItemCode = :itemCode", ["itemCode" => $item->code]);
        foreach ($prices as $price) {
            Price::updateOrInsert(["item_id" => $item->id, "list_id" => $price['PriceList']], ["price" => $price['Price']]);
        }

        return $item;
    }

    public function getNcmLabel($code)
    {
        $sap = new Company(false);
        $ncm = $sap->getDb()
            ->selectOne("select AbsEntry as value, concat(NcmCode, ' - ', Descrip) as name from ONCM where AbsEntry = :ncm",
                ["ncm" => $code]);
        
        return $ncm->name;
    }

    public function getSupplierLabel($cardCode)
    {
        $sap = new Company(false);
        try {
            $supplier = $sap->getDb()->selectOne("select top 7 CardCode as value, concat(CardCode, ' - ', CardName) as name
                from OCRD where CardCode = :cardCode", ["cardCode" => $cardCode]);
            return $supplier->name;
            
        } catch (\Exception $e) {
            return null;
        }


    }

    public static function cron()
    {
        /** @var Item[] $items */
        $items = Item::where("is_locked", "=", 1)->get();

        foreach ($items as $item) {
            try {
                $item->saveInSap();
            } catch (\Exception $e) {
                $item->message = $e->getMessage();
                $item->save();
            }
        }
    }

    private function saveInCatalog($obj, $key)
    {
        try {
            $catalog = new Catalog();
            $catalog->idUser = 'SISTEM';
            $catalog->idXMLItem = $key;
            $catalog->cardCode = $cardCode = Partner::getCardCode(new Company(false), $this->getParner($key)->cnpj);
            $catalog->cardName = $this->getParner($key)->name;
            $catalog->itemCode = $obj->codSAP;
            $catalog->itemName = $obj->name;
            $catalog->substitute = $obj->codSupllier;
            $catalog->save();
            $logsErrors = new LogsError();
            SAPCatalogPartners::dispatch($catalog);
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0222Ç', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        }

    }

    private function getParner($key)
    {
        return xPartner::join('xml_imports', 'xml_imports.id', '=', 'xml_partners.idImportXML')
            ->join('xml_items', 'xml_items.idImportXML', '=', 'xml_imports.id')
            ->where('xml_items.id', '=', $key)
            ->select('xml_partners.name', 'xml_partners.cnpj')->first();
    }

    private function getCardCode($cnpj)
    {
        return DB::SELECT("SELECT T0.id, T0.codSAP from partners as T0 where T0.cnpj = '{$cnpj}'");
    }

    public function getDataWarehouse($itemCode, $whsCode){
        $sap = new Company(false);
        return $sap->getDb()->table('OITW')
            ->join('OWHS', 'OWHS.WhsCode', 'OITW.WhsCode')
            ->select("ItemCode", 
                        "OITW.WhsCode", 
                        "OWHS.WhsName", 
                        DB::raw("(OnHand - IsCommited) as disponivel"))
            ->where('ItemCode', '=', $itemCode)
            ->where('OITW.WhsCode', $whsCode)
            ->first();
    }

    public function getPriceList($itemCode){
        $price = NewCompany::getDb()->table('ITM1')
            ->leftJoin('OPLN', 'OPLN.ListNum', '=', 'ITM1.PriceList')
            ->where('ITM1.ItemCode', '=', $itemCode)
            ->get();
        return $price;
    }

}