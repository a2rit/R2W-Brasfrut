<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;

class SetInSAPPurchaseRequest implements ShouldQueue
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
         $obj->saveInSap($this->pa);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('1E0227',$e->getFile().'|'.$e->getLine(),$e->getMessage());

      }
   }
}
