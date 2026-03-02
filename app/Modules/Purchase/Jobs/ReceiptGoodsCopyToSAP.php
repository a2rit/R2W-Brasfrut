<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods;
use App\LogsError;

class ReceiptGoodsCopyToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $receiptGoods;
    private $idXML;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($receiptGoods, $idXML)
    {
      $this->receiptGoods = $receiptGoods;
      $this->idXML = $idXML;
    }
            
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{
          $obj = new ReceiptGoods();
          $obj->saveCopyXMLInSAP($this->receiptGoods, $this->idXML);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('EF288',$e->getFile().'|'.$e->getLine() ,$e->getMessage());
         ReceiptGoodsCopyToSAP::dispatch($this->receiptGoods, $this->idXML)
                ->delay(now()->addMinutes(5));
      }
    }
}
