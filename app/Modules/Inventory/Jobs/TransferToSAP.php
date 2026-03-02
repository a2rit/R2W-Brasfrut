<?php

namespace App\Modules\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Inventory\Models\Transfer\Transfer;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;

class TransferToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $item;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    public function __construct($item){
      $this->item = $item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        try{
          $obj = new Transfer();
          $obj->saveInSAP($this->item);
        }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E2A3FA#', $e->getFile().' | '. $e->getLine(),$e->getMessage());
         TransferToSAP::dispatch($this->item)
         ->delay(now()->addMinutes(2));
      }
   }
}
