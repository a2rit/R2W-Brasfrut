<?php

namespace App\Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\SupplierCatalog
 *
 * @property int $id
 * @property string $itemCode
 * @property string $cardCode
 * @property string $substitute
 * @property bool $inSAP
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereInSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereSubstitute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\SupplierCatalog query()
 */
class SupplierCatalog extends Model
{
    //
}
