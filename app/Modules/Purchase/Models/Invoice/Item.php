<?php

namespace App\Modules\Purchase\Models\Invoice;

use App\Modules\Purchase\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\Invoice\Item
 *
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property int $group
 * @property int $type
 * @property string|null $barcode
 * @property bool $is_inventory_item
 * @property bool $is_sales_item
 * @property bool $is_purchase_item
 * @property string $classification
 * @property int $manufacturer
 * @property int|null $source
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
 * @property string|null $inventory_um
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereContractedServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereDefaultWarehouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereDnfCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereGlMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereInventoryUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIsInventoryItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIsPurchaseItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIsSalesItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereMaterialType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereMaterialsGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereNcm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item wherePreferredSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item wherePurchaseUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereSalesUm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereServiceGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $invoice_id
 * @property string|null $ean
 * @property string $description
 * @property string $cfop
 * @property string $unity_measurement
 * @property float $quantity
 * @property float $unity_value
 * @property float|null $freight
 * @property float|null $secure
 * @property float|null $discount
 * @property float|null $other_values
 * @property string $icms
 * @property string $icms_origin
 * @property string $icms_csosn
 * @property string $ipi_framework
 * @property string $ipi_cst
 * @property float $ipi_quantity
 * @property string $ipi_unit_value
 * @property float $ipi_value
 * @property string $pis_cst
 * @property float $pis_value
 * @property string $cofins_cst
 * @property float $cofins_value
 * @property-read \App\Modules\Purchase\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereCfop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereCofinsCst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereCofinsValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereEan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereFreight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIcms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIcmsCsosn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIcmsOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIpiCst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIpiFramework($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIpiQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIpiUnitValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereIpiValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereOtherValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item wherePisCst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item wherePisValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereSecure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereUnityMeasurement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item whereUnityValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Invoice\Item query()
 */
class Item extends Model
{
    protected $table = 'purchase_invoice_items';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public static function createByXml(\SimpleXMLElement $xml, $invoiceId)
    {

    }
}
