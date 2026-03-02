<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\IncoingInvoice\Expenses
 *
 * @property int $id
 * @property string $idIncoingInvoice
 * @property int $expenseCode
 * @property string $tax
 * @property float $lineTotal
 * @property string $project
 * @property string $distributionRule
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereExpenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereIdIncoingInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereLineTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\Expenses whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Expenses extends Model
{
    protected $table = 'incoing_invoice_expenses';
    
    public function saveInDB($check, $id){
        $this->idIncoingInvoice = $id;
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
