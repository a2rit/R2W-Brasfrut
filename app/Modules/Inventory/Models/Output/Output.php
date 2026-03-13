<?php

namespace App\Modules\Inventory\Models\Output;

use App\User;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Modules\Inventory\Models\Output\Item;
use Illuminate\Support\Facades\Session;
use App\LogsError;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;

/**
 * App\Output
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $date
 * @property string $code
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereUpdatedAt($value)
 * @property string $description
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereDescription($value)
 * @property string|null $codSAP
 * @property string $DocDate
 * @property string $TaxDate
 * @property string $warehouse
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereWarehouse($value)
 * @property string|null $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Inventory\Models\Output\Output query()
 * @property-read \App\Modules\Inventory\Models\Output\Item $items
 * @property-read \App\User $user
 */
class Output extends Model
{
    public function saveInDB($request)
    {
        try {
            $this->code = $this->createCode();
            $this->idUser = !empty($request->creator_user_id) ? $request->creator_user_id : auth()->user()->id;
            $this->DocDate = $request->data;
            $this->TaxDate = DATE('Y-m-d');
            $this->description = $request->obsevacoes;
            $this->is_locked = false;
            $this->dbUpdate = '0';
            $this->save();
            foreach ($request->items as $key => $value) {
                $item = new Item();
                $item->saveInDB($value, $this->id);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function createCode()
    {
        $busca = DB::select("select top 1 outputs.code from outputs order by outputs.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'OTP0001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function user(){
        return $this->belongsTo(User::class, 'idUser','id');
    }

    public function items(){
        return $this->belongsTo(Item::class, 'id','idOutputs');
    }
    public function saveInSAP($objOut)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();

            if(!empty($objOut->codSAP) || existsInSAP('OIGE', 'U_R2W_CODE', $objOut->code)) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('DUPLIC002','DUPLICACAO DE DOCUMENTO', $objOut->code);
                return true;
            }

            $item = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenExit);
            $item->DocDate = $objOut->DocDate;
            $item->TaxDate = $objOut->TaxDate;
            $item->PaymentGroupCode = -1; //Ultimo preço de compra
            $item->Comments = (String)$objOut->description;
            $item->JournalMemo = 'S.M Código web: '. $objOut->code;
            $item->UserFields->fields->Item("U_R2W_CODE")->value = $objOut->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $objOut->getNameUser($objOut->idUser);

            foreach ($objOut->getItens($objOut->id) as $key => $value) {
                $item->Lines->ItemCode = (String)$value->itemCode;
                $item->Lines->Quantity = (Double)$value->quantity;
                $item->Lines->UnitPrice = (Double)$value->price;
                $item->Lines->ProjectCode = (String)$value->projectCode;
                $item->Lines->CostingCode = (String)$value->costCenter;
                $item->Lines->CostingCode2 = (String)$value->costCenter2;
                $item->Lines->AccountCode = (String)$value->accountCode;
                $item->Lines->WarehouseCode = (String)$value->wareHouseCode;
                $item->Lines->Add();
            }

            if ($item->Add() !== 0) {
                $logsError = new LogsError();
                $logsError->saveInDB("E0034", "cadastro de saida de mercadoria no SAP", $sap->GetLastErrorDescription());
                $objOut->message = $sap->GetLastErrorDescription();
                $objOut->dbUpdate = '0';
                $objOut->is_locked = true;
                $objOut->save();
                return $sap->GetLastErrorDescription();
            } else {
                $objOut->codSAP = $sap->GetNewObjectKey();
                $objOut->message = "Item salvo no SAP com sucesso.";
                $objOut->is_locked = false;
                $objOut->dbUpdate = '0';
                $objOut->save();
                return true;
            }

        } catch (\Throwable $e) {
            $logsError = new LogsError();
            $logsError->saveInDB("E0035", $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $objOut->message = $e->getMessage();
            $objOut->dbUpdate = '0';
            $objOut->is_locked = true;
            $objOut->save();
        }
    }

    public function getNameUser($id)
    {
        return Output::join('users', 'users.id', '=', 'outputs.idUser')
            ->where('users.id', '=', $id)
            ->get(['users.name'])[0]->name;
    }

    public function getItens($id)
    {
        return DB::SELECT("SELECT T0.itemCode,T0.quantity,T0.price, T0.costCenter,T0.costCenter2, T0.projectCode,T0.accountCode,T0.wareHouseCode FROM output_items T0 join outputs T1 on T0.idOutputs = T1.id WHERE T1.id = '{$id}'");
    }
}
