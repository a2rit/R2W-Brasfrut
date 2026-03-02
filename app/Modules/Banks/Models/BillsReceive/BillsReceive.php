<?php

namespace App\Modules\Banks\Models\BillsReceive;

use App\logsError;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Banks\Models\BillsReceive\Payment;
use App\Modules\Banks\Models\BillsReceive\Invoice;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoYesNoEnum;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoORCTPaymentTypeEnum;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use Litiano\Sap\Enum\BoRcptInvTypes;
use App\User;
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive whereIdUser($value)
 * @property string|null $coin
 * @property float|null $quotation
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive whereQuotation($value)
 * @property string $cardName
 * @property float $docTotal
 * @property string|null $identification
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Banks\Models\BillsReceive\BillsReceive whereStatus($value)
 */
class BillsReceive extends Model
{
    const STATUS_OPEM = 1;
    const STATUS_CLOSE = 0;
    const STATUS_CANCELED = 2;

    public function saveInDB(Request $request)
    {
        try {
            $this->code = $this->createCode();
            $this->idUser = auth()->user()->id;
            $this->cardCode = $request->cardCode;
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

    private function createCode()
    {
        $busca = DB::select("select top 1 bills_receives.code from bills_receives order by bills_receives.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'BR00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
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
        return User::find($id, ['name'])->name;
    }

    private function getPayment($id)
    {
        $busca = DB::SELECT("SELECT T0.idBillsReceive,T0.[money],T0.cashAccount,T0.cashSum,T0.credit,
	   T0.creditCard,T0.cardValidUntil,T0.creditCardNumber,T0.numOfCreditPayments,
	   T0.creditAcct,T0.creditSum,T0.[transfer],T0.transferDate,T0.transferAccount,
	   T0.transferSum,T0.transferReference,T0.other,T0.otherAccount,T0.otherSum,
	   T0.[check],T0.[checkSum],T0.dueDate,T0.countryCode,T0.bankCode,T0.branch,
	   T0.acctNum,T0.checkAccount,T0.trnsfrable,T0.docTotal
     FROM bills_receive_payments T0 join bills_receives T1 on T0.idBillsReceive = T1.id WHERE T1.id = '{$id}'");
        if (empty($busca)) {
            return false;
        } else {
            return $busca;
        }
    }

    private function getInvoices($id)
    {
        return DB::SELECT("SELECT T0.[type],T0.[docEntry],T0.[docNum],T0.[docDate],T0.[dueDate],T0.[serial],T0.[installmentId],T0.[parcel],T0.[lineSum] FROM bills_receive_invoices as T0 JOIN bills_receives T1 on T0.idBillsReceive = T1.id WHERE T1.id = '{$id}'");
    }

    public function saveInSap($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $vPay = $sap->GetBusinessObject(BoObjectTypes::oIncomingPayments);
            $vPay->CardCode = $obj->cardCode;
            $vPay->DocDate = $obj->docDate;
            $vPay->TaxDate = $obj->taxDate;
            $vPay->PaymentType = BoORCTPaymentTypeEnum::bopt_None;
            $vPay->DocObjectCode = BoPaymentsObjectType::bopot_IncomingPayments;

            #$vPay->DocCurrency = $obj->coin;
            #$vPay->DocRate = $obj->docTotal;

            $check = $this->getPayment($obj->id);

            #$vPay->DocRate = (double) '0';
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
                    $vPay->Invoices->DocEntry = $value->docEntry;
                    if (!is_null($value->installmentId) && $value->installmentId != 0) {
                        $vPay->Invoices->InstallmentId = (int)$value->installmentId;
                    }
                    if ($value->type == 'NS') {
                        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_Invoice;
                    }
                    if ($value->type == 'LC') {
                        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_JournalEntry;
                    }


                    $vPay->Invoices->add();
                    $aux++;
                }
            }

            $vPay->JournalRemarks = 'Baseado no contas a receber WEB ';
            $vPay->Remarks = 'code:' . $obj->code;
            $vPay->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $vPay->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);
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
            $logsError->saveInDB('FE903F', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
        }
    }

    public function cancelInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $opor = $sap->GetBusinessObject(BoObjectTypes::oIncomingPayments);
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

    public function user()
    {
        return User::find($this->idUser);
    }

    public function getCNPJ_CPF()
    {
        try {
            $sap = new Company(false);
            $busca = $sap->getDb()->table('OCRD')->join('CRD7', 'OCRD.CardCode', '=', 'CRD7.CardCode')
                ->where('OCRD.CardCode', $this->cardCode)
                ->select('CRD7.TaxId0', 'CRD7.TaxId4')->first();
            if (!empty($busca->TaxId4) && !is_null($busca->TaxId4)) {
                return $busca->TaxId4;
            } else {
                $busca->TaxId0;
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E903B', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return '';
        }

    }

    public function getSumAllOpen()
    {
        $sap = new Company(false);
        $date_fist = subtrairData(DATE('Y-m-d'), 30);
        $date_last = DATE('Y-m-d');

        $total = 0;

        $busca = $sap->getDb()->table('U_R2W_BILLS_RECEIVE')
            ->where('DTEMISSAO', '>=', $date_fist)
            ->where('DTEMISSAO', '<=', $date_last)->get();

        foreach ($busca as $key => $value) {
            $total += $value->VALORRECEBIDO;
        }
        return $total;

    }

    public function getAccounts($cardCode)
    {
        $sap = new Company(false);
        return $sap->query("SELECT DISTINCT
          'NS' AS [TIPO], F.DOCNUM AS [TRS], 'ABERTO' AS [STATUS], F.DOCDATE AS [EMISSAO], E.DUEDATE AS [VENCTO], B.CARDNAME AS [CLIENTE], F.Serial AS [NNF],
          (CAST(E.INSTLMNTID AS CHAR(2)) + 'de ' + CAST(F.INSTALLMNT AS CHAR(2))) AS [NPARC], (E.INSTOTAL - E.PAID) AS [VALOR], F.COMMENTS AS 'OBSERVACOES',
          C.SLPNAME AS 'VENDEDOR', b.cardcode AS cod, H.GROUPNAME, h.groupcode, F.BPLId, f.BPLName, F.DOCENTRY AS [RI], E.INSTLMNTID as [NP]
          FROM OINV F INNER JOIN
          INV6 E ON F.DOCENTRY = E.DOCENTRY INNER JOIN
          OCRD B ON F.CARDCODE = B.CARDCODE INNER JOIN
          OSLP C ON F.SLPCODE = C.SLPCODE INNER JOIN
          OCRG H ON H.GROUPCODE = B.GROUPCODE
          WHERE E.STATUS = 'O' AND b.cardcode = '{$cardCode}' AND F.DOCENTRY NOT IN
          (SELECT T4.DOCENTRY
          FROM OINV T4 INNER JOIN
          INV1 T5 ON T4.DOCENTRY = T5.DOCENTRY INNER JOIN
          RIN1 T6 ON T6.BASEENTRY = T5.DOCENTRY AND T6.BASECARD = T4.CARDCODE INNER JOIN
          ORIN T7 ON T7.DOCENTRY = T6.DOCENTRY)
          UNION ALL
          SELECT DISTINCT
          CASE WHEN T0.TransType = 30 THEN 'LC' ELSE 'SI' END AS [TIPO], T0.TRANSID AS [TRS], 'ABERTO' AS [STATUS], T0.REFDATE AS [EMISSAO],
          T1.DUEDATE AS [VENCTO], T4.CARDNAME AS [CLIENTE], T0.TRANSID AS [NDOC], '' AS [NPARC],
          CASE WHEN T1.BALDUECRED = 0 THEN T1.BALDUEDEB ELSE T1.BALDUECRED * - 1 END AS [VALOR], T0.MEMO AS [OBSERVACOES], C.SLPNAME AS 'VENDEDOR',
          t4.cardcode AS cod, H.GROUPNAME, h.groupcode, T1.BPLId, t1.BPLName, T0.TRANSID, '0' as [NP]
          FROM OJDT T0 INNER JOIN
          JDT1 T1 ON T0.TRANSID = T1.TRANSID INNER JOIN
          OCRD T4 ON (T1.SHORTNAME = T4.CARDCODE) INNER JOIN
          OSLP C ON C.SLPCODE = T4.SLPCODE INNER JOIN
          OCRG H ON H.GROUPCODE = T4.GROUPCODE
          WHERE  T4.CARDTYPE = 'C' AND t4.cardcode = '{$cardCode}' and
          (T0.TRANSTYPE = 30 OR
          T0.TRANSTYPE = - 2 OR
          T0.TRANSTYPE = 24) AND (T1.MTHDATE IS NULL OR
          BALDUEDEB <> 0 OR
          T1.BALDUECRED <> 0) order by E.DUEDATE asc");
    }
}
