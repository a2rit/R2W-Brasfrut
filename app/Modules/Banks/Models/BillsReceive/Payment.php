<?php

namespace App\Modules\Banks\Models\BillsReceive;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\BillsReceivePayment
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idBillsReceive
 * @property float|null $docTotal
 * @property string $money
 * @property string|null $cashAccount
 * @property float|null $cashSum
 * @property string $credit
 * @property string|null $creditCard
 * @property string|null $cardValidUntil
 * @property string|null $creditCardNumber
 * @property int|null $numOfCreditPayments
 * @property string|null $creditAcct
 * @property float|null $creditSum
 * @property string $debit
 * @property string|null $transferDate
 * @property string|null $transferAccount
 * @property float|null $transferSum
 * @property string|null $transferReference
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCardValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCashAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCashSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCreditAcct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCreditCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCreditSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereIdBillsReceive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereNumOfCreditPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTransferAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTransferReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTransferSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereUpdatedAt($value)
 * @property string $transfer
 * @property string $other
 * @property string|null $otherAccount
 * @property float|null $otherSum
 * @property string $check
 * @property float|null $checkSum
 * @property string|null $dueDate
 * @property string|null $countryCode
 * @property string|null $bankCode
 * @property string|null $branch
 * @property string|null $acctNum
 * @property string|null $checkAccount
 * @property string $trnsfrable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereAcctNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCheckAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCheckSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereOtherAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereOtherSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment whereTrnsfrable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\Payment query()
 */
class Payment extends Model
{
    protected $table = 'bills_receive_payments';

    public function saveInDB($request, $id){
          $docTotal = 0;
          $request = (object) $request;
          if($this->checkPayments($request,'1') || $this->checkPayments($request,'2') || $this->checkPayments($request,'3') || $this->checkPayments($request,'4') || $this->checkPayments($request,'5')){
            $this->idBillsReceive = $id;
            if(checkPayments($request,'1')){//dinheiro
              $this->money = 'Y';
              $this->cashAccount = (String) $request->conta_dinheiro;
              $this->cashSum = clearNumberDouble($request->total_dinheiro);
              $docTotal += $this->cashSum;
            }
            if($this->checkPayments($request,'2')){//transfer
              $this->transfer = 'Y';
              $this->transferDate = (String) $request->dt_transferencia;
              $this->transferAccount = (String) $request->conta_transferencia;
              $this->transferSum = clearNumberDouble($request->total_transfrencia);
              $this->transferReference = (String) $request->referencia_transferencia;
              $docTotal += $this->transferSum;
            }
            /*if($this->checkPayments($request,'3')){//credito
              $this->debit = 'Y';
              $this->creditCard = $request->name_cartao;
              $this->cardValidUntil = DATE('Y-m-d');
              #$this->creditCardNumber = (String) $request->num_cartao;
              $this->numOfCreditPayments = (Int) $request->parcelas_cartao;
              $this->creditAcct = (String)  $request->conta_cartao;
              $this->creditSum = clearNumberDouble($request->total_credito);
              $docTotal += $this->creditSum;
            }
            /*if($this->checkPayments($request,'4')){//outro
              $this->other = 'Y';
              $this->otherAccount = (String) DB::SELECT("select value from settings where code like 'acctMoney'")[0]->value;
              $this->otherSum = clearNumberDouble($request->total_dinheiro);
              $docTotal += $this->otherSum;
            }
            if($this->checkPayments($request,'5')){//check
              $this->check = 'Y';
              $this->checkSum = clearNumberDouble($request->valor_cheque);
              $this->dueDate = date_USA($request->dt_vencimento_cheque);
              $this->countryCode = 'BR';
              $this->bankCode = $request->nome_banco_cheque;
              $this->branch = $request->filial_cheque;
              $this->acctNum = $request->numero_conta_cheque;
              $this->checkAccount = $request->conta_cheque;
              $this->trnsfrable = $request->endosso_cheque;
              $docTotal += $this->checkSum;
            }*/
            $this->docTotal = $docTotal;
            $this->save();
          }
        }
    private function checkPayments($request, $value){
          switch ($value) {
            case '1':
              if(isset($request->total_dinheiro) && ($request->total_dinheiro > 0) && (!is_null($request->total_dinheiro))){
                return true;
              }else{
                return false;
              }
              break;
            case '2':
               if(isset($request->dt_transferencia) && (!is_null($request->total_transfrencia))
               && (isset($request->total_transfrencia) && (!is_null($request->total_transfrencia)) && ($request->total_transfrencia > 0))){
                 return true;
               }else{
                 return false;
               }
            break;
             case '3':
               if(isset($request->name_cartao) && (!is_null($request->name_cartao))
               && (isset($request->total_credito) && (!is_null($request->total_credito)) && ($request->total_credito > 0))
               && (isset($request->parcelas_cartao) && (!is_null($request->parcelas_cartao)) && ($request->parcelas_cartao > 0))){
                 return true;
               }else{
                 return false;
               }
               break;
               case '4':
                 if(isset($request->conta_outros) && (!is_null($request->conta_outros))
                 && (isset($request->total_outros) && (!is_null($request->total_outros)) && ($request->total_outros > 0))){
                   return true;
                 }else{
                   return false;
                 }
                 break;
              case '5':
                   if(isset($request->dt_vencimento_cheque) && (!is_null($request->dt_vencimento_cheque))
                   && (isset($request->nome_banco_cheque) && (!is_null($request->nome_banco_cheque)))
                   && (isset($request->filial_cheque) && (!is_null($request->filial_cheque)))
                   && (isset($request->numero_conta_cheque) && (!is_null($request->numero_conta_cheque)))
                   && (isset($request->endosso_cheque) && (!is_null($request->endosso_cheque)))
                   && (isset($request->valor_cheque) && (!is_null($request->valor_cheque)) && ($request->valor_cheque > 0))){
                     return true;
                   }else{
                     return false;
                   }
                   break;
            default:
                return false;
              break;
          }
        }
}
