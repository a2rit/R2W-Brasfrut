<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\logsError;
use App\Models\NFCe;
use App\Models\Erro;
use App\Models\PontoVenda;
use App\Models\SalesOrder\Item;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoYesNoEnum;
use Litiano\Sap\Enum\BoObjectTypes;

class SalesOrder extends Model
{
    
    protected $table = 'sales_orders';

    const STATUS_OPEN = 1;
    const STATUS_CANCEL = 2;
    const STATUS_CLOSE = 0;

    public function items()
    {
        return $this->hasMany(Item::class, 'idSalesOrder', 'id');
    }

    public function saveInDBFromNfceError($model_id){
        try {
          DB::beginTransaction();
            $nfce = NFCe::find($model_id);
            $pv = PontoVenda::find($nfce->pv_id);
            
            $this->erro_id = $nfce->erro_id;
            $this->code = $this->createCode();
            $this->cardCode = $pv->cliente;
            $this->docDate = $nfce->data_emissao;
            $this->docDueDate = $nfce->data_emissao;
            $this->taxDate = $nfce->data_emissao;
            $this->slpCode = $pv->vendedor;
            $this->comments = $nfce->info_adicional;
            $this->project = $pv->projeto;
            $this->jrnlMemo = $nfce->info_adicional;
            $this->status = self::STATUS_OPEN;
            $this->docTotal = (Double)$nfce->total;
            $this->discount = (Double)$nfce->desconto;
            $this->paymentCondition = 27; // À vista
            $this->nfc_id = $nfce->id;
            $this->chave = $nfce->chave;
           
            if($this->save()){
                foreach($nfce->itens()->get() as $key => $value){
                    $value->idSalesOrder = $this->id;
                    $value->usage = $pv->utilizacao;
                    $value->taxCode = $pv->taxCode;
                    $value->costCenter = $pv->regra_distribuicao;
                    $value->codProject = $pv->projeto;
                    
                    $item = new Item;
                    $item->saveInDBFromNfceError($value);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
          DB::rollback();
          $logsError = new logsError();
          $logsError->saveInDB('SO880', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function saveInSAP(){
      try{
        DB::beginTransaction();
          $sap = NewCompany::getInstance()->getCompany();
          $sale_order = $sap->GetBusinessObject(BoObjectTypes::oOrders);
          $sale_order->DocDate = $this->docDate;
          $sale_order->DocDueDate = $this->docDueDate;
          $sale_order->TaxDate = $this->taxDate;
          $sale_order->CardCode = $this->cardCode;
          $sale_order->PaymentGroupCode = $this->paymentCondition;
          $sale_order->Comments = "Pedido de venda WEB: {$this->code} - {$this->comments}";
          $sale_order->SalesPersonCode = $this->slpCode ?? -1;
          $sale_order->UserFields->fields->Item("U_R2W_CODE")->value = $this->code;
          $sale_order->UserFields->Fields->Item("U_chaveacesso")->Value = $this->chave;

          
          // if(!empty($this->discountPercent) && (Double)$this->discountPercent > 0){
          //     $sale_order->DocTotal = (Double)number_format(((Double)$items->sum('lineSum') - $this->discountPercent), 2, '.', '');
          // }
          
          $items = $this->items()->get();
          if(!empty($items)){
            foreach ($items as $line => $value) {

              $sale_order->Lines->SetCurrentLine($line);

              $sale_order->Lines->ItemCode = (String) $value->itemCode;
              $sale_order->Lines->Quantity = (Double) $value->quantity;
              $sale_order->Lines->UnitPrice = (Double) $value->price;
              $sale_order->Lines->ProjectCode = (String) $value->codProject;
              $sale_order->Lines->CostingCode = (String) $value->costCenter;
              $sale_order->Lines->CostingCode2 = (String) $value->costCenter2;
              $sale_order->Lines->Usage = $value->usage;
              $sale_order->Lines->WarehouseCode = (String)$value->warehouseCode;

              if($value->discount > 0){
                  $sale_order->Lines->DiscountPercent = (double)(($value->discount / $value->price) * 100) / $value->quantity;
              }

              $value->lineNum = $sale_order->Lines->LineNum;
              $value->save();
              
              $sale_order->Lines->Add();
            }
          }
          
          $ret = $sale_order->Add();
          
          if ($ret !== 0) {
            $logsErro = new logsError();
            $logsErro->saveInDB('SO0098', 'Error SAP',$sap->GetLastErrorDescription());
            $this->message = $sap->GetLastErrorDescription();
            $this->save();
          }else{
            $this->codSAP = $sap->GetNewObjectKey();
            $this->message = 'Item salvo com sucesso';
            $this->save();
            if(!empty($this->erro_id)){
              $erro = Erro::find($this->erro_id);
              $erro->pedido_venda = $this->codSAP;
              $erro->save();
            }
          }
        DB::commit();
      }catch (\Throwable $e) {
        DB::rollback();
        $logsErro = new logsError();
        $logsErro->saveInDB('SO0099', $e->getFile().' | '.$e->getLine(),"ID: {$this->id} -> ".$e->getMessage());
        $this->message = $e->getMessage();
        $this->is_locked = false;
        $this->save();
      }
    }

    public function createCode(){
      $busca = $this::orderBy('id', 'desc')->first();

      if (empty($busca)) {
            $codigo = 'SO000001';
      } else {
            $codigo = $busca->code;
            $codigo++;
      }

      return $codigo;
  }
}
