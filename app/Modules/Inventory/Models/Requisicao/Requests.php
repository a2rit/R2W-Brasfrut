<?php

namespace App\Modules\Inventory\Models\Requisicao;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use App\LogsError;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Litiano\Sap\NewCompany;
use Throwable;

/**
 * App\Requests
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $codSAP
 * @property string $requesterUser
 * @property string $clerkUser
 * @property string $qtd
 * @property string $costCenter
 * @property string $project
 * @property string $requiredDate
 * @property string $documentDate
 * @property string $codStatus
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereClerkUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereCodStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereDocumentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereQtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereRequesterUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereRequiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Requests whereUpdatedAt($value)
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Requests whereDescription($value)
 * @property string $code
 * @property string|null $description2
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Requests whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Requests whereDescription2($value)
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Requests whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Requisicao\Requests whereMessage($value)
 */
class Requests extends Model
{
    const STATUS_WAIT_CLERK = 0;
    const STATUS_CLERK_LINK = 1;  
    const STATUS_PARTIAL_ATTENDED = 2;
    const STATUS_REFUSED = 3;  
    const STATUS_RECEIVED = 4;  
    const STATUS_WAIT_REQUESTER = 5;  
    const STATUS_NFS_SAP = 6;   
    const STATUS_LINK = 7;  
    const STATUS_CANCELED = 8;  
    const TEXT_STATUS = [
        '0' => 'Aguardando',
        '1' => 'Atendente Vinculado',
        '2' => 'Parcialmente Atendida',
        '3' => 'Recusada',
        '4' => 'Recebida',
        '5' => 'Aguardando Solicitante',
        '6' => 'Compra Realizada',
        '7' => 'Vincular',
        '8' => 'Cancelada'
    ]; // retorno da nota fiscal de saida do SAP

    protected $table = 'requests';

    protected $casts = [
        'created_at' => 'datetime:Y-d-m',
        'updated_at' => 'datetime:Y-d-m',
    ];

    public function purchase_requests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'idInternalRequest', 'id');
    }

    public function requester(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'requesterUser');
    }

    public function userClerk(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'clerkUser');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Products::class, 'idRequest', 'id');
    }

    public function getComplementar($id){
        $products = Products::join('requests','requests.id', '=', 'request_products.idRequest')
                    ->where('request_products.idRequest', '=', $id)
                    ->select('request_products.*')->get();

        $newArray = [];
        foreach ($products as $key => $value) {
            $sap = new Company(false);
            $newArray[$key]['id'] = $value->id;
            $newArray[$key]['codSAP'] = $value->codSAP;
            $newArray[$key]['name'] = $sap->getDb()->table('OITM')->where('itemCode','=', $value->codSAP)->get(['ItemName'])[0]->ItemName;
            $newArray[$key]['quantityRequest'] = $value->quantityRequest;
            $newArray[$key]['quantityServed'] = $value->quantityServed;
            $newArray[$key]['pendingAmount'] = $value->pendingAmount;
            $newArray[$key]['costCenter'] = $value->costCenter;
            $newArray[$key]['costCenter2'] =  $value->costCenter2;
            $newArray[$key]['project'] = $value->project;
            $newArray[$key]['status'] = self::TEXT_STATUS[$value->status];
        }
        return $newArray;
    }
    public function saveInDB(Request $request)
    {
        $this->requesterUser = auth()->user()->id;
        $this->code = $this->createCode();
        $this->requiredDate = $request->data;
        $this->documentDate = DATE('Y-m-d');
        $this->description = $request->obsSolicitante;
        $this->codStatus = self::STATUS_LINK;
        $this->is_locked = false;
        $this->whs = $request->whs;
        if ($this->save()) {
            foreach ($request->requiredProducts as $key => $value) {
                $products = new Products();
                $products->saveInDB($value, $this->id);
            }
        }
    }
    private function createCode()
    {
        $busca = DB::select("select top 1 requests.code from requests order by requests.code desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'REC00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }
    public function getDflWhs($itemCode){
        $sap = new Company(false);
        return $sap->getDb()->table('OITM')->select('DfltWH')->where('ItemCode','=', $itemCode)->get();
    }

    public function saveOutputInSAP(Model $obj)
    {
        try {
            DB::transaction(function () use ($obj) {
                $newObject = $obj::query()->lockForUpdate()->find($obj->id);
                $this->saveOutputInSapTransaction($newObject);
            });
        } catch (Throwable $exception) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB(
                'E01226F', 'Error ao salvar no sap',
                "Request {$obj->code}({$obj->id}): {$exception->getMessage()}"
            );
            throw $exception;
        }
    }

    protected function saveOutputInSapTransaction($obj)
    {
        if($obj->codSAP || $this->existsInSAP('OIGE',$obj->code)) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('DUPLIC001','DUPLICACAO DE DOCMENTO', $obj->code);
            return true;
        }
        $sap = NewCompany::getInstance()->getCompany();
        #try {

        $item = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenExit);
        $item->DocDate = $obj->requiredDate;
        $item->TaxDate = $obj->documentDate;
        $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
        $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->requesterUser);
        $item->Comments = 'Requisição WEB: ' . $obj->code;
        
        foreach ($obj->getItens($obj->id) as $key => $value) {
            if($value->quantityServed > 0){
                $item->Lines->ItemCode = (String) $value->codSAP;
                $item->Lines->Quantity = (Double) $value->quantityServed;
                $item->Lines->WarehouseCode = (String) $this->getDflWhs($value->codSAP)[0]->DfltWH;
                $item->Lines->ProjectCode = (String) $value->project;
                $item->Lines->CostingCode = (String) ($value->costCenter === 'Centr_z' ? null : $value->costCenter);
                $item->Lines->CostingCode2 = (String) ($value->costCenter2 === 'Centr_z2' ? null : $value->costCenter2);
                $item->Lines->Add();
                if ($value->quantityServed != $value->quantityRequest) {
                    $end = false;
                }
            }
        }

        if ($item->Add() == 0) {
            $obj->codSAP = $sap->GetNewObjectKey();
            $obj->message = "Salvo no SAP com sucesso.";
            $obj->is_locked = false;
            $obj->save();
        } else {
            $obj->message = $sap->GetLastErrorDescription();
            $obj->codStatus = 2;
            $obj->is_locked = true;
            $obj->save();
            throw new \Exception($sap->GetLastErrorDescription());
        }
        /*} catch (\Exception $e) {
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
			$logsErrors = new LogsError();
            $logsErrors->saveInDB('E021326F', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            OutputToSAP::dispatch($obj)->delay(now()->addMinutes(10));
        }*/
    }

    public function saveInputInSAP(Company $sap, $obj)
    {
        if($this->existsInSAP('OIGN',$obj->code)) {
            return true;
        }
        try {
            $user = User::find($obj->requesterUser);
            $item = $sap->getBusinessObject(BoObjectTypes::oInventoryGenEntry);
            $item->DocDate = $obj->requiredDate;
            $item->TaxDate = $obj->documentDate;
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            #$item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->requesterUser);
			$item->Comments = 'Requisição WEB: ' . $obj->code;
            $end = true;
            foreach ($obj->getItens($obj->id) as $key => $value) {
                $item->Lines->ItemCode = $value->codSAP;
                $item->Lines->Quantity = $value->quantityServed;
                $item->Lines->WarehouseCode =  $user->whsDefault;
                $item->Lines->ProjectCode = $value->project;
                $item->Lines->CostingCode = (String)$value->costCenter;
                $item->Lines->CostingCode2 = (String)$value->costCenter2;
                $item->Lines->Add();
                if ($value->quantityServed != $value->quantityRequest) {
                    $end = false;
                }
            }

            if ($item->Add() == 0) {
                $obj->codSAP = $sap->getNewObjectKey();
                $obj->message = "Salvo no SAP com sucesso.";
                $obj->is_locked = false;
                /*if ($end) {
                    $obj->codStatus = self::STATUS_RECEIVED;
                } else {
                    $obj->codStatus = self::STATUS_PARTIAL_ATTENDED;
                }*/
                $obj->save();
            } else {
                $obj->message = $sap->getLastErrorDescription();
                $obj->is_locked = true;
                $obj->save();
				$logsErrors = new LogsError();
				$logsErrors->saveInDB('E0F226F', 'Error ao salvar no sap', $sap->getLastErrorDescription());
			}
        } catch (Throwable $e) {
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
			$logsErrors = new LogsError();
            $logsErrors->saveInDB('E02226F', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        
        }
    }

    public function cancel(){
        if(empty($this->codSAP)){
            $this->codStatus = self::STATUS_CANCELED;
            return $this->save();
        }
        return false;
    }

    private function existsInSAP($table,$code){
        $sap = new Company(false);
        $check = $sap->getDb()->table($table)->where('U_R2W_CODE', $code)->get();
        if(count($check) > 0) {
            $aux = true;
        }else{
            $aux = false;
        }
//        if($aux){
//            if($code->is_locked){
//                $aux = false;
//            }else{
//                $aux = true;
//            }
//        }
        return $aux;

    }
    public function getNameUser($id)
    {
        return User::where('id', '=', $id)->get()[0]->name;
    }
    public function getItens($id)
    {
        return Products::where('request_products.idRequest', '=', $id)->get();
    }

}
