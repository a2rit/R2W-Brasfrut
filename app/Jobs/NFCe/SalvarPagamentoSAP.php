<?php

namespace App\Jobs\NFCe;

use App\Models\NFCe;
use DB;
use Throwable;

class SalvarPagamentoSAP extends BaseJob
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
                $nfce->salvarPagamentosSAP();
            });
        } catch (Throwable $e) {
            $this->retryLater();
            $this->logError($e);
            $nfc = NFCe::find($this->itemId, ['id', 'pv_id', 'data_emissao']);
            $this->createOrUpdateError(NFCe::class, $e, $nfc->pv_id, $nfc->data_emissao);
        }
    }
}
