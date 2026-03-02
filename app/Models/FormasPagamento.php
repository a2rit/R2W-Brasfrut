<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FormasPagamento
 *
 * @property int $id
 * @property int $pv_id
 * @property string $chave_colibri
 * @property string $codigo_unico
 * @property string $valor
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $nome
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereChaveColibri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereCodigoUnico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento wherePvId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento whereValor($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FormasPagamento query()
 */
class FormasPagamento extends Model
{
    protected $table = "formas_pagamento";

    public function getNomeAttribute()
    {
        $arr = json_decode($this->valor, true);
        return $arr["nome"] ?? '';
    }
}
