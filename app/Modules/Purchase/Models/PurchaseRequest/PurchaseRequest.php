<?php

namespace App\Modules\Purchase\Models\PurchaseRequest;

use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\Item as ItemPO;
use App\Upload;
use App\Jobs\Queue;
use App\Jobs\LinkUploadsInDocument;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Datetime;

use Litiano\Sap\Enum\BoObjectTypes;
use App\Modules\Inventory\Models\Requisicao\Products;
use App\LogsError;
use App\Models\Alertas;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\NewCompany;
use Log;
use Request;
use Throwable;

/**
 * App\purchaseRequest
 *
 * @mixin Eloquent
 * @property int $id
 * @property string $name
 * @property string $requriedDate
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Item[] $items
 * @method static Builder|PurchaseRequest whereCode($value)
 * @method static Builder|PurchaseRequest whereCreatedAt($value)
 * @method static Builder|PurchaseRequest whereId($value)
 * @method static Builder|PurchaseRequest whereName($value)
 * @method static Builder|PurchaseRequest whereRequriedDate($value)
 * @method static Builder|PurchaseRequest whereUpdatedAt($value)
 * @property string|null $codSAP
 * @method static Builder|PurchaseRequest newModelQuery()
 * @method static Builder|PurchaseRequest newQuery()
 * @method static Builder|PurchaseRequest query()
 * @method static Builder|PurchaseRequest whereCodSAP($value)
 */
class PurchaseRequest extends Model
{
    const STATUS_WAIT_CLERK = 0;
    const STATUS_OPEN = 1;
    // const STATUS_WAIT_CLERK = 0;
    // const STATUS_CLERK_LINK = 1;  
    // const STATUS_PARTIAL_ATTENDED = 2;
    // const STATUS_REFUSED = 3;  
    // const STATUS_RECEIVED = 4;
    // const STATUS_WAIT_REQUESTER = 5;  
    // const STATUS_NFS_SAP = 6;   
    // const STATUS_LINK = 7;
    // const STATUS_OPEN = 8;
    // const STATUS_CLOSE = 9;
    const STATUS_CANCEL = 2;
    const STATUS_PENDING = 3;
    const STATUS_CLOSE = 4;
    const STATUS_PC_G = 5;
    const STATUS_PQ_G = 6;
    const STATUS_LINK = 7;
    const STATUS_OPEN_old = 8;
    const TEXT_STATUS = [
        '0' => 'Pendente RI',
        '1' => 'Aberto',
        '2' => 'Cancelado',
        '3' => 'PC Parcial',
        '4' => 'Fechado',
        '5' => 'Fechado',
        '6' => 'Cot. Gerada',
        '7' => 'Pendente RI',
        '8' => 'Aberto'
    ];

    const STATUS_SAP = ['O' => '1', 'C' => '2', 'F' => '4'];

    const STATUS_COLOR = [
        self::STATUS_OPEN => 'btn-primary',
        self::STATUS_CANCEL => 'btn-danger',
        self::STATUS_PENDING => 'btn-secondary',
        self::STATUS_CLOSE => 'btn-success',
        self::STATUS_PC_G => 'btn-success',
        self::STATUS_PQ_G => 'btn-warning',
        self::STATUS_LINK => 'btn-primary',
        self::STATUS_OPEN_old => 'btn-primary'
    ];

    protected $table = 'purchase_requests'; // retorno da nota fiscal de saida do SAP
    protected $fillable = ['name', 'requriedDate', 'code', 'codStatus'];

    public function items()
    {
        return $this->hasMany(Item::class, 'idPurchaseRequest', 'id');
    }

    public function internal_request(): BelongsTo
    {
        return $this->belongsTo(Requests::class, 'idInternalRequest', 'id');
    }

    public function getNextAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '>', $this->id)->orderBy('id','asc')->first() : $this->select('id')->orderBy('id','desc')->first();
    }
    
    public function getPreviousAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '<', $this->id)->orderBy('id','desc')->first() : $this->select('id')->orderBy('id','asc')->first();
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alertas::class, 'id_document', 'id');
    }

    public function createCode()
    {
        $busca = DB::select("select top 1 purchase_requests.code from purchase_requests order by purchase_requests.code desc");

        if (!empty($busca)) {
            $pos = strpos($busca[0]->code, 'REC');

            if (!$pos) {
                $busca = DB::select("select top 1 purchase_requests.code from purchase_requests where code like '%SLC%' order by purchase_requests.code desc");
            }
        }

        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'SLC00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function saveInDB($code)
    {
        $internal_request = $this->getRequest($code);
        $this->name = $internal_request->name;
        $this->requriedDate = $internal_request->requiredDate;
        $this->code = $code;
        $this->codStatus = self::STATUS_OPEN;
        $this->requesterUser = $internal_request->requesterUser;
        $this->idSolicitante = $this->getUserRequest($code);
        $this->solicitante = $this->getNameRequester($this->idSolicitante);
        $this->idUser = auth()->user()->id;
        $this->origem = 'R2W';
        if ($this->save()) {
            $this->saveItems($code, $this->id);
        }
    }

    public function saveInDBFromInternalRequest(Requests $request)
    {
        $userClerk = $request->userClerk;
        $user = auth()->user();

        $this->name = $user->name;
        $this->requriedDate = $request->requiredDate;
        $this->code = $request->code;
        $this->requesterUser = $request->requesterUser;
        $this->idSolicitante = $userClerk->userClerk;
        $this->solicitante = $this->getNameRequester($this->idSolicitante);
        $this->codStatus = self::STATUS_OPEN;
        $this->idUser = $user->id;
        $this->idInternalRequest = $request->id;
        $this->origem = 'R2W';
        if ($this->save()) {
            $this->saveItemsInternalRequest($request->items, $this->id);
        }
    }

    public function saveInDBRequest($request)
    {
        $user = auth()->user();
        $this->idUser = $user->id;
        $this->name = $user->name;
        $this->requriedDate = $request->data;
        $this->code = $this->createCode();
        $this->requesterUser = $this->idUser;
        $this->idSolicitante = $request->requester;
        $this->solicitante = $this->getNameRequester($request->requester);
        $this->whs = $request->whs ?? NULL;
        $this->observation = $request->observation;
        $this->codStatus = self::STATUS_OPEN;
        $this->origem = 'R2W';
        $this->is_locked = true;
        if ($this->save()) {
            $this->saveItemsRequest($this->id, $request);
        }
    }

    public function saveInDBSuggestion($request){

        $user = auth()->user();
        $this->idUser = $user->id;
        $this->name = $user->name;
        $this->requriedDate = $request->data;
        $this->code = $this->createCode();
        $this->requesterUser = $this->idUser;
        $this->idSolicitante = $request->requester;
        $this->solicitante = $this->getNameRequester($request->requester);
        $this->whs = NULL;
        $this->observation = NULL;
        $this->codStatus = self::STATUS_OPEN;
        $this->origem = 'R2W';
        $this->is_locked = false;

        if($this->save()){
            $this->saveItemsSuggestion($this->id, $request);
        }

    }

    public function updateInDBRequest($request)
    {

        $this->requriedDate = $request->data;
        $this->dbUpdate = true;
        $this->observation = $request->observation;
        $this->is_locked = true;

        if ($this->save()) {
            $this->updateItemsRequest($this->id, $request);
        }
    }

    public function saveInSAP(PurchaseRequest $obj)
    {
        try {
            $obj = $this->find($obj->id);
            $update = false;

            $sap = NewCompany::getInstance()->getCompany();

            /** @var IDocuments $pr */
            $pr = $sap->GetBusinessObject(BoObjectTypes::oPurchaseRequest);
            
            if ($obj->codSAP) {
                app('PurchaseRequestJobLogger')->info('SAP Update', ['codSap' => $obj->codSAP]);
                $pr->GetByKey((int)$obj->codSAP);
                $update = true;
            }
            
            $pr->RequriedDate = $obj->requriedDate;
            $pr->Comments = 'Solicitação de compra WEB: ' . $obj->code . ' - ' . $obj->observation;
            $pr->ReqType = 171;
            $pr->Requester = $obj->idSolicitante;
            
            /**
             * Update without delete lines.
             */
            $items = $obj->items()->get();
            foreach ($items as $line => $value) {
                if ($update && $pr->Lines->Count > $line) {
                    $pr->Lines->SetCurrentLine($line);
                }
                $pr->Lines->ItemCode = (String)$value->itemCode;
                $pr->Lines->Quantity = (Double)$value->quantity;
                $pr->Lines->CostingCode = (String)$value->distrRule;
                $pr->Lines->MeasureUnit = (String)$value->itemUnd;
                $pr->Lines->CostingCode2 = (String)$value->distriRule2;
                $pr->Lines->ProjectCode = (String)$value->project;
                if(!empty($value->wareHouseCode)) $pr->Lines->WarehouseCode = (String)$value->wareHouseCode;
                $pr->Lines->UserFields->Fields->Item("U_R2W_ID")->Value = (String)$value->id;
                $pr->Lines->UserFields->Fields->Item("U_ContaOrcameto")->Value = (String)$value->accounting_account;
                $pr->Lines->Add();
                
                $value->update(['lineNum' => $line]);
            }
            
            for ($i = $items->count(); $i < $pr->Lines->Count; $i++) {
                $pr->Lines->SetCurrentLine($i);
                $pr->Lines->Delete();
            }

            $pr->UserFields->Fields->Item("U_R2W_CODE")->Value = $obj->code;
            $pr->UserFields->Fields->Item("U_R2W_USERNAME")->Value = $obj->name;

            if ($update) {
                $ret = $pr->Update();
            } else {
                $ret = $pr->Add();
            }

            if ($ret != 0) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('E0227', "saveInSAP", $sap->GetLastErrorDescription());
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = false;
                $obj->save();
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = null;
                $obj->is_locked = false;
                $obj->sync_at = new DateTime();
                $obj->save();

                $uploads = Upload::where('idReference', $obj->id)->where('reference', 'purchase_requests')->first();
                if(!empty($uploads)){
                    LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }

                if((Int)$obj->codStatus === (Int)self::STATUS_CANCEL){
                    $obj->cenceledInSAP($obj);
                }
            }
            return;
        } catch (Throwable $e) {
            $obj->is_locked = false;
            $obj->save();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0226', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function updateUpload(){

        try {

            $attachment = Upload::where('reference', '=', 'purchase_requests')
                ->where('idReference', '=', $this->id)
                ->first();
    
            if(!is_null($attachment)){

              $sap = NewCompany::getInstance()->getCompany();
              $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseRequest);
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

    public function duplicate($obj)
    {
        $user = auth()->user();
        $this->idUser = $user->id;
        $this->name = $user->name;
        $this->requriedDate = $obj->requriedDate;
        $this->code = $this->createCode();
        $this->requesterUser = $this->idUser;
        $this->idSolicitante = $obj->idSolicitante;
        $this->solicitante = $obj->solicitante;
        $this->whs = $obj->whs;
        $this->observation = $obj->observation;
        $this->codStatus = self::STATUS_OPEN;
        $this->origem = 'r2w';
        if ($this->save()) {
            $this->saveItemsDuplicate($obj->id, $this->id);
        }
    }

    public function getItems($id)
    {
        return DB::SELECT("SELECT T0.id, T0.itemCode, T0.idPurchaseRequest, T0.quantity, T0.quantityPendente, T0.project,T0.distrRule,T0.distriRule2,T0.wareHouseCode FROM purchase_request_items T0 WHERE T0.idPurchaseRequest = '$id' AND T0.quantityPendente > 0 ORDER BY id ASC");
    }

    public function getNameUser($id)
    {
        $dados = Products::join('requests', 'requests.id', '=', 'request_products.idRequest')->select('request_products.codSAP', 'request_products.idRequest', 'request_products.quantityRequest', 'request_products.quantityServed', 'request_products.project', 'request_products.costCenter')->where('requests.code', '=', $code)->get();

        foreach ($dados as $key => $value) {
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value->codSAP;
            $item->quantity = ($value->quantityRequest - $value->quantityServed);
            $item->quantityPendente = ($value->quantityRequest - $value->quantityServed);
            $item->project = $value->project;
            $item->distrRule = $value->costCenter;
            $item->save();
        }
    }

    public function getNameRequester($id)
    {
        $sap = new Company(false);

        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");

        return $sap->getDb()->table('OHEM')->where('empID', $id)->get([$fullNameRaw])->first()->name;

    }

    public function cenceledInSAP($obj)
    {
        try {
            if (empty($obj->codSAP) || is_null($obj->codSAP)) {
                $obj->is_locked = false;
                $obj->dbUpdate = false;
                $obj->codStatus = self::STATUS_CANCEL;
                $obj->save();
            }else{
                $sap = new Company(false);
                $sapQuery = $sap->query("SELECT DocStatus, CANCELED FROM OPRQ WHERE DocNum = $obj->codSAP");
      
                if(!empty($sapQuery) && $sapQuery[0]['CANCELED'] == 'Y' && $sapQuery[0]['DocStatus'] == 'C'){
                    $obj->is_locked = false;
                    $obj->dbUpdate = false;
                    $obj->codStatus = self::STATUS_CANCEL;
                    $obj->save();
                    return;
                }

                $sap = NewCompany::getInstance()->getCompany();
                $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseRequest);
                if($obj->codStatus == self::STATUS_OPEN && $opor->GetByKey((string) $obj->codSAP)){
                    if ($opor->Cancelled || $opor->Cancel() === 0) {
                        $obj->is_locked = false;
                        $obj->dbUpdate = false;
                        $obj->codStatus = self::STATUS_CANCEL;
                        $obj->save();
                    }else{
                        $obj->message = $sap->GetLastErrorDescription();
                        $obj->is_locked = true;
                        $obj->save();
                    }
                }
            }

        } catch (Exception $e) {
            $obj->is_locked = true;
            $obj->message = $e->getMessage();
            $obj->dbUpdate = true;
            $obj->save();
        }
    }

    public function closedInSAP($obj){
        try {
          
          if(empty($obj->codSAP) || is_null($obj->codSAP)){
            $obj->is_locked = false;
            $obj->dbUpdate = false;
            $obj->codStatus = self::STATUS_CLOSE;
            $obj->save();
          }else{
            $sap = new Company(false);
            $sapQuery = $sap->query("SELECT DocStatus, CANCELED FROM OPRQ WHERE DocNum = $obj->codSAP");
  
            if(!empty($sapQuery) && $sapQuery[0]['CANCELED'] == 'N' && $sapQuery[0]['DocStatus'] == 'C'){
                $obj->is_locked = false;
                $obj->dbUpdate = false;
                $obj->status = self::STATUS_CLOSE;
                $obj->save();
                return;
            }
  
            $sap = NewCompany::getInstance()->getCompany();
            $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseRequest);
  
            if($opor->GetByKey((string) $obj->codSAP)){
              if($opor->Close === 0){
                $obj->dbUpdate = false;
                $obj->codStatus = self::STATUS_CLOSE;
                $obj->save();
              }else{
                $obj->message = $sap->GetLastErrorDescription();
                $obj->save();
              }
            }
          }
        } catch (\Exception $e) {
          $obj->message = $e->getMessage();
          $obj->dbUpdate = true;
          $obj->save();
        }
    }

    private function saveItems($code, $id)
    {
        $dados = Products::join('requests', 'requests.id', '=', 'request_products.idRequest')->select('requests.whs', 'request_products.codSAP', 'request_products.idRequest', 'request_products.quantityRequest', 'request_products.quantityServed', 'request_products.project', 'request_products.costCenter')->where('requests.code', '=', $code)->get();

        foreach ($dados as $key => $value) {
            $item_sap = getItemSAP($value->codSAP);
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value->codSAP;
            $item->itemName = $item_sap["ItemName"];
            $item->itemUnd = $item_sap["BuyUnitMsr"];
            $item->quantity = (Double)$value->quantityServed - (Double)$item_sap['ONHAND'];
            $item->quantityPendente = $item->quantity;
            $item->project = $value->project;
            $item->distrRule = $value->costCenter;
            $item->accounting_account = $value->accounting_account ?? null;
            $item->save();
        }
    }

    private function saveItemsInternalRequest($products, $id)
    {
        foreach ($products as $key => $value) {
            $itemSAP = getItemSAP($value->codSAP);
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value->codSAP;
            $item->itemName = $itemSAP['ItemName'];
            $item->itemUnd = $itemSAP['BuyUnitMsr'];
            $item->quantity = $value->quantityServed;
            $item->quantityPendente = $item->quantity;
            $item->project = $value->project;
            $item->distrRule = $value->costCenter;
            $item->accounting_account = $value->accounting_account ?? null;
            $item->save();
        }
    }

    private function saveItemsRequest($id, $request)
    {
        foreach ($request->get('requiredProducts') as $key => $value) {
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value['codSAP'];
            $item->itemName = $value['itemName'];
            $item->itemUnd = $value['itemUnd'];
            $item->quantity = (Double)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
            $item->quantityPendente = $item->quantity;
            $item->project = $value['projeto'];
            $item->distrRule = $value['centroCusto'];
            $item->distriRule2 = $value['centroCusto2'];
            $item->wareHouseCode = $value['wareHouseCode'];
            $item->accounting_account = $value['accounting_account'] ?? null;
            $item->save();
        }
    }

    private function updateItemsRequest($id, $request)
    {
        Item::where('idPurchaseRequest', '=', $id)->delete();

        foreach ($request->get('requiredProducts') as $key => $value) {
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value['codSAP'];
            $item->itemName = $value['itemName'];
            $item->itemUnd = $value['itemUnd'];
            $item->quantity = (Double)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
            $item->quantityPendente = $item->quantity;
            $item->project = $value['projeto'];
            $item->distrRule = $value['centroCusto'];
            $item->distriRule2 = $value['centroCusto2'];
            $item->wareHouseCode = $value['wareHouseCode'];
            $item->accounting_account = $value['accounting_account'] ?? null;
            $item->save();

            $item_purchase_order = ItemPO::where('idItemPurchaseRequest', $key)->get();

            if (!empty($item_purchase_order)) {
                ItemPO::where('idItemPurchaseRequest', $key)->update(['idItemPurchaseRequest' => $item->id]);
            }
        }
    }

    private function saveItemsSuggestion($id, $request){
        foreach ($request->requiredProducts as $key => $value) {
            $item = new Item();
            $item->idPurchaseRequest = $id;
            $item->itemCode = $value['codSAP'];
            $item->itemName = $value['itemName'];
            $item->itemUnd = $value['itemUnd'];
            $item->quantity = (Double)(is_numeric($value['qtd']) ? $value['qtd'] : clearNumberDouble($value['qtd']));
            $item->quantityPendente = $item->quantity;
            $item->project = $value['projeto'];
            $item->distrRule = $value['centroCusto'];
            $item->distriRule2 = $value['centroCusto2'];
            $item->wareHouseCode = $value['wareHouseCode'];
            $item->accounting_account = $value['accounting_account'] ?? null;
            $item->save();
        }
    }

    private function getUserRequest($code)
    {
        try {
            return DB::SELECT("Select T0.userClerk from users T0
                        JOIN requests as T1 on T0.id = T1.clerkUser
                        where T1.code =  '{$code}'")[0]->userClerk;
        } catch (Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0226', 'Erro ao localizar o  usuario', $e->getMessage());
        }
    }

    private function saveItemsDuplicate($oldPRId, $newPRId)
    {
        foreach (Item::where("idPurchaseRequest", $oldPRId)->get() as $value) {
            $item = new Item();
            $item->idPurchaseRequest = $newPRId;
            $item->itemCode = $value->itemCode;
            $item->itemName = $value['itemName'];
            $item->itemUnd = $value['itemUnd'];
            $item->quantity = (double)$value['quantity'];
            $item->quantityPendente = (double)$value['quantity'];
            $item->project = $value['project'];
            $item->distrRule = $value['distrRule'];
            $item->distriRule2 = $value['distriRule2'];
            $item->wareHouseCode = $value['wareHouseCode'];
            $item->accounting_account = $value['accounting_account'] ?? null;
            $item->save();
        }
    }

    private function getRequest($code)
    {
        return DB::SELECT("SELECT T1.name, T0.requesterUser, T0.requiredDate FROM requests as T0 JOIN users T1 on T1.id = T0.requesterUser WHERE T0.code = '$code'")[0];
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
            "form_url" => route('purchase.request.listRequestsTopNav'),
            "read_document_url" => route('purchase.request.read'),
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
                    "title" => "Solicitante",
                    "fieldName" => "solicitante",
                    "list" => true
                ],
                [
                    "title" => "Usuário",
                    "fieldName" => "name",
                    "list" => true
                ],
                [
                    "title" => "Data solicitação",
                    "fieldName" => "requriedDate",
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
            "back_page_url" => route('purchase.request.index'),
            "previous_record_url" => !empty($previousRecord) ? route('purchase.request.read', $previousRecord) : "",
            "create_record_url" => route('purchase.request.create'),
            "next_record_url" => !empty($nextRecord) ? route('purchase.request.read', $nextRecord) : "",
            "print_urls" => $this->getPrintUrls(),
        ];
    }

    public function getPrintUrls(): array
    {
        if($this->id && $this->codSAP){
            return [
                "PDF" => route('purchase.request.print', [$this->id, 'pdf']),
                "EXCEL" => route('purchase.request.print', [$this->id, 'excel'])
            ];
        }
        return [];
    }
}
