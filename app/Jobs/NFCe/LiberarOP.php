<?php

namespace App\Jobs\NFCe;

use App\Jobs\Queue;
use App\Models\NFCe\Item;
use Throwable;

class LiberarOP extends BaseJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \DB::transaction(function () {
                /** @var Item $item */
                $item = Item::lockForUpdate()->find($this->itemId);
                $item->liberarOrdemProducao();
            });
            EntradaOP::dispatch($this->itemId)->onQueue(Queue::QUEUE_LOW);
        } catch (Throwable $e) {
            $this->logError($e);
            $this->retryLater();
            $nfc = Item::with('nfc:id,pv_id,data_emissao')->find($this->itemId, ['id', 'nfc_id'])->nfc;
            $this->createOrUpdateError(Item::class, $e, $nfc->pv_id, $nfc->data_emissao);
        }
    }
}
