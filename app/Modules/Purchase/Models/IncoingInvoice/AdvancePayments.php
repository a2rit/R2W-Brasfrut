<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;


class AdvancePayments extends Model
{
    protected $table = 'incoing_invoice_advance_payments';
    public $timestamps = false;

    public function saveInDB($obj){


        $sap = new Company(false);
        $adPayment = $sap->query("SELECT T0.[Comments], T0.[DocDate], T0.[DocTotal], T0.[DpmAppl] FROM ODPO T0 WHERE T0.[DocNum] = ".$obj['codSAP']);
        
        if(array_key_exists(0, $adPayment)){
            $this->idIncoingInvoice = $obj['idIncoingInvoice'];
            $this->codSAP = $obj['codSAP'];
            $this->Comments = $adPayment[0]['Comments'];
            $this->DocDate = $adPayment[0]['DocDate'];
            $this->DpmAppl = $adPayment[0]['DpmAppl'];
            $this->DocTotal = $adPayment[0]['DocTotal'];
            $this->save();
        }
    }
}
