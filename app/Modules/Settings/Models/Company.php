<?php


namespace App\Modules\Settings\Models;
use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\Company
 *
 * @property int $id
 * @property string $company
 * @property string $cnpj
 * @property string $address
 * @property string $number
 * @property string $neighborhood
 * @property string $cep
 * @property string $city
 * @property string $telephone
 * @property string $telephone2
 * @property string $email
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereCep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereTelephone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Company query()
 */
class Company extends Model
{
  protected $fillable = ['company', 'cnpj', 'address', 'number', 'neighborhood', 'cep', 'city', 'telephone', 'telephone2', 'email'];

  public static function get($id, $default = null){
      $config = Config::where('id', '=', $code)->first();
      if($config) {
          return $config->value;
      }
      return $default;
  }

}
