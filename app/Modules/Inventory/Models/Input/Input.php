<?php

namespace App\Modules\Inventory\Models\Input;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Modules\Inventory\Models\Input\Item;
use Illuminate\Support\Facades\Session;
use App\LogsError;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
use App\Upload;

/**
 * App\Input
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $date
 * @property string $code
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereUpdatedAt($value)
 * @property string|null $codSAP
 * @property string $DocDate
 * @property string $TaxDate
 * @property string $warehouse
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereWarehouse($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Input\Input query()
 */
class Input extends Model
{

    public function saveInDB(Request $request)
    {
        try {
            $this->code = $this->createCode();
            $this->idUser = auth()->user()->id;
            $this->DocDate = $request->data;
            $this->TaxDate = DATE('Y-m-d');
            $this->description = $request->obsevacoes;
            $this->is_locked = false;
            
            if ($this->save()) {
                foreach ($request->get('items',[]) as $key => $value) {
                    $item = new Item();
                    $item->saveInDB($value, $this->id);
                }
            }

        } catch (\Throwable $e) {
            $this->is_locked = true;
            $this->message = ($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB("E0027", $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }


    }

    private function createCode()
    {
        $busca = DB::select("select top 1 inputs.code from inputs order by inputs.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'IN00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function saveInSAP($objIn)
    {
        try {
            
            try {
                $sap = NewCompany::getInstance()->getCompany();
            } catch (\Exception $e) {
                $objIn->message = $e->getMessage();
                $objIn->save();
                throw $e;
            }

            $item = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenEntry);
            $item->DocDate = $objIn->DocDate;
            $item->TaxDate = $objIn->TaxDate;
            $item->PaymentGroupCode = -2; //Ultimo preço atualizado
            $item->Comments = mb_convert_encoding((String)$objIn->description, 'UTF-8');
            $item->JournalMemo = 'E.M Código web: '. $objIn->code;
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $objIn->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $objIn->getNameUser($objIn->idUser);

            $attachment = Upload::where('reference', '=', 'inputs')
                ->where('idReference', '=', $objIn->id)
                ->first();

            if(!is_null($attachment)){

                $codeAttachment = $attachment->saveInSAP();

                if(!is_null($codeAttachment)){
                    $item->AttachmentEntry = $codeAttachment;
                }

            }

            $j = 0;
            foreach ($objIn->getItens($objIn->id) as $key => $value) {

                $item->Lines->SetCurrentLine($j);
                $item->Lines->ItemCode = (String)$value->itemCode;
                $item->Lines->Quantity = (Double)$value->quantity;
                $item->Lines->UnitPrice = (Double)$value->price;
                $item->Lines->ProjectCode = (String)$value->projectCode;
                #$item->Lines->CostingCode = (String)$value->costingCode;
                $item->Lines->CostingCode = (String)$value->costCenter;
                $item->Lines->CostingCode2 = (String)$value->costCenter2;
                $item->Lines->AccountCode = (String)$value->accountCode;
                $item->Lines->WarehouseCode = (String)$value->wareHouseCode;
                $item->Lines->Add();
                
                $j++;
            }

            if ($item->Add() !== 0) {
                $logsError = new LogsError();
                $logsError->saveInDB("E0029", 'Erro SAP', $sap->GetLastErrorDescription());
                $objIn->message = $sap->GetLastErrorDescription();
                $objIn->is_locked = true;
                $objIn->save();
                
            } else {
                $objIn->codSAP = $sap->GetNewObjectKey();
                $objIn->message = "Item salvo no SAP com sucesso.";
                $objIn->is_locked = false;
                $objIn->dbUpdate = false;
                $objIn->save();
            }
        } catch (\Throwable $e) {
            $objIn->message = $e->getMessage();
            $objIn->is_locked = true;
            $objIn->save();
            $logsError = new LogsError();
            $logsError->saveInDB("E0029", $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function getNameUser($id)
    {
        return Input::join('users', 'users.id', '=', 'inputs.idUser')
            ->where('users.id', '=', $id)
            ->get(['users.name'])[0]->name;
    }

    public function getItens($id)
    {
        return DB::SELECT("SELECT T0.itemCode,T0.quantity, T0.price, T0.costCenter, T0.costCenter2, T0.projectCode,T0.accountCode,T0.wareHouseCode FROM input_items T0 join inputs T1 on T0.idInputs = T1.id WHERE T1.id = '{$id}'");
    }

    public function updateUpload(){

        try {

            $attachment = Upload::where('reference', '=', 'inputs')
                ->where('idReference', '=', $this->id)
                ->first();
    
            if(!is_null($attachment)){

              $sap = NewCompany::getInstance()->getCompany();
              $item = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenEntry);
              $item->GetByKey((string)$this->codSAP);
      
              $codeAttachment = $attachment->saveInSAP();
              
              if(!is_null($codeAttachment)){
                $item->AttachmentEntry = $codeAttachment;
              }

              $ret = $item->Update();
              
              if($ret !== 0){
                $this->message = $sap->GetLastErrorDescription();
                $this->save();
                }
            }
        } catch (\Throwable $e) {
            $this->message = (String)$e->getMessage();
            $this->save();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('PRUP01', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        }

    }

}
