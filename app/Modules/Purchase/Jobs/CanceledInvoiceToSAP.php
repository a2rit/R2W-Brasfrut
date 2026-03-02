<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\LogsError;
use App\Scheduling;

class CanceledInvoiceToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opor;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($opor){
      $this->opor = $opor;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{
          $obj = new IncoingInvoice();
          $obj->canceledInSAP($this->opor);
      }catch (\Throwable $e) {
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('EF012270', $e->getFile().' | '.$e->getLine(),$e->getMessage());
        CanceledInvoiceToSAP::dispatch($this->opor)
              ->delay(now()->addMinutes(5));
      }
    }
}
