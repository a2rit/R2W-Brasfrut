<?php

namespace App\Modules\Inventory\Models\Requisicao;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\Requisicao\Products
 *
 * @property int $id
 * @property string $codSAP
 * @property string $idRequest
 * @property string $quantityRequest
 * @property string|null $quantityServed
 * @property string $costCenter
 * @property string $project
 * @property string $deposit
 * @property string|null $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereIdRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereQuantityRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereQuantityServed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $pendingAmount
 * @property string $costCenter2
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products whereCostCenter2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Products wherePendingAmount($value)
 */
class Products extends Model
{
    protected $table = 'request_products';

    public function saveInDB($value, $id)
    {
        $this->codSAP = $value['codSAP'];
        $this->idRequest = $id;
        $this->quantityRequest = clearNumberDouble($value['qtd']);
        $this->quantityServed = 0;
        $this->pendingAmount = $this->quantityRequest;
        $this->costCenter = $value['centroCusto'];
        $this->costCenter2 = $value['centroCusto2'];
        $this->project = $value['projeto'];
        $this->status = 0;
        $this->created_at = null;
        $this->updated_at = null;
        $this->save();
    }
}
