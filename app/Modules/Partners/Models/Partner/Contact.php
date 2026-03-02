<?php

namespace App\Modules\Partners\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;
use Litiano\Sap\IdeHelper\IBusinessPartners;

/**
 * App\Modules\Partners\Models\Partner\Contact
 *
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string|null $telephone
 * @property string|null $email
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact wherePartnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $line
 * @property int|null $internal_code
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereInternalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereLine($value)
 * @property-read mixed $can_delete
 * @property bool|null $delete
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Contact query()
 */
class Contact extends Model
{
    protected $fillable = ['partner_id', 'name', 'telephone', 'email', 'line', 'internal_code', 'delete'];
    protected $table = 'partners_contacts';

    /**
     * @param $partner IBusinessPartners
     */
    public function saveInSap(&$partner)
    {
        if($this->line != "") {
            $partner->ContactEmployees->SetCurrentLine((int)$this->line);
            if($this->delete) {
                if($this->can_delete) {
                    $partner->ContactEmployees->Delete();
                } else {
                    \Log::error("Não é possivel deletar ContactEmployees. Partner {$partner->CardCode}, code {$partner->ContactEmployees->InternalCode}");
                }
                return;
            }
        } elseif($partner->ContactEmployees->Name != "") {
            $partner->ContactEmployees->Add();
        }
        $partner->ContactEmployees->Name = (string)$this->name;
        $partner->ContactEmployees->MobilePhone = (string)$this->telephone;
        $partner->ContactEmployees->E_Mail = (string)$this->email;
    }

    public function getCanDeleteAttribute()
    {
        $sap = new Company(false);
        $qty = $sap->getDb()->selectOne("SELECT COUNT(*) as qty FROM [ORDR] WHERE [CntctCode] = :internalCode",
            ["internalCode" => $this->attributes["internal_code"]]);

        if($qty && $qty->qty > 0) {
            return false;
        }

        $qty = $sap->getDb()->selectOne("SELECT COUNT(*) as qty FROM [OQUT] WHERE [CntctCode] = :internalCode",
            ["internalCode" => $this->attributes["internal_code"]]);

        if($qty && $qty->qty > 0) {
            return false;
        }
        
        return true;
    }
}
