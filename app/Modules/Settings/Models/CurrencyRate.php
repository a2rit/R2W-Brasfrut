<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\CurrencyRate
 *
 * @property int $id
 * @property int $idUser
 * @property string $posting_date
 * @property string $coin
 * @property float $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate wherePostingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CurrencyRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurrencyRate extends Model
{
    protected $fillable = ['idUser', 'posting_date', 'coin', 'rate'];
}
