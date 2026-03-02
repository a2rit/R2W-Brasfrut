<?php

namespace App\Modules\Purchase\Models\PurchaseOrder;

use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseRequest\Item as pItem;
use Illuminate\Database\Eloquent\Model;
use App\logsError;
use Litiano\Sap\Company;

/**
 * App\PurchaseOrderItems
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idPurchaseOrders
 * @property string $codSAP
 * @property string $price
 * @property string $quantity
 * @property string $codUse
 * @property string $codProject
 * @property string $codCost
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereCodCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereCodProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereCodUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereIdPurchaseOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereUpdatedAt($value)
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrderItems whereStatus($value)
 * @property string $itemCode
 * @property float $lineSum
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item whereLineSum($value)
 * @property string|null $itemName
 * @property string|null $taxCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\Item whereTaxCode($value)
 */
class Item extends Model
{
  protected $table = 'purchase_order_items';

  public function purchaseRequest()
  {
    return $this->hasOne(PurchaseRequest::class, 'id', 'idPurchaseRequest');
  }


  public function saveInDB($value, $id)
  {
    try {

      $this->idPurchaseOrders = $id;
      $this->idPurchaseRequest = isset($value['idPurchaseRequest']) ? $value['idPurchaseRequest'] : null;
      $this->idItemPurchaseRequest = isset($value['idItemPurchaseRequest']) ? $value['idItemPurchaseRequest'] : null;
      $this->itemCode = $value['codSAP'];
      $this->itemName = $value['itemName'];
      $this->itemUnd = $value['itemUnd'];
      $this->quantity = (float)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
      $this->price = (float)(is_numeric($value['preco']) ? $value['preco'] : clearNumberDouble($value['preco']));
      $this->codProject = $value['projeto'];
      $this->warehouseCode = $value['whsCode'];
      $this->accounting_account = $value["accounting_account"] ?? null;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = $value['costCenter'];
      $this->costCenter = $value['costCenter'];
      $this->costCenter2 = $value['costCenter2'];
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->contract = $value['contract'] ?? NULL;
      $this->status = 1;
      $this->synced = 'N';
      $this->save();
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBSuggestion($value, $id)
  {
    try {
      $this->idPurchaseOrders = $id;
      $this->itemCode = $value['codSAP'];
      $this->itemName = $value['itemName'];
      $this->itemUnd = $value['itemUnd'];
      $this->quantity = (float)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
      $this->price = (float)clearNumberDouble($value['price']);
      $this->codProject = $value['projeto'];
      $this->warehouseCode = $value['wareHouseCode'];
      $this->accounting_account = $value["accounting_account"] ?? null;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = $value['centroCusto'];
      $this->costCenter = $value['centroCusto'];
      $this->costCenter2 = $value['centroCusto2'];
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->contract = $value['contract'] ?? NULL;
      $this->status = 1;
      $this->synced = 'N';
      $this->save();
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBFromRequest($value, $id)
  {
    try {
      $price = getItemAvgPrice($value->codSAP);
      $this->idPurchaseOrders = $id;
      $this->idPurchaseRequest = $value->idPurchaseRequest;
      $this->idItemPurchaseRequest = isset($value->idItemPurchaseRequest) ? $value->idItemPurchaseRequest : null;
      $this->itemCode = $value->codSAP;
      $this->itemName = $value->itemName;
      $this->itemUnd = $value->itemUnd;
      $this->quantity = (float)(is_numeric($value->quantityPendente) ? $value->quantityPendente : clearNumberDouble($value->quantityPendente));
      $this->price = (float)(is_numeric($price) ? $price : clearNumberDouble($price));
      $this->codProject = $value->projeto;
      $this->warehouseCode = $value->wareHouseCode;
      $this->accounting_account = $value->accounting_account;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = $value->centroCusto;
      $this->costCenter = $value->centroCusto;
      $this->costCenter2 = $value->centroCusto2;
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->status = 1;
      $this->synced = 'N';
      $this->save();
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBDuplicate($value, $id)
  {
    try {

      $this->idPurchaseOrders = $id;
      //$this->idPurchaseRequest = isset($value['idPurchaseRequest']) ? $value['idPurchaseRequest'] : null;
      //$this->idItemPurchaseRequest = isset($value['idItemPurchaseRequest']) ? $value['idItemPurchaseRequest'] : null;
      $this->itemCode = $value->itemCode;
      $this->itemName = $value['itemName'];
      $this->itemUnd = $value['itemUnd'];
      $this->quantity = (float)$value['quantity'];
      $this->price = (float)$value['price'];
      $this->codProject = $value['codProject'];
      $this->warehouseCode = $value['warehouseCode'];
      $this->accounting_account = $value["accounting_account"] ?? null;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = $value['codCost'];
      $this->costCenter = $value['costCenter'];
      $this->costCenter2 = $value['costCenter2'];
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->contract = $value['contract'] ?? NULL;
      $this->status = 1;
      $this->synced = 'N';
      $this->save();
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function updateInDB($value, $head)
  {
    try {

      if ($this->idItemPurchaseRequest) {
        $pItem = pItem::find($this->idItemPurchaseRequest);
        $quantity = $pItem->quantity - clearNumberDouble($value['qtd']);
        $pItem->quantityPendente = $quantity > 0 ? $quantity : 0;
        $pItem->save();
      }

      $this->quantity = (float)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
      $this->price = (float)(is_numeric($value['preco']) ? $value['preco'] : clearNumberDouble($value['preco']));
      $this->codProject = $value['projeto'];
      $this->warehouseCode = $value['whsCode'];
      $this->accounting_account = $value["accounting_account"] ?? null;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = $value['costCenter'];
      $this->costCenter = $value['costCenter'];
      $this->costCenter2 = $value['costCenter2'];
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->contract = $value['contract'] ?? NULL;
      $this->status = isset($value['deleted']) ? 3 : 1;
      $this->synced = 'N';
      if ($this->save()) {
        if ($this->status == 3) {
          $head->docTotal -= $this->lineSum;
          $head->save();
        }
      }
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XR', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBFromQuotationF($value, $id)
  {
    try {
      $this->idPurchaseOrders = $id;
      $this->itemCode = $value->codSAP;
      $this->itemName = $value->itemName;
      // $this->itemUnd = $value->itemUnd;
      $this->quantity =  clearNumberDouble(str_replace('.', ',', $value->qtd));
      $this->price = clearNumberDouble(str_replace('.', ',', $value->price));
      $this->codProject = $value->projeto;
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = '1.0';
      $this->costCenter = $value->centroCusto;
      $this->costCenter2 = $value->centroCusto2;
      $this->lineSum = ((float)$value->price * (float)$value->qtd);
      $this->idPurchaseRequest = $value->idPurchaseRequest;
      $this->idItemPurchaseRequest = isset($value->idItemPurchaseRequest) ? $value->idItemPurchaseRequest : null;
      $this->status = 1;
      $this->synced = 'N';
      $this->save();
      //dd($this);


    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }
  public function saveInDBFromQuotationI($value, $id)
  {
    try {
      $this->idPurchaseOrders = $id;
      $this->itemCode = $value['codSAP'];
      $this->itemName = $value['itemName'];
      // $this->itemUnd = $value['itemUnd'];
      $this->quantity =  $value['qtd'];
      $this->price = $value['price'];
      $this->codProject = $value['projeto'];
      $this->codUse = '';
      $this->taxCode = '';
      $this->codCost = '';
      $this->warehouseCode = $value['warehouseCode'];
      $this->costCenter = $value['centroCusto'];
      $this->costCenter2 = $value['centroCusto2'];
      $this->lineSum = ($this->quantity) * ($this->price);
      $this->status = 1;
      $this->idPurchaseRequest = $value['idPurchaseRequest'];
      $this->idItemPurchaseRequest = $value['idItemPurchaseRequest'] ?? null;
      $this->idItemPurchaseQuotation = $value['idItemPurchaseQuotation'] ?? null;
      $this->synced = 'N';
      $this->save();
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('Eas29XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public static function getAccountingGroupAccount(String $itemCode, String $whsCode)
  {
    $sap = new Company(false);
    return $sap->getDb()->table("OITB")->select("OITB.ExpensesAc")
      ->join("OITM", "OITM.ItmsGrpCod", "=", "OITB.ItmsGrpCod")
      ->where("OITM.ItemCode", "=", $itemCode)
      ->first()->ExpensesAc ?? null;
  }
}
