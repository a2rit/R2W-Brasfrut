<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;
use App\Jobs\Queue;

class PurchaseQuotationToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opoq;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($opoq){
      $this->opoq = $opoq;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{

        // if($this->attempts() > 3){
        //   self::dispatch($opoq)->delay(now()->addMinutes(2));
        //   return;
        // }
        if ($this->attempts() > 1) {
          app('PurchaseRequestJobLogger')
              ->info(
                  'Job PurchaseQuotationToSAP Delay',
                  ['id' => $this->opoq->id, 'attempts' => $this->attempts()]
              );
          self::dispatch($this->opoq)->delay(now()->addMinutes($this->attempts()))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);

          return;
        }

        $obj = new PurchaseQuotation();
        $obj->saveInSAP($this->opoq);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('EF2280', $e->getFile().' | '.$e->getLine(),$e->getMessage());
         self::dispatch($this->opoq)->delay(now()->addMinutes(2))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
      }
    }
}
