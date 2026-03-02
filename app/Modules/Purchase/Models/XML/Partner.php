<?php

namespace App\Modules\Purchase\Models\XML;

use Illuminate\Database\Eloquent\Model;
/**
 * App\xmlPartners
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idImportXML
 * @property string|null $name
 * @property string|null $cnpj
 * @property string|null $IE
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereIE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereIdImportXML($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Partner query()
 */
class Partner extends Model
{
  protected $table = 'xml_partners';

  public function saveInDB($xml, $id){
      $this->idImportXML = $id;
      $this->name = $xml->NFe->infNFe->emit->xNome;
      $this->CNPJ = formatCNPJ($xml->NFe->infNFe->emit->CNPJ);
      $this->IE =  $xml->NFe->infNFe->emit->IE;
      $this->save();
  }

}
