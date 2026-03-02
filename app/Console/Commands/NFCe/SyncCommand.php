<?php

namespace App\Console\Commands\NFCe;

use App\Models\NFCe;

class SyncCommand extends BaseCommand
{
    protected $signature = 'nfce:sync';

    protected $description = 'Sync NFCe SAP';

    protected int $mutexMinutes = 120;

    public function __construct()
    {
        parent::__construct(app('NFCeLogger'));
    }

    public function handle()
    {
        $this->start();

        try {
            // Salva as notas no SAP
            $this->logAndOutputInfo("Iniciou tarefas de Salvar no SAP.");
            NFCe::tarefaSalvarSAP();
            $this->logAndOutputInfo("Finalizou tarefas de Salvar no SAP {$this->partialTimeDiff()}.");

            $this->logAndOutputInfo("Iniciou tarefas de atualizar itens com erro.");
            NFCe::tarefaAtualizarItensComErro();
            $this->logAndOutputInfo("Finalizou tarefas de atualizar itens com erro {$this->partialTimeDiff()}.");

            $this->logAndOutputInfo("Iniciou a tarefa lançamento da gorjeta.");
            NFCe::lancamentoGorjetaCron();
            $this->logAndOutputInfo("Finalizou a tarefa lançamento da gorjeta {$this->partialTimeDiff()}.");

//            $this->logAndOutputInfo("Iniciou a tarefa pagamento servicos.");
//            NFCe::tarefaPagamentoServicos();
//            $this->logAndOutputInfo("Finalizou a tarefa pagamento servicos {$this->partialTimeDiff()}.");
        } catch (\Throwable $e) {
            $this->logAndOutputError($e->getMessage());
        }

        $this->end();
    }
}
