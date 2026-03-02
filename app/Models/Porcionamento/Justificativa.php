<?php

namespace App\Models\Porcionamento;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Porcionamento\Justificativa
 *
 * @property int $id
 * @property string $justificativa
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa whereJustificativa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Justificativa query()
 */
class Justificativa extends Model
{
    protected $table = 'porcionamento_justificativas';
}
