<?php

namespace App\Models\Porcionamento;

use App\Models\Porcionamento;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Porcionamento\Item
 *
 * @property int $id
 * @property int $porcionamento_id
 * @property string $cod_item
 * @property string $nome_item
 * @property float $quantidade_produzida
 * @property float $quantidade_gasta
 * @property string $deposito
 * @property string $tipo
 * @property float $custo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Item whereCodItem($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereCusto($value)
 * @method static Builder|Item whereDeposito($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereNomeItem($value)
 * @method static Builder|Item wherePorcionamentoId($value)
 * @method static Builder|Item whereQuantidadeGasta($value)
 * @method static Builder|Item whereQuantidadeProduzida($value)
 * @method static Builder|Item whereTipo($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read mixed $cor_linha
 * @property-read mixed $porcentagem_perda
 * @property-read mixed $url
 * @property-read \App\Models\Porcionamento\PorcentagemPerda $porcentagemPerda
 * @property-read \App\Models\Porcionamento $porcionamento
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento\Item query()
 */
class Item extends Model
{
    protected $table = "porcionamento_itens";

    public function getPorcentagemPerdaAttribute()
    {
        return PorcentagemPerda::whereCodigo($this->cod_item)->first();
    }

    public function getCorLinhaAttribute()
    {
        $p = $this->porcentagemPerda;
        if ($p) {
            if ($this->porcionamento->porcentagem_perdas_value > $p->porcentagem_aceita) {
                return 'red';
            }
            if ($this->porcionamento->porcentagem_perdas_value > $p->porcentagem_base) {
                return 'yellow';
            }
        }
        return 'white';
    }

    public function porcionamento()
    {
        return $this->belongsTo(Porcionamento::class, 'porcionamento_id', 'id');
    }

    public function porcentagemPerda()
    {
        return $this->belongsTo(PorcentagemPerda::class, 'cod_item', 'codigo');
    }

    public function getUrlAttribute()
    {
        if ($this->porcentagemPerda) {
            return route('porcionamento.porcentagemPerdaListar', ['search' => $this->cod_item]);
        }
        return '';
    }
}
