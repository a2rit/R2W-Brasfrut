<?php

namespace App\Modules\Purchase\Models\PurchaseOrder;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Upload;
use App\Jobs\LinkUploadsInDocument;
use App\CFItems;
use App\logsError;
use App\Jobs\Queue;
use App\Models\Alertas;
use \Datetime;
use App\Modules\Partners\Models\Partner\Contract;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\PurchaseQuotation\Item as ItemQ;
use App\Modules\Purchase\Models\PurchaseRequest\Item as ItemR;
use App\Modules\Purchase\Models\PurchaseOrder\Item;
use App\Modules\Purchase\Models\PurchaseOrder\Expenses;
use App\Modules\Purchase\Models\PurchaseOrder\Payment;
use App\Modules\Settings\Models\Config;
use App\Modules\Settings\Models\Lofted;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\NewCompany;
use Illuminate\Database\Query\Builder;

/**
 * App\PurchaseOrder
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string|null $codSAP
 * @property string $code
 * @property string $codPN
 * @property string $docDate
 * @property string $docDueDate
 * @property string $taxDate
 * @property string $paymentTerms
 * @property string $freight
 * @property string $amount
 * @property string|null $branch
 * @property string $coin
 * @property string $comments
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCodPN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereFreight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereUpdatedAt($value)
 * @property string|null $paindSum
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder wherePaindSum($value)
 * @property string $cardCode
 * @property float|null $discPrcnt
 * @property float|null $docTotal
 * @property bool $is_locked
 * @property string|null $message
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder whereDiscPrcnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder whereMessage($value)
 * @property float|null $quotation
 * @property string|null $cardName
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereQuotation($value)
 * @property bool $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereDbUpdate($value)
 * @property string|null $identification
 * @property string|null $freightDocument
 * @property float|null $discountPercent
 * @property string|null $incoTerm
 * @property string|null $contact
 * @property string|null $transporter
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereFreightDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereIncoTerm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder whereTransporter($value)
 */
class PurchaseOrder extends Model
{
  const STATUS_OPEN = 1;
  const STATUS_CLOSE = 0;
  const STATUS_CANCEL = 2;
  const STATUS_PENDING = 3;
  const STATUS_REPROVE = 4;

  const STATUS_NEW = 'new';
  const STATUS_AUTHORIZED = 'authorized';
  const STATUS_UNAUTHORIZED = 'unauthorized';
  //const STATUS_WAITING_PRODUCTION_ORDER = 'waiting_production_order';
  const STATUS_FINALIZED = 'finalized';
  const STATUS_CANCELED = 'canceled';

  const STATUS_TEXT = [
    "0" => "FECHADO",
    "1" => "ABERTO",
    "2" => "CANCELADO",
    "3" => "PENDENTE",
    "4" => "REPROVADO"
  ];

  const STATUS_COLOR = [
    "0" => "btn-success",
    "1" => "btn-primary",
    "2" => "btn-danger",
    "3" => "btn-warning",
    "4" => "btn-danger"
  ];

  const STATUS_SAP = [
    'O' => '1',
    'C' => '2',
    'F' => '0'
  ];

  protected $table = 'purchase_orders';


  public function items()
  {
    return $this->hasMany(Item::class, 'idPurchaseOrders', 'id');
  }

  public function expenses(): HasMany
  {
    return $this->hasMany(Expenses::class, 'idPurchaseOrder', 'id');
  }


  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'idUser', 'id');
  }

  public function getContract()
  {
    return $this->items()->where('contract', '!=', null)->first()->contract ?? null;
  }

  public function getNextAttribute()
  {
    return !empty($this->id) ? $this->select('id')->where('id', '>', $this->id)->orderBy('id', 'asc')->first() : $this->orderBy('id', 'desc')->first();
  }

  public function getPreviousAttribute()
  {
    return !empty($this->id) ? $this->select('id')->where('id', '<', $this->id)->orderBy('id', 'desc')->first() : $this->orderBy('id', 'asc')->first();
  }

  public function alerts(): HasMany
  {
    return $this->hasMany(Alertas::class, 'id', 'id_document');
  }

  public function uploads()
  {
    return $this->hasMany(Upload::class, 'idReference', 'id')->where("reference", "=", $this->table);
  }

  public function saveInDB(Request $request)
  {

    try {
      $sap = new Company(false);
      $auth_user = auth()->user();
      $partner = getProviderData($request->cardCode);
      $this->idUser = $auth_user->id;
      $this->code = $this->createCode();
      $this->cardCode = $request->cardCode;
      $this->cardName = $partner['CardName'];
      $this->identification = !empty($partner['TaxId0']) ? $partner['TaxId0'] : $partner['TaxId4'];
      $this->docDate = $request->dataLancamento;
      $this->docDueDate = $request->dataVencimento;
      $this->taxDate = $request->dataLancamento;
      $this->paymentTerms = $request->condPagamentos;
      $this->freightDocument = is_null($request->valorFrete) ? 0 : $request->valorFrete;
      $this->discountPercent = clearNumberDouble($request->discountPercent ?? 0);
      $this->contact = $request->contact;
      $this->buyer = $request->buyer;
      $this->origem = "R2W";
      $this->contract = $request->contract;
      $this->docTotal = (float)(is_numeric($request->docTotal) ? $request->docTotal : clearNumberDouble($request->docTotal));
      $this->paindSum = creatPaindSum($request);
      $this->comments = mb_convert_encoding($request->obsevacoes ?? ' ', 'UTF-8');
      $this->incoTerm = $request->incoTerm;
      $this->status = self::STATUS_OPEN;
      $this->approval_method = (int)$request->approval_method;

      $valid_approver = null;

      $needApproval = false;

      if ($this->save()) {

        if (isset($request->payment) && !is_null($request->payment) && !empty($request->payment)) {
          $pay = new Payment();
          $pay->saveInDB($request->payment, $this->id);
        }

        foreach ($request->requiredProducts as $key => $value) {
          $item = new Item();
          $item->saveInDB($value, $this->id);
        }

        $itensPedido = Item::where('idPurchaseOrders', $this->id)->get()->groupBy('idPurchaseRequest');
        if ($itensPedido->isEmpty() == false) {
          foreach ($itensPedido as $value) {

            if ($value[0]->idPurchaseRequest != null) {
              $p_request = PurchaseRequest::where('id', '=', $value[0]->idPurchaseRequest)->first();

              foreach ($value as $item) {
                $p_request_item = ItemR::find($item->idItemPurchaseRequest);
                $p_request_item->quantityPendente > 0 ? $p_request_item->quantityPendente -= (float) $item->quantity : 0;
                $p_request_item->save();
                $this->checkQuantityPendentePR($p_request_item);
              }
              $this->isRequest = 1;
              $this->idRequest = $value[0]->idPurchaseRequest;
              $this->save();


              $p_request->save();
            }

            if (Config::get('approvePurchaseOrderR2W') && (int)$this->approval_method === 1 && (int)$auth_user->freeCompra != 1) {
              // $itemSAP = $sap->query("SELECT TOP 1 U_R2W_APROVAITEM FROM OITM WHERE ItemCode = '{$value['codSAP']}'");

              // if ((!empty($itemSAP) && $itemSAP[0]['U_R2W_APROVAITEM'] == 'Y') && !isset($value->deleted)) {
              if (!isset($value->deleted)) {
                $valid_approver = Lofted::where("cost_center_id", $value['costCenter'])
                  ->where('docNum', '=', Lofted::PURCHASE_ORDER)
                  ->where('first', '<=', $this->docTotal)
                  ->where('last', '>=', $this->docTotal)
                  ->get()
                  ->last();

                if (!empty($valid_approver)) {
                  $item->lofted_approveds_id = $valid_approver->id;
                  $item->save();
                  $needApproval = true;
                }
              }
            }
          }
        }

        if ($valid_approver && $auth_user->freeCompra != '1' && $needApproval) {
          $this->status = self::STATUS_PENDING;
          $this->idLofted = $valid_approver->id;
          $this->is_locked = false;
        } else {
          $this->status = self::STATUS_OPEN;
          $this->is_locked = true;
        }

        $this->docTotal = (float)$this->items()->sum('lineSum') - ($this->discountPercent ?? 0);
        // $this->checkIfNeedApprove();
        $this->save();
        $this->needApproval = $needApproval;
        $this->saveExpenses($this->id, $request->expenses);
      }
    } catch (\Exception $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBSuggestion($request)
  {
    try {
      $partner = getProviderData($request->cardCode);
      $this->idUser = auth()->user()->id;
      $this->code = $this->createCode();
      $this->cardCode = $request->cardCode;
      $this->cardName = $partner['CardName'];
      $this->identification = !empty($partner['TaxId0']) ? $partner['TaxId0'] : $partner['TaxId4'];
      $this->docDate = $request->dataLancamento;
      $this->docDueDate = $request->dataLancamento;
      $this->taxDate = $request->dataLancamento;
      $this->paymentTerms = $partner["GroupNum"];
      $this->origem = "R2W";
      $this->docTotal = 0;
      $this->status = self::STATUS_OPEN;
      $this->is_locked = false;
      if ($this->save()) {

        foreach ($request->requiredProducts as $key => $value) {
          $item = new Item();
          $item->saveInDBSuggestion($value, $this->id);
        }

        $itensPedido = Item::where('idPurchaseOrders', $this->id)->get()->groupBy('idPurchaseRequest');
        if ($itensPedido->isEmpty() == false) {
          foreach ($itensPedido as $value) {
            if (Config::get('approvePurchaseOrderR2W') && (int)$this->approval_method === 1 && (int)auth()->user()->freeCompra != 1) {
              // $itemSAP = $sap->query("SELECT TOP 1 U_R2W_APROVAITEM FROM OITM WHERE ItemCode = '{$value['codSAP']}'");

              // if ((!empty($itemSAP) && $itemSAP[0]['U_R2W_APROVAITEM'] == 'Y') && !isset($value->deleted)) {
              if (!isset($value->deleted)) {
                $valid_approver = Lofted::where("cost_center_id", $value['costCenter'])
                  ->where('docNum', '=', Lofted::PURCHASE_ORDER)
                  ->where('first', '<=', $this->docTotal)
                  ->where('last', '>=', $this->docTotal)
                  ->get()
                  ->last();

                if (!empty($valid_approver)) {
                  $item->lofted_approveds_id = $valid_approver->id;
                  $item->save();
                  $needApproval = true;
                }
              }
            }
          }
        }

        if ($valid_approver && auth()->user()->freeCompra != '1' && $needApproval) {
          $this->status = self::STATUS_PENDING;
          $this->idLofted = $valid_approver->id;
          $this->is_locked = false;
        } else {
          $this->status = self::STATUS_OPEN;
          $this->is_locked = true;
        }

        $this->docTotal = (float)$this->items()->sum('lineSum');
        $this->save();

        if ($needApproval) {
          $loftedId = null;
          foreach ($itensPedido as $index => $item) {
            $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
              ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
              ->where('docNum', '=', Lofted::PURCHASE_ORDER)
              ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
              ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
              ->orderby('nivel')
              ->get();


            if (count($search) > 0 && $loftedId != $item->lofted_approveds_id) {

              foreach ($search as $key => $value) {

                $attributes['idPurchaseOrder'] = $this->id;
                $attributes['idLofted'] = $value->idLofted;
                $attributes['idApproverDocuments'] = $value->id;
                $attributes['nivel'] = $value->nivel;
                $attributes['idUser'] = $value->approverUser;
                $attributes['status'] = Approve::STATUS_CLOSE;

                Approve::create($attributes);
              }
              $loftedId = $item->lofted_approveds_id;
            }
          }
        }
      }
    } catch (\Exception $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function saveInDBFromSAP($data)
  {

    try {
      DB::beginTransaction();
      $user = User::where('userClerk', $data['head']['OwnerCode'])->first();
      $purchase_order = new PurchaseOrder();

      $purchase_order->idUser = $user->id;
      $purchase_order->codSAP = $data['head']['DocEntry'];
      $purchase_order->code = $purchase_order->createCode();
      $purchase_order->cardCode = $data['head']['CardCode'];
      $purchase_order->cardName = $data['head']['CardName'];
      $purchase_order->docDate = $data['head']['DocDate'];
      $purchase_order->docDueDate = $data['head']['DocDueDate'];
      $purchase_order->taxDate = $data['head']['TaxDate'];
      $purchase_order->identification = $data['head']['TaxId0'];
      $purchase_order->paymentTerms = $data['head']['GroupNum'];
      $purchase_order->incoTerm = $data['head']['TrnspCode'];
      $purchase_order->docTotal = $data['head']['DocTotal'];
      $purchase_order->origem = 'SAP';
      $purchase_order->is_locked = '0';
      $purchase_order->status = $data['head']['CANCELED'] == 'N' && $data['head']['DocStatus'] == 'C' ? $purchase_order::STATUS_SAP['F'] : $purchase_order::STATUS_SAP[$data['head']['DocStatus']];
      $purchase_order->message = 'Documento gerado através do SAP';
      $purchase_order->comments = $data['head']['POComments'];
      $purchase_order->creator_user_id = $user->id;

      if ($purchase_order->save()) {
        $purchase_order->created_at = new \DateTime(date("Y-m-d", strtotime($data['head']['TaxDate'])) . " " . preg_replace('/(\d{1,2})(\d{2})(\d{2})$/', '$1:$2:$3', $data['head']['CreateTS']));

        foreach ($data['expenses'] as $key => $expense) {
          $expense_r2w = new Expenses();
          $expense_r2w->idPurchaseOrder = $purchase_order->id;
          $expense_r2w->expenseCode = $expense['ExpnsCode'];
          $expense_r2w->lineTotal = number_format($expense['LineTotal'], 2, '.', '');
          $expense_r2w->project = $expense['Project'] ?? "SEM PROJETOS";
          $expense_r2w->distributionRule = $expense['OcrCode'];
          $expense_r2w->costCenter = $expense['OcrCode'];
          $expense_r2w->costCenter2 = $expense['OcrCode2'];
          $expense_r2w->tax = $expense['TaxCode'];
          $expense_r2w->comments = $expense['Comments'];
          $expense_r2w->save();
        }

        foreach ($data['body'] as $key => $itemSAP) {

          $item_r2w = new Item();
          $item_r2w->idPurchaseOrders = $purchase_order->id;
          $item_r2w->itemCode = $itemSAP['ItemCode'];
          $item_r2w->itemUnd = $itemSAP['UnitMsr'];
          $item_r2w->ItemName = $itemSAP['Dscription'];
          $item_r2w->price = (float)number_format($itemSAP['Price'], 4, '.', '');
          $item_r2w->lineSum = (float)number_format($itemSAP['LineTotal'], 2, '.', '');
          $item_r2w->codCost = isset($itemSAP['OcrCode']) ? $itemSAP['OcrCode'] : '';
          $item_r2w->accounting_account = $itemSAP["AcctCode"];
          $item_r2w->codUse = '';
          $item_r2w->status = 1;
          $item_r2w->quantity = (float)number_format($itemSAP['Quantity'], 3, '.', '');
          $item_r2w->costCenter = $itemSAP['OcrCode'];
          $item_r2w->costCenter2 = $itemSAP['OcrCode2'];
          $item_r2w->codProject = $itemSAP['Project'] ?? 'SEM PROJETOS';
          $item_r2w->warehouseCode = $itemSAP['WhsCode'];

          $item_r2w->save();
        }
      }

      if ($purchase_order->save()) {

        $items = Item::where('idPurchaseOrders', $purchase_order->id)->get();
        $loftedId = null;

        foreach($items as $index => $item){
          $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
              ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
              ->where('docNum', '=', Lofted::PURCHASE_ORDER)
              ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
              ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
              ->orderby('nivel')
              ->get();

          
          if (count($search) > 0 && $loftedId != $item->lofted_approveds_id) {

              foreach($search as $key => $value){

                  $attributes['idPurchaseOrder'] = $purchase_order->id;
                  $attributes['idLofted'] = $value->idLofted;
                  $attributes['idApproverDocuments'] = $value->id;
                  $attributes['nivel'] = $value->nivel;
                  $attributes['idUser'] = $value->approverUser;
                  $attributes['status'] = Approve::STATUS_CLOSE;

                  Approve::create($attributes);
              }
              $loftedId = $item->lofted_approveds_id;
          }
        }

        if (!empty($data['head']['AtcEntry'])) {
          $upload = new Upload;
          $upload->saveFromSAP($data['head']['AtcEntry'], $purchase_order, 'purchase_orders');
        }

        DB::commit();

        $this->setApproveStatusToSAP($purchase_order);
      }
    } catch (\Exception $e) {
      DB::rollback();
      $logsError = new LogsError();
      $logsError->saveInDB("PFS002", "Baixando pedido de compras para o R2W", $e->getMessage());
    }
  }

  private function setApproveStatusToSAP($purchase_order)
  {
    try {
      $sap = NewCompany::getInstance()->getCompany();
  
      $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
      
      if ($purchase_order->codSAP) {
        $item->GetByKey((string)$purchase_order->codSAP);
      }
  
      $item->UserFields->fields->Item("U_APROV_STATUS")->value = 0;
      $ret = $item->Update();

      if ($ret !== 0) {
        $logsErro = new logsError();
        $logsErro->saveInDB('E0082', 'Line 271', $sap->GetLastErrorDescription());
        $purchase_order->message = $sap->GetLastErrorDescription();
        $purchase_order->is_locked = false;
        $purchase_order->save();
      } else {
        $purchase_order->is_locked = false;
        $purchase_order->message = NULL;
        $purchase_order->sync_at = new DateTime();
        $purchase_order->save();
      }
    } catch (\Throwable $th) {
      $purchase_order->message = "Não foi possível atualizar o status da aprovação do pedido de compras no SAP.";
      $purchase_order->save();
    }
  }


  public function saveInDBFromPurchase(Request $request, $p_request)
  {
    try {
      DB::beginTransaction();
      $this->idUser = auth()->user()->id;
      $this->code = $this->createCode();
      $this->cardCode = $request->cardCode;
      $this->cardName = $this->getPartnerName($request->cardCode);
      $this->identification = $this->getPartnerIdentification($request->cardCode);
      $this->docDate = DATE('Y-m-d');
      $this->docDueDate = $p_request->requriedDate;
      $this->taxDate = DATE('Y-m-d');
      $this->paymentTerms = $request->condPagamentos;
      $this->comments = mb_convert_encoding(is_null($request->observation) ? ' ' : $request->observation, 'UTF-8');
      $this->is_locked = false;
      $this->isRequest = true;
      $this->idRequest = $p_request->id;
      $this->origem = "R2W";
      // $this->docTotal = clearNumberDouble($request->docTotal);
      // $valid_approver = Lofted::where('first','<=',$this->docTotal)
      //                         ->where('last', '>=',$this->docTotal)->get(); 
      // if(count($valid_approver) > 0){
      //   $this->status = self::STATUS_PENDING;
      // }else{
      $this->status = self::STATUS_OPEN;
      // }
      if ($this->save()) {
        foreach ($p_request->items()->where('quantityPendente', '>', 0)->get() as $value) {
          $item = new Item();
          $value->codSAP = $value->itemCode;
          $value->qtd = $value->quantityPendente;
          $value->itemName = $value->itemName;
          $value->projeto = $value->project;
          $value->itemUnd = $value->itemUnd;
          $value->centroCusto = $value->distrRule;
          $value->centroCusto2 = $value->distriRule2;
          $value->accounting_account = $value->accounting_account ?? null;
          $value->idItemPurchaseRequest = $value->id;
          $item->saveInDBFromRequest($value, $this->id);
        }
        DB::commit();
      }
    } catch (\Exception $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }
  //Função que cria uma ordem de compra a partir de uma cotação TOTAL por fornecedor
  public function saveInDBFromQuotationF(Request $request, $p_quotation)
  {
    try {

      $p_request = PurchaseRequest::find($p_quotation->idRequest);

      $this->idUser = auth()->user()->id;
      $this->code = $this->createCode();
      $this->cardCode = $request->providerF;
      $this->cardName = $this->getPartnerName($request->providerF);
      $this->identification = $this->getPartnerIdentification($request->providerF);
      $this->docDate = DATE('Y-m-d');
      $this->docDueDate = DATE('Y-m-d');
      $this->taxDate = DATE('Y-m-d');
      $this->paymentTerms = $request->paymentTermsF;
      $this->comments =  '';
      $this->is_locked = false;
      $this->isQuotation = true;
      $this->idQuotation = $p_quotation->id;
      $this->origem = "R2W";

      $this->status = self::STATUS_OPEN;
      if ($this->save()) {
        $itemR = ItemR::where('idPurchaseRequest', $p_request->id)->get();

        $j = 0;
        $docTotal = 0;
        foreach ($p_quotation->getItems($p_quotation->id) as $key => $value) {

          $sap = new Company(false);
          $item = new Item();

          $itemRToUpdate = ItemR::find($itemR[$j]->id);
          if ($itemRToUpdate->quantityPendente > 0) {
            $itemRToUpdate->quantityPendente = (float)$itemRToUpdate->quantityPendente - (float)$value->qtd;
            $itemR[$j]->quantityPendente = (float)$itemRToUpdate->quantityPendente - (float)$value->qtd;
          } else {
            $itemRToUpdate->quantityPendente = 0;
            $itemR[$j]->quantityPendente = 0;
          }

          $value->codSAP = $value->itemCode;
          $value->qtd = $value->{'qtdP' . ($request->colunaF)};
          $value->price = clearNumberDouble($value->{'priceP' . ($request->colunaF)});
          $value->itemName = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'];
          $value->projeto = $itemRToUpdate->project;
          $value->centroCusto =  $itemRToUpdate->distrRule;
          $value->centroCusto2 =  $itemRToUpdate->distrRule2;
          $value->idPurchaseRequest = $itemRToUpdate->idPurchaseRequest;
          $value->idItemPurchaseRequest = $itemRToUpdate->id;
          $item->saveInDBFromQuotationF($value, $this->id);
          $docTotal += clearNumberDouble($value->{'priceP' . ($request->colunaF)}) * clearNumberDouble($value->{'qtdP' . ($request->colunaF)});
          $itemRToUpdate->save();
          $j++;
        }

        $this->docTotal = (float) $docTotal;
        $this->save();
        $p_quotation->id_order = '0';
        $p_quotation->status = $p_quotation::STATUS_PENDING;
        $p_quotation->save();

        $c = 0;
        foreach ($itemR as $index => $item) {
          if ((int)$item->quantityPendente == 0) {
            $c++;
          }
        }
        if (count($itemR) != $c) {
          $p_request->codStatus = 3;
        } else {
          $p_request->codStatus = 5;
        }
        //$p_request->codePC = $this->code;
        $p_request->save();

        //Tentando atualizar a cotacao para parcial/fechado
        if ($p_quotation->status == $p_quotation::STATUS_PENDING) {
          $cont = 0;
          foreach ($p_quotation->getItems($p_quotation->id) as $key => $value) {
            if ($value->id_order != null) {
              $cont++;
            }
          }

          if ($cont == count($p_quotation->getItems($p_quotation->id))) {
            $p_quotation->status = $p_quotation::STATUS_CLOSE;
            $p_quotation->id_order = '-2';
            $p_quotation->save();
          }
        }
      }
    } catch (\Exception $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }
  //Função que cria uma ordem de compra a partir de uma cotação PARCIAL/por item
  public function saveInDBFromQuotationI($attr)
  {

    try {
      foreach ($attr as $code => $itens) {
        $docTotal = 0;
        $p_quotation = PurchaseQuotation::find($itens['idQuotation']);
        $p_order = new PurchaseOrder();
        $partner = getProviderData($code);

        $p_request = PurchaseRequest::find($p_quotation->idRequest);
        $p_order->idUser = auth()->user()->id;
        $p_order->code = $p_order->createCode();
        $p_order->cardCode = $code;
        $p_order->cardName = $partner['CardName'];
        $p_order->identification = $partner['TaxId0'] ?? $partner['TaxId4'];
        $p_order->docDate = DATE('Y-m-d');
        $p_order->docDueDate = DATE('Y-m-d');
        $p_order->taxDate = DATE('Y-m-d');
        $p_order->paymentTerms = 0;
        $p_order->comments =  '';
        $p_order->is_locked = false;
        $p_order->isQuotation = true;
        $p_order->idQuotation = $p_quotation->id;
        $p_order->paymentTerms = $p_quotation->paymentTerms;
        $p_order->origem = "R2W";
        $p_order->status = self::STATUS_OPEN;
        if ($p_order->save()) {
          $colunm = $this->getProviderIndexQuotation($p_quotation, $code);

          $itemR = ItemR::where('idPurchaseRequest', $p_quotation->idRequest)->get();
          $j = 0;
          $countPendingItems = 0;
          if (array_key_exists('idQuotation', $itens)) {
            unset($itens['idQuotation']);
          }

          foreach ($itens as $indice => $item) {
            foreach ($item as $key => $id) {
              $sap = new Company(false);
              $itemQ = itemQ::find($id);

              //atualiza o valor pendente na solicitacao
              $itemRToUpdate = ItemR::find($itemQ->idItemPurchaseRequest);
              if ($itemRToUpdate->quantityPendente > 0) {

                $itemRToUpdate->quantityPendente =  (float)$itemRToUpdate->quantityPendente - (float)$itemQ->{'qtdP' . $colunm};
                $itemR[$j]->quantityPendente =  (float)$itemRToUpdate->quantityPendente - (float)$itemQ->{'qtdP' . $colunm};
                $itemRToUpdate->quantityPendente == 0 ? $countPendingItems++ : null;
              } else {
                $itemRToUpdate->quantityPendente = 0;
                $itemR[$j]->quantityPendente = 0;
                $countPendingItems++;
              }

              $itemPO = [];
              $itemPO['codSAP'] = $itemQ->itemCode;
              $itemPO['idPurchaseRequest'] = $p_quotation->idRequest;
              $itemPO['qtd'] = (float)(is_numeric($itemQ->{'qtdP' . $colunm}) ? $itemQ->{'qtdP' . $colunm} : clearNumberDouble($itemQ->{'qtdP' . $colunm}));
              $itemPO['price'] = (float)(is_numeric($itemQ->{'priceP' . $colunm}) ? $itemQ->{'priceP' . $colunm} : clearNumberDouble($itemQ->{'priceP' . $colunm}));
              $itemPO['itemName'] = $sap->query("SELECT T0.[ItemCode], T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$itemQ->itemCode}'")[0]['ItemName'];
              $itemPO['projeto'] = $itemRToUpdate->project;
              $itemPO['centroCusto'] = $itemRToUpdate->distrRule;
              $itemPO['centroCusto2'] = $itemRToUpdate->distriRule2;
              $itemPO['warehouseCode'] = $itemRToUpdate->wareHouseCode;
              $itemPO['idItemPurchaseRequest'] = $itemQ->idItemPurchaseRequest;
              $itemPO['idItemPurchaseQuotation'] = $itemQ->id;

              $itemOrder = new item();
              $itemOrder->saveInDBFromQuotationI($itemPO, $p_order->id);
              $itemQ->id_order = $p_order->id;
              $itemQ->code_order = $p_order->code;
              //$itemQ->quantityPendente = $itemQ->checkQuantityPendentePR - $itemQ->{'priceP'.$colunm};
              $itemQ->lastProvider = $code;
              $itemQ->quantityPendente = (float)($itemQ->quantityPendente - $itemQ->qtdP1) < 0 ? 0 : (float)$itemQ->quantityPendente - $itemQ->qtdP1;
              //$itemQ->status = $itemQ->quantityPendente == 0 ? 2 : 1;
              $itemQ->save();

              $itemRToUpdate->save();
              $this->checkQuantityPendentePR($itemRToUpdate);
              $docTotal += $itemPO['qtd'] * $itemPO['price'];

              $j++;
            }
          }

          $p_request->codePC = $p_order->code;
          $p_request->save();

          $p_quotation->id_order = '0';
          $p_quotation->code_order = $p_order->code;
          //$p_quotation->status = $p_quotation::STATUS_PENDING;
          $p_quotation->save();
          $this->checkQuantityPendentePQ($p_order->id);
          $p_order->docTotal = (float) $docTotal;
          $p_order->save();
        }
      }
    } catch (\Throwable $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0110E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function getProviderIndexQuotation($p_quotation, $code)
  {
    /**GAMBI para buscar a coluna correspondente ao fornecedor
     * No model de cotação, atribui $attributes como publico e consegui acessar pelo foreach abaixo
     * acessando os attributes(colunas da tabela) consigo sabe qual delas possui o valor procurado
     * achando qual possui esse valor, consigo pegar o nome dela, e como o indice da coluna é o ultimo
     * caracter da mesma, consigo identificar a qual coluna aquele fornecedor pertence na cotação e
     * transformo esse indice na variavel $coluna
     */
    //$search_code = PurchaseQuotation
    $coluna = '';
    foreach ($p_quotation as $key => $value) {


      if ($key == 'attributes') {
        foreach ($value as $chave => $valor) {
          if ($valor == $code) {
            $coluna = substr($chave, -1);
          }
        }
      }
    }

    return $coluna;
  }


  public function updateInDB(Request $request, $obj)
  {
    try {
      $sap = new Company(false);
      $auth_user = auth()->user();

      Expenses::join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_expenses.idPurchaseOrder')
        ->where('purchase_order_expenses.idPurchaseOrder', '=', $obj->id)->delete();
      Payment::join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_payments.idPurchaseOrders')
        ->where('purchase_order_payments.idPurchaseOrders', '=', $obj->id)->delete();

      if (empty($obj->codSAP)) {
        $obj->cardCode = $request->cardCode;
        $obj->identification = $this->getPartnerIdentification($request->cardCode);
        $obj->cardName = $this->getPartnerName($request->cardCode);
        $obj->approval_method = $request->approval_method;
      }

      $obj->comments = mb_convert_encoding($request->obsevacoes ?? '', 'UTF-8');
      $obj->message = null;
      $obj->docTotal = (float)(is_numeric($request->docTotal) ? $request->docTotal : clearNumberDouble($request->docTotal));
      $obj->discountPercent = clearNumberDouble($request->discountPercent ?? 0);
      $obj->incoTerm = $request->incoTerm;
      $obj->paymentTerms = $request->condPagamentos;
      $obj->freightDocument = is_null($request->valorFrete) ? 0 : $request->valorFrete;
      $obj->docDueDate = $request->dataVencimento;
      $obj->buyer = $request->buyer;
      $obj->contract = $request->contract;
      $obj->status = self::STATUS_OPEN;
      $needApproval = false;

      $valid_approver = null;

      if ($obj->save()) {

        $this->saveExpenses($obj->id, $request->expenses);

        $pay = new Payment();
        $pay->saveInDB($request->payment, $obj->id);
        foreach ($request->requiredProducts as $key => $value) {
          if (isset($value['id'])) {
            $item = Item::find($value['id']);
            $item->updateInDB($value, $obj);
          } else {
            $item = new Item();
            $item->saveInDB($value, $obj->id);
          }

          if (Config::get('approvePurchaseOrderR2W') && (int)$obj->approval_method === 1 && (int)$auth_user->freeCompra != 1) {
            // $itemSAP = $sap->query("SELECT TOP 1 U_R2W_APROVAITEM FROM OITM WHERE ItemCode = '{$value['codSAP']}'");

            // if ((!empty($itemSAP) && $itemSAP[0]['U_R2W_APROVAITEM'] == 'Y') && !isset($value->deleted)) {
            if (!isset($value->deleted)) {
              $valid_approver = Lofted::where("cost_center_id", $value['costCenter'])
                ->where('docNum', '=', Lofted::PURCHASE_ORDER)
                ->where('first', '<=', $obj->docTotal)
                ->where('last', '>=', $obj->docTotal)
                ->get()
                ->last();

              if (!empty($valid_approver)) {
                $needApproval = true;
                $item->lofted_approveds_id = $valid_approver->id;
                $item->save();
              }
            }
          }
        }

        // $groups_items = $obj->items->groupBy("costCenter");
        // foreach ($groups_items as $key => $group_items) {
        //   $budgetSAP = $sap->getDb()->table("@A2RORCPC")
        //     ->select("U_A2RVLRORCS")
        //     ->where("U_A2RCC", "=", "{$key}")
        //     ->first();

        //   $group_items_sum = $group_items->sum("lineSum");
        //   if (!empty($budgetSAP) && (float)$group_items_sum > (float)$budgetSAP->U_A2RVLRORCS) {
        //     $valid_approver = Lofted::where("cost_center_id", $key)
        //       ->where('docNum', '=', Lofted::BUDGET_PURCHASE_ORDER)
        //       ->first();

        //     if (!empty($valid_approver)) {
        //       $loftedId = null;
        //       foreach ($group_items as $item) {
        //         $item->lofted_approveds_id = $valid_approver->id;
        //         $item->save();

        //         $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
        //           ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
        //           ->where('docNum', '=', Lofted::BUDGET_PURCHASE_ORDER)
        //           ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
        //           ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
        //           ->orderby('nivel')
        //           ->get();

        //         if (count($search) > 0 && $loftedId != $item->lofted_approveds_id) {
        //           foreach ($search as $key => $value) {
        //             $attributes['idPurchaseOrder'] = $obj->id;
        //             $attributes['idLofted'] = $value->idLofted;
        //             $attributes['idApproverDocuments'] = $value->id;
        //             $attributes['nivel'] = $value->nivel;
        //             $attributes['idUser'] = $value->approverUser;
        //             $attributes['status'] = Approve::STATUS_CLOSE;

        //             Approve::create($attributes);
        //           }
        //           $loftedId = $item->lofted_approveds_id;
        //         }
        //       }
        //       $obj->status = self::STATUS_PENDING;
        //     }
        //   };
        // }

        if ($valid_approver && $auth_user->freeCompra != '1' && $needApproval) {
          $obj->status = self::STATUS_PENDING;
          $obj->idLofted = $valid_approver->id;
          $obj->is_locked = false;
        } else {
          $obj->status = self::STATUS_OPEN;
          $obj->is_locked = true;
        }

        $obj->docTotal = (float)$obj->items()->sum('lineSum') - ($obj->discountPercent ?? 0);
        // $obj->checkIfNeedApprove();
        $obj->save();
        $obj->needApproval = $needApproval;
      }
    } catch (\Throwable $e) {
      $logsError = new logsError();
      $logsError->saveInDB('E9XF', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function checkIfPurchaseIsComplete($value)
  {

    $isComplete = true;

    $itensSolicitacao = ItemR::where('idPurchaseRequest', '=', $value[0]->idPurchaseRequest)->where('quantityPendente', '>', '0')->get();
    if (count($value) != count($itensSolicitacao)) {
      $isComplete = false;
    }

    foreach ($itensSolicitacao as $item) {

      foreach ($value as $valor) {
        if ($valor->itemCode == $item->itemCode) {

          if ($item->quantityPendente != $valor->quantity) {
            $isComplete = false;
          }

          $item->quantityPendente = $item->quantityPendente - $valor->quantity;

          if ($item->quantityPendente < 0) {
            $item->quantityPendente = 0;
          }

          $item->idPurchaseOrders = $valor->idPurchaseOrders;
          $item->save();
        }
      }
    }
    return $isComplete;
  }

  public function saveExpenses($id, $expenses)
  {
    foreach ($expenses as $key => $value) {
      if ($this->checkExpenses($value)) {
        $expense = new Expenses();
        $value["idPurchaseOrder"] = $id;
        $value["distributionRule"] = $value['costCenter'];
        $value["lineTotal"] = is_numeric($value["lineTotal"]) ? $value["lineTotal"] : clearNumberDouble($value["lineTotal"]);
        $expense->create($value);
      }
    }
  }

  private function checkExpenses($value)
  {
    return (!empty($value["expenseCode"]) && !empty($value["lineTotal"]) && !empty($value["tax"]) && !empty($value["project"]) && !empty($value["costCenter"]));
  }

  public function getCashFlowLabel()
  {
    try {
      return CFItems::join('cash_flows', 'cash_flows.id', '=', 'cash_flow_items.idCashFlow')
        ->where(
          [
            'cash_flow_items.idTransation' => $this->id,
            'cash_flow_items.transation' => 'purchase_orders'
          ]
        )
        ->get()[0]->id;
    } catch (\Exception $e) {
      $logsError = new logsError();
      $logsError->saveInDB('E036F', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
      return false;
    }
  }

  public function createCode()
  {
    $busca = DB::select("select top 1 purchase_orders.code from purchase_orders order by purchase_orders.id desc");
    $codigo = '';
    if (empty($busca) || is_null($busca) || $busca == '') {
      $codigo = 'PO00001';
    } else {
      $codigo = $busca[0]->code;
      $codigo++;
    }
    return $codigo;
  }

  private function getExpenses($id)
  {
    $busca = DB::SELECT("SELECT T0.idPurchaseOrder,T0.expenseCode, T0.tax,T0.lineTotal,T0.project, T0.distributionRule,
                          T0.comments, T0.costCenter, T0.costCenter2 from purchase_order_expenses T0
                            WHERE T0.idPurchaseOrder = {$id}");
    if (empty($busca)) {
      return;
    } else {
      return $busca;
    }
  }
  public function saveInSAP($obj)
  {
    try {

      $sap = NewCompany::getInstance()->getCompany();

      if ((int)Config::get('approvePurchaseOrderSAP') && (int)$obj->approval_method === 2) {
        $item = $sap->GetBusinessObject(BoObjectTypes::oDrafts);
        $item->DocObjectCode = BoObjectTypes::oPurchaseOrders;
      } else {
        $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
      }

      $update = false;
      if ($obj->codSAP) {
        $item->GetByKey((string)$obj->codSAP);
        $update = true;
      }

      $contract = Contract::where('code', '=', $obj->contract)->first();
      if (!empty($contract) && $obj->docTotal > (float)$contract->residualAmount) {
        $obj->message = "O valor total do documento não deve exceder o valor residual do contrato";
        $obj->save();
        throw new Exception("O valor total do documento não deve exceder o valor residual do contrato");
      }

      $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
      $item->UserFields->fields->Item("U_R2W_USERNAME")->value = User::find($obj->idUser)->name;
      $item->UserFields->fields->Item("U_APROV_STATUS")->value = 1;

      $item->DocDate = $obj->docDate;
      $item->DocDueDate = $obj->docDueDate;
      $item->TaxDate = $obj->taxDate;
      $item->CardCode = $obj->cardCode;
      $item->PaymentGroupCode = $obj->paymentTerms;
      $item->Comments = "Pedido de compra WEB: $obj->code - $obj->comments";
      $item->SalesPersonCode = $obj->buyer ?? -1;
      $item->TransportationCode = (int)$obj->incoTerm;

      $check = $this->getExpenses($obj->id);
      if ($check) {
        foreach ($check as $key => $value) {
          $item->Expenses->ExpenseCode = (int) $value->expenseCode;
          $item->Expenses->LineTotal = (float)clearNumberDouble(number_format($value->lineTotal, 2, ',', '.'));
          $item->Expenses->Project = (string) $value->project;
          $item->Expenses->DistributionRule = (string) $value->distributionRule;
          $item->Expenses->DistributionRule2 = (string) $value->costCenter2;
          $item->Expenses->TaxCode = (string) $value->tax;
          $item->Expenses->Remarks = (string) $value->comments;
          $item->Expenses->add();
        }
      }

      $checkR = false;
      $checkQ = false;
      $j = 0;

      if ($obj->isRequest) {
        $OPR = $sap->GetBusinessObject(BoObjectTypes::oPurchaseRequest);

        $checkR = true;
      }
      if ($obj->isQuotation) {
        $oPurchaseQuotation = PurchaseQuotation::find($obj->idQuotation);
        $OPQ = $sap->GetBusinessObject(BoObjectTypes::oPurchaseQuotations);
        $OPQ->GetByKey((int)$oPurchaseQuotation->codSAP);
        $checkQ = true;
      }

      $OPOR1 = $obj->items()->get();

      if (!empty($obj->discountPercent) && (float)$obj->discountPercent > 0) {
        $item->DocTotal = (float)number_format(((float)$OPOR1->sum('lineSum') - $obj->discountPercent), 2, '.', '');
      }

      if (!empty($OPOR1)) {
        foreach ($OPOR1 as $line => $value) {

          $item->Lines->SetCurrentLine($line);

          $item->Lines->ItemCode = (string) $value->itemCode;
          $item->Lines->Quantity = (float) $value->quantity;
          $item->Lines->UnitPrice = (float) $value->price;
          $item->Lines->ProjectCode = (string) $value->codProject;
          #$item->Lines->CostingCode = (String) $value->codCost;
          $item->Lines->CostingCode = (string) $value->costCenter;
          $item->Lines->MeasureUnit = (string) $value->itemUnd;
          $item->Lines->CostingCode2 = (string) $value->costCenter2;
          //$item->Lines->UserFields->Fields->Item("U_A2R_CONTRATOPN")->Value = (string)$value->contract;
          $item->Lines->UserFields->Fields->Item("U_A2R_CONTRATOPN")->Value = (string)$value->contract;

          if ($value->warehouseCode) {
            $item->Lines->WarehouseCode = (string)$value->warehouseCode;
          }
          // $item->Lines->Usage =  $value->codUse;
          if ($update == false) {
            if ($checkR) {
              $purchase_request = PurchaseRequest::find($value->idPurchaseRequest);
              if (isset($purchase_request) && $purchase_request->codSAP) {
                $OPR->GetByKey((int)$purchase_request->codSAP);
                $p_item = ItemR::find($value->idItemPurchaseRequest);

                if (is_null($p_item->lineNum)) {
                  $p_item->updateLineNum();
                }

                if (!empty($value->idItemPurchaseRequest)) {
                  $itemSAP = $this->getItemPurchaseRSAP($OPR->DocNum, $value->idItemPurchaseRequest);
                  $item->Lines->BaseEntry = (int)$itemSAP['DocEntry'];
                  $item->Lines->BaseType = (int)1470000113;
                  $item->Lines->BaseLine = (int)$p_item->lineNum;
                }
                $item->Lines->Quantity = (float) $value->quantity;
              }
            } else if ($checkQ) {
              $c_item = ItemQ::select('lineNum')->where('id', $value->idItemPurchaseQuotation)->first();
              $item->Lines->BaseEntry = (int)$OPQ->DocNum;
              $item->Lines->BaseType = (int)540000006;
              $item->Lines->BaseLine = (int)$c_item->lineNum;
            } else {
              $item->Lines->UserFields->Fields->Item("U_R2W_ID")->Value = (string)$value->id;
            }
            $item->Lines->UserFields->Fields->Item("U_ContaOrcameto")->Value = (string)$value->accounting_account;
          }
          $check = $this->getExpenses($value->id);

          if (!empty($check)) {
            foreach ($check as $keyCheck => $vCheck) {
              $item->Lines->Expenses->ExpenseCode = $vCheck->expenseCode;
              $item->Lines->Expenses->LineTotal = (float) $vCheck->lineTotal;
              $item->Lines->Expenses->Project = $value->codProject;
              $item->Lines->Expenses->DistributionRule = $value->costCenter;
              $item->Lines->Expenses->CostingCode = $value->costCenter;
              $item->Lines->Expenses->CostingCode2 = $value->costCenter2;
              $item->Lines->Expenses->Add();
            }
          }

          $value->lineNum = $item->Lines->LineNum;
          $value->save();

          $item->Lines->Add();
        }

        $deletedItems = $obj->items()->where('status', '3')->orderBy('id', 'desc')->get();

        foreach ($deletedItems as $key => $deletedItem) {
          $item->Lines->SetCurrentLine((int)$deletedItem->lineNum);
          $item->Lines->Delete();
        }
      }

      if ($update) {
        $ret = $item->Update();
      } else {
        $ret = $item->Add();
      }

      if ($ret !== 0) {
        $logsErro = new logsError();
        $logsErro->saveInDB('E0082', 'Line 271', $sap->GetLastErrorDescription());
        $obj->message = $sap->GetLastErrorDescription();
        $obj->is_locked = false;
        $obj->save();
      } else {
        $obj->codSAP = $sap->GetNewObjectKey();
        $obj->is_locked = false;
        $obj->message = NULL;
        $obj->sync_at = new DateTime();
        $obj->save();

        $obj->items()->where('status', '3')->delete();

        $uploads = Upload::where('idReference', $obj->id)->where('reference', 'purchase_orders')->first();
        if (!empty($uploads)) {
          LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }

        if ((int)$obj->status === (int)self::STATUS_CANCEL) {
          $obj->cenceledInSAP($obj);
        }
      }
    } catch (\Throwable $e) {
      $logsErro = new logsError();
      $logsErro->saveInDB('E0082', $e->getFile() . ' | ' . $e->getLine(), "ID: {$obj->id} -> " . $e->getMessage());
      $obj->message = $e->getMessage();
      $obj->is_locked = false;
      $obj->save();
    }
  }

  public function cenceledInSAP($obj, $justification)
  {
    try {
      
      $obj->justification = $justification;

      if (empty($obj->codSAP) || is_null($obj->codSAP)) {
        $obj->is_locked = false;
        $obj->dbUpdate = false;
        $obj->status = self::STATUS_CANCEL;
        $obj->save();
      } else {

        $sap = new Company(false);
        $sapQuery = $sap->query("SELECT DocStatus, CANCELED FROM OPOR WHERE DocNum = $obj->codSAP");

        if (!empty($sapQuery) && $sapQuery[0]['CANCELED'] == 'Y' && $sapQuery[0]['DocStatus'] == 'C') {
          $obj->is_locked = false;
          $obj->dbUpdate = false;
          $obj->status = self::STATUS_CANCEL;
          $obj->save();
          return;
        }

        $sap = NewCompany::getInstance()->getCompany();
        $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);

        if ($opor->GetByKey((string) $obj->codSAP)) {
          if ($opor->Cancel === 0) {
            $obj->is_locked = false;
            $obj->dbUpdate = false;
            $obj->status = self::STATUS_CANCEL;
            $obj->sync_at = new DateTime();
            $obj->save();
          } else {
            $obj->message = $sap->GetLastErrorDescription();
            $obj->is_locked = true;
            $obj->save();
          }
        }
      }
    } catch (\Exception $e) {
      $obj->is_locked = false;
      $obj->message = $e->getMessage();
      $obj->dbUpdate = true;
      $obj->save();
    }
  }

  public function updateUpload()
  {
    $attachment = Upload::where('reference', '=', 'purchase_orders')
      ->where('idReference', '=', $this->id)
      ->first();

    if (!is_null($attachment)) {
      $sap = NewCompany::getInstance()->getCompany();
      $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
      $item->GetByKey((string)$this->codSAP);

      $codeAttachment = $attachment->saveInSAP();

      if (!is_null($codeAttachment)) {
        $item->AttachmentEntry = $codeAttachment;
      }
      $ret = $item->Update();

      if ($ret !== 0) {
        $this->message = $sap->GetLastErrorDescription();
        $this->save();
      }
    }
  }

  public function updateR2WUploadsFromSAP()
  {
    $sapq = new Company(false);
    $headSAP = $sapq->getDb()->table("OPOR")->where("DocEntry", "=", $this->codSAP)->first();
    if (!empty($headSAP)) {
      $attachments = $sapq->getDb()->table("ATC1")->where("AbsEntry", "=", $headSAP->AtcEntry)->get();
      saveUpload($attachments, $this->table, $this->id, 'SAP');
    }
  }

  public function duplicate($obj)
  {
    try {
      $this->idUser = auth()->user()->id;
      $this->code = $this->createCode();
      $this->cardCode = $obj->cardCode;
      $this->docDate = $obj->docDate;
      $this->docDueDate = $obj->docDueDate;
      $this->taxDate = $obj->taxDate;
      $this->paymentTerms = $obj->paymentTerms;
      //$this->freightDocument = is_null($obj->valorFrete)? 0 : $obj->valorFrete;
      $this->discountPercent = $obj->discountPercent ?? 0;
      $this->contact = $obj->contact;
      // $this->transporter = $obj->transporter;
      $this->buyer = $obj->buyer;
      $this->contract = $obj->contract;

      $this->identification = $obj->identification;
      $this->paindSum = creatPaindSum($obj);
      $this->comments = mb_convert_encoding($obj->obsevacoes ?? ' ', 'UTF-8');
      $this->cardName = $obj->cardName;
      $this->incoTerm = $obj->incoTerm;

      $this->is_locked = false;
      $this->docTotal = $obj->docTotal;
      $this->origem = "R2W";
      $this->status = self::STATUS_OPEN;
      // $valid_approver = Lofted::where('first','<=',$this->docTotal)
      // ->where('last', '>=',$this->docTotal)->get()->last();

      if ($this->save()) {
        $items = Item::where('idPurchaseOrders', $obj->id)->get();

        foreach ($items as $key => $value) {
          $item = new Item();
          $item->saveInDBDuplicate($value, $this->id);
        }
        $expenses = Expenses::where('idPurchaseOrder', '=', $obj->id)->get()->toArray();

        $this->saveExpenses($this->id, $expenses);
      }
    } catch (\Exception $e) {
      $logsError = new LogsError();
      $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
    }
  }

  public function closedInSAP($obj)
  {
    try {

      if (empty($obj->codSAP) || is_null($obj->codSAP)) {
        $obj->is_locked = false;
        $obj->dbUpdate = false;
        $obj->status = self::STATUS_CLOSE;
        $obj->save();
      } else {
        $sap = new Company(false);
        $sapQuery = $sap->query("SELECT DocStatus, CANCELED FROM OPOR WHERE DocNum = $obj->codSAP");

        if (!empty($sapQuery) && $sapQuery[0]['CANCELED'] == 'N' && $sapQuery[0]['DocStatus'] == 'C') {
          $obj->is_locked = false;
          $obj->dbUpdate = false;
          $obj->status = self::STATUS_CLOSE;
          $obj->save();
          return;
        }

        $sap = NewCompany::getInstance()->getCompany();
        $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);

        if ($opor->GetByKey((string) $obj->codSAP)) {
          if ($opor->Close === 0) {
            $obj->is_locked = false;
            $obj->dbUpdate = false;
            $obj->status = self::STATUS_CLOSE;
            $obj->save();
          } else {
            $obj->message = $sap->GetLastErrorDescription();
            $obj->is_locked = true;
            $obj->save();
          }
        }
      }
    } catch (\Exception $e) {
      $obj->is_locked = true;
      $obj->message = $e->getMessage();
      $obj->dbUpdate = true;
      $obj->save();
    }
  }

  public function getTaxFromSAP($docNum)
  {
    $sap = new Company(false);
    return $sap->query("SELECT ISNULL((SELECT SUM (POR4.TaxRate) FROM POR4 WHERE POR4.LineNum=B.LINENUM AND POR4.staType = 10 AND POR4.DocEntry = A.DocEntry), 0.00) AS 'ICMS',
                              ISNULL((SELECT SUM (POR4.TaxRate) FROM POR4 WHERE POR4.LineNum=B.LINENUM AND POR4.staType = 16 AND POR4.DocEntry = A.DocEntry), 0.00) AS 'IPI',
                              ISNULL((SELECT SUM (POR4.TAXSUM) FROM POR4 WHERE POR4.LineNum=B.LINENUM AND POR4.staType = 16 AND POR4.DocEntry = A.DocEntry), 0.00) AS 'VIPI'
                              FROM OPOR A INNER JOIN POR1 B ON A.DOCENTRY =B.DOCENTRY and A.DocNum  = '{$docNum}'");
  }

  function getPartnerName($code)
  {

    $sap = new Company(false);
    $query = $sap->query("SELECT  T0.CardName FROM OCRD T0
      WHERE T0.CardCode = '{$code}'");

    if ($query) {
      foreach ($query as $partner) {
        return $partner['CardName'];
      }
    } else {
      return "";
    }
  }
  function getPartnerIdentification($code)
  {

    $sap = new Company(false);
    $query = $sap->query("SELECT top 1 TaxId0,TaxId4 FROM OCRD left join CRD7 on CRD7.CardCode = OCRD.CardCode
      WHERE OCRD.CardCode = '{$code}'");
    if ($query) {
      foreach ($query as $partner) {

        if ($partner['TaxId0'] != '') {
          return $partner['TaxId0'];
        } elseif ($partner['TaxId4'] != '') {
          return $partner['TaxId4'];
        } else {
          return '';
        }
      }
    } else {
      return "";
    }
  }

  public function getItemPurchaseRSAP($docEntry, $U_R2W_ID)
  {
    $itemPR = ItemR::find($U_R2W_ID);
    if (!empty($itemPR)) {
      $sap = new Company(false);
      $query = "SELECT DocEntry, LineNum, ItemCode, Dscription, Quantity, Price, LineTotal, 
          WhsCode, DocDate, Project, OcrCode, U_R2W_ID
          FROM PRQ1 WHERE DocEntry = $docEntry";

      return $sap->query($query)[$itemPR->lineNum];
    }
  }


  function checkQuantityPendentePR($item)
  {

    $purchase_request = PurchaseRequest::find($item->idPurchaseRequest);
    if (!empty($purchase_request)) {
      $items = ItemR::where('idPurchaseRequest', $purchase_request->id)->where('quantityPendente', '>', 0)->get();

      if (count($items) <= 0) {
        $purchase_request->codStatus = $purchase_request::STATUS_PC_G;
        $purchase_request->save();
      } else {
        $purchase_request->codStatus = $purchase_request::STATUS_PENDING;
        $purchase_request->save();
      }
    }
    return $purchase_request;
  }

  private function checkQuantityPendentePQ($idPurchase)
  {

    $itemsPQ = ItemQ::where('id_order', $idPurchase)->get();

    $j = 0;
    foreach ($itemsPQ as $key => $value) {
      ItemQ::where('idItemPurchaseRequest', $value->idItemPurchaseRequest)->update(['quantityPendente' => $value->quantityPendente]);
      ItemQ::where('idItemPurchaseRequest', $value->idItemPurchaseRequest)->where('quantityPendente', 0)->update(['status' => 2]);
    }

    $updatedItemsPQ = ItemQ::where('idPurchaseQuotation', $value->idPurchaseQuotation)->get();

    foreach ($updatedItemsPQ as $key => $item) {
      if ((float)$item->quantityPendente == 0) {
        $j++;
      }
    }

    if (count($updatedItemsPQ) == $j) {
      PurchaseQuotation::where('id', $updatedItemsPQ[0]->idPurchaseQuotation)->orWhere('parent', $updatedItemsPQ[0]->idPurchaseQuotation)->update(['status' => PurchaseQuotation::STATUS_PC_G]);
    } else {
      PurchaseQuotation::where('id', $updatedItemsPQ[0]->idPurchaseQuotation)->orWhere('parent', $updatedItemsPQ[0]->idPurchaseQuotation)->update(['status' => PurchaseQuotation::STATUS_PENDING]);
    }
  }

  public static function uploadsToSAP()
  {
    $sap = NewCompany::getInstance()->getCompany();
    foreach (PurchaseOrder::where('codSAP', '!=', null)->orderBy('id', 'desc')->get() as $index => $value) {
      $attachment = Upload::where('reference', '=', 'purchase_orders')
        ->where('idReference', '=', $value->id)
        ->first();

      if (!empty($attachment)) {
        $codeAttachment = $attachment->saveInSAP();
        if (!is_null($codeAttachment)) {
          $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
          $item->GetByKey((string)$value->codSAP);
          $item->AttachmentEntry = $codeAttachment;
          $item->Update();
        }
      }
    }
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
      "form_url" => route('purchase.order.listOrdersTopNav'),
      "read_document_url" => route('purchase.order.read'),
      "fields" => [
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
          "title" => "Usuário",
          "fieldName" => "name",
          "list" => true
        ],
        [
          "title" => "cardCode",
          "fieldName" => "cardCode",
          "list" => false
        ],
        [
          "title" => "Fornecedor",
          "fieldName" => "cardName",
          "list" => true
        ],
        [
          "title" => "Total",
          "fieldName" => "docTotal",
          "render" => "renderFormatedMoney",
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
      "back_page_url" => route('purchase.order.index'),
      "previous_record_url" => !empty($previousRecord) ? route('purchase.order.read', $previousRecord) : "",
      "create_record_url" => route('purchase.order.create'),
      "next_record_url" => !empty($nextRecord) ? route('purchase.order.read', $nextRecord) : "",
      "print_urls" => $this->getPrintUrls(),
    ];
  }

  private function getPrintUrls(): array
  {
    if ($this->id) {
      $array = [
        "PDF" => route('purchase.order.print', [$this->id, 'pdf']),
        "EXCEL" => route('purchase.order.print', [$this->id, 'excel'])
      ];

      if (checkAccess('purchase_order_budget_relatory')) {
        $array["PDF - Orçamento"] = route('purchase.order.print', [$this->id, 'pdf-budget']);
      }

      return $array;
    }
    return [];
  }

  // private function checkIfNeedApprove()
  // {
  //   $groups_items = $this->items->where("status", "<>", "3")->groupBy(["costCenter", "costCenter2", "accounting_account"]);
  //   foreach ($groups_items as $key => $group_items) {
  //     if ($key == "1.0") {
  //       foreach ($group_items as $cost_center_code2 => $group_item) {
  //         foreach($group_item as $acct => $group_account){
  //           $this->checkAproveBudgetCCenter($key, $cost_center_code2, $group_account, $acct);
  //         }
  //       }
  //     } else {
  //       foreach ($group_items as $cost_center_code => $group_item) {
  //         foreach($group_item as $acct => $group_account){
  //           $this->checkAproveBudgetCCenter($cost_center_code, null, $group_account, $acct);
  //         }
  //       }
  //     }
  //   }
  // }

  // private function checkAproveBudgetCCenter(String $cost_center_code, String $cost_center_code2 = null, $items, $acct)
  // {
  //   $sap = new Company(false);

  //   $budgetCostCenterSearch = $cost_center_code2 ?? $cost_center_code;
  //   $budgetSAP = $sap->getDb()->table("@A2RORCPC")
  //     ->select("U_A2RVLROPC", "U_A2RVLRORCU")
  //     ->where("Name", "=", $acct)
  //     ->where("U_A2RCC", "=", "{$budgetCostCenterSearch}")
  //     ->where(function (Builder $builder) {
  //       $builder->whereDate("U_A2RDIOPC", "<=", date("Y-m-d"))
  //         ->whereDate("U_A2RDFOPC", ">=", date("Y-m-d"));
  //     })
  //     ->orderBy("U_A2RDFOPC",  "ASC")
  //     ->first();

  //   $residualAmount = $budgetSAP->U_A2RVLROPC - $budgetSAP->U_A2RVLRORCU;

  //   $group_items_sum = $items->sum("lineSum");
  //   if (!empty($budgetSAP) && (float)$group_items_sum > (float)$residualAmount) {

  //     if(!empty($cost_center_code2)){
  //       $valid_approver = Lofted::where("cost_center_2_id", $cost_center_code2)
  //         ->where('docNum', '=', Lofted::BUDGET_PURCHASE_ORDER)
  //         ->first();
  //     }else{
  //       $valid_approver = Lofted::where("cost_center_id", $cost_center_code)
  //         ->where('docNum', '=', Lofted::BUDGET_PURCHASE_ORDER)
  //         ->first();
  //     }

  //     if (!empty($valid_approver)) {
  //       foreach ($items as $item) {
  //         $item->lofted_approveds_id = $valid_approver->id;
  //         $item->save();
  //       }

  //       $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
  //         ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
  //         ->where('docNum', '=', Lofted::BUDGET_PURCHASE_ORDER)
  //         ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
  //         ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
  //         ->orderby('nivel')
  //         ->get();

  //       if (count($search) > 0) {
  //         foreach ($search as $value) {
  //           $attributes['idPurchaseOrder'] = $this->id;
  //           $attributes['idLofted'] = $value->idLofted;
  //           $attributes['idApproverDocuments'] = $value->id;
  //           $attributes['nivel'] = $value->nivel;
  //           $attributes['idUser'] = $value->approverUser;
  //           $attributes['status'] = Approve::STATUS_CLOSE;

  //           Approve::create($attributes);
  //         }
  //       }
  //       $this->is_locked = false;
  //       $this->status = self::STATUS_PENDING;
  //     }
  //   };
  // }
}
