<?php

namespace App\Modules\Inventory\Models\Input;

use App\Modules\Inventory\Models\Item\Lot;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\Input\Item
 *
 * @property int $id
 * @property string $idInputs
 * @property string $itemCode
 * @property string $quantity
 * @property string $price
 * @property string $projectCode
 * @property string $costingCode
 * @property string $accountCode
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereAccountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereCostingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereIdInputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $wareHouseCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Item whereWareHouseCode($value)
 */
class Item extends Model
{
    protected $table = 'input_items';

    public function saveInDB($value, $id){
               
        $this->idInputs = $id;
        $this->itemCode =  $value['itemCode'];
        $this->quantity = clearNumberDouble($value['quantity']);
        $this->price = clearNumberDouble($value['price']) ?? 0;
        $this->projectCode = $value['projectCode'];
        $this->costingCode = $value['costCenter'];
        $this->costCenter = $value['costCenter'];
        $this->costCenter2 = $value['costCenter2'];
        $this->accountCode = $value['account'];
        $this->wareHouseCode = $value['whsCode'];
        $this->save();
    }

    public function updateInDB($value){
        $this->idInputs = $value['idInputs'];
        $this->itemCode =  $value['itemCode'];
        $this->quantity = clearNumberDouble($value['quantity']);
        $this->price = clearNumberDouble($value['price']);
        $this->projectCode = $value['projectCode'];
        $this->costingCode = $value['costCenter'];
        $this->costCenter = $value['costCenter'];
        $this->costCenter2 = $value['costCenter2'];
        $this->accountCode = $value['account'];
        $this->wareHouseCode = $value['whsCode'];
        $this->save();
    }
}
