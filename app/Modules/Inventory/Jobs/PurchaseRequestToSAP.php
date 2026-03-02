<?php

namespace App\Modules\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Jobs\Queue;

class PurchaseRequestToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $pa;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pa){
        $this->pa = $pa;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
      try{
         $obj = new PurchaseRequest();
         $obj->saveInSAP($this->pa);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('ERF228',$e->getFile().'|'.$e->getLine(),$e->getMessage());
         PurchaseRequestToSAP::dispatch($this->pa)->delay(now()->addMinutes(10))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
      }
   }
}
