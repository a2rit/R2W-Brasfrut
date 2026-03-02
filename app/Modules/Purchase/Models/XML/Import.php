<?php

namespace App\Modules\Purchase\Models\XML;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\Company;
use  App\Modules\Partners\Models\Partner\Catalog;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;

/**
 * App\Modules\Purchase\Models\XML\Import
 *
 * @property int $id
 * @property string $idUser
 * @property string $chNFe
 * @property string|null $taxDate
 * @property string|null $nNF
 * @property string|null $serie
 * @property string|null $comments
 * @property float $docTotal
 * @property float|null $totalFrete
 * @property float|null $totalDesconto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property string|null $codSAP
 * @property string|null $document
 * @property string|null $docDueDate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereChNFe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereNNF($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereTotalDesconto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereTotalFrete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\XML\Import whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Import extends Model
{
   protected $table = 'xml_imports';
   
   const STATUS_OPEN = 1;
   const STATUS_CLOSE = 0;
   const STATUS_CANCEL = 2;

   public function saveInDB($xml){
     $search = Import::where([
       'chNFe' => $xml->protNFe->infProt->chNFe,
       'status'=> self::STATUS_CLOSE
     ])->get(['chNFe']);
     
     if(count($search) == 0){
         $this->idUser = auth()->user()->id;
         $this->chNFe = $xml->protNFe->infProt->chNFe;
         $this->comments = $xml->NFe->infNFe->infAdic->infCpl;
         $this->docTotal = $xml->NFe->infNFe->total->ICMSTot->vNF;
         $this->totalFrete = $xml->NFe->infNFe->total->ICMSTot->vFrete;
         $this->totalDesconto = $xml->NFe->infNFe->total->ICMSTot->vDesc;
         $this->taxDate = $xml->NFe->infNFe->ide->dhEmi;
         $this->serie = $xml->NFe->infNFe->ide->serie;
         $this->nNF = $xml->NFe->infNFe->ide->nNF;
         $this->status = self::STATUS_OPEN;
         $this->docDueDate = empty($xml->NFe->infNFe->cobr->dup->dVenc) ? NULL : $xml->NFe->infNFe->cobr->dup->dVenc;

         if($this->save()){
           Session::put('idImportXML', $this->id);
           $this->saveItems($xml);
           $partner = new Partner();
           $partner->saveInDB($xml,$this->id);
         }
         return true;
     }else{
       return false;
     }
   }
   public function checkPurcharseOpen($cnpj){
     return PurchaseOrder::join('partners', 'partners.codSAP','=','purchase_orders.cardCode')
            ->where([
              'purchase_orders.status'=> self::STATUS_OPEN,
              'partners.cnpj' => $cnpj
            ])
            ->select('purchase_orders.id','purchase_orders.idUser','purchase_orders.codSAP','purchase_orders.code','purchase_orders.cardCode','purchase_orders.status' ,'purchase_orders.docTotal')
            ->get();
   }

   private function existItemInCatalog($cProd){
       $search = Catalog::where('substitute','=', $cProd)->get();
       if(count($search) > 0){
         return $search[0]->itemCode;
       }else{
         return null;
       }
   }

   public function saveItems($xml){
       $itens = (array) $xml->NFe->infNFe->det;

       if(count($xml->NFe->infNFe->det) == 1){
             $item = new Items();
             $item->idImportXML = $this->id;
             $item->codPartners = $xml->NFe->infNFe->det->prod->cProd;
             $item->itemCode = $this->existItemInCatalog($xml->NFe->infNFe->det->prod->cProd);
             $item->EAN = $xml->NFe->infNFe->det->prod->cEAN;
             $item->name = clearString($xml->NFe->infNFe->det->prod->xProd);
             $item->uCom = $xml->NFe->infNFe->det->prod->uCom;
             $item->qCom = $xml->NFe->infNFe->det->prod->qCom;
             $item->vUnCom = $xml->NFe->infNFe->det->prod->vUnCom;
             $item->vProd = $xml->NFe->infNFe->det->prod->vProd;
             $item->NCM = $xml->NFe->infNFe->det->prod->NCM;
             $item->CFOP = $xml->NFe->infNFe->det->prod->CFOP;
             if(isset($xml->NFe->infNFe->det->imposto->ICMS->ICMS00->pICMS)){
              $item->ICMS = compressText($xml->NFe->infNFe->det->imposto->ICMS->ICMS00->pICMS,6,false);
            }else{
              $item->ICMS = '0.0000';
            }
            if(isset($xml->NFe->infNFe->det->imposto->IPI->IPITrib->pIPI)){
              $item->IPI = compressText($xml->NFe->infNFe->det->imposto->IPI->IPITrib->pIPI,6,false);
            }else{
              $item->IPI = '0.0000';
            }
            if(isset($xml->NFe->infNFe->det->imposto->PIS->PISAliq->pPIS)){
              $item->PIS = compressText($xml->NFe->infNFe->det->imposto->PIS->PISAliq->pPIS,6,false);
            }else{
              $item->PIS = '0.0000';
            }
            if(isset($xml->NFe->infNFe->det->imposto->COFINS->COFINSAliq->pCOFINS)){
              $item->COFINS = compressText($xml->NFe->infNFe->det->imposto->COFINS->COFINSAliq->pCOFINS,6,false);
            }else{
              $item->COFINS = '0.0000';
            }
             $item->save();
       }else{
          foreach ($xml->NFe->infNFe->det as $key => $value) {
             $item = new Items();
             $item->idImportXML = $this->id;
             $item->itemCode = $this->existItemInCatalog($value->prod->cProd);
             $item->codPartners = $value->prod->cProd;
             $item->EAN = $value->prod->cEAN;
             $item->name = compressText(clearString($value->prod->xProd),100,false);
             $item->uCom = $value->prod->uCom;
             $item->qCom = $value->prod->qCom;
             $item->vUnCom = $value->prod->vUnCom;
             $item->vProd = $value->prod->vProd;
             $item->NCM = $value->prod->NCM;
             $item->CFOP = $value->prod->CFOP;
             if(isset($value->imposto->ICMS->ICMS00->pICMS)){
              $item->ICMS =  compressText($value->imposto->ICMS->ICMS00->pICMS,6,false);
              }else{
                $item->ICMS = '0.0000';
              }
              if(isset($value->imposto->IPI->IPITrib->pIPI)){
                $item->IPI =  compressText($value->imposto->IPI->IPITrib->pIPI,6,false);
              }else{
                $item->IPI = '0.0000';
              }
              if(isset($value->imposto->PIS->PISAliq->pPIS)){
                $item->PIS =  compressText($value->imposto->PIS->PISAliq->pPIS,6,false);
              }else{
                $item->PIS = '0.0000';
              }
              if(isset($value->imposto->COFINS->COFINSAliq->pCOFINS)){
                $item->COFINS =  compressText($value->imposto->COFINS->COFINSAliq->pCOFINS,6,false);
              }else{
                $item->COFINS = '0.0000';
              }
             $item->save();
          }
      }
   }
}
