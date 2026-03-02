<?php

namespace App\Modules\Purchase\Models\PurchaseQuotation;

use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\ItemR;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Litiano\Sap\Company;

/**
 * App\PurchaseQuotationItem
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $idPurchaseQuotation
 * @property string $itemCode
 * @property float $quantity
 * @property float|null $project
 * @property float|null $distrRule
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereDistrRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereIdPurchaseQuotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\Item query()
 */
class Item extends Model
{
    protected $table = 'purchase_quotation_items';
    protected $fillable = ['idPurchaseQuotation', 'idItemPurchaseRequest', 'itemCode','itemName','itemUnd','qtd','priceP1','qtdP1','totalP1','priceP2','qtdP2','totalP2','priceP3','qtdP3','totalP3','priceP4','qtdP4','totalP4','priceP5','qtdP5','totalP5','id_order','code_order'];

    const TEXT_STATUS = [
        '1' => 'Aberto',
        '2' => 'Cancelado'
    ];

    public function purchase_request(): HasOne
    {
        return $this->hasOne(PurchaseRequest::class, 'id', 'idPurchaseRequest');
    }

    public function purchase_request_items(): HasMany
    {
        return $this->hasMany(ItemR::class, 'idPurchaseRequest', 'id');
    }


    public function saveInDB($value, $idQuotation){
        try {
            $sap = new Company(false);
            $this->idPurchaseQuotation = $idQuotation;
            $this->idItemPurchaseRequest = (int)$value['idItemPurchaseRequest'];
            $this->itemCode = isset($value['itemCode']) ? $value['itemCode'] : $value['codSAP'];
    
            $lastPurchaseItem = getLastPurchaseItem($this->itemCode);
    
            $this->itemName = $value['itemName'];
            $this->itemUnd = $sap->query("SELECT T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$this->itemCode}'")[0]['BuyUnitMsr'] ?? '';
            $this->lastProvider = $lastPurchaseItem[0]['CardCode'] ?? null;
            $this->lastPrice = $lastPurchaseItem[0]['Price'] ?? null;
            $this->qtd = (Double)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
            $this->quantityPendente = (Double)(is_numeric($value['quantityPendente']) ? $value['quantityPendente'] : clearNumberDouble($value['quantityPendente']));
            $this->priceP1 = NULL;
            $this->qtdP1 = NULL;
            $this->totalP1 = NULL;
            $this->status = $this->quantityPendente == 0 ? 2 : 1;
            $this->parent = $value['parent'] ?? NULL;
            $this->save();
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('Eas30XF', $e->getFile().' | '.$e->getLine(), $e->getMessage());
        }
    }
}
