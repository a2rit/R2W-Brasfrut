<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CashFlow
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $value
 * @property string $status
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereValue($value)
 * @property string|null $module
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\CashFlow query()
 */
class CashFlow extends Model
{
    protected $table = 'cash_flows';
    protected $fillable = ['idUser', 'description', 'module'];

    public function updateStatus($obj){
      if($obj->status == 0){
        $obj->status = 1;
      }else{
        $obj->status = 0;
      }
      $obj->save();
    }
}
