<?php

namespace App\Models\NFCe;

use App\ErrorTrait;
use App\Exceptions\SapIntegrationException;
use App\Exceptions\StockErrorException;
use App\Jobs\NFCe\CriarOP;
use App\Jobs\NFCe\EntradaOP;
use App\Jobs\NFCe\FecharOP;
use App\Jobs\NFCe\LiberarOP;
use App\Jobs\Queue;
use App\Models\NFCe;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoProductionOrderStatusEnum;
use Litiano\Sap\IdeHelper\IProductionOrders;
use Litiano\Sap\NewCompany;
use Throwable;

/**
 * App\Models\NFCe\Item
 *
 * @property int $id
 * @property int $nfc_id
 * @property string $codigo_pdv
 * @property string $nome
 * @property string $unidade_comercial
 * @property float $quantidade
 * @property float $valor_unitario
 * @property float $desconto
 * @property float $outros_valores
 * @property int $cfop
 * @property int $ncm
 * @property string|null $tipo
 * @property string|null $codigo_sap
 * @property string|null $codigo_entrada_item
 * @property string|null $status_op
 * @property int|null $codigo_op
 * @property string $cst_icms
 * @property string $cst_pis
 * @property string $cst_cofins
 * @property string $cst_ipi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $grupo
 * @property float|null $total
 * @property-read mixed $deposito
 * @property-read mixed $erro_estoque
 * @property-read mixed $is_service
 * @property-read mixed $qtd_sap
 * @property-read NFCe $nfc
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item whereCfop($value)
 * @method static Builder|Item whereCodigoEntradaItem($value)
 * @method static Builder|Item whereCodigoOp($value)
 * @method static Builder|Item whereCodigoPdv($value)
 * @method static Builder|Item whereCodigoSap($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereCstCofins($value)
 * @method static Builder|Item whereCstIcms($value)
 * @method static Builder|Item whereCstIpi($value)
 * @method static Builder|Item whereCstPis($value)
 * @method static Builder|Item whereDesconto($value)
 * @method static Builder|Item whereErroId($value)
 * @method static Builder|Item whereGrupo($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereNcm($value)
 * @method static Builder|Item whereNfcId($value)
 * @method static Builder|Item whereNome($value)
 * @method static Builder|Item whereOutrosValores($value)
 * @method static Builder|Item whereQuantidade($value)
 * @method static Builder|Item whereStatusOp($value)
 * @method static Builder|Item whereTipo($value)
 * @method static Builder|Item whereTotal($value)
 * @method static Builder|Item whereUnidadeComercial($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item whereValorUnitario($value)
 * @mixin Eloquent
 */
class Item extends Model
{
    use ErrorTrait;

    const STATUS_OP_PLANEJADO = "PLANEJADO";
    const STATUS_OP_LIBERADO = "LIBERADO";
    const STATUS_OP_ENTREGUE = "ENTREGUE";
    const STATUS_OP_FECHADO = "FECHADO";
    const STATUS_OP_CANCELADO = "CANCELADO";
    const TYPE_IP = 'IP';
    const TYPE_IV = 'IV';

    protected $table = 'nfc_itens';
    protected $_qtdSAP;

    /**
     * Fluxo de ordem de produção
     * 1 - Criar OP -> STATUS_OP_PLANEJADO
     * 2 - Liberar OP -> STATUS_OP_LIBERADO
     * 3 - Entregar OP -> STATUS_OP_ENTREGUE - Obs.: Ocorre erro caso o estoque de algum subitem esteja abaixo do necessário.
     * 4 - Fechar OP -> STATUS_OP_FECHADO
     *
     * @return void
     */
    public static function tarefasOP()
    {
//        self::tarefaCriarOP(); // Check Stock
        self::tarefaLiberarOP();
        self::tarefaEntradaItem();
        self::tarefaFecharOP();
    }

    protected static function tarefaLiberarOP()
    {
        $items = Item::where('tipo', '=', self::TYPE_IP)
            ->where('status_op', '=', self::STATUS_OP_PLANEJADO)
            ->get(['id']);

        foreach ($items as $item) {
            LiberarOP::dispatch($item->id)->onQueue(Queue::QUEUE_LOW);
        }
    }

    protected static function tarefaEntradaItem()
    {
        $items = Item::where('tipo', '=', self::TYPE_IP)
            ->where('status_op', '=', self::STATUS_OP_LIBERADO)
            ->get(['id']);

        /** @var Item $item */
        foreach ($items as $item) {
            EntradaOP::dispatch($item->id)->onQueue($item->erro()->exists() ? Queue::QUEUE_VERY_LOW : Queue::QUEUE_LOW);
        }
    }

    protected static function tarefaFecharOP()
    {
        $items = Item::where('tipo', '=', self::TYPE_IP)
            ->where('status_op', '=', self::STATUS_OP_ENTREGUE)
            ->get(['id']);

        /** @var Item $id */
        foreach ($items as $item) {
            FecharOP::dispatch($item->id)->onQueue(Queue::QUEUE_LOW);
        }
    }

    /**
     * @deprecated
     */
    protected static function tarefaCriarOP()
    {
        $itens = Item::where('tipo', '=', self::TYPE_IP)
            ->whereNull('status_op')
            ->get(['id']);

        foreach ($itens as $item) {
            CriarOP::dispatch($item->id)->onQueue(Queue::QUEUE_LOW);
        }
    }

    /**
     * @TODO Optimizar a instancia da Classe SAP;
     *
     * 1- Criar a ordem de produção
     * 2- Setar como liberado
     * 3- Dar entrada de produto acabado ou concluido.
     * @throws Exception
     */

    public function criarOrdemProducao(): bool
    {
        if ($this->tipo != "IP" || !$this->codigo_sap || $this->codigo_op) {
            return false;
        }
        app('NFCeLogger')->info("Criando ordem de produção", $this->only(['id', 'nfc_id', 'codigo_sap']));

        $sap = NewCompany::getInstance()->getCompany();
        $ordem = $sap->GetBusinessObject(202); // oProductionOrders
        /** Cabeçalho do Documento */
        $ordem->ItemNo = $this->codigo_sap; // Código do Item
        $ordem->PlannedQuantity = (double)$this->quantidade; // Quantidade a ser produzida
        $ordem->DueDate = $this->nfc->data_emissao->format("d/m/Y"); // Vencimento
        $ordem->PostingDate = $this->nfc->data_emissao->format("d/m/Y"); // Data do Pedido
        $ordem->Warehouse = $this->nfc->pv->deposito;
        $ordem->Remarks = "Adicionado via aplicação web.";

        /** Campos definidos pelo usuário */
        $ordem->UserFields->Fields->Item("U_A2R_CUPOM_FISCAL")->Value = (string)$this->nfc->chave;
        $ordem->UserFields->Fields->Item("U_A2R_PDV")->Value = (string)$this->nfc->pv->nome;
        $ordem->UserFields->Fields->Item("U_A2R_NUMERO_VENDA")->Value = (string)$this->nfc->id;

        if ($ordem->Add() != "0") {
            throw new SapIntegrationException(
                "Erro ao criar Ordem de Produção do item $this->codigo_sap: {$sap->GetLastErrorDescription()}.",
                $sap->GetLastErrorCode()
            );
        }

        $this->destroyError();

        $this->codigo_op = $sap->GetNewObjectKey();
        $this->status_op = self::STATUS_OP_PLANEJADO;
        $this->save();
        app('NFCeLogger')->info("Ordem de produção criada com sucesso.", $this->only(['id', 'nfc_id', 'codigo_sap', 'codigo_op']));

        return true;
    }

    protected function getSapItemBy($field) {
        try {
            if (!in_array($field, ['ItemCode', 'SWW'])) {
                throw new Exception("Invalid field {$field}");
            }
            $item = NewCompany::getDb()
                ->select("SELECT TOP 1 OITM.ItemCode as codigo_sap, OITT.Code as ip, OITM.ItmsGrpCod FROM OITM
                                left join OITT on OITM.ItemCode = OITT.Code
                                where OITM.{$field} = :itemCode", ['itemCode' => $this->codigo_pdv]);

            return $item[0] ?? null;
        } catch (Throwable $e) {
            app('NFCeLogger')->alert("Error on getSapCodeByAlternativeCode({$this->codigo_pdv}): {$e->getMessage()}");
        }
        return null;
    }

    public function getItemSap()
    {
        $itemSap = $this->getSapItemBy('ItemCode');
        if (!$itemSap) {
            $itemSap = $this->getSapItemBy('SWW');
        }

        if ($itemSap) {
            $itemSap = (array)$itemSap;
            if ($itemSap['ip'] == null) {
                $this->tipo = self::TYPE_IV;
            } else {
                $this->tipo = self::TYPE_IP;
            }
            $this->codigo_sap = $itemSap['codigo_sap'];
            $this->grupo = $itemSap["ItmsGrpCod"];
            $this->destroyError();
            $this->save();

            return $itemSap;
        } else {
            $this->createOrUpdateError(
                new Exception("Item não encontrado no SAP. Código PDV: $this->codigo_pdv, Item: $this->nome"),
                $this->nfc->data_emissao,
                $this->nfc->pv_id
            );
            return false;
        }
    }

    /**
     * @throws Exception
     */
    public function liberarOrdemProducao(): bool
    {
        if ($this->status_op !== self::STATUS_OP_PLANEJADO) {
            return false;
        }
        app('NFCeLogger')->info("Liberando ordem de produção.", $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap']));
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IProductionOrders $ordem */
        $ordem = $sap->GetBusinessObject(BoObjectTypes::oProductionOrders);
        $ordem->GetByKey($this->codigo_op);

//        if ($ordem->ProductionOrderStatus === BoProductionOrderStatusEnum::boposCancelled) {
//            app('NFCeLogger')->warning("Ordem de produção cancelada.", $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap']));
//            $this->destroyError();
//            $this->status_op = self::STATUS_OP_CANCELADO;
//            $this->save();
//
//            return true;
//        }

        $ordem->ProductionOrderStatus = BoProductionOrderStatusEnum::boposReleased;
        if ($ordem->Update() != "0") {
            $msg = "Erro ao Liberar Ordem de Produção do item: {$this->codigo_sap}, " .
                "Nº OP: {$this->codigo_op}. {$sap->GetLastErrorDescription()}.";

            throw new SapIntegrationException($msg, $sap->GetLastErrorCode());
        }

        $this->destroyError();

        $this->status_op = self::STATUS_OP_LIBERADO;
        $this->save();
        app('NFCeLogger')->info("Ordem de produção liberada com sucesso", $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap']));

        return true;
    }

    /**
     * @throws Exception
     */
    public function entradaProdutoAcabado(): bool
    {
        if ($this->status_op !== self::STATUS_OP_LIBERADO) {
            return false;
        }

        $onHandQuery = NewCompany::getDb()
            ->table('OITW')
            ->whereColumn('OITW.ItemCode', 'WOR1.ItemCode')
            ->whereColumn('OITW.WhsCode', 'WOR1.wareHouse')
            ->select(['OITW.OnHand'])
        ;
        $hasStockError = NewCompany::getDb()
            ->table('WOR1')
            ->where('WOR1.DocEntry', $this->codigo_op)
            ->groupBy(['WOR1.ItemCode', 'WOR1.wareHouse'])
            ->havingRaw("SUM(WOR1.PlannedQty) > ({$onHandQuery->toSql()})")
            ->exists();

        if ($hasStockError) {
            throw new StockErrorException("Ordem de produção {$this->codigo_op} com itens abaixo do estoque necessário.");
        }

//        app('NFCeLogger')->info("Entregando ordem de produção.", $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap']));

        $sap = NewCompany::getInstance()->getCompany();
        $entrada = $sap->GetBusinessObject(59);
        $entrada->DocDate = $this->nfc->data_emissao->format("d/m/Y");
        $entrada->Comments = "Adicionado via aplicação web.";
        //$entrada->UserFields->Fields->Item("U_A2R_CUPOM_FISCAL")->Value;
        //$entrada->UserFields->Fields->Item("U_A2R_PDV")->Value;
        //$entrada->UserFields->Fields->Item("U_A2R_NUMERO_VENDA")->Value;

        $entrada->Lines->BaseEntry = (int)$this->codigo_op; // numero da OP

        $ret = $entrada->Add();
        if ($ret != "0") {
            $msg = "Erro ao dar Entrada na Ordem de Produção do item: $this->codigo_sap, " .
                "Nº OP: $this->codigo_op. {$ret}: {$sap->GetLastErrorDescription()}.";

            throw new SapIntegrationException($msg, $sap->GetLastErrorCode());
        }

        $this->destroyError();

        $this->status_op = self::STATUS_OP_ENTREGUE;
        $this->codigo_entrada_item = $sap->GetNewObjectKey();
        $this->save();
        app('NFCeLogger')->info(
            "Ordem de produção entregue com sucesso",
            $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap', 'codigo_entrada_item'])
        );

        return true;
    }

    /**
     * @throws Exception
     */
    public function fecharOrdemProducao(): bool
    {
        if ($this->status_op !== self::STATUS_OP_ENTREGUE) {
            return false;
        }
        app('NFCeLogger')->info("Fechando ordem de produção", $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap']));
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IProductionOrders $ordem */
        $ordem = $sap->GetBusinessObject(202); // oProductionOrders
        $ordem->GetByKey($this->codigo_op);

        if ($ordem->ProductionOrderStatus === BoProductionOrderStatusEnum::boposClosed) {
            return $this->setProductionOrderClosed();
        }

        $ordem->ProductionOrderStatus = BoProductionOrderStatusEnum::boposClosed;
        if ($ordem->Update() != "0") {
            $msg = "Erro ao Fechar Ordem de Produção do item: $this->codigo_sap, Nº OP: " .
                "$this->codigo_op. {$sap->GetLastErrorDescription()}.";

            throw new SapIntegrationException($msg, $sap->GetLastErrorCode());
        }

        return $this->setProductionOrderClosed();
    }

    protected function setProductionOrderClosed(): bool
    {
        $this->destroyError();

        $this->status_op = self::STATUS_OP_FECHADO;
        $this->save();
        app('NFCeLogger')->info(
            "Ordem de produção fechada com sucesso.",
            $this->only(['id', 'nfc_id', 'codigo_op', 'codigo_sap', 'codigo_entrada_item'])
        );

        return true;
    }

    public function getQtdSapAttribute()
    {
        if (isset($this->_qtdSAP)) {
            return $this->_qtdSAP;
        }
        $item = NewCompany::getDb()->select(
            "select OITW.OnHand from OITW where OITW.ItemCode = :itemCode and OITW.WhsCode = :whsCode",
            ['itemCode' => $this->codigo_sap, "whsCode" => $this->deposito]
        );
        if (count($item) == 0) {
            return "Item não encontrado no depósito {$this->deposito}.";
        }
        $this->_qtdSAP = number_format($item[0]->OnHand, 2, ".", "");
        return $this->_qtdSAP;
    }

    public function getDepositoAttribute()
    {
        if ($this->grupo == $this->nfc->pv->grupo_servico) {
            return $this->nfc->pv->deposito_servico;
        } else {
            return $this->nfc->pv->deposito;
        }
    }

    public function getIsServiceAttribute()
    {
        if ($this->grupo == $this->nfc->pv->grupo_servico) {
            return true;
        }
        return false;
    }

    public function getErroEstoqueAttribute()
    {
        $quantidade = $this->nfc->itens()->where('codigo_sap', $this->codigo_sap)->sum('quantidade');
        if (bccomp($this->qtd_sap, $quantidade, 3) === -1 && !$this->is_service && !$this->nfc->codigo_sap) {
            return true;
        }
        return false;
    }

    public function nfc()
    {
        return $this->belongsTo(NFCe::class, 'nfc_id');
    }

    public function paraProduzir()
    {
        if ($this->tipo !== "IP" || $this->codigo_op) {
            return false;
        }

        $pvId = $this->nfc->pv_id;
        $emProducao = Item::whereHas('nfc', function (Builder $query) use ($pvId) {
            $query->whereNull('codigo_sap')
                ->where('pv_id', '=', $pvId);
        })->where('codigo_sap', '=', $this->codigo_sap)
            ->whereNull('codigo_entrada_item')
            ->whereNotNull('codigo_op')
            ->where('tipo', '=', self::TYPE_IP)
            ->sum('quantidade');

        $necessario = Item::whereHas('nfc', function (Builder $query) use ($pvId) {
            $query->whereNull('codigo_sap')
                ->where('pv_id', '=', $pvId);
        })->where('codigo_sap', '=', $this->codigo_sap)
            ->where('tipo', '=', self::TYPE_IP)
            ->sum('quantidade');

        $emEstoque = (float)$this->qtd_sap + $emProducao;

        if (bccomp($emEstoque, $necessario, 3) >= 0) {
            app('NFCeLogger')->info(
                "Pulando produção do item: {$this->codigo_sap}, necessario: " .
                "{$necessario}, em estoque: {$emEstoque}, em produção: {$emProducao}",
                array_merge(
                    $this->only(['id', 'nfc_id', 'codigo_sap']),
                    compact('necessario', 'emEstoque', 'emProducao')
                )
            );
            return false;
        }
        return true;
    }

    public function getProductionOrderItems(): Collection
    {
        if (!$this->codigo_op) {
            return collect([]);
        }

        $items = NewCompany::getDb()
            ->table('WOR1')
            ->where('WOR1.DocEntry', $this->codigo_op)
            ->selectSub(
                NewCompany::getDb()
                    ->table('OITW')
                    ->whereColumn('OITW.ItemCode', 'WOR1.ItemCode')
                    ->whereColumn('OITW.WhsCode', 'WOR1.wareHouse')
                    ->select(['OITW.OnHand']),
                'OnHand'
            )
            ->selectSub(
                NewCompany::getDb()
                    ->table('OITM')
                    ->whereColumn('OITM.ItemCode', 'WOR1.ItemCode')
                    ->select(['OITM.ItemName']),
                'ItemName'
            )
            ->addSelect([
                'WOR1.ItemCode',
                \DB::raw('SUM(WOR1.PlannedQty) as PlannedQty'),
                'WOR1.Warehouse',
                \DB::raw('SUM(1) as LinesQty'),
            ])
            ->groupBy(['WOR1.ItemCode', 'WOR1.wareHouse'])
            ->get()
        ;

        $items->transform(function ($item) {
            $item->has_stock_error = bccomp($item->PlannedQty, $item->OnHand, 3) === 1;

            return $item;
        });

        return $items;
    }

    public function dispatchNextProductionOrderJob(string $queue = Queue::QUEUE_LOW)
    {
        switch ($this->status_op) {
            case self::STATUS_OP_PLANEJADO:
                return LiberarOP::dispatch($this->id)->onQueue($queue);
            case self::STATUS_OP_LIBERADO:
                return EntradaOP::dispatch($this->id)->onQueue($queue);
            case self::STATUS_OP_ENTREGUE:
                return FecharOP::dispatch($this->id)->onQueue($queue);
            case self::STATUS_OP_FECHADO:
            case self::STATUS_OP_CANCELADO:
            default:
        }
    }
}
