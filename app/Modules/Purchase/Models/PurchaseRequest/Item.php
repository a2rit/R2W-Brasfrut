<?php

namespace App\Modules\Purchase\Models\PurchaseRequest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Litiano\Sap\Company;

/**
 * App\PurchaseRequestItem
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $idPurchaseRequest
 * @property string $itemCode
 * @property float $quantity
 * @property float|null $project
 * @property float|null $distrRule
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereDistrRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereIdPurchaseRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseRequest\Item query()
 */
class Item extends Model
{
  protected $table = 'purchase_request_items';
  protected $fillable = ['idPurchaseRequest', 'itemCode', 'itemName', 'itemUnd', 'quantity', 'quantityPendente', 'project', 'distrRule', 'distriRule2', 'wareHouseCode', 'accounting_account'];


  public function purchase_request(): BelongsTo
  {
    return $this->belongsTo(PurchaseRequest::class, 'idPurchaseRequest', 'id');
  }

  public function updateLineNum()
  {
    $items = $this::where('idPurchaseRequest', $this->idPurchaseRequest)->get();
    $line = 0;
    foreach ($items as $item) {
      $item->lineNum = $line;
      $item->save();
      $line++;
    }
  }
}
