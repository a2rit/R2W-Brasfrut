<?php

namespace App\Models\SalesOrder;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = "sales_order_items";

    
    public function saveInDBFromNfceError($item){

        $this->idSalesOrder = $item->idSalesOrder;
        $this->idNfcItem = $item->id;
        $this->itemCode = $item->codigo_sap;
        $this->itemName = $item->nome;
        $this->quantity = (Double)$item->quantidade;
        $this->price = (Double)$item->valor_unitario;
        $this->discount = (Double)$item->desconto;
        $this->lineSum = (Double)$item->total;
        $this->anotherValues = (Double)$item->outros_valores;
        $this->usage = $item->usage;
        $this->taxCode = $item->taxCode;
        $this->cfop = $item->cfop;
        $this->warehouseCode = $item->deposito;
        $this->costCenter = $item->costCenter;
        $this->costCenter2 = $item->costCenter2;
        $this->cst_icms = (Double)$item->cst_icms;
        $this->codProject = $item->codProject;
        $this->save();
    }
}
