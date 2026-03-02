<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\IncoingInvoice\Items
 *
 * @property int $id
 * @property string $idIncoingInvoice
 * @property string $itemCode
 * @property string|null $itemName
 * @property float $quantity
 * @property float $price
 * @property float $lineSum
 * @property string $codUse
 * @property string $codProject
 * @property string $codCost
 * @property string|null $codCFOP
 * @property string|null $taxCode
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereCodCFOP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereCodCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereCodProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereCodUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereIdIncoingInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereLineSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Items whereTaxCode($value)
 * @mixin \Eloquent
 */
class Items extends Model
{
    protected $table = 'incoing_invoice_items';
    public $timestamps = false;


    public function saveInDB($value, $idIncoingInvoice){
        $this->idIncoingInvoice = $idIncoingInvoice;
        $this->itemCode = $value['codSAP'];
        $this->itemName = $value['itemName'];
        $this->itemUnd = $value['itemUnd'];
        $this->quantity = is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']);
        $this->price = is_numeric($value['preco']) ? $value['preco'] : clearNumberDouble($value['preco']);
        $this->codUse = $value['use'];
        $this->codProject = $value['projeto'];
        $this->codCost = $value['costCenter'];
        $this->costCenter = $value['costCenter'];
        $this->costCenter2 = $value['costCenter2'];
        $this->codCFOP = $value['cfop'];
        $this->taxCode = $value['taxCode'];
        $this->contract = $value['contract'] ?? NULL;
        $this->status = '1';
        $this->lineSum = $this->price * $this->quantity;
        $this->idItemPurchaseOrder = $value['idItemPurchaseOrder'] ?? NULL;
        $this->accounting_account = $value['accounting_account'] ?? NULL;
        return $this->save();
    }

    public function duplicate($value, $idIncoingInvoice){

        $this->idIncoingInvoice = $idIncoingInvoice;
        $this->itemCode = $value->itemCode;
        $this->itemName = $value->itemName;
        $this->itemUnd = $value->itemUnd;
        $this->quantity = $value->quantity;
        $this->price = $value->price;
        $this->codUse = $value->codUse;
        $this->codProject = $value->codProject;
        $this->codCost = $value->codCost;
        $this->costCenter = $value->costCenter;
        $this->costCenter2 = $value->costCenter2;
        $this->codCFOP = $value->codCFOP;
        $this->taxCode = $value->taxCode;
        $this->contract = $value->contract;
        $this->status = $value->status;
        $this->lineSum = $this->price * $this->quantity;
        $this->accounting_account = $this->accounting_account;
        $this->save();
    }
}
