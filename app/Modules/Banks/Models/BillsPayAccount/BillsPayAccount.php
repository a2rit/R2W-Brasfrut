<?php

namespace  App\Modules\Banks\Models\BillsPayAccount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Modules\Banks\Models\BillsPayAccount\Items;
use App\Modules\Banks\Models\BillsPayAccount\Payment;
use App\LogsError;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoRcptTypes;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use App\CFItems;
use Litiano\Sap\NewCompany;

/**
 * App\Modules\Banks\Models\BillsPayAccount
 *
 * @mixin \Eloquent
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCardValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCashSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCreditCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCreditSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereNumOfCreditPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereTransferReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereTransferSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereUpdatedAt($value)
 * @property string $code
 * @property string $docDate
 * @property string $docDueDate
 * @property float|null $docTotal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount whereDocTotal($value)
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\BillsPayAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\BillsPayAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\BillsPayAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPayAccount\BillsPayAccount whereStatus($value)
 */
class BillsPayAccount extends Model
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
          $CFItems->saveInDB($request->cashFlow,$this->id,'bills_pay_accounts', $this->docTotal);
        }

      }
    }catch (\Exception $e) {
      $logsErrors = new LogsError();
      $logsErrors->saveInDB('EF845', $e->getFile().' | '.$e->getLine(), $e->getMessage());
    }
  }

    private function createCode(){
      $busca = DB::select("select top 1 bills_pay_accounts.code from bills_pay_accounts order by bills_pay_accounts.id desc");
      $codigo = '';

        if (empty($busca) || is_null($busca) || $busca == '') {
              $codigo = 'BPA00001';
        } else {
              $codigo = $busca[0]->code;
              $codigo++;
        }
        return $codigo;
    }

    private function getNameUser($id){
        return DB::SELECT("SELECT T0.name FROM Users T0 JOIN bills_pay_accounts T1 on T1.idUser =  T0.id WHERE T1.id = '{$id}'")[0]->name;
    }
    private function getItems($id){
      return Items::where('idBillsPayAccount', '=', $id)->get();
    }
    private function getPayments($id){
      return Payment::where('idBillsPayAccount', '=', $id)->get();
    }
    public function saveInSAP($obj){
        try{
            $sap = NewCompany::getInstance()->getCompany();
          $vPay = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);
          $vPay->DocType = BoRcptTypes::rAccount;
          $vPay->DocObjectCode= BoPaymentsObjectType::bopot_OutgoingPayments;
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
              }if(trim($value->debit) == 'Y'){
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
        }catch (\Throwable $e) {
          $obj->message = $e->getMessage();
          $obj->is_locked = true;
          $obj->save();
        }
    }

    public function cancelInSAP($obj){
      try {
          $sap = NewCompany::getInstance()->getCompany();
        $opor = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);
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
    public function updateInDB($request, $obj){
      $obj->docDate = date_USA($request->data_lancamento);
      $obj->docDueDate = date_USA($request->data_vencimento);
      $obj->taxDate = date_USA($request->data_documento);
      $obj->comments = $request->obsevacoes;
      $obj->is_locked = false;
      $docTotal=0;

      foreach ($request->items as $key => $value) {
        $items = new Items();
        $objItem = Items::find((int) $key);
        $items->updateInDB($value, $objItem);
        $docTotal += clearNumberDouble($value['vl']);
      }
      $obj->docTotal = $docTotal;
      $obj->save();

      $busca1 = DB::SELECT("SELECT T0.id from bills_pay_account_payments as T0 Join bills_pay_accounts T1 on T0.idBillsPayAccountPayment = T1.id WHERE T1.id = '{$obj->id}'");
      $pay1 = new Payment();
      $objPay = Payment::find($busca1[0]->id);
      $pay1->updateInDB($request, $objPay);

    }
public function getCashFlowLabel()
    {
		$id = $this->id;
        try {
            $item = CFItems::join('cash_flows', 'cash_flows.id', '=', 'cash_flow_items.idCashFlow')
                ->where([
                    'cash_flow_items.idTransation' => $id,
                    'cash_flow_items.transation' => 'incoing_invoices'
                ])
                ->select('cash_flows.id')
                ->get();
            if (isset($item[0]->id)) {
                return $item[0]->id;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('F90f', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return false;
        }
    }
}
