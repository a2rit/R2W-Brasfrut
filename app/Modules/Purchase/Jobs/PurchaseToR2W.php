<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\Queue;
use App\LogsError;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;

class PurchaseToR2W implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $array_data;

    public function __construct($array_data)
    {
        $this->array_data = $array_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->attempts() > 1) {
                self::dispatch($this->array_data)->delay(now()->addMinutes($this->attempts()))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                return;
            }

            $purchase = new PurchaseOrder;
            $purchase->saveInDBFromSAP($this->array_data);
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EF2290', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            self::dispatch($this->array_data)
                ->delay(now()->addMinutes(2))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
