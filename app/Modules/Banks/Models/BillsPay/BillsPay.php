<?php

namespace App\Modules\Banks\Models\BillsPay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Banks\Models\BillsPay\Payment;
use App\Modules\Banks\Models\BillsPay\Invoice;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoORCTPaymentTypeEnum;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use Litiano\Sap\Enum\BoRcptTypes;
use Litiano\Sap\Enum\BoYesNoEnum;
use Litiano\Sap\Enum\BoRcptInvTypes;
use App\LogsError;
use Litiano\Sap\NewCompany;

/**
 * App\billsPay
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $codSAP
 * @property string $cardCode
 * @property string $docDate
 * @property string $docDueDate
 * @property string $taxDate
 * @property string|null $comments
 * @property bool $is_locked
 * @property string|null $cashSum
 * @property string|null $transferDate
 * @property string|null $transferSum
 * @property string|null $transferReference
 * @property string|null $creditCard
 * @property string|null $cardValidUntil
 * @property string|null $creditCardNumber
 * @property string|null $numOfCreditPayments
 * @property string|null $creditSum
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCardValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCashSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCreditCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereCreditSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereNumOfCreditPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereTransferReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereTransferSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereUpdatedAt($value)
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\billsPay whereMessage($value)
 * @property string $idUser
 * @property string|null $code
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay whereIdUser($value)
 * @property float|null $docTotal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay whereDocTotal($value)
 * @property string|null $coin
 * @property float|null $quotation
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay whereQuotation($value)
 * @property string $cardName
 * @property string|null $identification
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsPay\BillsPay whereStatus($value)
 */
class BillsPay extends Model
{
    const STATUS_OPEM = 1;
    const STATUS_CLOSE = 0;
    const STATUS_CANCELED = 2;

    public function saveInDB(Request $request)
    {
        try {
            $this->code = $this->createCode();
            $this->idUser = auth()->user()->id;
            $this->cardCode = $request->codPN;
            $this->cardName = $request->cardName;
            $this->identification = $request->identification;
            $this->coin = $request->coin;
            $this->quotation = clearNumberDouble($request->cotacao);
            $this->docTotal = clearNumberDouble($request->docTotal);
            $this->docDate = $request->docDate;
            $this->docDueDate = $request->docDueDate;
            $this->taxDate = $request->taxDate;
            $this->comments = $request->comments;
            $this->status = self::STATUS_CLOSE;
            if ($this->save()) {
                if (isset($request->accounts)) {
                    foreach ($request->accounts as $key => $value) {
                        if (isset($value['check']) && ($value['check'] == 'on')) {
                            $invoice = new Invoice();
                            $invoice->saveInDB($value, $this->id);
                        }
                    }
                }
                if (isset($request->payment)) {
                    $pay = new Payment();
                    $pay->saveInDB($request->payment, $this->id);
                }
            }
        } catch (\Exception $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('E903A', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }

    }

    private function dateDB($data)
    {
        $aux = explode('/', $data);
        return $aux[2] . '-' . $aux[1] . '-' . $aux[0];
    }

    private function createCode()
    {
        $busca = DB::select("select top 1 bills_pays.code from bills_pays order by bills_pays.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'BP00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function getSumAllOpen()
    {
        try{
            $sap = new Company(false);
            $date_fist = subtrairData(DATE('Y-m-d'), 30);
            $date_last = DATE('Y-m-d');

            $total = 0;

            $busca = $sap->getDb()->table('U_R2W_BILLS_PAY')
                ->where('DTEMISSAO', '>=', $date_fist)
                ->where('DTEMISSAO', '<=', $date_last)->get();

            foreach ($busca as $key => $value) {
                $total += $value->VALORPAGO;
            }
            return $total;
        }catch (\Exception $e){
            $logsError = new LogsError();
            $logsError->saveInDB('EEE03F', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return '';
        }

    }

    public function getAccounts($cardCode)
    {
        $sap = new Company(false);
        return $sap->query("SELECT DISTINCT
                      'NE' AS [TIPO],T0.DocNum AS [TRS], T0.DocDate AS [EMISSAO], T1.DueDate AS [VENCTO],
                      T0.CardName AS [FORNECEDOR],T0.Serial AS [NNF],
                      (CAST(T1.InstlmntID AS CHAR(2)) + 'de '+ CAST(T0.Installmnt AS CHAR(2))) AS [NPARC],
                      case when t0.DocCur = 'R$' then (T1.InsTotal - T1.PaidToDate)  else (T1.InsTotalFC - (T1.PaidToDate/(SELECT DISTINCT W.RATE FROM ORTT W WHERE W.RATEDATE = T0.DOCDATE and W.Currency = 'EUR'))) end AS [VALOR],
                      T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY AS [RI], T1.InstlmntID as [NP]
                      FROM OPCH T0
                      JOIN PCH6 T1 ON T0.DocEntry = T1.DocEntry
                      JOIN OCRD T4 ON T0.CardCode = T4.CardCode
                      join pch1 t2 on T0.DocEntry = T2.DocEntry
                      WHERE T1.STATUS =  'O' and T4.CardCode =  '{$cardCode}'
                      group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC ,
                      T0.Comments, t0.CardCode, T0.DOCENTRy
                      UNION
                      SELECT DISTINCT 'AT' AS [TIPO],T0.DocNum AS [TRS], T0.DocDate AS [EMISSAO], T1.DueDate AS [VENCTO],
                      T0.CardName AS [FORNECEDOR],T0.Serial AS [NNF],
                      (CAST(T1.InstlmntID AS CHAR(2)) +  'de ' + CAST(T0.Installmnt AS CHAR(2))) AS [NPARC],
                      (T1.InsTotal - T1.PaidToDate) AS [VALOR],
                      T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY, T1.InstlmntID
                      FROM ODPO T0
                      JOIN DPO6 T1 ON T0.DocEntry = T1.DocEntry
                      JOIN OCRD T4 ON T0.CardCode = T4.CardCode
                      join dpo1 t2 on T0.DocEntry = T2.DocEntry
                      WHERE T1.STATUS =  'O' and T4.CardCode =  '{$cardCode}'
                      group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC ,
                      T0.Comments, t0.CardCode, T0.DOCENTRy
                      UNION
                      SELECT DISTINCT'LC' AS [TIPO], T0.TransId AS [TRS], T0.RefDate AS [EMISSAO], T0.DueDate AS VENCTO,
                      T4.CardName AS [FORNECEDOR],T0.TransId AS [NNF],'' AS [NPARC],
                      CASE WHEN T1.BalDueCred = 0 THEN T1.BALDUEDEB*-1 ELSE T1.BalDueCred END  AS [VALOR],
                      T0.Memo AS [OBSERVACOES], t4.CardCode, T0.TransId, '0' as [NP]
                      FROM OJDT T0
                      JOIN JDT1 T1 ON T0.TransId = T1.TransId
                      JOIN OCRD T4 ON T1.ShortName = T4.CardCode
                      WHERE (T0.TransId NOT IN (SELECT T5.DocTransId FROM VPM2 T5) OR T0.TransId NOT IN (SELECT T6.TransId FROM OVPM T6)) AND T4.CardType = 'S'
                      AND (T1.BalDueCred+T1.BALDUEDEB) <> 0
                      AND T0.TransType = 30 and T4.CardCode = '{$cardCode}'
                      UNION ALL
                      SELECT DISTINCT
                      'DEV-NE' AS [TIPO],T0.DocNum AS [TRS], T0.DocDate AS [EMISSAO], T1.DueDate AS [VENCTO],
                      T0.CardName AS [FORNECEDOR],T0.Serial AS [NNF],
                      (CAST(T1.InstlmntID AS CHAR(2)) + 'de '+ CAST(T0.Installmnt AS CHAR(2))) AS [NPARC],
                      case when t0.DocCur = 'R$' then (T1.InsTotal - T1.PaidToDate)*-1 else (T1.InsTotalFC - (T1.PaidToDate/(SELECT DISTINCT W.RATE FROM ORTT W WHERE W.RATEDATE = T0.DOCDATE and W.Currency = 'EUR')))*-1 end AS [VALOR],
                      T0.Comments AS [OBSERVACOES], t0.CardCode, T0.DOCENTRY AS [RI], T1.InstlmntID as [NP]
                      FROM ORPC T0
                      JOIN RPC6 T1 ON T0.DocEntry = T1.DocEntry
                      JOIN OCRD T4 ON T0.CardCode = T4.CardCode
                      join RPC1 t2 on T0.DocEntry = T2.DocEntry
                      WHERE T1.STATUS =  'O' and T4.CardCode = '{$cardCode}'
                      group by T0.DocNum , T0.DocDate ,T1.DueDate,T0.CardName,T0.Serial ,T1.InstlmntID,T0.Installmnt,T0.DocCur,T1.InsTotal,T1.PaidToDate,T1.InsTotalFC ,
                      T0.Comments, t0.CardCode, T0.DOCENTRy");
    }

    public function saveInSap($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $vPay = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);
            $vPay->CardCode = $obj->cardCode;
            $vPay->DocDate = $obj->docDate;
            $vPay->TaxDate = $obj->taxDate;
            #$vPay->PaymentType = BoORCTPaymentTypeEnum::bopt_None;
            #$vPay->DocObjectCode = BoPaymentsObjectType::bopot_OutgoingPayments;
            $vPay->DocType = BoRcptTypes::rSupplier;
            $check = $this->getPayment($obj->id);

            if (!empty($check)) {
                foreach ($check as $key => $value) {
                    if (trim($value->money) == 'Y') {//dinheiro
                        $vPay->CashAccount = (String)$value->cashAccount;
                        $vPay->CashSum = (Double)$value->cashSum;
                    }
                    if (trim($value->transfer) == 'Y') {//debito
                        $vPay->TransferDate = (String)$value->transferDate;
                        $vPay->TransferAccount = (String)$value->transferAccount;
                        $vPay->TransferSum = (Double)$value->transferSum;
                        $vPay->TransferReference = (String)$value->transferReference;
                    }
                    if (trim($value->credit) == 'Y') {//credito
                        $vPay->CreditCards->CreditCard = (double)$value->creditCard;
                        $vPay->CreditCards->CardValidUntil = $value->cardValidUntil;
                        $vPay->CreditCards->CreditCardNumber = (String)$value->creditCardNumber;
                        $vPay->CreditCards->NumOfCreditPayments = (Int)$value->numOfCreditPayments;
                        $vPay->CreditCards->CreditAcct = (String)$value->creditAcct;
                        $vPay->CreditCards->CreditSum = (Double)$value->creditSum;
                    }
                    if (trim($value->check) == 'Y') {//cheques
                        $vPay->Checks->CheckSum = (double)$value->checkSum;
                        $vPay->Checks->DueDate = $value->dueDate;
                        $vPay->Checks->BankCode = (String)$value->bankCode;
                        $vPay->Checks->Branch = (String)$value->branch;
                        $vPay->Checks->AccounttNum = (String)$value->acctNum;
                        $vPay->Checks->CheckAccount = (String)$value->checkAccount;
                        if ($value->trnsfrable == 'Y') {
                            $vPay->Checks->Trnsfrable = BoYesNoEnum::tYES;
                        } else {
                            $vPay->Checks->Trnsfrable = BoYesNoEnum::tNO;
                        }
                    }
                }
            }

            $validInvoices = $this->getInvoices($obj->id);
            if (!empty($validInvoices)) {
                $aux = 0;
                foreach ($validInvoices as $key => $value) {
                    $vPay->Invoices->SetCurrentLine((int)$aux);
                    $vPay->Invoices->DocEntry = (String)$value->docEntry;

                    if ($value->type == 'NE') {
                        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_PurchaseInvoice;
                        $vPay->Invoices->InstallmentId = (int)$value->installmentId;
                    }
                    if ($value->type == 'AT') {
                        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_PurchaseDownPayment;
                    }
                    if ($value->type == 'LC') {
                        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_PaymentAdvice;
                        $vPay->Invoices->DocLine = (Double)1;
                    }
                    $vPay->Invoices->add();
                    $aux++;
                }
            }

            $vPay->JournalRemarks = 'Baseado no contas a receber WEB ';
            $vPay->Remarks = 'code:' . $obj->code;
            $vPay->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $vPay->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->id);
            if ($vPay->Add() !== 0) {
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = true;
                $obj->save();
                return false;
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = "Item salvo no SAP com sucesso.";
                $obj->is_locked = false;
                $obj->save();
                return true;
            }
        } catch (\Exception  $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('E903F', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
        }
    }

    public function cancelInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $opor = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);
            if (empty($obj->codSAP) || is_null($obj->codSAP)) {
                $obj->is_locked = false;
                $obj->status = self::STATUS_CANCELED;
                $obj->save();
            } else {
                if ($opor->GetByKey((string)$obj->codSAP)) {
                    if ($opor->Cancel === 0) {
                        $obj->is_locked = false;
                        $obj->status = self::STATUS_CANCELED;
                        $obj->save();
                    } else {
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

    private function clearRequest(Request $request)
    {
        $aux = $request->idParcela;
        if (isset($request->removeID) && isset($request->idParcela)) {
            foreach ($request->removeID as $key => $value) {
                unset($aux[$key]);
            }
        }
        return $aux;
    }

    private function getNameUser($id)
    {
        return DB::SELECT("SELECT T0.name FROM Users T0 JOIN bills_pays T1 on T1.idUser =  T0.id WHERE T1.id = '{$id}'")[0]->name;
    }

    private function getPayment($id)
    {
        $busca = DB::SELECT("SELECT T0.idBillsPay,T0.[money],T0.cashAccount,T0.cashSum,T0.credit,
	   T0.creditCard,T0.cardValidUntil,T0.creditCardNumber,T0.numOfCreditPayments,
	   T0.creditAcct,T0.creditSum,T0.[transfer],T0.transferDate,T0.transferAccount,
	   T0.transferSum,T0.transferReference,T0.other,T0.otherAccount,T0.otherSum,
	   T0.[check],T0.[checkSum],T0.dueDate,T0.countryCode,T0.bankCode,T0.branch,
	   T0.acctNum,T0.checkAccount,T0.trnsfrable,T0.docTotal
     FROM bills_pay_payments T0 join bills_pays T1 on T0.idBillsPay = T1.id WHERE T1.id = '{$id}'");
        if (empty($busca)) {
            return false;
        } else {
            return $busca;
        }
    }

    private function getInvoices($id)
    {
        return DB::SELECT("SELECT T0.[type],T0.[docEntry],T0.[docNum],T0.[docDate],T0.[dueDate],T0.[serial],T0.[installmentId],T0.[parcel],T0.[lineSum] FROM bills_pay_invoices as T0 JOIN bills_pays T1 on T0.idBillsPay = T1.id WHERE T1.id = '{$id}'");
    }
}
