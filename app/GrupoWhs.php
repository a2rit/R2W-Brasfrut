<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\GrupoWhs
 *
 * @property int $id
 * @property string $idUser
 * @property string $whsCode
 * @property string $whsName
 * @property string $type
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereWhsCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GrupoWhs whereWhsName($value)
 * @mixin \Eloquent
 */
class GrupoWhs extends Model
{
    protected $fillable = [
        'idUser', 'whsCode', 'whsName', 'type', 'status'
    ];
}
