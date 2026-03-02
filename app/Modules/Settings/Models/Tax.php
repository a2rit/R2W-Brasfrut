<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\Tax
 *
 * @property int $id
 * @property string $codSAP
 * @property string|null $name
 * @property string $idUser
 * @property string|null $ICMS
 * @property string|null $IPI
 * @property string|null $PIS
 * @property string|null $COFINS
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereCOFINS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereICMS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereIPI($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax wherePIS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Tax whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tax extends Model
{
  protected $table = 'taxes';
  protected $fillable = ['idUser','codSAP','name','ICMS','IPI','COFINS','PIS','status'];
}
