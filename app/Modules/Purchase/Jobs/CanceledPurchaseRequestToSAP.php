<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Jobs\Queue;

class CanceledPurchaseRequestToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oPR;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oPR)
    {
        $this->oPR = $oPR;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $obj = new PurchaseRequest();
            $obj->cenceledInSAP($this->oPR);
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('EF2207', $e->getFile().' | '.$e->getLine(),$e->getMessage());
           CanceledPurchaseRequestToSAP::dispatch($this->oPR)
                  ->delay(now()->addMinutes(5))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
