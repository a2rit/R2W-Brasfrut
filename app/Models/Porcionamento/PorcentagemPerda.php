<?php

namespace App\Models\Porcionamento;

use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;

/**
 * App\Models\Porcionamento\LossPercent
 *
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $codigo
 * @property float $porcentagem_base
 * @property float $porcentagem_aceita
 * @property string|null $comentario
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda whereComentario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda wherePorcentagemAceita($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda wherePorcentagemBase($value)
 * @property-read mixed $delete_url
 * @property-read mixed $item_name
 * @property-read \Litiano\Sap\Models\Item $itemSap
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\PorcentagemPerda query()
 */
class PorcentagemPerda extends Model
{
    protected $table = "porcionamento_porcentagens_perda";
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = "codigo";
    protected $casts = [
        'porcentagem_base' => 'double',
        'porcentagem_aceita' => 'double',
    ];
    protected $appends = ['item_name', 'delete_url'];

    public function getDeleteUrlAttribute()
    {
        return route('porcionamento.porcentagemPerdaExcluir', ['codigo'=>$this->codigo]);
    }

    public function getItemNameAttribute()
    {
        //dd($this->getItemSapAttribute());
        if($this->itemSap) {
            return $this->itemSap->ItemName;
        }
        return '';
    }

    public function itemSap()
    {
        return $this->belongsTo(\Litiano\Sap\Models\Item::class, 'codigo', 'ItemCode');
    }


}
