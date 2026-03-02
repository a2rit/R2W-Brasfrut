<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\LogsError;

class CanceledPurchaseQuotationToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $opq;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($opq)
    {
        $this->opq = $opq;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $obj = new PurchaseQuotation();
            $obj->cenceledInSAP($this->opq);
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('EF2207', $e->getFile().' | '.$e->getLine(),$e->getMessage());
           CanceledPurchaseQuotationToSAP::dispatch($this->opq)
                  ->delay(now()->addMinutes(5));
        }
    }
    
}
