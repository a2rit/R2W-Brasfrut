<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\Config
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $code
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Config query()
 */
class Config extends Model
{
    protected $fillable = ['code', 'value'];
    protected $table = 'settings';

    public static function get($code, $default = null)
    {
        $config = Config::where('code', '=', $code)->first();
        if($config) {
            return $config->value;
        }
        return $default;
    }
}
