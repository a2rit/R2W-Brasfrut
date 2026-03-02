<?php

namespace App\Modules\Purchase\Models\ReceiptGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

/**
 * App\receiptGoodsExpenses
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idReceiptGoods
 * @property string $expenseCode
 * @property string $lineTotal
 * @property string|null $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereExpenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereIdReceiptGoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoodsExpenses whereUpdatedAt($value)
 * @property string $idReceiptGoodsItems
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses whereIdReceiptGoodsItems($value)
 * @property string $tax
 * @property string $project
 * @property string $distributionRule
 * @property string|null $comments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\Expenses whereTax($value)
 */
class Expenses extends Model
{
  protected $table = 'receipt_goods_expenses';

  public function saveInDB($check, $id){
      $this->idReceiptGoods = $id;
      $this->expenseCode= $check['cPagamentos'];
      $this->lineTotal=  clearNumberDouble($check['vFrete']);
      $this->save();
  }

  public function saveCopyInDB($idRGE){
    $items = DB::select("select * from purchase_order_expenses T0 where T0.idPurchaseOrderItems = '{$idRGE}'");
    if(!empty($items)){
      foreach ($items as $key => $value) {
          $RGE = new ReceiptGoodsExpenses();
          $RGE->idReceiptGoods = $value->idPurchaseOrderItems;;
          $RGE->expenseCode = $value->expenseCode;
          $RGE->lineTotal = $value->lineTotal;
          $RGE->status = $value->status;
          $RGE->save();
      }
    }

  }
}
