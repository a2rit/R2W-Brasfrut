<?php

namespace App\Modules\Purchase\Models\AdvanceProvider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\logsError;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoORCTPaymentTypeEnum;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use Litiano\Sap\Enum\BoRcptInvTypes;
use Litiano\Sap\NewCompany;

class Payments extends Model
{
  protected $table = 'advance_provider_payments';

  const STATUS_OPEN = 1;
  const STATUS_CANCEL = 2;

  public function advanced_provider(): HasOne
  {
    return $this->hasOne(AdvanceProvider::class, 'id', 'idAdvanceProvider');
  }


  public function saveInDB($request, $id)
  {

    try {
      $docTotal = 0;
      $request = (object) $request;
      if ($this->checkPayments($request, '1') || $this->checkPayments($request, '2')) {
        $this->idAdvanceProvider = $id;
        if ($this->checkPayments($request, '1')) { //dinheiro
          $this->money = 'Y';
          $this->cashAccount = (string) $request->conta_dinheiro;
          $this->cashSum = (float)(is_numeric($request->total_dinheiro) ? $request->total_dinheiro : clearNumberDouble($request->total_dinheiro));
          $docTotal += $this->cashSum;
        }
        if ($this->checkPayments($request, '2')) { //transfer
          $this->transfer = 'Y';
          $this->transferDate = (string) $request->dt_transferencia;
          $this->transferAccount = (string) $request->conta_transferencia;
          $this->transferSum = (float)(is_numeric($request->total_transferencia) ? $request->total_transferencia : clearNumberDouble($request->total_transferencia));
          $this->transferReference = (string) $request->referencia_transferencia;
          $docTotal += $this->transferSum;
        }
        $this->status = self::STATUS_OPEN;
        $this->save();
      }
    } catch (\Throwable $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('APE0007', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInSAP()
  {
    try {
      $advance_provider = $this->advanced_provider;
      $sap = NewCompany::getInstance()->getCompany();
      $downPay = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDownPayments);

      if ($downPay->GetByKey((int) $advance_provider->codSAP)) {
        $vPay = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);
        $vPay->CardCode =  $downPay->CardCode;
        $vPay->DocDate = $downPay->DocDate;
        $vPay->PaymentType = BoORCTPaymentTypeEnum::bopt_None;
        $vPay->DocObjectCode = BoPaymentsObjectType::bopot_OutgoingPayments;

        if ($this->money === 'Y') { //dinheiro
          $vPay->CashAccount = (string) $this->cashAccount;
          $vPay->CashSum = (float) $this->cashSum;
        }

        if ($this->transfer == 'Y') { //Transfer
          $vPay->TransferDate = (string) $this->transferDate;
          $vPay->TransferAccount = (string) $this->transferAccount;
          $vPay->TransferSum = (float) $this->transferSum;
          $vPay->TransferReference = (string) $this->transferReference;
        }

        $vPay->JournalRemarks = 'Baseado no Adiantamento ' . $downPay->DocNum;
        $vPay->Remarks = $downPay->CardCode;
        $vPay->Invoices->DocEntry = (int) $downPay->DocEntry;
        $vPay->Invoices->InvoiceType = BoRcptInvTypes::it_PurchaseDownPayment;
        $vPay->Invoices->SumApplied = (float) $this->cashSum + $this->transferSum;
        $vPay->Invoices->DocLine = 0;
        $vPay->Invoices->Add();
        if ($vPay->Add() !== 0) {
          $logsErro = new logsError();
          $logsErro->saveInDB('APE0008', 'DownPaymentAP', $sap->GetLastErrorDescription());
        } else {
          $this->codSAP = $sap->GetNewObjectKey();
          $this->save();
          $advance_provider->status = $advance_provider::STATUS_CLOSE;
          $advance_provider->save();
        }
        return;
      }
    } catch (\Exception $e) {
      $logsErro = new logsError();
      $logsErro->saveInDB('E0089', 'Não conseguimos salvar o pedido de compras na base do SAP:DownPaymentAP', "ID: {$obj->id} -> " . $e->getMessage());
    }
  }

  public function refund()
  {
    $sap = NewCompany::getInstance()->getCompany();
    $payment = $sap->GetBusinessObject(BoObjectTypes::oVendorPayments);

    if ($payment->GetByKey((int) $this->codSAP)) {
      $sapToQuery = new Company(false);
      $query = $sapToQuery->getDb()->table('OVPM')->select('Canceled')->where('DocEntry', '=', $this->codSAP)->first();
      if((!empty($query) && $query->Canceled === 'Y') || $payment->Cancel() === 0){
        $advance_provider = $this->advanced_provider;
        $advance_provider->status = $advance_provider::STATUS_REFUND;
        $advance_provider->save();

        $this->status = self::STATUS_CANCEL;
        $this->save();
        return ['status' => 'success', 'message' => "Estornado com sucesso"];
      }else{
        return ['status' => 'error', 'message' => $sap->GetLastErrorDescription()];
      }
    }
  }

  private function checkPayments($request, $value)
  {
    switch ($value) {
      case '1':
        if (
          !empty($request->total_dinheiro) && clearNumberDouble($request->total_dinheiro) > 0
          && !empty($request->conta_dinheiro)
        ) {
          return true;
        } else {
          return false;
        }
        break;
      case '2':
        if (
          !empty($request->dt_transferencia) && !empty($request->conta_transferencia) &&
          clearNumberDouble($request->total_transferencia) > 0
        ) {
          return true;
        } else {
          return false;
        }
      default:
        return false;
        break;
    }
  }
}
