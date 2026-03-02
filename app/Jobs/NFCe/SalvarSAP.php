<?php

namespace App\Jobs\NFCe;

use App\Exceptions\StockErrorException;
use App\Jobs\Queue;
use App\Models\NFCe;
use DB;
use Throwable;

class SalvarSAP extends BaseJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::transaction(function () {
                /** @var NFCe $nfce */
                $nfce = NFCe::lockForUpdate()->find($this->itemId);
                return $nfce->salvarSAP();
            });

            SalvarPagamentoSAP::dispatch($this->itemId)->onQueue(Queue::QUEUE_LOW);

            $nfce = NFCe::find($this->itemId);
            $hasGorjeta = $nfce->itens()->where('codigo_sap', $nfce->pv->item_gorjeta_colibri)->exists();
            if ($hasGorjeta) {
                LancarGorjetaSAP::dispatch($nfce->id)->onQueue(Queue::QUEUE_LOW);
            }
        } catch (Throwable $e) {
            if (!$e instanceof StockErrorException) {
                $this->logError($e, false);
            }
            $this->retryLater();
            $nfc = NFCe::find($this->itemId, ['id', 'pv_id', 'data_emissao']);
            $this->createOrUpdateError(NFCe::class, $e, $nfc->pv_id, $nfc->data_emissao);
        }
    }
}
