<?php

namespace App\Console\Commands\NFCe;

use App\Models\NFCe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FallbackSyncCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfce:fallback-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Falback Sync';

    public function __construct()
    {
        parent::__construct(app('NFCeLogger'));
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->start();

        $this->logAndOutputInfo('Iniciou fallback tarefas de atualizar itens com erro.');
        NFCe::tarefaAtualizarItensComErro();
        $this->logAndOutputInfo("Finalizou fallback tarefas de atualizar itens com erro {$this->partialTimeDiff()}.");

        $this->logAndOutputInfo("Iniciou fallback tarefas de ordens de produção.");
        $this->handleItems();
        $this->logAndOutputInfo("Finalizou fallback tarefas de ordens de produção {$this->partialTimeDiff()}.");

        $this->logAndOutputInfo("Iniciou fallback tarefas de Salvar no SAP.");
        $this->handleNfce();
        $this->logAndOutputInfo("Finalizou fallback tarefas de Salvar no SAP {$this->partialTimeDiff()}.");

        $this->logAndOutputInfo("Iniciou fallback tarefa lançamento da gorjeta.");
        NFCe::lancamentoGorjetaCron(true);
        $this->logAndOutputInfo("Finalizou fallback tarefa lançamento da gorjeta {$this->partialTimeDiff()}.");

        $this->logAndOutputInfo("Iniciou fallback tarefa salvar pagamento.");
        NFCe::tarefaSalvarPagamentosSap(true);
        $this->logAndOutputInfo("Finalizou fallback tarefa salvar pagamento {$this->partialTimeDiff()}.");

        $this->end();
    }

    private function handleNfce()
    {
        $items = NFCe::whereNull('codigo_sap')
            ->where('data_emissao', '<=', Carbon::now()->subDay())
            ->doesntHave('inProductionItems')
            ->doesntHave('recentError')
            ->get()
        ;

        if (!$items->count()) {
            $this->logAndOutputInfo('Nenhuma NFCe para resgatar via fallback.');
            return;
        }

        $this->logAndOutputInfo(
            "Resgatando {$items->count()} NFCe's via fallback.",
            $items->pluck('id')->toArray()
        );
        $items->each(fn(NFCe $nf) => $nf->checkAndSendToSap());
    }

    private function handleItems()
    {
        $items = NFCe\Item::where('tipo', NFCe\Item::TYPE_IP)
            ->whereHas('nfc', function (Builder $builder) {
                $builder->where('data_emissao', '<=', Carbon::now()->subDay());
            })
            ->whereNotNull('codigo_op')
            ->whereNotIn('status_op', [NFCe\Item::STATUS_OP_CANCELADO, NFCe\Item::STATUS_OP_FECHADO])
            ->doesntHave('recentError')
            ->get()
        ;

        if (!$items->count()) {
            $this->logAndOutputInfo('Nenhum NFCe\Item para resgatar via fallback.');
            return;
        }

        $this->logAndOutputInfo(
            "Resgatando {$items->count()} NFCe\Item's via fallback.",
            $items->pluck('id')->toArray()
        );
        $items->each(fn(NFCe\Item $item) => $item->dispatchNextProductionOrderJob());
    }
}
