<?php

namespace App\Modules\Banks\Models\BillsReceiveAccount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\LogsError;

use App\Modules\Banks\Models\BillsReceiveAccount\Items;
use App\Modules\Banks\Models\BillsReceiveAccount\Payment;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoRcptTypes;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use App\CFItems;
use Litiano\Sap\NewCompany;

/**
 * App\Modules\Banks\Models\BillsReceiveAccount
 *
 * @property int $id
 * @property string $idUser
 * @property string|null $codSAP
 * @property string $taxDate
 * @property string|null $cashSum
 * @property string|null $transferDate
 * @property string|null $transferSum
 * @property string|null $transferReference
 * @property string|null $creditCard
 * @property string|null $cardValidUntil
 * @property string|null $creditCardNumber
 * @property string|null $numOfCreditPayments
 * @property string|null $creditSum
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCardValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCashSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCreditCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCreditSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereNumOfCreditPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereTransferReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereTransferSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $code
 * @property string $docDate
 * @property string $docDueDate
 * @property float|null $docTotal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount whereDocTotal($value)
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\BillsReceiveAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\BillsReceiveAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\BillsReceiveAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceiveAccount\BillsReceiveAccount whereStatus($value)
 */
class BillsReceiveAccount extends Model
{
  const STATUS_OPEM = 1;
  const STATUS_CLOSE = 0;
  const STATUS_CANCELED = 2;

  public function saveInDB(Request $request){
    try{
      $this->idUser = auth()->user()->id;
      $this->code = $this->createCode();
      $this->docDate = $request->docDate;
      $this->docDueDate = $request->docDueDate;
      $this->taxDate = $request->taxDate;
      $this->comments = $request->obsevacoes;
      $this->status = self::STATUS_CLOSE;
      if($this->save()){
        $docTotal = 0;
        foreach ($request->items as $key => $value) {
           $BPA = new Items();
           $BPA->saveInDB($value, $this->id);
           $docTotal += clearNumberDouble($value['sumPaid']);
        }
        if(isset($request->payment)){
          $pay = new Payment();
          $pay->saveInDB($request->payment, $this->id);
        }
        $this->docTotal = $docTotal;
        $this->save();
        if(workCashFlow()){
          $CFItems =  new CFItems(); //fluxo de caixa;
          $CFItems->saveInDB($request->cashFlow,$this->id,'bills_receive_accounts', $this->docTotal);
        }
      }
    }catch (\Exception $e) {
      $logsErrors = new LogsError();
      $logsErrors->saveInDB('E0057', 'Cadastro de conntas a receber por contas na base WEB', $e->getMessage());
    }
  }

  private function createCode(){
      $busca = DB::select("select top 1 bills_receive_accounts.code from bills_receive_accounts order by bills_receive_accounts.id desc");
      $codigo = '';
      if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'BRA00001';
      } else {
            $codigo = $busca[0]->code;
            $codigo++;
      }
      return $codigo;
  }
  private function getNameUser($id){
      return DB::SELECT("SELECT T0.name FROM Users T0 JOIN bills_receive_accounts T1 on T1.idUser =  T0.id WHERE T1.id = '{$id}'")[0]->name;
  }
  private function getItems($id){
    return Items::where('idBillsReceiveAccount', '=', $id)->get();
  }
  private function getPayments($id){
    return Payment::where('idBillsReceiveAccount', '=', $id)->get();
  }
  public function saveInSAP($obj){
    try {
        $sap = NewCompany::getInstance()->getCompany();
      $vPay = $sap->GetBusinessObject(BoObjectTypes::oIncomingPayments);
      $vPay->DocType = BoRcptTypes::rAccount;
      $vPay->DocObjectCode= BoPaymentsObjectType::bopot_IncomingPayments;
      $checkItems = $this->getItems($obj->id);

      foreach ($checkItems as $key => $value) {
        $vPay->AccountPayments->AccountCode= (String) $value->accountCode;
        $vPay->AccountPayments->Decription = (String) $value->decription;
        $vPay->AccountPayments->SumPaid  = (Double) $value->sumPaid;
        $vPay->AccountPayments->ProjectCode = (String) $value->projectCode;
        $vPay->AccountPayments->ProfitCenter = (String) $value->profitCenter;
        $vPay->AccountPayments->Add();
      }

      $vPay->TaxDate= $obj->taxDate;
      $vPay->Remarks = (String) $obj->comments;

      $checkPayment = $this->getPayments($obj->id);
      if(!empty($checkPayment)){
        foreach ($checkPayment as $key => $value) {
          if(trim($value->money) == 'Y'){
            $vPay->CashAccount = (String) $value->cashAccount;
            $vPay->CashSum = (Double) $value->cashSum;
          }if(trim($value->credit) == 'Y'){
            $vPay->CreditCards->CreditCard = $value->creditCard;
            $vPay->CreditCards->CardValidUntil = $value->cardValidUntil;
            $vPay->CreditCards->CreditCardNumber = $value->creditCardNumber;
            $vPay->CreditCards->NumOfCreditPayments = $value->numOfCreditPayments;
            $vPay->CreditCards->CreditAcct = $value->creditAcct;
            $vPay->CreditCards->CreditSum = (Double) $value->creditSum;
          }if(trim($value->transfer) == 'Y'){
            $vPay->TransferDate = $value->transferDate;
            $vPay->TransferAccount = $value->transferAccount;
            $vPay->TransferReference = $value->transferReference;
            $vPay->TransferSum = (Double)  $value->transferSum;
          }
        }
      }
      if ($vPay->Add() !== 0) {
        $obj->message = $sap->GetLastErrorDescription();
        $obj->is_locked = true;
        $obj->save();
      }else{
        $obj->codSAP = $sap->GetNewObjectKey();
        $obj->message = 'Salvo no SAP';
        $obj->is_locked = false;
        $obj->save();
      }
    } catch (\Exception $e) {
      $logsErrors = new LogsError();
      $logsErrors->saveInDB('E01IF',$e->getFile(). ' | '. $e->getLine(), $e->getMessage());
    }

  }
  public function cancelInSAP($obj){
    try {
        $sap = NewCompany::getInstance()->getCompany();
      $opor = $sap->GetBusinessObject(BoObjectTypes::oIncomingPayments);
      if(empty($obj->codSAP) || is_null($obj->codSAP)){
        $obj->is_locked = false;
        $obj->status = self::STATUS_CANCELED;
        $obj->save();
      }else{
        if($opor->GetByKey((string) $obj->codSAP)){
          if($opor->Cancel === 0){
            $obj->is_locked = false;
            $obj->status =  self::STATUS_CANCELED;
            $obj->save();
          }else{
            $obj->message = $sap->GetLastErrorDescription();
            $obj->is_locked = true;
            $obj->save();
          }
        }
      }

    } catch (\Exception $e) {
      $obj->is_locked = true;
      $obj->message = $e->getMessage();
      $obj->save();
    }
  }
}
