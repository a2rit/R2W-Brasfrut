<?php

namespace App\Modules\Inventory\Models\Output;

use App\Modules\Inventory\Models\Item\Lot;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\Output\Item
 *
 * @property int $id
 * @property string $idOutputs
 * @property string $itemCode
 * @property string $quantity
 * @property string $price
 * @property string $projectCode
 * @property string $costingCode
 * @property string $accountCode
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereAccountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereCostingCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereIdOutputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $wareHouseCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Item whereWareHouseCode($value)
 */
class Item extends Model
{
  protected $table = 'output_items';

  public function saveInDB($value, $id){
      $this->idOutputs = $id;
      $this->itemCode = $value['itemCode'];
      $this->projectCode = $value['projectCode'];
      $this->costingCode = $value['centroCusto'];
      $this->costCenter = $value['centroCusto'];
      $this->costCenter2 = $value['centroCusto2'];
      $this->accountCode = $value['conta'];
      $this->wareHouseCode = $value['whsCode'];

      if(!empty($value['intern_consumption'])){
        $this->quantity = $value['qtd'];
        $this->price = $value['price'];
      }else{
        $this->quantity = clearNumberDouble($value['qtd']);
        $this->price = clearNumberDouble($value['price']);
      }
      
      $this->save();
  }

  public function updateInDB($value, $obj){
    $obj->idOutputs = $value['idOutputs'];
    $obj->itemCode = $value['itemCode'];
    $obj->quantity = clearNumberDouble($value['quantity']);
    $obj->price = clearNumberDouble($value['price']);
    $obj->projectCode = $value['projectCode'];
    $obj->costingCode = $value['centroCusto'];
    $obj->costCenter = $value['centroCusto'];
    $obj->costCenter2 = $value['centroCusto2'];
    $obj->accountCode = $value['account'];
    $obj->wareHouseCode = $value['whsCode'];
    $obj->save();
  }
  
}
