<?php

namespace App\Modules\Inventory\Models\Transfer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Models\Transfer\Item;
use App\Modules\Inventory\Models\TransferTaking\TransferTaking;
use App\Upload;
use App\LogsError;
use App\User;
use Litiano\Sap\NewCompany;

use Litiano\Sap\Enum\BoObjectTypes;

/**
 * App\Transfer
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereFromWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereToWhs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereUpdatedAt($value)
 * @property string $docDate
 * @property string $taxDate
 * @property string|null $codSAP
 * @property string $fromWarehouse
 * @property string $toWarehouse
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereFromWarehouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereToWarehouse($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Transfer\Transfer query()
 */
class Transfer extends Model
{
    public function saveInDB(Request $request)
    {
        try {
            $this->idUser = auth()->user()->id;
            $this->docDate = $request->data;
            $this->taxDate = DATE('Y-m-d');
            $this->code = $this->createCode();
            
            $this->fromWarehouse = $request->warehouse; //Session::get('fomWarehouse');
            
            $this->toWarehouse = $request->toWarehouse; //Session::get('toWarehouse');
            
            $this->comments = mb_convert_encoding((String)$request->comments, 'UTF-8');
            $this->is_locked = false;
            // dd($request);
            if ($this->save()) {
                foreach ($request->items as $key => $value) {
                    $item = new Item();
                    $item->saveInDB($value, $this->id);
                }
            } else {
                return ['type' => 'error', 'message' => 'contate o suporte erro na validação dos dados'];
            }
            return ['type' => 'success'];
        } catch (\Throwable $e) {
            $this->is_locked = true;
            $this->message = ($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB("E0038", "cadastro de transferencia de estoque na WEB", $e->getMessage());
            return ['type' => 'error', 'message' => $e->getMessage()];
        }

    }

    public function saveFromTransferTaking($obj){

        try{
    
            $this->idUser = auth()->user()->id;
            $this->idTransferTaking = $obj->id;
            $this->docDate = DATE('Y-m-d');
            $this->taxDate = $obj->taxDate;
            $this->code = $this->createCode(); 
            $this->fromWarehouse = $obj->fromWarehouse; //Session::get('fomWarehouse'); 
            $this->toWarehouse = $obj->toWarehouse; //Session::get('toWarehouse');
            $this->comments = mb_convert_encoding($obj->comments, 'UTF-8');
            $this->is_locked = false;

            if($this->save()){
                foreach($obj->getItensTransf($obj->id) as $key => $value){
                    if($value->quantityTransfer > 0 && ($value->quantity >= $value->quantityServed)){
                        $valor['itemCode'] = $value->itemCode;
                        $valor['qtd'] =  $value->quantityTransfer;
                        $valor['projectCode'] = $value->projectCode;
                        $valor['centroCusto'] = $value->costCenter;
                        $valor['centroCusto2'] = $value->costCenter2;
                        $valor['id_transfer_taking_item'] = $value->id;
                        $item = new Item();
                        $item->saveInDB($valor, $this->id);
                    }
                }
            }else {
                return ['type' => 'error', 'message' => 'contate o suporte erro na validação dos dados'];
            }
            return ['type' => 'success'];
        } catch (\Throwable $e) {
            $this->is_locked = true;
            $this->message = ($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB("E0039", "cadastro de transferencia de estoque na WEB", $e->getMessage());
            return ['type' => 'error', 'message' => $e->getMessage()];
        }

    }

    public function saveInSAP($obj,$transferTaking = null)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $item = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
            $item->DocDate = $obj->docDate;
            $item->TaxDate = $obj->taxDate;
            $item->FromWarehouse = $obj->fromWarehouse;
            $item->ToWarehouse = $obj->toWarehouse;
            $item->Comments = mb_convert_encoding((String)$obj->comments, 'UTF-8');
            //Teste para salvar em observação diario
            $item->JournalMemo = 'Transferência. Código web: '. $obj->code;
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);
            $attachment = Upload::where('reference', '=', 'transfers')
                ->where('idReference', '=', $obj->id)
                ->first();

            if(!is_null($attachment)){
                $codeAttachment = $attachment->saveInSAP();
                
                if(!is_null($codeAttachment)){
                    $item->AttachmentEntry = $codeAttachment;
                }
            }

            $j = 0;
            foreach ($obj->getItens($obj->id) as $key => $value) {
                $item->Lines->SetCurrentLine($j);
                $item->Lines->ItemCode = (String)$value->itemCode;

                if(is_numeric($value->quantity)){
                    $item->Lines->Quantity = floatval($value->quantity);
                } else {
                    $item->Lines->Quantity = clearNumberDouble($value->quantity);
                }

                $item->Lines->ProjectCode = (String)$value->projectCode;
                $item->Lines->DistributionRule = (String)$value->costCenter;
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
                //Atualiza o pedido com o codigo SAP da transferência gerada
                if($transferTaking){
                    $transferTaking->codSAPTransf = $obj->codSAP;
                    //$transferTaking->codWEBTransf = $obj->code;
                    $transferTaking->idTransf = $obj->id;
                    $transferTaking->message = "Transferência gerada. cod WEB: ".$obj->code ;
                    $transferTaking->is_locked = false;
                    $transferTaking->dbUpdate = 1;
                    $transferTaking->save();
                }
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

    private function createCode()
    {
        $busca = DB::select("select top 1 transfers.code from transfers order by transfers.id desc");
        
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'TRA00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function getNameUser($id)
    {
        return Transfer::join('users', 'users.id', '=', 'transfers.idUser')
            ->where('users.id', '=', $id)
            ->select('users.name')
            ->get()[0]->name;
    }

    public function getItens($id)
    {
        return DB::SELECT("SELECT T0.itemCode, T0.quantity, T0.projectCode, T0.distributionRule, T0.costCenter, T0.costCenter2 FROM transfer_items T0 join transfers T1 on T0.idTransfer = T1.id WHERE T1.id = '{$id}'");
    }
}
