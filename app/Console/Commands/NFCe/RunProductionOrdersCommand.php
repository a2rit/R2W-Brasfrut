<?php

namespace App\Console\Commands\NFCe;

use App\Models\NFCe;

class RunProductionOrdersCommand extends BaseCommand
{
    protected $signature = 'nfce:production-orders';

    protected $description = 'Executa tarefas de ordens de produção';

    protected int $mutexMinutes = 120;

    public function __construct()
    {
        parent::__construct(app('NFCeLogger'));
    }

    public function handle()
    {
        $this->start();

        try {
            $this->logAndOutputInfo("Iniciou tarefas de Ordem de Produção.");
            NFCe\Item::tarefasOP();
            $this->logAndOutputInfo("Finalizou tarefas de Ordem de Produção {$this->partialTimeDiff()}.");
        } catch (\Throwable $e) {
            $this->logAndOutputError($e->getMessage());
        }

        $this->end();
    }
}
