<?php

namespace App\Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\Issuer
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $cpf_cnpj
 * @property string|null $sap_code
 * @property string $social_name
 * @property string|null $fantasy_name
 * @property string $street
 * @property string $number
 * @property string|null $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $city_code
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $telephone
 * @property string $state_registration
 * @property string $municipal_registration
 * @property string|null $cnae
 * @property string $crt
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Purchase\Models\Invoice[] $invoices
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCnae($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCpfCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereCrt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereFantasyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereMunicipalRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereSapCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereSocialName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereStateRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereUpdatedAt($value)
 * @property string $state
 * @property string $postcode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\Issuer query()
 */
class Issuer extends Model
{
    protected $table = 'purchase_issuers';

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'issuer_id', 'id');
    }

    /**
     * @param string $cpfCnpj
     * @return Issuer|null
     */
    public static function findByCpfCnpj(string $cpfCnpj)
    {
        $cpfCnpj = preg_replace("/[^0-9]/", "", $cpfCnpj);

        return Issuer::whereCpfCnpj($cpfCnpj)->first();
    }

    public static function updateOrCreateByXml(\SimpleXMLElement $xml)
    {
        $issuer = self::findByCpfCnpj($xml->CNPJ ? $xml->CNPJ : $xml->CPF);
        if(!$issuer) {
            $issuer = new Issuer();
        }
        $issuer->cpf_cnpj = $xml->CNPJ ? $xml->CNPJ : $xml->CPF;
        $issuer->fantasy_name = $xml->xFant;
        $issuer->social_name = $xml->xNome;

        $issuer->street = $xml->enderEmit->xLgr;
        $issuer->number = $xml->enderEmit->nro;
        $issuer->complement = $xml->enderEmit->xCpl;
        $issuer->neighborhood = $xml->enderEmit->xBairro;
        $issuer->city_code = $xml->enderEmit->cMun;
        $issuer->city = $xml->enderEmit->xMun;
        $issuer->state = $xml->enderEmit->UF;
        $issuer->postcode = $xml->enderEmit->CEP;
        $issuer->country_code = $xml->enderEmit->cPais;
        $issuer->country = $xml->enderEmit->xPais;
        $issuer->telephone = $xml->enderEmit->fone;

        $issuer->state_registration = $xml->IE;
        $issuer->municipal_registration = $xml->IM;
        $issuer->cnae = $xml->CNAE;
        $issuer->crt = $xml->CRT;
        $issuer->save();
        return $issuer;
    }
}
