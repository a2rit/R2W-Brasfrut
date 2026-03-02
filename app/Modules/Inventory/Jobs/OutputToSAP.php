<?php

namespace App\Modules\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\LogsError;
use App\Modules\Inventory\Models\Requisicao\Requests;

class OutputToSAP implements ShouldQueue
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
         $obj = new Requests();
         $obj->saveOutputInSAP($this->pa);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB(
             'ERF228',
             $e->getFile().'|'.$e->getLine(),
             "Request OutputToSap {$this->pa->id}:{$e->getMessage()}"
         );
         OutputToSAP::dispatch($this->pa)->delay(now()->addMinutes(10));
      }
   }
}
