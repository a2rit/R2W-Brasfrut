<?php

namespace App\Modules\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Modules\Inventory\Models\Transfer\Transfer;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;

class TransferTakingToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $item;
    private $item2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    //Item = Transfer
    //Item2= TransferTaking 
    public function __construct($item,$item2){
      $this->item = $item;
      $this->item2 = $item2;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        try{

          $obj = new Transfer();
          $obj->saveInSAP($this->item,$this->item2);
        }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E2A3FA#', $e->getFile().' | '. $e->getLine(),$e->getMessage());
         TransferTakingToSAP::dispatch($this->item,$this->item2)
         ->delay(now()->addMinutes(2));
      }
   }
}
