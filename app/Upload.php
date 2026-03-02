<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Company;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Inventory\Models\Input\Input;
use App\Modules\Inventory\Models\Output\Output;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Modules\Inventory\Models\Transfer\Transfer;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use Litiano\Sap\Enum\BoObjectTypes;
use App\logsError;

/**
 * App\Upload
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $reference
 * @property string $idReference
 * @property string $diretory
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereDiretory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereIdReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Upload query()
 */
class Upload extends Model
{

    protected $table = "uploads";
    protected $fillable = ['idUser','reference','idReference','directory'];


    public function saveInSAP(){

        try {
            $sap = NewCompany::getInstance()->getCompany();
            $atc = $sap->GetBusinessObject(221);

            $attachments = Upload::where('reference', '=', $this->reference)
                            ->where('idReference', '=', $this->idReference);
                            
            $update = false;
            if($attachments->get()->contains('absEntry', !null)){
                $update = true;
                $atc->GetByKey((string)array_values(array_filter($attachments->pluck('absEntry')->toArray()))[0]);
            }

            foreach($attachments->where('absEntry', null)->get() as $attachment){
                $path_parts = pathinfo($attachment['diretory']);
                if(!is_null($path_parts) && file_exists(public_path().$path_parts['dirname'])) {
                    $atc->Lines->Add();
                    $atc->Lines->FileName = $path_parts['filename'];
                    $atc->Lines->FileExtension = $path_parts['extension']; 
                    $atc->Lines->SourcePath = public_path().$path_parts['dirname'];
                    $atc->Lines->Override = 1;
                }
            }

            if($update){
                $absentry = $atc->Update();
            }else{
                $absentry = $atc->Add();
            }

            if ($absentry !== 0) {
                $attachments->where('absEntry', null)->update(['vinculado' => 'N', 'is_locked' => true]);
                $logsErro = new logsError();
                $logsErro->saveInDB('E0082', 'App/Upload.php', $sap->GetLastErrorDescription());
            }else{
                $attachments->where('absEntry', null)->update(['absEntry' => $sap->GetNewObjectKey(), 'vinculado' => 'N', 'is_locked' => false]);
                return $sap->GetNewObjectKey();
            }

            return null;
                               
        } catch (\Throwable $e) {
            $logsErro = new logsError();
            $logsErro->saveInDB('E0082', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return null;
        }
    }

    public function saveFromSAP($absEntry, $obj, $reference){

        $sap = new Company(false);
        $uploads = $sap->query("SELECT AbsEntry, trgtPath, FileName, FileExt FROM ATC1 T0 WHERE T0.[AbsEntry] = {$absEntry}");

        foreach($uploads as $index => $value){
            
            $upload = New Upload;
            
            $absolute_upload_path = "{$value['trgtPath']}\\{$value['FileName']}.{$value['FileExt']}";
            $extension = $value['FileExt'];
            $fileName = str_random(5)."-".date('his')."-".str_random(3)."=".$value['FileName'];
            $folderpath  = public_path("uploads/{$reference}/item-".$obj->id);
            
            if (!file_exists($folderpath)) {
                mkdir($folderpath, 0777, true);
            }

            copy($absolute_upload_path , $folderpath."/$fileName.$extension");
            
            $upload->absEntry = $value['AbsEntry'];
            $upload->reference = $reference;
            $upload->idReference = $obj->id;
            $upload->diretory = "/uploads/{$reference}/item-$obj->id/$fileName.$extension";
            $upload->idUser = $obj->idUser;
            $upload->save();
        }
    }

    public function deleteInSAP($obj){
        $path = pathinfo(public_path($obj->diretory));
        $sap = new Company(false);
        $query = $sap->query("SELECT T0.[Line] FROM ATC1 T0 WHERE T0.[AbsEntry] = $obj->absEntry AND T0.[FileName] = '{$path['filename']}'");
        if(!empty($query)){
            try {
                $sap = NewCompany::getInstance()->getCompany();
                $atc = $sap->GetBusinessObject(221);
                $atc->GetByKey((string)$obj->absEntry);
                
                $atc->Lines->SetCurrentLine((Int)$query[0]['Line'] - 1);
                $atc->Lines->FileName = '';
                $ret = $atc->Update();
                if($ret !== 0){
                    dd($sap->GetLastErrorDescription());
                }else{
                    dd('foi');
                }
            } catch (\Throwable $e) {
                dd($e->getMessage());
            }
        }
    }

    public function linkDocument(){

        try {
            if(empty($this->absEntry) && $this->is_locked == '1'){
                $this->saveInSAP();
            }else{
                $reference = $this->getReferences($this->reference);
                $documentModel = $reference['model'];
                $document = $documentModel->find($this->idReference);
                $sap = NewCompany::getInstance()->getCompany();
                $sapDocument = $sap->GetBusinessObject($reference['boType']);
                $sapDocument->GetByKey((string)$document->codSAP);
                $sapDocument->AttachmentEntry = $this->absEntry;
                $ret = $sapDocument->Update();
        
                if($ret === 0){
                    $this->is_locked = 'N';
                    $this->vinculado = 'Y';
                    $this->save();
                }else{
                    $this->is_locked = 'Y';
                    $this->vinculado = 'N';
                    $this->save();
                }
            }
        } catch (\Exception $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('ATC0005',$e->getFile().' | '.$e->getLine(), "ATC: {$this->id} - {$this->reference} -> ".$e->getMessage());
        }

    }

    public function syncUploads($obj, $boType, $model){
        $sap = NewCompany::getInstance()->getCompany();

        $codeAttachment = $obj->saveInSAP();

        if(!is_null($codeAttachment)){
            $item = $sap->GetBusinessObject($boType);
            $item->GetByKey((string)$model->codSAP);
            $item->AttachmentEntry = $codeAttachment;
            $ret = $item->Update();
        }else{
            $obj->is_locked = 'Y';
            $obj->save();
        }
    }

    public function getReferences($reference){

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
            "advance_provider" => [
                "boType" =>  BoObjectTypes::oPurchaseDownPayments,
                "model" => new AdvanceProvider()
            ],
        ];

        return $references[$reference];
    }
}
