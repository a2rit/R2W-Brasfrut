<?php

namespace App\Modules\Purchase\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PurchaseOrderExpenses
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idPurchaseOrderItems
 * @property string $expenseCode
 * @property string $lineTotal
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereExpenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereIdPurchaseOrderItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereUpdatedAt($value)
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderExpenses whereStatus($value)
 * @property string $idPurchaseOrder
 * @property string $tax
 * @property string $project
 * @property string $distributionRule
 * @property string|null $comments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses whereIdPurchaseOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Expenses whereTax($value)
 */
class Expenses extends Model
{
  protected $table = 'purchase_order_expenses';
  protected $fillable = ['idPurchaseOrder', 'expenseCode', 'lineTotal', 'tax', 'comments', 'project', 'distributionRule','costCenter','costCenter2'];

  public function saveInDB($value, $id, $projec, $distributionRules,$costCenter,$costCenter2){
    $this->idPurchaseOrder = $id;
    $this->expenseCode = $value['cPagamentos'];
    $this->lineTotal = clearNumberDouble($value['vFrete']);
    $this->project = $projec;
    $this->distributionRule = $distributionRules;
    $this->costCenter = $costCenter;
    $this->costCenter = $costCenter2;
    $this->save();
  }
}
