<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class QueuesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Status das jobs em fila';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->table(['Job', 'Qtd', 'Descrição'], $this->getData());
        $this->warn('* Esta operação necessita de estoque disponível!');
        $this->info('');
        $this->table(['Queue', 'Qty'], $this->getQueues());
    }

    private function getQueues(): array
    {
        $data = [];
        $queues = DB::table('jobs')->groupBy('queue')->get(['queue', DB::raw('count(*) as qty')]);
        foreach ($queues as $item) {
            $data[] = [$item->queue, $item->qty];
        }

        return $data;
    }

    private function getData(): array
    {
        $jobs = [
            ['class' => 'CriarOP', 'label' => 'NFCe: Criação de OP'],
            ['class' => 'LiberarOP', 'label' => 'NFCe: Liberação de OP'],
            ['class' => 'EntradaOP', 'label' => 'NFCe: Entrada de OP *'],
            ['class' => 'FecharOP', 'label' => 'NFCe: Fechamendo de OP'],
            ['class' => 'SalvarSAP', 'label' => 'NFCe: Salvar NF no SAP *'],
            ['class' => 'SalvarPagamentoSAP', 'label' => 'NFCe: Salvar pagamento da NF no SAP'],
            ['class' => 'LancarGorjetaSAP', 'label' => 'NFCe: Lançamento de gorjeta da NF'],
            ['class' => 'InterConsumptionCron', 'label' => 'Consumo Interno (CRON)'],
        ];

        $data = [];
        foreach ($jobs as $job) {
            $qty = DB::table('jobs')
                ->where('payload', 'like', "%{$job['class']}%")
                ->count();

            $data[] = [$job['class'], $qty, $job['label']];
        }

        return $data;
    }
}
