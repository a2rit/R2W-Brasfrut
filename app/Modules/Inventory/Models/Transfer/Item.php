<?php

namespace App\Modules\Inventory\Models\Transfer;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\Transfer\Item
 *
 * @property int $id
 * @property string $idTransfer
 * @property string $itemCode
 * @property string $quantity
 * @property string $projectCode
 * @property string $distributionRule
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereIdTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Item query()
 */
class Item extends Model
{

  protected $table = 'transfer_items';

  public function saveInDB($value, $id){
     
    $this->idTransfer = $id;
    $this->itemCode = $value['itemCode'];
    $this->quantity = $value['qtd'];
    $this->projectCode = $value['projectCode'];
    $this->distributionRule = $value['centroCusto'];
    $this->costCenter = $value['centroCusto'];

    if(isset($value['centroCusto2'])){
      $this->costCenter2 = $value['centroCusto2'];
    }
    if(isset($value['id_transfer_taking_item'])){
      $this->id_transfer_taking_item = $value['id_transfer_taking_item'];
    }else{
      $this->quantity = clearNumberDouble($value['qtd']);
    }

    $this->save();
  }

}
