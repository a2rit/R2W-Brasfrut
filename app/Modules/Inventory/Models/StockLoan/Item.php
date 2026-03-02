<?php

namespace App\Modules\Inventory\Models\StockLoan;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Inventory\Models\StockLoan\Item
 *
 * @property int $id
 * @property string $idTransfer
 * @property string $itemCode
 * @property string $quantity
 * @property string $projectCode
 * @property string $distributionRule
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereIdTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereProjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\StockLoan\Item query()
 */
class Item extends Model
{

  protected $table = 'stock_loans_items';

  public function saveInDB($value, $id){
      $this->idStockLoan = $id;
      $this->itemCode =  $value['codSAP'] ?? $value['itemCode'];
      $this->itemName = $value['itemName'];
      $this->itemUnd = $value['itemUnd'];
      $this->quantity = clearNumberDouble($value['qtd'] ?? $value['quantity']);
      $this->quantityDevolved = clearNumberDouble($value['quantityDevolved']);
      $this->quantityPending = 0;
      $this->save();
  }
}

