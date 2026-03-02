<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Settings\Models\Lofted
 *
 * @property int $id
 * @property string $docNum
 * @property string $docName
 * @property string $idUser
 * @property float $first
 * @property float $last
 * @property float $quantity
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereDocName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereDocNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Settings\Models\Lofted whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Lofted extends Model
{
    protected $table = 'lofted_approveds';
    protected $fillable = [
            'docNum',
            'name',
            'docName',
            'idUser',
            'first',
            'last',
            'quantity',
            'status',
            'cost_center_id',
            'cost_center_2_id'
      ];

    const PURCHASE_ORDER = 0,
          INCOING_INVOICE = 1;

      const DOCUMENTS_TEXTS = [
            self::PURCHASE_ORDER => "Pedido de Compras",
            self::INCOING_INVOICE => "NFE Serviços",
      ];
    
    const STATUS_OPEN = 1,
          STATUS_CLOSED = 0;

}
