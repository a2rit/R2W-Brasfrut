<?php

namespace App\Console\Commands\NFCe;

use App\Models\NFCe;

class SyncPaymentsCommand extends BaseCommand
{
    protected $signature = 'nfce:sync-payments';

    protected $description = 'NFCe sync payments';

    public function __construct()
    {
        parent::__construct(app('NFCeLogger'));
    }

    public function handle()
    {
        $this->start();

        try {
            NFCe::tarefaSalvarPagamentosSap();
        } catch (\Throwable $e) {
            $this->logAndOutputError("Erro ao salvar pagamentos: {$e->getMessage()}");
        }

        $this->end();
    }
}
