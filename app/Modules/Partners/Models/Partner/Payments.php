<?php

namespace App\Modules\Partners\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Litiano\Sap\IdeHelper\IBusinessPartners;

/**
 * App\Modules\Partners\Models\Partner\Address
 *
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property int $type
 * @property string $postcode
 * @property string $street
 * @property string $number
 * @property string $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $line
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereLine($value)
 * @property bool|null $delete
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereDeletedAt($value)
 * @property string|null $country
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Address whereCountry($value)
 */
class Payments extends Model
{
    protected $fillable = ['partner_id', 'code','description'];
    protected $table = 'partners_payments';

    /**
     * @param $partner IBusinessPartners
     * @throws \Exception
     */
    
}
