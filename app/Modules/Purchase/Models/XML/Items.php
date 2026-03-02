<?php

namespace App\Modules\Purchase\Models\XML;

use Illuminate\Database\Eloquent\Model;

/**
 * App\xmlItems
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idImportXML
 * @property string|null $codPartners
 * @property string|null $EAN
 * @property string|null $name
 * @property string|null $uCom
 * @property float|null $qCom
 * @property float|null $vUnCom
 * @property float|null $vProd
 * @property string|null $CFOP
 * @property string|null $NCM
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereCFOP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereCodPartners($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereEAN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereIdImportXML($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereNCM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereQCom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereUCom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereVProd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereVUnCom($value)
 * @property string|null $itemCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Item whereItemCode($value)
 * @property string $ICMS
 * @property string $IPI
 * @property string $PIS
 * @property string $COFINS
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items whereCOFINS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items whereICMS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items whereIPI($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Items wherePIS($value)
 */
class Items extends Model
{
  protected $table = 'xml_items';
}
