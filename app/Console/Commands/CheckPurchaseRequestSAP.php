<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item;
use App\Modules\Inventory\Jobs\PurchaseRequestToSAP;
use App\User;

class CheckPurchaseRequestSAP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-purchase-request-sap:r2w';

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
        $sapQuery = new Company(false);
        
        $cont = 0;
        $headSAP = $sapQuery->query("SELECT DocNum, DocEntry, U_R2W_USERNAME, ReqDate, DocStatus, Comments, ReqDate, 
                                    ReqName, Email, TaxDate, U_R2W_CODE, CANCELED, DocDate
                                FROM OPRQ 
                                WHERE U_R2W_CODE is null 
                                AND TaxDate > '2022-04-01 00:00:00.000'");
        
        if(!empty($headSAP)){

            foreach($headSAP as $key => $head){
                try {
                    DB::beginTransaction();
                    $sapUp = NewCompany::getInstance()->getCompany();
    
                    if(!empty(PurchaseRequest::where('codSAP', $head['DocNum'])->first())){
                        $cont++;
                        $user = User::where('name', 'r2w')->first();
                        $requester = User::where('email', $head['Email'])->first();
                        $body = $sapQuery->query("SELECT ItemCode, Quantity, OcrCode, OcrCode2, UnitMsr, Dscription, 
                                                    Project, WhsCode, LineNum
                                                FROM PRQ1 
                                                WHERE DocEntry = ".$head['DocEntry']);
    
                        $purchase_request = new PurchaseRequest();
                        $purchase_request->codSAP = $head['DocEntry'];
                        $purchase_request->code = $purchase_request->createCode();
                        $purchase_request->name = $user->name;
                        $purchase_request->taxDate = $head['TaxDate'];
                        $purchase_request->docDate = $head['DocDate'];
                        $purchase_request->requriedDate = date("Y/m/d", strtotime($head['ReqDate']));
                        $purchase_request->requesterUser = $user->id;
                        $purchase_request->codStatus = $head['CANCELED'] == 'N' && $head['DocStatus'] == 'C' ? $purchase_request::STATUS_SAP['F'] : $purchase_request::STATUS_SAP[$head['DocStatus']] ;
                        $purchase_request->message = 'Documento gerado através do SAP';
                        $purchase_request->observation = $head['Comments'];
                        $purchase_request->idUser = $user->name;
                        $purchase_request->idSolicitante = $requester->userClerk;
                        $purchase_request->solicitante = $requester->name;
                        $purchase_request->origem = 'sap';
                        if($purchase_request->save()){
                            $purchase_request->created_at = $head['DocDate'];
                            $purchase_request->save();
                            foreach($body as $key => $value){
                                $purchase_request_item = new Item();
                                $purchase_request_item->idPurchaseRequest = $purchase_request->id;
                                $purchase_request_item->itemCode = $value['ItemCode'];
                                $purchase_request_item->quantity = (Double)number_format($value['Quantity'],3);
                                $purchase_request_item->quantityPendente = (Double)number_format($value['Quantity'],3);
                                $purchase_request_item->distrRule = $value['OcrCode'];
                                $purchase_request_item->distriRule2 = $value['OcrCode2'];
                                $purchase_request_item->itemUnd = $value['UnitMsr'];
                                $purchase_request_item->ItemName = $value['Dscription'];
                                $purchase_request_item->wareHouseCode = $value['WhsCode'];
                                $purchase_request_item->project = $value['Project'];
                                $purchase_request_item->lineNum = $value['LineNum'];
                                $purchase_request_item->save();
                            }
            
                            $pr = $sapUp->GetBusinessObject(BoObjectTypes::oPurchaseRequest);
                            $pr->GetByKey((string)$purchase_request->codSAP);
                            $pr->UserFields->fields->Item("U_R2W_CODE")->value = $purchase_request->code;
                            if($pr->Update() == 0){
                                DB::commit();
                            }else{
                                DB::rollback();
                            }
                        }
                    }
                } catch (\Throwable $th) {
                    DB::rollback();
                    $this->info("Ocorreu um erro durante a tentativa de baixar dados do SAP. Erro: ".$th->getMessage());
                }
            }
        }
        //$this->call("check-purchase-order-sap:r2w");
        \Log::error("[PurchaseRequest SAP To R2W] $cont documentos baixados");
    }
}
