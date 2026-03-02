<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use App\Modules\Purchase\Models\AdvanceProvider\Payments;
use App\Modules\Purchase\Models\AdvanceProvider\Items;
use Litiano\Sap\Company;
use App\User;
use App\Upload;
use App\logsError;

class SyncAdvancePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-advance-payments:r2w';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza a sincronização dos adiantamentos para fornecedores, entre o SAP e o R2W';

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
        $docsSAP = $sap
                    ->getDb()
                    ->table('ODPO')
                    ->select("DocNum", "DocEntry", "U_R2W_USERNAME", "DocDate", "DocDueDate", "TaxDate", "CardCode", "CardName", "OwnerCode", 
                                "GroupNum", "PeyMethod", "Installmnt", "JrnlMemo", "Comments", "CANCELED", "DocStatus")
                    ->where("U_R2W_CODE", '=', null)
                    ->where('TaxDate', '>', '2022-09-01 00:00:00.000')
                    ->orderBy('DocNum', 'DESC')
                    ->get();
        try {
            DB::beginTransaction();
                foreach($docsSAP as $index => $docSAP){
                    if(empty(AdvanceProvider::where('codSAP', $docSAP->DocNum)->first())){
                        $user = User::where('userClerk', $docSAP->OwnerCode)->first() ?? User::where('name', 'r2w')->first();
                        $advance_payment = new AdvanceProvider;
                        $advance_payment->codSAP = $docSAP->DocNum;
                        $advance_payment->code = $advance_payment->createCode();
                        $advance_payment->cardCode = $docSAP->CardCode;
                        $advance_payment->docDate = $docSAP->DocDate;
                        $advance_payment->docDueDate = $docSAP->DocDueDate;
                        $advance_payment->taxDate = $docSAP->TaxDate;
                        $advance_payment->idUser = $user->id;
                        $advance_payment->comments =  mb_convert_encoding($docSAP->Comments, 'UTF-8');
                        $advance_payment->paymentCondition = $docSAP->GroupNum;
                        $advance_payment->paymentForm = '';
                        $advance_payment->status = $docSAP->CANCELED == 'N' && $docSAP->DocStatus == 'C' ? $advance_payment::STATUS_CLOSE : $advance_payment::STATUS_SAP[$docSAP->DocStatus];
    
                        if($advance_payment->save()){
    
                            // begin Items
                                $itemsSAP = $sap
                                            ->getDb()
                                            ->table("DPO1")
                                            ->select("ItemCode", "Dscription", "Quantity", "Price", "LineTotal", "Project", "OcrCode", "OcrCode2", "UnitMsr")
                                            ->where("DocEntry", $docSAP->DocNum)
                                            ->get();
                
                                foreach ($itemsSAP as $index => $itemSAP) {
                                    $item = new Items();
                                    $item->idAdvanceProvider = $advance_payment->id;
                                    $item->itemCode = $itemSAP->ItemCode;
                                    $item->itemName = $itemSAP->Dscription;
                                    $item->itemUnd = $itemSAP->UnitMsr;
                                    $item->quantity = is_numeric($itemSAP->Quantity) ? $itemSAP->Quantity : clearNumberDouble($itemSAP->Quantity);
                                    $item->price = is_numeric($itemSAP->Price) ? $itemSAP->Price : clearNumberDouble($itemSAP->Price);
                                    $item->project = $itemSAP->Project;
                                    $item->distrRule = $itemSAP->OcrCode ?? '';
                                    $item->distrRule2 = $itemSAP->OcrCode2 ?? '';
                                    $item->save();
                                }
                            // end Items
    
                            // begin payments
                                $paymentsSAP = $sap
                                                ->getDb()
                                                ->table('OVPM AS T0')
                                                ->select('T0.CashAcct', 'T0.CashSum', 'T0.TrsfrAcct', 'T0.TrsfrDate', 'T0.TrsfrRef', 'T0.TrsfrSum')
                                                ->join('VPM2 as T1', 'T0.DocNum', '=', 'T1.DocNum')
                                                ->where('T1.DocEntry', '=', $docSAP->DocNum)
                                                ->get();
                                                
                                foreach($paymentsSAP as $index => $paymentSAP){
                                    $payment = new Payments();
                                    $paymentSAP->conta_dinheiro = $paymentSAP->CashAcct;
                                    $paymentSAP->total_dinheiro = $paymentSAP->CashSum;
                                    $paymentSAP->conta_transferencia = $paymentSAP->TrsfrAcct;
                                    $paymentSAP->dt_transferencia = $paymentSAP->TrsfrDate;
                                    $paymentSAP->total_transferencia = $paymentSAP->TrsfrSum;
                                    $paymentSAP->referencia_transferencia = $paymentSAP->TrsfrRef;
                                    $payment->saveInDB($paymentSAP, $advance_payment->id);
                                }
                            //end payments
                        }
                    }
                }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
        }
    }
}
