<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;

class AdvanceProviderToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oapt;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oapt){
      $this->oapt = $oapt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{

        if($this->attempts() > 3){
          self::dispatch($this->oapt)->delay(now()->addMinutes(2));
          return;
        }

        $obj = new AdvanceProvider();
        $obj->saveInSAP($this->oapt);
      }catch (\Throwable $e) {
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('AP0005', $e->getFile().' | '.$e->getLine(),$e->getMessage());
        AdvanceProviderToSAP::dispatch($this->oapt)
              ->delay(now()->addMinutes(5));
      }
    }
}
