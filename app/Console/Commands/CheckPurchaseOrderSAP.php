<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\Item;
use App\Modules\Purchase\Models\PurchaseOrder\Expenses;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item as ItemR;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\PurchaseQuotation\Item as ItemQ;
use App\Modules\Purchase\Jobs\PurchaseToSAP;
use App\Modules\Purchase\Jobs\PurchaseQuotationToSAP;
use App\Modules\Purchase\Jobs\PurchaseToR2W;
use App\User;
use App\Upload;
use App\LogsError;
use App\Jobs\Queue;

class CheckPurchaseOrderSAP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-purchase-order-sap:r2w';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Irá verificar se existe algum documento salvo no SAP que no R2W não exista e irá criar no R2W';

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

        $sap = new Company(false);
        $cont = 0;
        $query = $sap->query("SELECT DISTINCT T0.[DocNum], T0.[DocEntry], T0.[U_R2W_USERNAME], T0.[DocStatus], T0.[CANCELED],
                                T0.[Comments] AS POComments, T0.[TaxDate], T0.[U_R2W_CODE], T0.[CardCode], T0.[CardName], 
                                T0.[TaxDate], T0.[DocDate], T0.[DocDueDate], T0.[DocTotal], T0.[GroupNum], T0.[OwnerCode], 
                                T0.[TrnspCode], T0.[AtcEntry], T0.[DocTime], T0.[CreateTS], T2.[TaxId0]
                            FROM OPOR T0 
                            INNER JOIN CRD7 T2 ON T0.[CardCode] = T2.[CardCode]
                            WHERE DocDate > '2023-07-01 00:00:00.000'
                            AND (T0.[U_R2W_CODE] ='' or T0.[U_R2W_CODE] IS NULL)
                            AND T0.[DocStatus] = 'O'
                            AND T2.TaxId0 is not null
                            ORDER BY T0.[DocNum] DESC");

        if (!empty($query)) {
            foreach ($query as $key => $po_head_sap) {

                try {

                    if (empty(PurchaseOrder::where('codSAP', $po_head_sap['DocNum'])->first())) {

                        $expenses_sap_query = $sap->query("SELECT T0.[DocEntry], T0.[ExpnsCode], T0.[Comments], T0.[TaxCode], T0.[LineTotal],
                                                        T0.[OcrCode], T0.[OcrCode2], T0.[Project], T0.[Comments]
                                                    FROM POR3 T0 WHERE T0.[DocEntry] = " . $po_head_sap['DocNum']);

                        $body_sap_query = $sap->query("SELECT DocEntry, ItemCode, Quantity, Price, LineTotal, LineStatus, OcrCode, OcrCode2, UnitMsr, AcctCode,
                                                    Dscription, Project, WhsCode, BaseRef, BaseEntry, BaseType, U_R2W_ID
                                                FROM POR1 WHERE DocEntry = " . $po_head_sap['DocNum']);

                        $array_data = [
                            'head' => $po_head_sap,
                            'body' => $body_sap_query,
                            'expenses' => $expenses_sap_query
                        ];

                        PurchaseToR2W::dispatch($array_data)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                    }
                } catch (\Throwable $e) {
                    $logsErrors = new LogsError();
                    $logsErrors->saveInDB('PFS001', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
                }
            }
        }
        $this->info("Foram encontrados $cont documentos no SAP que não existiam no R2W.");
    }
}
