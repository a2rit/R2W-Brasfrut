<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\LogsError;
use App\Jobs\Queue;

class SplitPurchaseToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opor, $lineNum;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($opor, $lineNum)
    {
        $this->opor = $opor;
        $this->lineNum = $lineNum;
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

                self::dispatch($this->opor, $this->lineNum)->delay(now()->addMinutes(1))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                return;
            }

            $obj = new PurchaseOrder();
            $obj->splitSaveInSAP($this->opor, $this->lineNum);
        } catch (Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EF2279', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            self::dispatch($this->opor, $this->lineNum)
                ->delay(now()->addMinutes(1))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
