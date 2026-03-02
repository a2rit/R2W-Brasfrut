<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Upload;
use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Inventory\Models\Input\Input;
use App\Modules\Inventory\Models\Output\Output;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Modules\Inventory\Models\Transfer\Transfer;
use Litiano\Sap\NewCompany;
use App\Jobs\SyncUploads as SUploads;
use App\Jobs\Queue;
use App\logsError;
use App\Modules\Partners\Models\Partner;

class SyncUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza os anexos para o SAP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $uploads = Upload::where("absEntry", null)->orderBy("id", "DESC")->get();

        $references = [
            "purchase_orders" => [
                "boType" => BoObjectTypes::oPurchaseOrders,
                "model" => new PurchaseOrder()
            ],
            "inputs" => [
                "boType" => BoObjectTypes::oInventoryGenEntry,
                "model" => new Input()
            ],
            "outputs" => [
                "boType" => BoObjectTypes::oInventoryGenExit,
                "model" => new Output()
            ],
            "incoing_invoices" => [
                "boType" => BoObjectTypes::oPurchaseInvoices,
                "model" => new IncoingInvoice()
            ],
            "purchase_quotation" => [
                "boType" => BoObjectTypes::oPurchaseQuotations,
                "model" => new PurchaseQuotation()
            ],
            "transferTakings" => [
                "boType" => BoObjectTypes::oStockTransfer,
                "model" => new TransferTaking()
            ],
            "transfers" => [
                "boType" => BoObjectTypes::oStockTransfer,
                "model" => new Transfer()
            ],
            "purchase_requests" => [
                "boType" =>  BoObjectTypes::oPurchaseRequest,
                "model" => new PurchaseRequest()
            ],
            "partners" => [
                "boType" => BoObjectTypes::oBusinessPartners,
                "model" => new Partner()
            ],
        ];

        try {
            foreach($uploads as $index => $value){
                $model = $references[$value->reference]["model"];
                $objModel = $model->find($value->idReference);
                if(!empty($objModel->codSAP)){
                    SUploads::dispatch($value, $references[$value->reference]["boType"], $objModel)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('UPS211',$e->getFile().'|'.$e->getLine(),$e->getMessage());
            print($e->getMessage());
        }
    }
}
