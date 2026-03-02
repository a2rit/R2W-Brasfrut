<?php

namespace App\Modules\Inventory\Models\TransferTaking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Models\TransferTaking\Item;
use App\LogsError;
use App\Modules\Inventory\Models\Transfer\Transfer;
use App\Upload;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Litiano\Sap\NewCompany;

use Litiano\Sap\Enum\BoObjectTypes;

/**
 * App\TransferTaking
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $date
 * @property string $code
 * @property string $toWhs
 * @property string $fromWhs
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereFromWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereToWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereUpdatedAt($value)
 * @property string $docDate
 * @property string $taxDate
 * @property string|null $codSAP
 * @property string $fromWarehouse
 * @property string $toWarehouse
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereFromWarehouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereToWarehouse($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\TransferTaking\TransferTaking query()
 */
class TransferTaking extends Model
{

    protected $table = 'transfersTaking';


    const STATUS_PENDING = 1;
    const STATUS_RECEIPT = 2;
    const STATUS_CANCEL = 3;
    const STATUS_OPEN = 4;
    
    const TEXT_STATUS = [
        '1' => 'Parcial',
        '2' => 'Recebido',
        '3' => 'Cancelado',
        '4' => 'Aberto',
    ];

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'idTransferTaking', 'id');
    }

    public function saveInDB(Request $request)
    {
        try {
            $this->idUser = auth()->user()->id;
            $this->docDate = DATE('Y-m-d');
            $this->taxDate = $request->data;
            $this->code = $this->createCode();
            $this->status = $this::STATUS_OPEN;
            $this->fromWarehouse = $request->warehouse; //Session::get('fomWarehouse');
            
            $this->toWarehouse = $request->toWarehouse; //Session::get('toWarehouse');
            
            $this->comments = mb_convert_encoding((String)$request->comments, 'UTF-8');
            $this->is_locked = false;
            if ($this->save()) {
                foreach ($request->items as $key => $value) {
                    $item = new Item();
                    $item->saveInDB($value, $this->id);
                }
            } else {
                return ['type' => 'error', 'message' => 'contate o suporte, erro na validação dos dados'];
            }
            return ['type' => 'success'];
        } catch (\Throwable $e) {
            $logsError = new LogsError();
            $logsError->saveInDB("E0040", "cadastro de transferencia de estoque na WEB", $e->getMessage());
            return ['type' => 'error', 'message' => $e->getMessage()];
        }

    }

    public function saveInSAP($obj)
    {
        try {
           
            try {
                $sap = NewCompany::getInstance()->getCompany();
            } catch (\Exception $e) {
                $obj->message = $e->getMessage();
                $obj->save();
                throw $e;
            }
            
            $item = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
            
            $item->DocDate = $obj->docDate;
            $item->TaxDate = $obj->taxDate;
            $item->FromWarehouse = $obj->fromWarehouse;
            $item->ToWarehouse = $obj->toWarehouse;
            $item->Comments = 'Pedido de transferência de estoque. Código web: '. $obj->code . '; '.is_null($obj->comments) ? '' : $obj->comments;
            
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);
        
            $j = 0;
            foreach ($obj->getItens($obj->id) as $key => $value) {
                
                $item->Lines->SetCurrentLine($j);
                $item->Lines->ItemCode = (String)$value->itemCode;
                $item->Lines->Quantity = clearNumberDouble($value->quantity);
                $item->Lines->ProjectCode = (String)$value->projectCode;
                $item->Lines->DistributionRule = (string)$value->costCenter;
                $item->Lines->DistributionRule2 = (String)$value->costCenter2;
                #$item->Lines->CostingCode2 = (String)$value->costCenter2;
                $item->Lines->Add();
                $j++;
            }

            if ($item->Add() !== 0) {
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = true;
                $obj->dbUpdate = 0;
                $obj->save();
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = "Item salvo no SAP com sucesso.";
                $obj->is_locked = false;
                $obj->dbUpdate = 0;
                $obj->save();
            }
        } catch (\Exception $e) {
            $LogsError = new LogsError();
            $LogsError->saveInDB('E0101', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->dbUpdate = 0;
            $obj->save();
        }

    }

    // public function updateUpload(){
    //     $attachment = Upload::where('reference', '=', 'transfersTaking')
    //         ->where('idReference', '=', $this->id)
    //         ->first();
  
    //     if(!is_null($attachment)){
    //       $sap = NewCompany::getInstance()->getCompany();
    //       $item = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
    //       $item->GetByKey((string)$this->codSAP);
  
    //       $codeAttachment = $attachment->saveInSAP();
          
    //       if(!is_null($codeAttachment)){
    //         $item->AttachmentEntry = $codeAttachment;
    //       }
    //       $ret = $item->Update();
          
    //       if($ret !== 0){
    //         $this->message = $sap->GetLastErrorDescription();
    //         $this->save();
    //       }
    //     }
    // }

    private function createCode()
    {
        $busca = DB::select("select top 1 transfersTaking.code from transfersTaking order by transfersTaking.id desc");
        
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'TRK00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function getNameUser($id)
    {
        return Transfer::join('users', 'users.id', '=', 'transfersTaking.idUser')
            ->where('users.id', '=', $id)
            ->select('users.name')
            ->get()[0]->name;
    }

    public function getItens($id)
    {
        return DB::SELECT("SELECT T0.itemCode,T0.id, T0.quantity,T0.quantityServed,T0.quantityTransfer, T0.projectCode, T0.distributionRule, T0.costCenter, T0.costCenter2 FROM transferTaking_items T0 join transfersTaking T1 on T0.idTransferTaking = T1.id WHERE T1.id = '{$id}'");
    }

    public function getItensTransf($id){
        return DB::SELECT("SELECT T0.itemCode,T0.id, T0.quantity,T0.quantityServed,T0.quantityTransfer, T0.projectCode, T0.distributionRule, T0.costCenter, T0.costCenter2 FROM transferTaking_items T0 join transfersTaking T1 on T0.idTransferTaking = T1.id WHERE T1.id = '{$id}' AND T0.quantityTransfer > '{0}' ");
    }

      
}
