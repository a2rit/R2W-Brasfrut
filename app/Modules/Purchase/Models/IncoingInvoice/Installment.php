<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Modules\Purchase\Models\IncoingInvoice\Installment
 *
 * @property int $id
 * @property int $invoice_id
 * @property float $value
 * @property Carbon $due_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Installment newModelQuery()
 * @method static Builder|Installment newQuery()
 * @method static Builder|Installment query()
 * @method static Builder|Installment whereCreatedAt($value)
 * @method static Builder|Installment whereDueDate($value)
 * @method static Builder|Installment whereId($value)
 * @method static Builder|Installment whereInvoiceId($value)
 * @method static Builder|Installment whereUpdatedAt($value)
 * @method static Builder|Installment whereValue($value)
 * @mixin Eloquent
 */
class Installment extends Model
{
    protected $table = 'incoming_invoice_installments';

    //protected $dates = ['due_date'];

    protected $casts = [
        'value' => 'float',
        //'due_date' => 'datetime:Y-m-d'
    ];

    protected $guarded = [];
}
