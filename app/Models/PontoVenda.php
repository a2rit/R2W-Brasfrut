<?php

namespace App\Models;

use App\Services\NfceFiles;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\PontoVenda
 *
 * @property int $id
 * @property string $nome
 * @property string $vendedor
 * @property string $cliente
 * @property string $modelo_nf
 * @property string $regra_distribuicao
 * @property string $regra_distribuicao_ov
 * @property string $codigo_imposto
 * @property string $codigo_imposto_ov
 * @property string $utilizacao
 * @property string $pasta_xml
 * @property string $projeto
 * @property string $projeto_ov
 * @property string $codigo_ov
 * @property string $conta_dinheiro
 * @property string $conta_troco
 * @property string|null $conta_pix
 * @property string $deposito
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $conta_cheque
 * @property string|null $deposito_servico
 * @property string|null $grupo_servico
 * @property string|null $pasta_xml_contingencia
 * @property int|null $serie
 * @property-read Collection|FormasPagamento[] $formasPagamento
 * @method static Builder|PontoVenda whereCliente($value)
 * @method static Builder|PontoVenda whereCodigoImposto($value)
 * @method static Builder|PontoVenda whereCodigoImpostoOv($value)
 * @method static Builder|PontoVenda whereCodigoOv($value)
 * @method static Builder|PontoVenda whereContaCheque($value)
 * @method static Builder|PontoVenda whereContaDinheiro($value)
 * @method static Builder|PontoVenda whereContaTroco($value)
 * @method static Builder|PontoVenda whereCreatedAt($value)
 * @method static Builder|PontoVenda whereDeposito($value)
 * @method static Builder|PontoVenda whereDepositoServico($value)
 * @method static Builder|PontoVenda whereGrupoServico($value)
 * @method static Builder|PontoVenda whereId($value)
 * @method static Builder|PontoVenda whereModeloNf($value)
 * @method static Builder|PontoVenda whereNome($value)
 * @method static Builder|PontoVenda wherePastaXml($value)
 * @method static Builder|PontoVenda wherePastaXmlContingencia($value)
 * @method static Builder|PontoVenda whereProjeto($value)
 * @method static Builder|PontoVenda whereProjetoOv($value)
 * @method static Builder|PontoVenda whereRegraDistribuicao($value)
 * @method static Builder|PontoVenda whereRegraDistribuicaoOv($value)
 * @method static Builder|PontoVenda whereUpdatedAt($value)
 * @method static Builder|PontoVenda whereUtilizacao($value)
 * @method static Builder|PontoVenda whereVendedor($value)
 * @mixin Eloquent
 * @property string|null $ci_config
 * @method static Builder|PontoVenda whereCiConfig($value)
 * @method static Builder|PontoVenda whereSerie($value)
 * @property string|null $item_gorjeta_colibri
 * @property string|null $item_gorjeta_sap
 * @property string|null $conta_gorjeta_credito
 * @property string|null $conta_gorjeta_debito
 * @method static Builder|PontoVenda newModelQuery()
 * @method static Builder|PontoVenda newQuery()
 * @method static Builder|PontoVenda query()
 * @method static Builder|PontoVenda whereContaGorjetaCredito($value)
 * @method static Builder|PontoVenda whereContaGorjetaDebito($value)
 * @method static Builder|PontoVenda whereItemGorjetaColibri($value)
 * @method static Builder|PontoVenda whereItemGorjetaSap($value)
 */
class PontoVenda extends Model
{
    protected $casts = [
        'ci_config' => 'array',
    ];

    protected $table = 'ponto_venda';

    public function processarArquivos(): void
    {
        $service = new NfceFiles($this);
        $service->scanDir($this->pasta_xml);
    }

    public function formasPagamento()
    {
        return $this->hasMany(FormasPagamento::class, "pv_id", "id");
    }

    public function processarArquivosContingencia(): void
    {
        $service = new NfceFiles($this);
        $service->processarArquivosContingencia();
    }
}
