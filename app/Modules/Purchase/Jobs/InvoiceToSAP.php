<?php

namespace App\Modules\Purchase\Jobs;

use App\Jobs\Queue;
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

class InvoiceToSAP implements ShouldQueue
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
    
    /** @var int */

    public $tries = 5;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{

        if($this->attempts() > 3){
          self::dispatch($this->opor)->delay(now()->addMinutes(2))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
          return;
        }

        $obj = new IncoingInvoice();
        $obj->saveInSAP($this->opor);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('EF012269', $e->getFile().' | '.$e->getLine(),$e->getMessage());
         InvoiceToSAP::dispatch($this->opor)
                ->delay(now()->addMinutes(5))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
      }
    }
}
