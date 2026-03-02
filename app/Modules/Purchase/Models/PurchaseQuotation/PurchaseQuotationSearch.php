<?php

namespace App\Modules\Purchase\Models\PurchaseQuotation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;

use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Inventory\Models\Requisicao\Products;
use App\LogsError;
use App\User;
use Litiano\Sap\NewCompany;

/**
 * App\purchaseRequest
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $requriedDate
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Purchase\Models\PurchaseQuotation\Item[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereRequriedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereUpdatedAt($value)
 * @property string|null $codSAP
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation whereCodSAP($value)
 */
class PurchaseQuotationSearch extends Model
{
    protected $table = 'purchase_quotation';
    protected $fillable = ['code','id_solicitante','status','update','name_solicitante','provider1','provider1_email','provider2','provider2_email','provider3','provider3_email','provider4','provider4_email','provider5','provider5_email','data_i','data_f'];
    public $attributes;

    
    const STATUS_WAIT_CLERK = 0;
    const STATUS_OPEN = 1;
    const STATUS_CANCEL = 2;
    const STATUS_PENDING = 3;
    const STATUS_CLOSE = 4;
    const STATUS_PC_G = 5;
    const STATUS_LINK = 7;
    
    const TEXT_STATUS = [
        '0' => 'Pendente RI',
        '1' => 'Aberto',
        '2' => 'Cancelado',
        '3' => 'Pendente',
        '4' => 'Fechado',
        '5' => 'PC Gerado',
        '7' => 'Aberto'
    ]; // retorno da nota fiscal de saida do SAP


    public function saveInDB($request){
     
            $this->code = $this->createCode();
            $this->id_solicitante = auth()->user()->id;
            $this->name_solicitante = auth()->user()->name;
            $this->provider1=isset($request->provider1) ? $request->provider1 : '' ;
           
            $this->provider1_email=isset($request->provider1_email) ? $request->provider1_email : '' ;
           
            $this->provider2=isset($request->provider2) ? $request->provider2 : '' ;
            $this->provider2_email=isset($request->provider2_email) ? $request->provider2_email : '' ;
            $this->provider3=isset($request->provider3) ? $request->provider3 : '' ;
            $this->provider3_email=isset($request->provider3_email) ? $request->provider3_email : '' ;
            $this->provider4=isset($request->provider4) ? $request->provider4 : '' ;
            $this->provider4_email=isset($request->provider4_email) ? $request->provider4_email : '' ;
            $this->provider5=isset($request->provider5) ? $request->provider5 : '' ;
            $this->provider5_email=isset($request->provider5_email) ? $request->provider5_email : '' ;
            $this->data_i = DATE('Y-m-d');
            $this->data_f = DATE('Y-m-d');
            $this->status = '1';
            if($this->save()){
              $this->saveItems($request);
            }

    }
    
    private function saveItems($request){

      foreach ($request->get('requiredProducts') as $key => $value) {
        $sap = new Company(false);
        $item = new Item();
        $item->idPurchaseQuotation = $this->id;
        //  dd($request);
        $item->itemCode = isset($value['itemCode']) ? $value['itemCode'] : $value['codSAP'] ;
        $item->itemName = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$item->itemCode}'")[0]['ItemName']; ;
        $item->qtd =  $value['qtd'];
       
        $item->priceP1 =  isset($value['priceP1']) ? $value['priceP1'] : '';
        $item->qtdP1 =  isset($value['qtdP1']) ? $value['qtdP1']  : '';
        $item->totalP1 =  isset($value['totalP1']) ? $value['totalP1'] : '';

        $item->priceP2 =  isset($value['priceP2']) ? $value['priceP2'] : '';
        $item->qtdP2 =  isset($value['qtdP2']) ? $value['qtdP2'] : '';
        $item->totalP2 =  isset($value['totalP2']) ? $value['totalP2'] : '';

        $item->priceP3 =  isset($value['priceP3']) ? $value['priceP3'] : '';
        $item->qtdP3 =  isset($value['qtdP3']) ? $value['qtdP3']   : '';
        $item->totalP3 =  isset($value['totalP3']) ? $value['totalP3'] : '';

        $item->priceP4 =  isset($value['priceP4']) ? $value['priceP4'] : '';
        $item->qtdP4 =  isset($value['qtdP4']) ? $value['qtdP4'] : '';
        $item->totalP4 =  isset($value['totalP4']) ? $value['totalP4'] : '';

        $item->priceP5 =  isset($value['priceP5']) ? $value['priceP5'] : '';
        $item->qtdP5 =  isset($value['qtdP5']) ? $value['qtdP5'] : '';
        $item->totalP5 =  isset($value['totalP5']) ? $value['totalP5'] : '';
       
        $item->save();

      }
    }
   

    public function updateInDB($request){
    
      $this->id_solicitante = auth()->user()->id;
      $this->name_solicitante = auth()->user()->name;
      $this->provider1=isset($request->provider1) ? $request->provider1 : '' ;
      $this->provider1_email=isset($request->provider1_email) ? $request->provider1_email : '' ;
      $this->provider2=isset($request->provider2) ? $request->provider2 : '' ;
      $this->provider2_email=isset($request->provider2_email) ? $request->provider2_email : '' ;
      $this->provider3=isset($request->provider3) ? $request->provider3 : '' ;
      $this->provider3_email=isset($request->provider3_email) ? $request->provider3_email : '' ;
      $this->provider4=isset($request->provider4) ? $request->provider4 : '' ;
      $this->provider4_email=isset($request->provider4_email) ? $request->provider4_email : '' ;
      $this->provider5=isset($request->provider5) ? $request->provider5 : '' ;
      $this->provider5_email=isset($request->provider5_email) ? $request->provider5_email : '' ;
      // $this->data_i = DATE('Y-m-d');
      $this->data_f = DATE('Y-m-d');
      $this->status = '1';
      if( $this->save()){
        $this->updateItems($request);
      }
    }

    private function updateItems($request){

      foreach ($request->get('requiredProducts') as $key => $value) {
        $sap = new Company(false);
        $item =  Item::find($value['idItem']);
        // $item->idPurchaseQuotation = $this->id;
         
        // $item->itemCode = isset($value['itemCode']) ? $value['itemCode'] : $value['codSAP'] ;
        // $item->itemName = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$item->itemCode}'")[0]['ItemName']; ;
        
        $item->qtd =  $value['qtd'];
       
        $item->priceP1 =  isset($value['priceP1']) ? $value['priceP1'] : '';
        $item->qtdP1 =  isset($value['qtdP1']) ? $value['qtdP1']  : '';
        $item->totalP1 =  isset($value['totalP1']) ? $value['totalP1'] : '';

        $item->priceP2 =  isset($value['priceP2']) ? $value['priceP2'] : '';
        $item->qtdP2 =  isset($value['qtdP2']) ? $value['qtdP2'] : '';
        $item->totalP2 =  isset($value['totalP2']) ? $value['totalP2'] : '';

        $item->priceP3 =  isset($value['priceP3']) ? $value['priceP3'] : '';
        $item->qtdP3 =  isset($value['qtdP3']) ? $value['qtdP3']   : '';
        $item->totalP3 =  isset($value['totalP3']) ? $value['totalP3'] : '';

        $item->priceP4 =  isset($value['priceP4']) ? $value['priceP4'] : '';
        $item->qtdP4 =  isset($value['qtdP4']) ? $value['qtdP4'] : '';
        $item->totalP4 =  isset($value['totalP4']) ? $value['totalP4'] : '';

        $item->priceP5 =  isset($value['priceP5']) ? $value['priceP5'] : '';
        $item->qtdP5 =  isset($value['qtdP5']) ? $value['qtdP5'] : '';
        $item->totalP5 =  isset($value['totalP5']) ? $value['totalP5'] : '';
       
        $item->save();

      }
    }
    
    private function createCode()
    {
        $busca = DB::select("select top 1 purchase_quotation.code from purchase_quotation order by purchase_quotation.code desc");
    
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'PQ00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function items(){
        return $this->hasMany(Item::class, 'idPurchaseQuotation', 'id');
    }
    
    private function getUserRequest($code){
      try {
        return DB::SELECT("Select T0.userClerk from users T0
                        JOIN requests as T1 on T0.id = T1.clerkUser
                        where T1.code =  '{$code}'")[0]->userClerk;
      } catch (\Exception $e) {
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0226', 'Erro ao localizar o  usuario',$e->getMessage());
      }

    }
    public function saveInSAP($obj){
      try{
        
        $sap = NewCompany::getInstance()->getCompany();
        $pr = $sap->GetBusinessObject(BoObjectTypes::oPurchaseQuotation);
        $pr->RequriedDate = $obj->requriedDate;
        $pr->UserFields->fields->Item("U_R2W_CODE")->value =  $obj->code;
        $pr->UserFields->fields->Item("U_R2W_USERNAME")->value = $obj->name;
        $pr->Comments =  'Solicitação de compra WEB: '.$obj->code.' - '.$obj->observation;
        $pr->ReqType = (int) 171;
        $pr->Requester = isset($obj->idSolicitante) ? $obj->idSolicitante  : $this->getUserRequest($code) ;

        foreach ($this->getItems($obj->id) as $key => $value) {
          $pr->Lines->ItemCode = (String) $value->itemCode;
          $pr->Lines->Quantity = (double) $value->quantity;
          $pr->Lines->CostingCode = (String) $value->distrRule;
          $pr->Lines->CostingCode2 = (String) $value->distriRule2;
          $pr->Lines->ProjectCode = (String) $value->project;
          $pr->Lines->add();
        }

        if($pr->add() != 0){
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('E0227',"saveInSAP",$sap->GetLastErrorDescription());
        }else{
          $obj->codSAP = $sap->GetNewObjectKey();
          $obj->codStatus = (string)$obj::STATUS_PENDING;
          $obj->save();
        }
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0226', $e->getFile().'|'.$e->getLine(),$e->getMessage());
      }
    }

    public function getItems($id){
      
        return DB::SELECT("SELECT * FROM purchase_quotation_items as T0 WHERE T0.idPurchaseQuotation = '$id' ");
    }

    private function getRequest($code){
      return DB::SELECT("SELECT T1.name, T0.requiredDate FROM requests as T0 JOIN users T1 on T1.id = T0.requesterUser WHERE T0.code = '$code'")[0];
    }

    public function getNameUser($id)
    {
        return User::where('id', '=', $id)->get()[0]->name;
    }
    
    public function getNameRequester($id)
    {
      $sap = new Company(false);
      
      $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");

      return $sap->getDb()->table('OHEM')
      ->where('empID',$id)
      ->get([$fullNameRaw])->first()->name;
      
    }

}
