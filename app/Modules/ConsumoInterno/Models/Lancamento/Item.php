<?php

namespace App\Modules\ConsumoInterno\Models\Lancamento;

use App\Modules\ConsumoInterno\Models\Lancamento;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\ConsumoInterno\Models\Lancamento\Item
 *
 * @property int $id
 * @property int $ci_id
 * @property float $qtd
 * @property string $cod_sap
 * @property string $descricao
 * @property int|null $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereCiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereCodSap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereQtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Modules\ConsumoInterno\Models\Lancamento $lancamento
 * @property-read \App\User $user
 * @property string|null $projeto
 * @property string|null $centro_custo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereCentroCusto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item whereProjeto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento\Item query()
 */
class Item extends Model
{
    protected $table = "consumo_interno_itens";

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function lancamento()
    {
        return $this->hasOne(Lancamento::class, "id", "ci_id");
    }

    public function getQtdAttribute()
    {
        return number_format($this->attributes["qtd"], 3);
    }
}
