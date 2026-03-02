<?php

namespace App\Modules\Inventory\Models\TransferTaking;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\TransferTaking\Item
 *
 * @property int $id
 * @property string $idTransfer
 * @property string $itemCode
 * @property string $quantity
 * @property string $projectCode
 * @property string $distributionRule
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereIdTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\Item query()
 */
class Item extends Model
{

  protected $table = 'transferTaking_items';

  public function saveInDB($value, $id){
      $this->idTransferTaking = $id;
      $this->itemCode = $value['itemCode'];
      $this->quantity = clearNumberDouble($value['qtd']);
      $this->quantityRequest = clearNumberDouble($value['qtd']);
      $this->quantityServed = '0';
      $this->quantityPending = clearNumberDouble($value['qtd']);
      $this->projectCode = $value['projectCode'];
      $this->distributionRule = $value['centroCusto'];
      $this->costCenter = $value['centroCusto'];
      $this->costCenter2 = $value['centroCusto2'];
      
      $this->save();
      
  }

}
