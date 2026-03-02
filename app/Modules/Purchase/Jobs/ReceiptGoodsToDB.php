<?php

namespace App\Modules\Purchase\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Litiano\Sap\Company;
use App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods;
use App\LogsError;

class ReceiptGoodsToDB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $ORG = new ReceiptGoods();
            $ORG->saveInDBFromJob($this->request);
            if (is_null($ORG->idPurchaseOrders)) {
                if (isset($this->request->idXML) && !is_null($this->request->idXML)) {
                    ReceiptGoodsCopyToSAP::dispatch($ORG, $this->request->idXML);
                } else {
                    ReceiptGoodsToSAP::dispatch($ORG);
                }
            } else {
                if (isset($this->request->idXML)) {
                    $ORG->saveCopyInSAP($ORG, $this->request->idXML);
                } else {
                    ReceiptGoodsToSAP::dispatch($ORG, true);
                }
            }
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('BD288F', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            ReceiptGoodsToDB::dispatch($this->request)
                ->delay(now()->addMinutes(5));
        }
    }
}
