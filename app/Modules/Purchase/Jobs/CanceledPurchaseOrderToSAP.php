<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Jobs\Queue;

class CanceledPurchaseOrderToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oPOR;
    protected $justification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oPOR, $justification)
    {
        $this->oPOR = $oPOR;
        $this->justification = $justification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            if (empty($this->oPOR->id)) {
                return;
            }
            $obj = new PurchaseOrder();
            $obj->cenceledInSAP($this->oPOR, $this->justification);
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('EF01280', $e->getFile().' | '.$e->getLine(),$e->getMessage());
           self::dispatch($this->oPOR)
                  ->delay(now()->addMinutes(1))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
