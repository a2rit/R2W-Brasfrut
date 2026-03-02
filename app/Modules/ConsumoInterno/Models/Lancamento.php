<?php

namespace App\Modules\ConsumoInterno\Models;

use App\Models\PontoVenda;
use App\Modules\ConsumoInterno\Models\Lancamento\Item;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IStockTransfer;

/**
 * App\Modules\ConsumoInterno\Models\Lancamento
 *
 * @property int $id
 * @property int $pv_id
 * @property \Carbon\Carbon|null $data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento wherePvId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\ConsumoInterno\Models\Lancamento\Item[] $itens
 * @property-read \App\Models\PontoVenda $pv
 * @property int|null $cod_transferencia
 * @property int|null $cod_pedido
 * @property string|null $mensagem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereCodPedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereCodTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento whereMensagem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\ConsumoInterno\Models\Lancamento query()
 */
class Lancamento extends Model
{
    protected $table = "consumo_interno";

    protected $dates = ["created_at", "updated_at", "deleted_at", "data"];

    public function itens()
    {
        return $this->hasMany(Item::class, "ci_id", "id");
    }

    public function pv()
    {
        return $this->belongsTo(PontoVenda::class, "pv_id", "id");
    }

    protected function criarPedido(Company $sap)
    {
        if($this->cod_pedido || !$this->cod_transferencia){
            return false;
        }
        /**
         * @var $order IDocuments
         */
        $order = $sap->getBusinessObject(BoObjectTypes::oOrders);
        $order->CardCode = $this->pv->ci_config["cliente"];
        $order->DocDueDate = $this->data->format('d/m/Y');
        $order->DocDate = $this->data->format('d/m/Y');
        $order->Comments = "Baseado no consumo interno diário {$this->data->format('d-m-Y')}";

        //$order->SalesPersonCode = 17;

        foreach ($this->itens as $item){
            $order->Lines->ItemCode = (string)$item->cod_sap;
            $order->Lines->Quantity = (float)$item->qtd;
            $order->Lines->Usage = $this->pv->ci_config["utilizacao"];
            $order->Lines->CostingCode = $item->centro_custo; //$this->pv->ci_config["regra_distribuicao"];
            $order->Lines->ProjectCode = $item->projeto; //$this->pv->ci_config["projeto"];
            $order->Lines->Add();
        }
        if($order->Add() != 0){
            $this->mensagem = "Erro ao cadastrar pedido de venda! Erro: " . $sap->GetLastErrorDescription();
            $this->save();
            throw new \Exception("Erro ao cadastrar pedido de venda! Erro: " . $sap->GetLastErrorDescription());
        }
        $this->cod_pedido = $sap->getNewObjectKey();
        $this->mensagem = "Pedido criado com sucesso!";
        $this->save();
        return true;
    }

    protected function transferirEstoque(Company $sap)
    {
        if($this->cod_transferencia){
            return false;
        }
        /** @var IStockTransfer $stockTransfer */
        $stockTransfer = $sap->getBusinessObject(BoObjectTypes::oStockTransfer);
        $stockTransfer->FromWarehouse = $this->pv->deposito;
        $stockTransfer->JournalMemo = "Transferido via aplicação web.";
        $stockTransfer->Comments = "Transferido por consumo interno.";
        foreach ($this->itens as $item){
            $stockTransfer->Lines->ItemCode = $item->cod_sap;
            $stockTransfer->Lines->Quantity = (float)$item->qtd;
            $stockTransfer->Lines->WarehouseCode = $this->pv->ci_config["deposito"];
            $stockTransfer->Lines->Add();
        }

        if($stockTransfer->Add() !== 0){
            $this->mensagem = "Erro ao transferir estoque: {$sap->getLastErrorDescription()}";
            $this->save();
            throw new \Exception("Erro ao transferir estoque: {$sap->getLastErrorDescription()}");
        }
        $this->mensagem = "Transferido com sucesso!";
        $this->cod_transferencia = $sap->getNewObjectKey();
        return true;
    }

    public function cron()
    {
        $lancamentos = Lancamento::whereDate("data", "<=", Carbon::now()->subDay(1))
            ->whereNull("cod_pedido")->get();

        if($lancamentos->count() === 0){
            return;
        }

        $sap = new Company();
        foreach ($lancamentos as $lancamento){
            $lancamento->transferirEstoque($sap);
            $lancamento->criarPedido($sap);
        }
    }
}
