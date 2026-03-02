<?php

namespace App\Modules\Partners\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Purchase\Models\XML\Items;
use App\LogsError;
use Litiano\Sap\NewCompany;

/**
 * App\partnerCatalogs
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idPartner
 * @property string $idUser
 * @property string $cardCode
 * @property string $cardName
 * @property string $itemCode
 * @property string $itemName
 * @property string $substitute
 * @property string|null $message
 * @property bool|null $is_locked
 * @property bool|null $dbUpdate
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereIdPartner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereSubstitute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereUpdatedAt($value)
 * @property string $idXMLItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog whereIdXMLItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Partners\Models\Partner\Catalog query()
 */
class Catalog extends Model
{
    protected $table = 'partner_catalogs';

    public function saveInDB($itemCode, $cnpj, $code){
        try {
          $this->idUser =  auth()->user()->id;
          $this->idXMLItem = $code;
          $this->cardCode = $this->getPartner($cnpj)[0]['CardCode'];
          $this->cardName = $this->getPartner($cnpj)[0]['CardName'];
          $this->itemCode = $itemCode;
          $this->itemName = $this->getItemXML($code)[0]->name;
          $this->substitute = $this->getItemXML($code)[0]->codPartners;
          $this->save();
          $xItem = Items::find($code);
          $xItem->itemCode = $itemCode;
          $xItem->save();
        } catch (\Exception $e) {
          $logsError = new logsError();
          $logsError->saveInDB('EC084I', $e->getFile().'|'.$e->getLine(), $e->getMessage());
        }

    }

    public function getPartner($cnpj){
      $sap = new Company(false);
      return $sap->query("SELECT distinct T0.CardCode, T0.CardName FROM OCRD  T0
                                        INNER JOIN CRD7 T2 ON T0.CardCode = T2.CardCode
                                        INNER JOIN CRD1 T1 ON T0.CardCode = T1.CardCode
                                        WHERE T2.TaxId0 like '{$cnpj}' ");
    }
    public function getItemXML($code){
      return DB::SELECT("SELECT * FROM xml_items WHERE id = '{$code}'");
    }

    public function existItemInCatalog($xml, $fornecedor, $cnpj){
      $item = new \StdClass();
      $array = array();
      if(count($xml['det']) == 1){
            $search = DB::SELECT("SELECT * FROM partner_catalogs WHERE substitute like '{$xml->prod->cProd}'");
            if(empty($search)){
              $item->codPartners = $xml->prod->cProd;
              $item->EAN = $xml->prod->cEAN;
              $item->name = $xml->prod->xProd;
              $item->uCom = $xml->prod->uCom;
              $item->qCom = $xml->prod->qCom;
              $item->vUnCom = $xml->prod->vUnCom;
              $item->vProd = $xml->prod->vProd;
              $item->NCM = $xml->prod->NCM;
              $item->CFOP = $xml->prod->CFOP;
              $item->fornecedor = $fornecedor;
              $item->cnpj =  $cnpj;
              $array[] =  $item;
            }
      }else if(count($xml['det']) > 1){
         foreach ($xml['det'] as $key => $value) {
           $search = DB::SELECT("SELECT * FROM partner_catalogs WHERE substitute like '{$value->prod->xProd}'");
           if(empty($search)){
            $item->codPartners = $value->prod->cProd;
            $item->EAN = $value->prod->cEAN;
            $item->name = $value->prod->xProd;
            $item->uCom = $value->prod->uCom;
            $item->qCom = $value->prod->qCom;
            $item->vUnCom = $value->prod->vUnCom;
            $item->vProd = $value->prod->vProd;
            $item->NCM = $value->prod->NCM;
            $item->CFOP = $value->prod->CFOP;
            $item->fornecedor = $fornecedor;
            $item->cnpj =  $cnpj;
            $array[] =  $item;
         }
       }
     }
     return $array;
    }

    public function saveInSap($obj){
      try {
          $sap = NewCompany::getInstance()->getCompany();
          $catalog = $sap->GetBusinessObject(BoObjectTypes::oAlternateCatNum);
          $catalog->CardCode =  (String) $obj->cardCode;
          $catalog->ItemCode = (String) $obj->itemCode;
          $catalog->Substitute = (String) $obj->substitute;
          if ($catalog->add() !== 0) {
            $obj->is_locked = true;
            $obj->dbUpdate = false;
            $obj->message = $sap->GetLastErrorDescription();
            $obj->save();
          }else{
            $obj->is_locked = false;
            $obj->dbUpdate = false;
            $obj->message = 'Salvo com sucesso!';
            $obj->save();
          }
      } catch (\Exception $e) {
        $logsError = new logsError();
        $logsError->saveInDB('EC085I', $e->getFile().'|'.$e->getLine(), $e->getMessage());
        $obj->is_locked = true;
        $obj->dbUpdate = false;
        $obj->message = $e->getMessage();
        $obj->save();
      }

    }

}
