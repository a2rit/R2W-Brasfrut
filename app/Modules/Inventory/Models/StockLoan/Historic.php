<?php

namespace App\Modules\Inventory\Models\StockLoan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Models\StockLoan\Item;
use App\Modules\Inventory\Models\StockLoan\StockLoan;
use App\LogsError;
use App\User;
use Litiano\Sap\NewCompany;

use Litiano\Sap\Enum\BoObjectTypes;


class Historic Extends Model
{

    protected $table = 'stock_loans_historics';


    const ACTION_TEXT = [
        '0' => 'RECEBIDO',
        '1' => 'DEVOLVIDO'
    ];


    public function saveInDB($value, $request){
        $item = Item::find($value['idItem']);
        
        $this->idStockLoan = $request->id;
        $this->deliveryUserId = $request->deliveryUser;
        $this->receiverUserId = $request->receiverUser;
        $this->idItem = $value['idItem'];
        $this->status = 0;

        if($request->transType == '1'){
            $this->quantityServed = clearNumberDouble($value['quantityPending']) - $item->quantityPending;
            $this->isReceipt = 1;
            $this->isDevolution = 0;
        }elseif($request->transType == '2'){
            $this->quantityServed = clearNumberDouble($value['quantityDevolved']) - $item->quantityDevolved;
            $this->isReceipt = 0;
            $this->isDevolution = 1;
        }
        $this->save();
    }
}
