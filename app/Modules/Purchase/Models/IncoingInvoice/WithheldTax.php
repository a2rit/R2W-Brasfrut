<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Litiano\Sap\Company;


class WithheldTax extends Model
{
    public $timestamps = false;
    protected $table = 'incoing_invoice_withheldtaxes';
    
    public function saveInDB($request, $item_id){

      $sap = new Company(false);
        
      $tax_data = $sap->query("SELECT T0.[WTCode], T0.[WTName], T0.[Rate], T0.[Category]
          FROM OWHT T0
          WHERE T0.[WTCode] = '". $request['WTCode']."'");

      $this->itemId = $item_id;
      $this->WTCode = $request['WTCode'];
      $this->WTName = $tax_data[0]['WTName'];
      $this->Rate = $request['Rate'];
      $this->Category = $tax_data[0]['Category'];
      $this->Value = $request['Value'];
      $this->save();
   }
}
