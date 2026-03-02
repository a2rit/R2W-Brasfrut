<?php

namespace App\Modules\Purchase\Jobs;

use App\LogsError;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use App\Jobs\Queue;

class PurchaseToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opor;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($opor)
    {
        $this->opor = $opor;
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
                app('PurchaseRequestJobLogger')
                    ->info(
                        'Job PurchaseToSAP Delay',
                        ['id' => $this->opor->id, 'attempts' => $this->attempts()]
                    );

                self::dispatch($this->opor)->delay(now()->addMinutes(1))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                return;
            }

            $obj = new PurchaseOrder();
            $obj->saveInSAP($this->opor);
        } catch (Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EF2269', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            self::dispatch($this->opor)
                ->delay(now()->addMinutes(1))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
