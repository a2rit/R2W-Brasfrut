<?php

namespace App\Modules\Purchase\Models\AdvanceProvider;

use App\Modules\Purchase\Models\AdvanceProvider\Items;
use App\Modules\Purchase\Models\AdvanceProvider\Payments;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseOrder\Item as ItemPO;
use Illuminate\Database\Eloquent\Model;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\DownPaymentTypeEnum;
use Illuminate\Support\Facades\DB;
use App\logsError;
use App\Upload;
use App\Jobs\LinkUploadsInDocument;
use App\Jobs\Queue;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdvanceProvider extends Model
{
    protected $table = 'advance_provider';

    const STATUS_CLOSE = 0;
    const STATUS_OPEN = 1;
    const STATUS_REFUND = 2;

    const TEXT_STATUS = [
        '2' => "ESTORNADO",
        '1' => 'ABERTO',
        '0' => 'FECHADO'
    ];

    const STATUS_SAP = [
        'O' => '1',
        'C' => '2',
    ];

    /**
     * Get all of the comments for the AdvanceProvider
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Items::class, 'idAdvanceProvider', 'id');
    }

    public function getNextAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '>', $this->id)->orderBy('id', 'asc')->first() : $this->select('id')->orderBy('id', 'desc')->first();
    }

    public function getPreviousAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '<', $this->id)->orderBy('id', 'desc')->first() : $this->select('id')->orderBy('id', 'asc')->first();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payments::class, 'idAdvanceProvider', 'id');
    }

    public function getDocTotal()
    {
        return $this->items()->sum(DB::raw('quantity * price'));
    }


    public function saveInDB($request)
    {
        try {
            $this->code = $this->createCode();
            $this->cardCode = $request['cardCode'];
            $this->docDate = $request['dataDocumento'];
            $this->docDueDate = $request['dataVencimento'];
            $this->taxDate = $request['dataLancamento'];
            $this->idUser = auth()->user()->id;
            $this->comments = mb_convert_encoding((string)$request['observacoes'], 'UTF-8');
            $this->paymentCondition = $request['condPagamentos'];
            $this->dpmTotal = is_numeric($request['totalAdiantado']) ? $request['totalAdiantado'] : clearNumberDouble($request['totalAdiantado']);
            $this->docTotal = is_numeric($request['totalSemDesconto']) ? $request['totalSemDesconto'] : clearNumberDouble($request['totalSemDesconto']);
            $this->paymentForm = '';
            $this->status = 1;
            $this->veiculo = $request['veiculo'];
            $this->ticket = $request['ticket'];

            if ($this->dpmTotal > 0) {
                $this->dpmPrcnt = $this->dpmTotal / $this->docTotal;
            }

            if ($this->save()) {

                foreach ($request['requiredProducts'] as $key => $value) {
                    $item = new Items();
                    $item->saveInDB($this->id, $value);
                }

                if (!empty($request['conta_dinheiro']) || !empty($request['conta_transferencia'])) {
                    $pay = new Payments();
                    $pay->saveInDB($request, $this->id);
                }
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
            $logsError = new LogsError();
            $logsError->saveInDB('APE0001', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function updateInDB($request, $obj)
    {
        try {
            if (empty($obj->codSAP)) {
                $obj->docDueDate = $request['dataVencimento'];
                $obj->docDate = $request['dataDocumento'];
                $obj->taxDate = $request['dataLancamento'];
                $obj->comments = mb_convert_encoding((string)$request['observacoes'], 'UTF-8');
                $obj->paymentCondition = $request['condPagamentos'];
                $obj->paymentForm = '';
                $obj->dpmTotal = is_numeric($request['totalAdiantado']) ? $request['totalAdiantado'] : clearNumberDouble($request['totalAdiantado']);
                $obj->docTotal = is_numeric($request['totalSemDesconto']) ? $request['totalSemDesconto'] : clearNumberDouble($request['totalSemDesconto']);
                $obj->status = 1;

                if ($obj->dpmTotal > 0) {
                    $obj->dpmPrcnt = $obj->dpmTotal / $obj->docTotal;
                }

                if ($obj->save()) {
                    Payments::where("idAdvanceProvider", $obj->id)->delete();
                    if (!empty($request['conta_dinheiro']) || !empty($request['conta_transferencia'])) {
                        $pay = new Payments();
                        $pay->saveInDB($request, $obj->id);
                    }

                    Items::where("idAdvanceProvider", $obj->id)->delete();
                    foreach ($request['requiredProducts'] as $key => $value) {
                        $item = new Items();
                        $item->saveInDB($obj->id, $value);
                    }
                }
            } else if (!empty($obj->codSAP)) {
                $obj->docDueDate = $request['dataVencimento'];
                $obj->save();
            }
        } catch (\Throwable $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('APE0001', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function createCode()
    {
        $busca = DB::select("select top 1 advance_provider.code from advance_provider order by advance_provider.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'AP00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function saveInSAP(AdvanceProvider $obj)
    {
        try {
            $obj = $this->find($obj->id);
            $sap = NewCompany::getInstance()->getCompany();
            /** @var IDocuments $ap */
            $ap = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDownPayments);
            $update = false;

            if ($obj->codSAP) {
                $ap->GetByKey((int)$obj->codSAP);
                $update = true;
            }

            $po_vinculated = false;

            if ($obj->idPurchaseOrder) {
                $po = PurchaseOrder::find($obj->idPurchaseOrder);
                $oPOrder = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
                $oPOrder->GetByKey((int) $po->codSAP);
                $po_vinculated = true;
            }

            $ap->DocDate = $obj->docDate;
            $ap->DocDueDate = $obj->docDueDate;
            $ap->TaxDate = $obj->taxDate;
            $ap->CardCode = $obj->cardCode;
            $ap->DownPaymentType = DownPaymentTypeEnum::dptInvoice;
            $ap->PaymentGroupCode = $obj->paymentCondition;
            $ap->Comments =  'Adiantamento de fornecedor WEB: ' . $obj->code . ' - ' . $obj->comments;
            $ap->DocTotal = (float)(float)$obj->dpmTotal;

            $line = 0;
            foreach ($obj->items()->get() as $value) {
                $ap->Lines->ItemCode = (string) $value->itemCode;
                $ap->Lines->MeasureUnit = (string) $value->itemUnd;
                $ap->Lines->Quantity = (float) $value->quantity;
                $ap->Lines->UnitPrice = (float) $value->price;
                $ap->Lines->CostingCode = (string) $value->distrRule;
                $ap->Lines->CostingCode2 = (string) $value->distrRule2;
                $ap->Lines->ProjectCode = (string) $value->project;
                if ($po_vinculated) {
                    $ap->Lines->BaseEntry = $oPOrder->DocEntry;
                    $ap->Lines->BaseType = (int) $oPOrder->DocObjectCodeEx;
                    $ap->Lines->BaseLine = $line;
                }
                $ap->Lines->Add();
                $line++;
            }

            //$ap->UserFields->fields->Item("U_R2W_CODE")->value =  $obj->code;
            $ap->UserFields->fields->Item("U_R2W_USERNAME")->value = getUserName($obj->idUser);
            $ap->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $ap->UserFields->fields->Item("U_R2W_Veiculo")->value = $obj->veiculo;
            $ap->UserFields->fields->Item("U_NTicket")->value = $obj->ticket;

            if ($update) {
                $ret = $ap->Update();
            } else {
                $ret = $ap->Add();
            }

            if ($ret != 0) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('APE0004', "saveInSAP", $sap->GetLastErrorDescription());
                $obj->message = $sap->GetLastErrorDescription();
                $obj->save();
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = '';
                $obj->save();

                if (!empty($obj->payment)) {
                    $obj->payment->saveInSAP();
                }

                $uploads = Upload::where('idReference', $obj->id)->where('reference', 'advance_provider')->first();
                if (!empty($uploads)) {
                    LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }
            }
            return;
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('APE0003', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function updateUpload()
    {

        try {

            $attachment = Upload::where('reference', '=', 'advance_provider')
                ->where('idReference', '=', $this->id)
                ->first();

            if (!is_null($attachment)) {

                $sap = NewCompany::getInstance()->getCompany();
                $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDownPayments);
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
        } catch (\Throwable $e) {
            $this->message = (string)$e->getMessage();
            $this->save();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('PRUP01', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
        }
    }

    public function copyFromPurchaseOrder($id)
    {

        try {
            $purchase_order = PurchaseOrder::find($id);
            $purchase_order_items = ItemPO::where('idPurchaseOrders', $purchase_order->id)->get();
            $this->code = $this->createCode();
            $this->idPurchaseOrder = $purchase_order->id;
            $this->cardCode = $purchase_order->cardCode;
            $this->docDate = $purchase_order->docDate;
            $this->docDueDate = $purchase_order->docDueDate;
            $this->taxDate = $purchase_order->taxDate;
            $this->idUser = auth()->user()->id;
            $this->comments = $purchase_order->comments;
            $this->paymentCondition = $purchase_order->paymentTerms;
            $this->paymentForm = '';
            $this->status = 1;
            if ($this->save()) {
                try {
                    DB::beginTransaction();
                    foreach ($purchase_order_items as $key => $value) {
                        $item = new Items();
                        $value['preco'] = $value['price'];
                        $value['projeto'] = $value['codProject'];
                        $value['qtd'] = $value['quantity'];
                        $item->saveInDB($this->id, $value);
                    }
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollback();
                    $logsError = new LogsError();
                    $logsError->saveInDB('APE0014', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('APE0001', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public function getItems($id)
    {
        return Items::select("id", "itemCode", "idAdvanceProvider", "quantity", "price", "project", "distrRule", "distrRule2")->where("idAdvanceProvider", $id)->get();
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
            "form_url" => route('purchase.advance.provider.listAdvancesTopNav'),
            "read_document_url" => route('purchase.advance.provider.read'),
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
                    "title" => "Usuário",
                    "fieldName" => "name",
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
            "back_page_url" => route('purchase.advance.provider.index'),
            "previous_record_url" => !empty($previousRecord) ? route('purchase.advance.provider.read', $previousRecord) : "",
            "create_record_url" => route('purchase.advance.provider.create'),
            "next_record_url" => !empty($nextRecord) ? route('purchase.advance.provider.read', $nextRecord) : "",
            "print_urls" => $this->getPrintUrls(),
        ];
    }

    public function getPrintUrls(): array
    {
        if ($this->codSAP) {
            return [
                "PDF" => route('purchase.advance.provider.print', $this->codSAP)
            ];
        }
        return [];
    }
}
