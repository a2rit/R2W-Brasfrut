<?php

namespace App\Modules\Partners\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Litiano\Sap\IdeHelper\IBusinessPartners;
Use Litiano\Sap\Company;

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
class Address extends Model
{
    protected $fillable = ['partner_id', 'name', 'type', 'postcode', 'street','typeofaddress','county','U_SKILL_indIEDest','U_SKILL_IE','number', 'complement', 'neighborhood', 'city', 'state', 'line', 'delete', 'country'];
    protected $table = 'partners_addresses';

    /**
     * @param $partner IBusinessPartners
     * @throws \Exception
     */
    public function saveInSap(&$partner)
    {
            
        if ($this->line != "") {
            $partner->Addresses->SetCurrentLine((int)$this->line);

            if ($this->delete) {
                $partner->Addresses->Delete();
                $this->delete();
                return;
            }
        }

        if($this->delete) {
            return;
        }
        
        $partner->Addresses->AddressName = (string)$this->name;
        $partner->Addresses->AddressType = (int)$this->type;
        $partner->Addresses->ZipCode = (string)$this->postcode;
        $partner->Addresses->Street = (string)$this->street;
        $partner->Addresses->StreetNo = (string)$this->number;
        $partner->Addresses->BuildingFloorRoom = (string)$this->complement;
        $partner->Addresses->Block = (string)$this->neighborhood;
        $partner->Addresses->TypeOfAddress = (string)$this->typeofaddress;
        $partner->Addresses->City = (string)$this->city;
        $partner->Addresses->State = (string)$this->state;
        $partner->Addresses->Country = (string) $this->country;
        $partner->Addresses->County = $this->getCountyCode($this->city, $this->state);
        $partner->Addresses->UserFields->fields->Item("U_SKILL_IE")->value = (string) $this->U_SKILL_IE;
        $partner->Addresses->UserFields->fields->Item("U_SKILL_indIEDest")->value = (string) $this->U_SKILL_indIEDest;
        $partner->Addresses->Add();
    }

    public function saveFromXML($id, $end, $type = 0){
      $this->partner_id = $id;
      $this->name = 'EMITENTE';
      $this->type = $type;
      $this->postcode = $end->CEP;
      $this->street = $end->xLgr;
      $this->number = $end->nro;
      $this->complement = $end->xCpl;
      $this->neighborhood = $end->xBairro;
      $this->city = $end->xMun;
      $this->state = $end->UF;
      $this->delete = 0;
      $this->save();

    }

    public function getCountyCode($name, $state){
        $sap = new Company(false);
        $absId = $sap->query("SELECT T0.[AbsId] FROM OCNT T0 WHERE T0.[Name] LIKE '%$name%' AND T0.[State] = '$state'");
        
        return !empty($absId) ? $absId[0]['AbsId'] : 537;
    }
}
