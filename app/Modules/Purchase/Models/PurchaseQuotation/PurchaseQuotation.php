<?php

namespace App\Modules\Purchase\Models\PurchaseQuotation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Purchase\Models\PurchaseQuotation\Item;
use App\Modules\Purchase\Models\PurchaseQuotation\Expenses;
use App\Modules\Inventory\Models\Requisicao\Products;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseRequest\Item as ItemR;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\LogsError;
use App\User;
use App\Upload;
use App\Jobs\LinkUploadsInDocument;
use App\Jobs\Queue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Litiano\Sap\NewCompany;
use Illuminate\Http\Request;

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
class PurchaseQuotation extends Model
{
    protected $table = 'purchase_quotation';
    protected $fillable = ['code','id_solicitante','status','update','name_solicitante','provider1','provider1_email','provider2','provider2_email','provider3','provider3_email','provider4','provider4_email','provider5','provider5_email','data_i','data_f','id_order','code_order', 'idRequest'];
    
    const STATUS_WAIT_CLERK = 0;
    const STATUS_OPEN = 1;
    const STATUS_CANCEL = 2;
    const STATUS_PENDING = 3;
    const STATUS_CLOSE = 4;
    const STATUS_PC_G = 5;
    const STATUS_LINK = 7;
    
    //2 = Parcial
    //1 = Aberto
    //0 = Fechado
    const TEXT_STATUS = [
        '0' => 'Pendente RI',
        '1' => 'Aberto',
        '2' => 'Cancelado',
        '3' => 'Parcial',
        '4' => 'Fechado',
        '5' => 'PC Gerado',
        '7' => 'Aberto'
    ]; // retorno da nota fiscal de saida do SAP

    const STATUS_SAP = [
      'O' => '1',
      'C' => '2',
      'F' => '4',
    ];

    public function items(): HasMany
    {
      return $this->hasMany(Item::class, 'idPurchaseQuotation', 'id');
    }

    public function expenses(): HasMany
    {
      return $this->hasMany(Expenses::class, 'idPurchaseQuotation', 'id');
    }

    public function purchase_request(): HasOne
    {
      return $this->hasOne(PurchaseRequest::class, 'id', 'idRequest');
    }

    public function getDocTotal() {
      return $this->items()->sum(DB::raw('CAST(totalP1 AS FLOAT)'));
    }

    public function partner()
    {
      $sap = new Company(false);
      return $sap->getDb()->table('OCRD')->select('CardCode', 'CardName', 'E_Mail', 'Phone1', 'Phone2')->where('CardCode', '=', $this->provider1)->first();
    }

    public function getNextAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '>', $this->id)->orderBy('id','asc')->first() : $this->select('id')->orderBy('id','desc')->first();
    }
    
    public function getPreviousAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '<', $this->id)->orderBy('id','desc')->first() : $this->select('id')->orderBy('id','asc')->first();
    }


    public function saveInDB($request){
      try {
       $this->code = $this->createCode();
       $this->id_solicitante = auth()->user()->id;
       $this->name_solicitante = auth()->user()->name;
       $this->provider1=isset($request->parceiroNegocio) ? $request->parceiroNegocio : '' ;
       //$this->provider1_email=isset($request->provider1_email) ? $request->provider1_email : '' ;
 
       $this->data_i = DATE('Y-m-d');
       $this->data_f = DATE('Y-m-d');
       $this->status = $this::STATUS_OPEN;
       if($this->save()){
        $a = 0;
         foreach ($request->get('requiredProducts') as $key => $value) {
           if((Double)(is_numeric($value['quantityPendente']) ? $value['quantityPendente'] : clearNumberDouble($value['quantityPendente'])) > 0){
             $item = new Item();
             $value['idItemPurchaseRequest'] = $key;
             $item->saveInDB($value, $this->id);
             $a++;
           }
         }
       }
     } catch (\Throwable $e) {
        $logsError = new logsError();
        $logsError->saveInDB('E0227', $e->getFile().' | '.$e->getLine(), $e->getMessage());
     }
    }


    public function updateInDB($request){
      // $this->id_solicitante = auth()->user()->id;
      // $this->name_solicitante = auth()->user()->name;
      $this->provider1= $request->cardCode;
      $this->paymentTerms = $request->paymentTerms;
      $this->data_i = $request->dataLancamento;
      $this->data_f = $request->dataVencimento;
      $this->update = true;
      $this->message = "Cotação sendo enviada para o SAP!";
      $this->observation = $request->obsevacoes;
      $this->is_locked = true;
      if($this->save()){
        $this->updateItems($request);
        $this->expenses()->delete();
        $this->saveExpenses($this->id, $request->expenses);
        return true;
      }
      return false;
    }

    private function updateItems($request){
      foreach ($request->get('requiredProducts') as $key => $value) {
        $item = Item::find($value['idItem']);

        $item->priceP1 = (Double)(is_numeric($value['priceP1']) ? $value['priceP1'] : clearNumberDouble($value['priceP1']));
        $item->qtdP1 = (Double)(is_numeric($value['qtdP1']) ? $value['qtdP1'] : clearNumberDouble($value['qtdP1']));
        $item->totalP1 = (Double)number_format($item->qtdP1 * $item->priceP1, 2, '.', '');
        $item->status = !empty($value['deleted']) ? 3 : $item->status;
        $item->save();
      }
    }
    
    public function createCode()
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

    public function updateUpload(){

      try {

          $attachment = Upload::where('reference', '=', 'purchase_quotation')
              ->where('idReference', '=', $this->id)
              ->first();
  
          if(!is_null($attachment)){

            $sap = NewCompany::getInstance()->getCompany();
            $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseQuotations);
            $item->GetByKey((string)$this->codSAP);
    
            $codeAttachment = $attachment->saveInSAP();
            
            if(!is_null($codeAttachment)){
              $item->AttachmentEntry = $codeAttachment;
            }

            $ret = $item->Update();
            
            if($ret !== 0){
              $this->message = $sap->GetLastErrorDescription();
              $this->save();
            }
          }
      } catch (\Throwable $e) {
          $this->message = (String)$e->getMessage();
          $this->save();
          $logsErrors = new LogsError();
          $logsErrors->saveInDB('PRUP01', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
      }

    }

    public function saveInSAP($obj){
      try{
        $sap = NewCompany::getInstance()->getCompany();
        $oPQ = $sap->GetBusinessObject(BoObjectTypes::oPurchaseQuotations);
        $update = false;
        
        if ($obj->codSAP) {
          $oPQ->GetByKey((string)$obj->codSAP);
          $update = true;
        }

        $oPQ->DocDueDate = date_format(date_create($obj->data_f), 'Y-m-d');
        $oPQ->TaxDate = date_format(date_create($obj->data_i), 'Y-m-d');
        $oPQ->RequriedDate = date_format(date_create($obj->data_f), 'Y-m-d');
        $oPQ->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
        $oPQ->UserFields->fields->Item("U_R2W_USERNAME")->value = $obj->name_solicitante;
        $oPQ->Comments = 'Cotacao de compra WEB: '.$obj->code.' - '.$obj->observation;
        $oPQ->CardCode = $obj->provider1;

        $check = $obj->expenses()->get();
        if($check){
          foreach ($check as $key => $value) {
            $oPQ->Expenses->ExpenseCode = (int) $value->expenseCode;
            $oPQ->Expenses->LineTotal = (Double)clearNumberDouble(number_format($value->lineTotal,2,',','.'));
            $oPQ->Expenses->Project = (String) $value->project;
            $oPQ->Expenses->DistributionRule = (String) $value->costCenter;
            $oPQ->Expenses->DistributionRule2 = (String) $value->costCenter2;
            $oPQ->Expenses->TaxCode = (String) $value->tax;
            $oPQ->Expenses->Remarks = (String) $value->comments;
            $oPQ->Expenses->add();
          }
        }
          
        // $oPQ->ReqType = (int) 171;

        // $attachment = Upload::where('reference', '=', 'purchase_quotation')
        //   ->where('idReference', '=', $obj->id)
        //   ->first();

        // if(!is_null($attachment)){
        //   $codeAttachment = $attachment->saveInSAP();
          
        //   if(!is_null($codeAttachment)){
        //     $oPQ->AttachmentEntry = $codeAttachment;
        //   }
        // }
        
        $checkR = false;
        
        if(!empty($obj->idRequest)){
          $checkR = true;
        }

        $items = $obj->items();
        foreach ($items->get() as $line => $value) {
          $p_item = ItemR::find($value->idItemPurchaseRequest);
          $oPQ->Lines->SetCurrentLine($line);

          $oPQ->Lines->ItemCode = (string) $value->itemCode;
          $oPQ->Lines->Quantity = (double)$value->qtdP1;
          $oPQ->Lines->UnitPrice = (double)is_numeric($value->priceP1) ? (Double)$value->priceP1 : clearNumberDouble(number_format($value->priceP1,3,',','.'));

          if(is_null($p_item->lineNum)){
            $p_item->updateLineNum();
          }

          $itemSAP = $this->getItemPurchaseRSAP($p_item->purchase_request->codSAP, $value->idItemPurchaseRequest);

          $oPQ->Lines->BaseEntry = (int)$itemSAP['DocEntry'];
          $oPQ->Lines->BaseType = (int)1470000113;
          $oPQ->Lines->BaseLine = (int)$itemSAP['LineNum'];
          $oPQ->Lines->UserFields->fields->Item("U_R2W_ID")->Value = (string)$itemSAP['U_R2W_ID'];
        
          // $oPQ->Lines->CostingCode = (String) $value->distrRule;
          // $oPQ->Lines->CostingCode2 = (String) $value->distriRule2;
          // $oPQ->Lines->ProjectCode = (String) $value->project;
          // if(empty($obj->codSAP)){
          //   $value->lineNum = $line;
          //   $value->save();
          // }

          $value->lineNum = $oPQ->Lines->LineNum;
          $value->save();

          $oPQ->Lines->Add();
        }

        $deletedItems = $items->where('status', 3)->orderBy('id', 'desc')->get();
        foreach($deletedItems as $key => $item){
          $oPQ->Lines->SetCurrentLine($item->lineNum);
          $oPQ->Lines->Delete();
        }
        
        if ($update) {
          $ret = $oPQ->Update();
        } else {
          $ret = $oPQ->Add();
        }
      
        if($ret != 0){
          $obj->message = $sap->GetLastErrorDescription();
          $obj->is_locked = false;
          $obj->save();
          $logsErrors = new LogsError();
          $logsErrors->saveInDB('E0249',"Salvando cotação em SAP",$sap->GetLastErrorDescription());
          return $sap->GetLastErrorDescription();
        }else{
          $obj->message = '';
          $obj->codSAP = (int)$sap->GetNewObjectKey();
          $obj->status = $obj::STATUS_OPEN;
          $obj->is_locked = false;
          $obj->save();

          $obj->items()->where('status', '3')->delete();

          $uploads = Upload::where('idReference', $obj->id)->where('reference', 'purchase_quotation')->first();
          if(!empty($uploads)){
              LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
          }
          return true;
        }
      }catch (\Throwable $e) {
        $obj->message = $e->getMessage();
        $obj->is_locked = false;
        $obj->save();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('E0226', $e->getFile().'|'.$e->getLine(),$e->getMessage());
        return $e->getFile().'|'.$e->getLine().'|'.$e->getMessage();
      }
    }

    public function cenceledInSAP($obj){
      try {
        if(empty($obj->codSAP)){
          $obj->status = $obj::STATUS_CANCEL;
          $obj->message = 'Cotação cancelada!';
          $obj->update = false;
          $obj->save();
        }else{
          $sap = NewCompany::getInstance()->getCompany();
          $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseQuotations);
          if($opor->GetByKey((string) $obj->codSAP)){
            if($opor->Cancel() === 0){
              $obj->message = 'Cotação cancelada!';
              $obj->update = false;
              $obj->status = $obj::STATUS_CANCEL;
              $obj->save();
            }else{
              $obj->message = $sap->GetLastErrorDescription();
              $obj->update = false;
              $obj->save();
            }
          }
        }
      } catch (\Exception $e) {
        $obj->update = false;
        $obj->message = $e->getMessage();
        $obj->save();
      }
    }

    public function saveExpenses($id, $expenses){
      foreach ($expenses as $key => $value) {
        if($this->checkExpenses($value)){
          $expense = new Expenses();
          $value["idPurchaseQuotation"] = $id;
          $value["distributionRule"] = $value['costCenter'] ?? null;
          $value["lineTotal"] = clearNumberDouble($value["lineTotal"]);
          $expense->create($value);
        }
      }
    }

    public function copyFromPurchaseRequest(Request $request){

      $this->code = $this->createCode();
      $this->id_solicitante = auth()->user()->id;
      $this->name_solicitante = auth()->user()->name;
      $this->idRequest = $request->get('id_doc')[0];
      $this->data_i = DATE('Y-m-d');
      $this->status = self::STATUS_OPEN;

      if($this->save()){
        foreach($request->get('id_doc') as $index => $id_purchase_request){
          $items_purchase_request = ItemR::where('idPurchaseRequest', $id_purchase_request)->where('quantityPendente', '>', '0')->get();
          foreach ($items_purchase_request as $key => $value) {
              $itemSAP = getItemSAP($value->itemCode);
              $item = new Item();
              $item->idPurchaseQuotation = $this->id;
              $item->idPurchaseRequest = $id_purchase_request;
              $item->idItemPurchaseRequest = $value->id;
              $item->itemCode = isset($value->itemCode) ? $value->itemCode : $value->codSAP;
              $item->itemName = $itemSAP['ItemName'];
              $item->itemUnd = $itemSAP['BuyUnitMsr'];
              $item->qtd = $value->quantity;
              $item->quantityPendente = $value->quantity;
              $item->priceP1 = 0;
              $item->qtdP1 = 0;
              $item->totalP1 = 0;
              $item->save();
          }

          $purchase_request = PurchaseRequest::find($id_purchase_request);
          $purchase_request->idQuotation = $this->id;
          $purchase_request->isQuotation = true;
          $purchase_request->save();
        }
      }
    } 

    private function checkExpenses($value){
      return (!empty($value["expenseCode"]) && !empty($value["lineTotal"]));
    }

    public function getItems($id){
        return DB::SELECT("SELECT * FROM purchase_quotation_items as T0 WHERE T0.idPurchaseQuotation = '$id' ");
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

    public function getItemPurchaseRSAP($docEntry, $U_R2W_ID){
      $itemPR = ItemR::find($U_R2W_ID);

      if(!empty($itemPR)){
        $sap = new Company(false);
        $query = "SELECT DocEntry, LineNum, ItemCode, Dscription, Quantity, Price, LineTotal, 
          WhsCode, DocDate, Project, OcrCode, U_R2W_ID
          FROM PRQ1 WHERE DocEntry = $docEntry";

        return $sap->query($query)[$itemPR->lineNum]; 
      }
    }

    public function getProviderIndexQuotation($p_quotation,$code){
      /**GAMBI para buscar a coluna correspondente ao fornecedor
         * No model de cotação, atribui $attributes como publico e consegui acessar pelo foreach abaixo
         * acessando os attributes(colunas da tabela) consigo sabe qual delas possui o valor procurado
         * achando qual possui esse valor, consigo pegar o nome dela, e como o indice da coluna é o ultimo
         * caracter da mesma, consigo identificar a qual coluna aquele fornecedor pertence na cotação e
         * transformo esse indice na variavel $coluna
         */
        //$search_code = PurchaseQuotation
      $coluna = '';
      foreach($p_quotation as $key => $value){
        
        if($key == 'attributes'){
          foreach($value as $chave => $valor){
            if($valor == $code){
              $coluna = substr($chave,-1);
            }
          }
        }
      }
      return $coluna;
    }

    public function getTopNavData(): array
    {
        return [
            "urls" => $this->getUrlsTopNav(),
            "searchFields" => $this->getSearchFields()
        ];
    }

    public function getSearchFields(): array
    {
        return [
            "form_url" => route('purchase.quotation.listQuotationsTopNav'),
            "read_document_url" => route('purchase.quotation.read'),
            "fields" => [ // campos da views VW_R2W_SOLICITACAO_COMPRA
                [
                    "title" => "id",
                    "fieldName" => "id",
                    "list" => false
                ],
                [
                    "title" => "COLOR_STATUS",
                    "fieldName" => "COLOR_STATUS",
                    "list" => false
                ],
                [
                    "title" => "Código SAP",
                    "fieldName" => "codSAP",
                    "list" => true
                ],
                [
                    "title" => "Código WEB",
                    "fieldName" => "code",
                    "list" => true
                ],
                [
                  "title" => "Fornecedor",
                  "fieldName" => "provider1",
                  "list" => true
                ],
                [
                  "title" => "Solicitante",
                  "fieldName" => "name_solicitante",
                  "list" => true
                ],
                [
                  "title" => "Data",
                  "fieldName" => "created_at",
                  "render" => "renderFormatedDate",
                  "list" => true
                ],
                [
                  "title" => "Status",
                  "fieldName" => "TEXT_STATUS",
                  "render" => "renderRedirectButton",
                  "list" => true
                ],
            ]
        ];
    }

    public function getUrlsTopNav(): array
    {
        $previousRecord = $this->getPreviousAttribute();
        $nextRecord = $this->getNextAttribute();
        return [
            "back_page_url" => route('purchase.quotation.index'),
            "previous_record_url" => !empty($previousRecord) ? route('purchase.quotation.read', $previousRecord) : "",
            "create_record_url" => '#',
            "next_record_url" => !empty($nextRecord) ? route('purchase.quotation.read', $nextRecord) : "",
            "print_urls" => $this->getPrintUrls(),
            // "send_to_email_element_attributes" => $this->getSendToEmailElementAttributes(),
        ];
    }

    public function getPrintUrls(): array
    {
        if($this->id){
            return [
                "PDF" => route('purchase.quotation.print', [$this->id, 'pdf'])
            ];
        }
        return [];
    }

    public function getSendToEmailElementAttributes(): array
    {
        if($this->id && (int)$this->status !==  self::STATUS_PC_G){
            return [
              "content_attributes" => [
                "data-coreui-toggle" => "modal",
                "data-coreui-target" => "#sendToPartnerModal"
                ]
              ];
        }
        return [];
    }
}
