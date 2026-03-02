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

class ReceiptGoodsToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $receiptGoods;
    private $copy;

    /**
     * Create a new job instance.
     *
     * @param $receiptGoods
     * @param bool $copy
     */
    public function __construct($receiptGoods, $copy = false)
    {
      $this->receiptGoods = $receiptGoods;
      $this->copy = $copy;
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
          if($this->copy){
            $obj->saveCopyInSAP($this->receiptGoods);
          }else{
            $obj->saveInSap($this->receiptGoods);
          }
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('EF288',$e->getFile().'|'.$e->getLine() ,$e->getMessage());
         ReceiptGoodsToSAP::dispatch($this->receiptGoods, $this->copy)
                ->delay(now()->addMinutes(5));
      }
    }
}
