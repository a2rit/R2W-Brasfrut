<?php

namespace App\Models;

use App\Models\Porcionamento\Item;
use App\Models\Porcionamento\PorcentagemPerda;
use App\User;
use Carbon\Carbon;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IMaterialRevaluation;
use Litiano\Sap\NewCompany;

/**
 * App\Models\Porcionamento
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $cod_item
 * @property string $nome_item
 * @property float $quantidade
 * @property string $cod_fornecedor
 * @property string $nome_fornecedor
 * @property string $nota_fiscal
 * @property int|null $cod_entrada
 * @property int|null $cod_saida
 * @property int|null $cod_reavaliacao
 * @property string $deposito
 * @property string $unidade_medida
 * @property float $preco
 * @property int $documento_id
 * @property int $linha
 * @property string $projeto
 * @property string $regra_distribuicao
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool|null $salvando
 * @property-read Collection|Item[] $itens
 * @method static Builder|Porcionamento whereCodEntrada($value)
 * @method static Builder|Porcionamento whereCodFornecedor($value)
 * @method static Builder|Porcionamento whereCodItem($value)
 * @method static Builder|Porcionamento whereCodSaida($value)
 * @method static Builder|Porcionamento whereCreatedAt($value)
 * @method static Builder|Porcionamento whereDeposito($value)
 * @method static Builder|Porcionamento whereDocumentoId($value)
 * @method static Builder|Porcionamento whereId($value)
 * @method static Builder|Porcionamento whereLinha($value)
 * @method static Builder|Porcionamento whereNomeFornecedor($value)
 * @method static Builder|Porcionamento whereNomeItem($value)
 * @method static Builder|Porcionamento whereNotaFiscal($value)
 * @method static Builder|Porcionamento wherePreco($value)
 * @method static Builder|Porcionamento whereProjeto($value)
 * @method static Builder|Porcionamento whereQuantidade($value)
 * @method static Builder|Porcionamento whereRegraDistribuicao($value)
 * @method static Builder|Porcionamento whereSalvando($value)
 * @method static Builder|Porcionamento whereUnidadeMedida($value)
 * @method static Builder|Porcionamento whereUpdatedAt($value)
 * @method static Builder|Porcionamento whereUserId($value)
 * @mixin Eloquent
 * @property int|null $usuario_autorizador_id
 * @property string|null $data_autorizacao
 * @property int|null $justificativa_id
 * @property string|null $justificativa
 * @property-read mixed $autorizado
 * @property-read mixed $porcentagem_perdas_value
 * @property-read Collection|Item[] $perdas
 * @method static Builder|Porcionamento whereDataAutorizacao($value)
 * @method static Builder|Porcionamento whereJustificativa($value)
 * @method static Builder|Porcionamento whereJustificativaId($value)
 * @method static Builder|Porcionamento whereUsuarioAutorizadorId($value)
 * @property-read PorcentagemPerda $porcentagemPerda
 * @property-read User|null $usuarioAutorizador
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Porcionamento query()
 * @property-read float $source_quantity
 */
class Porcionamento extends Model
{
    public $incrementing = false;
    protected $table = "porcionamentos";
    protected $dates = ['data_autorizacao', 'created_at', 'updated_at'];

    public function itens()
    {
        return $this->hasMany(Item::class, "porcionamento_id", "id");
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function salvarSAP()
    {
        $sap = NewCompany::getInstance()->getCompany();

        if (!$this->cod_saida && bccomp($this->quantidade, $this->source_quantity, 3) === 1) {
            throw new Exception('A quantidade em estoque é menor que a quantidade utilizada.');
        }

        if (!$this->cod_reavaliacao) {

            $itemSap = NewCompany::getDb()->table('OITW')->where('ItemCode', $this->cod_item)
                ->where('WhsCode', $this->deposito)->first(['AvgPrice']);

            if (!$itemSap) {
                throw new Exception("Custo do item {$this->cod_item} no depósito {$this->deposito} não foi encontrado!");
            }

            if (bccomp($itemSap->AvgPrice, $this->preco, 3) === 0) {
                // Reavaliação não necessária!
                $this->cod_reavaliacao = -1;
            } else {
                /** @var IMaterialRevaluation $rev */
                $rev = $sap->GetBusinessObject(BoObjectTypes::oMaterialRevaluation);
                $rev->RevalType = 'P';
                $rev->Comments = 'Adicionado via aplicação web. Porcionamento id: ' . $this->id;

                $rev->Lines->ItemCode = $this->cod_item;
                $rev->Lines->Price = (float)$this->preco;
                $rev->Lines->WarehouseCode = $this->deposito;
                $rev->Lines->Project = $this->projeto;
                $rev->Lines->DistributionRule = $this->regra_distribuicao;

                if ($rev->Add() !== 0) {
                    throw new Exception("Erro ao reavaliar estoque: {$sap->GetLastErrorDescription()}");
                }
                $this->cod_reavaliacao = $sap->GetNewObjectKey();
            }

            $this->save();
        }

        if (!$this->cod_saida && $this->cod_reavaliacao) {
            /** @var IDocuments $saida */
            $saida = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenExit); //oInventoryGenExit
            $saida->PaymentGroupCode = 1; //Lista de preços A
            $saida->Comments = "Saida por porcionamento. PorcionamentoId = " . $this->id;
            $saida->Lines->ItemCode = $this->cod_item;
            $saida->Lines->Quantity = (double)$this->quantidade;
            $saida->Lines->WarehouseCode = $this->deposito;
            $saida->Lines->ProjectCode = $this->projeto;
            $saida->Lines->CostingCode = $this->regra_distribuicao;
            $saida->Lines->Price = (float)$this->preco;
            $saida->Lines->Add();

            if ($saida->Add() !== 0) {
                throw new Exception("Erro ao dar saída de itens: " . $sap->GetLastErrorDescription());
            }
            $this->cod_saida = $sap->GetNewObjectKey();
            $this->save();
        }

        if (!$this->cod_entrada && $this->cod_saida && $this->cod_reavaliacao) {
            /** @var IDocuments $entrada */
            $entrada = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenEntry); //oInventoryGenEntry
            $entrada->PaymentGroupCode = 1; //Lista de preços A
            $entrada->Comments = "Entrada por porcionamento. Fornecedor: $this->cod_fornecedor, NF: $this->nota_fiscal. PorcionamentoId: $this->id.";

            foreach ($this->itens as $item) {
                $entrada->Lines->ItemCode = $item->cod_item;
                $entrada->Lines->Quantity = (double)$item->quantidade_produzida;
                $entrada->Lines->WarehouseCode = $item->deposito;
                $entrada->Lines->ProjectCode = $this->projeto;
                $entrada->Lines->CostingCode = $this->regra_distribuicao;
                $entrada->Lines->UnitPrice = (double)($item->custo / $item->quantidade_produzida);
                $entrada->Lines->Add();
            }

            if ($entrada->Add() !== 0) {
                throw new Exception("Erro ao dar entrada de itens: " . $sap->GetLastErrorDescription());
            }

            $this->cod_entrada = $sap->GetNewObjectKey();
            $this->save();
        }

        return true;
    }

    public function perdas()
    {
        return $this->hasMany(Item::class, "porcionamento_id", "id")
            ->where("cod_item", "=", "ip00172");
    }

    public function getPorcentagemPerdasValueAttribute()
    {
        $perda = $this->perdas->sum("quantidade_gasta");
        return ($perda / $this->quantidade) * 100;
    }

    public function porcentagemPerda()
    {
        return $this->belongsTo(PorcentagemPerda::class, "cod_item", "codigo");
    }

    public function getAutorizadoAttribute()
    {
        if ($this->porcentagemPerda && $this->porcentagem_perdas_value > $this->porcentagemPerda->porcentagem_aceita) {
            if (!$this->data_autorizacao) {
                return false;
            }
        }
        return true;
    }

    public function usuarioAutorizador()
    {
        return $this->belongsTo(User::class, 'usuario_autorizador_id', 'id');
    }

    public function getSourceQuantityAttribute()
    {
        $item = NewCompany::getDb()
            ->table('OITW')
            ->where('OITW.WhsCode', $this->deposito)
            ->where('OITW.ItemCode', $this->cod_item)
            ->first(['OITW.OnHand']);

        if ($item) {
            return $item->OnHand;
        }

        return 0;
    }
}
