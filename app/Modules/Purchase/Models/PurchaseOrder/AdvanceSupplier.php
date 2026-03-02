<?php

namespace App\Modules\Purchase\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;


/**
 * App\AdvanceSupplier
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idPurchaseOrders
 * @property string|null $trsfrAcct
 * @property string|null $trsfrDate
 * @property string|null $trsfrRef
 * @property string|null $transferSum
 * @property string|null $cashAcct
 * @property string|null $cashSum
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereCashAcct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereCashSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereIdPurchaseOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereTransferSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereTrsfrAcct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereTrsfrDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereTrsfrRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\AdvanceSupplier query()
 */
class AdvanceSupplier extends Model
{

}
