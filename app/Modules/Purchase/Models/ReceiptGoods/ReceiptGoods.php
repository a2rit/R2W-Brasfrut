<?php

namespace App\Modules\Purchase\Models\ReceiptGoods;

use App\Modules\Partners\Models\Partner\Catalog;
use App\Modules\Purchase\Jobs\ReceiptGoodsToDB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\LogsError;
use App\CFItems;
use App\User;
use Illuminate\Http\Request;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\ReceiptGoods\Items;
use App\Modules\Purchase\Models\ReceiptGoods\Expenses;
use App\Modules\Purchase\Models\ReceiptGoods\Tax;
use App\Modules\Purchase\Jobs\ReceiptGoodsCopyToSAP;
use App\Modules\Purchase\Models\XML\Import;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoAPARDocumentTypes;

/**
 * App\receiptGoods
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $idPurchaseOrders
 * @property string|null $codSAP
 * @property string $code
 * @property string $codPN
 * @property string $docDate
 * @property string $docDueDate
 * @property string $taxDate
 * @property string $paymentTerms
 * @property string|null $freight
 * @property string|null $amount
 * @property string|null $branch
 * @property string|null $coin
 * @property string|null $comments
 * @property string $status
 * @property string|null $paindSum
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCodPN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereFreight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereIdPurchaseOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods wherePaindSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereTaxDate($value)
 * @property string $type_tax
 * @property string $number_nf
 * @property string|null $serie
 * @property string|null $sserie
 * @property string|null $model_tax
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereModelTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereNumberNf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereSserie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereTypeTax($value)
 * @property string|null $message
 * @property bool $is_locked
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods whereMessage($value)
 * @property string $cardCode
 * @property float|null $discPrcnt
 * @property float|null $docTotal
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods whereDiscPrcnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods whereDocTotal($value)
 * @property float|null $quotation
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereQuotation($value)
 * @property string|null $cardName
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCardName($value)
 * @property string $dbUpdate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereDbUpdate($value)
 * @property string|null $identification
 * @property string|null $freightDocument
 * @property float|null $discountPercent
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereFreightDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods whereUpdatedAt($value)
 */
class ReceiptGoods extends Model
{
    public $timestamps = false;
    protected $table = 'receipt_goods';
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;
    const STATUS_CANCEL = 2;

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function saveInDB($request)
    {
        if ($this->checkItemCodeItems($request)) {
            try {
                $this->idUser = isset(auth()->user()->id) ? auth()->user()->id : 1;
                $this->code = $this->createCode();
                $this->cardCode = $request->codPN;
                $this->idPurchaseOrders = $request->idPurchaseOrders;
                $this->identification = $request->identification;
                $this->docDate = $request->dataLancamento;
                $this->docDueDate = $request->dataVencimento;
                $this->taxDate = $request->dataDocumento;
                $this->paymentTerms = $request->condPagamentos;
                $this->freightDocument = is_null($request->valorFrete) ? 0 : $request->valorFrete;
                $this->discountPercent = is_null($request->valorDesconto) ? 0 : $request->valorDesconto;
                $this->branch = is_null($request->valorDesconto) ? 1 : $request->valorDesconto;
                if (workCoin()) {
                    $this->coin = $request->coin;
                }

                $this->paindSum = creatPaindSum($request);
                $this->comments = is_null($request->obsevacoes) ? ' ' : $request->obsevacoes;
                $this->status = self::STATUS_OPEN;
                $this->cardName = $request->cardName;
                if (workQuotation()) {
                    $this->quotation = clearNumberDouble($request->cotacao);
                }

                $this->is_locked = false;
                $this->docTotal = clearNumberDouble($request->docTotal);
                if ($this->save()) {
                    if (workCashFlow()) {
                        $CFItems = new CFItems(); //fluxo de caixa;
                        $CFItems->saveInDB($request->cashFlow, $this->id, 'receipt_goods', $this->docTotal);
                    }
                    $tax = new Tax();
                    $tax->saveInDB($request, $this->id);
                    foreach ($request->requiredProducts as $key => $value) {
                        $item = new Items();
                        $item->saveInDB($value, $this->id);
                    }

                    $this->saveExpenses($this->id, $request);
                }
            } catch (\Exception $e) {
                $logsError = new LogsError();
                $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
                
                throw new \Exception("Estamos processando alguns itens, por favor aguarde");
            }
        } else {
            throw new \Exception("Estamos processando alguns itens, por favor aguarde");
        }

    }

    public function saveInDBFromJob($request)
    {
        if ($this->checkItemCodeItems($request)) {
            try {
                if (!isset($request['condPagamentos'])) {
                    $logsError = new LogsError();
                    $logsError->saveInDB('INP0923', 'Erro ao salvar no R2W', 'Não conseguimos encontrar a condição de pagamento e utilizamos como default a -1 a Vista');
                }
                $this->idUser = isset(auth()->user()->id) ? auth()->user()->id : 1;
                $this->code = $this->createCode();
                $this->cardCode = $request['codPN'];
                $this->identification = $request['identification'];
                $this->docDate = $request['dataLancamento'];
                $this->docDueDate = $request['dataVencimento'];
                $this->taxDate = $request['dataLancamento'];
                $this->paymentTerms = isset($request['condPagamentos']) ? $request['condPagamentos'] : -1;
                $this->freightDocument = isset($request['valorFrete']) ? $request['valorFrete'] : 0 ;
                $this->discountPercent = isset($request['valorDesconto']) ? $request['valorDesconto']: 0;
                $this->branch = isset($request['valorDesconto']) ? $request['valorDesconto'] :  1;
                if (workCoin()) {
                    $this->coin = $request['coin'];
                }

                $this->paindSum = creatPaindSum($request);
                $this->comments = is_null($request['obsevacoes']) ? ' ' : $request['obsevacoes'];
                $this->status = self::STATUS_OPEN;
                $this->cardName = $request['cardName'];
                if (workQuotation()) {
                    $this->quotation = clearNumberDouble($request['cotacao']);
                }

                $this->is_locked = false;
                $this->docTotal = clearNumberDouble($request['docTotal']);
                if ($this->save()) {
                    if (workCashFlow()) {
                        $CFItems = new CFItems(); //fluxo de caixa;
                        $CFItems->saveInDB($request['cashFlow'], $this->id, 'receipt_goods', $this->docTotal);
                    }
                    $tax = new Tax();
                    $tax->saveInDB($request, $this->id);
                    
                    foreach ($request['requiredProducts'] as $key => $value) {
                        $item = new Items();
                        $item->saveInDB($value, $this->id);
                    }
                }
            } catch (\Exception $e) {
                $logsError = new LogsError();
                $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
                throw new \Exception("Estamos processando alguns itens, por favor aguarde 01");
            }
        } else {
            throw new \Exception("Estamos processando alguns itens, por favor aguarde");
        }

    }

    private function checkItemCodeItems($request)
    {
        $check = true;
        foreach ($request['requiredProducts'] as $key => $value) {
            if (!isset($value['codSAP'])) {
                if (!isset(Catalog::where('substitute', '=', $value['codPartners'])->select('itemCode')->first()->itemCode) ||
                    is_null(Catalog::where('substitute', '=', $value['codPartners'])->select('itemCode')->first()->itemCode)) {
                    $check = false;
                }
            }
        }
        return $check;
    }

    public function updateInDB($obj, Request $request)
    {
        try {
            $obj->docDate = $request->dataLancamento;
            $obj->docDueDate = $request->dataVencimento;
            $obj->taxDate = $request->dataDocumento;
            $obj->paymentTerms = $request->condPagamentos;
            $obj->freightDocument = is_null($request->valorFrete) ? 0 : $request->valorFrete;
            $obj->discountPercent = is_null($request->valorDesconto) ? 0 : $request->valorDesconto;
            $obj->branch = is_null($request->valorDesconto) ? 1 : $request->valorDesconto;
            if (workCoin()) {
                $obj->coin = $request->coin;
            }

            $obj->paindSum = creatPaindSum($request);
            $obj->comments = is_null($request->obsevacoes) ? ' ' : $request->obsevacoes;
            $obj->status = self::STATUS_OPEN;

            if (workQuotation()) {
                $obj->quotation = clearNumberDouble($request->cotacao);
            }

            $obj->is_locked = false;
            $obj->docTotal = clearNumberDouble($request->docTotal);

            if ($obj->save()) {
                if (workCashFlow()) {
                    CFItems::where(['idTransation' => $obj->id,
                        'transation' => 'receipt_goods'])->delete();
                    $CFItems = new CFItems(); //fluxo de caixa;
                    $CFItems->saveInDB($request->cashFlow, $obj->id, 'receipt_goods', $obj->docTotal);
                }
                Tax::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_taxes.idReceiptGoods')
                    ->where('receipt_goods.id', $obj->id)->delete();
                $tax = new Tax();
                $tax->saveInDB($request, $obj->id);
                Items::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_items.idReceiptGoods')
                    ->where('receipt_goods.id', $obj->id)->delete();

                foreach ($request->requiredProducts as $key => $value) {
                    $item = new Items();
                    $item->saveInDB($value, $obj->id);
                }
                Expenses::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_expenses.idReceiptGoods')
                    ->where('receipt_goods.id', $obj->id)->delete();

                $this->saveExpenses($obj->id, $request);
            }
        } catch (\Exception $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    public
    function getCashFlowLabel()
    {
        try {
            $item = DB::SELECT("SELECT T0.id FROM cash_flows as T0
          JOIN cash_flow_items as T1 on T0.id = T1.idCashFlow
          WHERE T1.idTransation = '$this->id' and T1.transation = 'receipt_goods'");
            if (isset($item[0]->id)) {
                return $item[0]->id;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('F90f', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return false;
        }
    }

    public
    function saveExpenses($id, $request)
    {
        $check = $this->checkExpenses($request);
        $despImport = true;
        $freight = true;
        $outhers = true;
        $safe = true;
        foreach ($check as $key => $value) {
            $expenses = new Expenses();
            if (isset($check['despImport']) && ($check['despImport']) && ($despImport)) {
                $despImport = false;
                $expenses->idReceiptGoods = $id;
                $expenses->expenseCode = 4;
                $expenses->lineTotal = clearNumberDouble($request->di_total);
                $expenses->project = $request->di_project;
                $expenses->distributionRule = $request->di_role;
                $expenses->tax = $request->di_tax;
                $expenses->comments = $request->di_comments;
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['freight']) && ($check['freight']) && ($freight)) {
                $freight = false;
                $expenses->idReceiptGoods = $id;
                $expenses->expenseCode = 1;
                $expenses->lineTotal = clearNumberDouble($request->freight_total);
                $expenses->project = $request->freight_project;
                $expenses->distributionRule = $request->freight_role;
                $expenses->tax = $request->freight_tax;
                $expenses->comments = $request->freight_comments;
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['outhers']) && ($check['outhers']) && ($outhers)) {
                $outhers = false;
                $expenses->idReceiptGoods = $id;
                $expenses->expenseCode = 3;
                $expenses->lineTotal = clearNumberDouble($request->outhers_total);
                $expenses->project = $request->outhers_project;
                $expenses->distributionRule = $request->outhers_role;
                $expenses->tax = $request->outhers_tax;
                $expenses->comments = $request->outhers_comments;
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['safe']) && ($check['safe']) && ($safe)) {
                $safe = false;
                $expenses->idReceiptGoods = $id;
                $expenses->expenseCode = 2;
                $expenses->lineTotal = clearNumberDouble($request->safe_total);
                $expenses->project = $request->safe_project;
                $expenses->distributionRule = $request->safe_role;
                $expenses->tax = $request->safe_tax;
                $expenses->comments = $request->safe_comments;
                $expenses->save();
                $expenses = new Expenses();
            }
        }

    }

    private
    function checkExpenses($request)
    {
        $expense = [];
        if (isset($request->di_total) && !is_null($request->di_total)
            && isset($request->di_tax) && !is_null($request->di_tax)
            && isset($request->di_project) && !is_null($request->di_project)
            && isset($request->di_role) && !is_null($request->di_role)) {
            $expense['despImport'] = true;
        }
        if (isset($request->freight_total) && !is_null($request->freight_total)
            && isset($request->freight_tax) && !is_null($request->freight_tax)
            && isset($request->freight_project) && !is_null($request->freight_project)
            && isset($request->freight_role) && !is_null($request->freight_role)) {
            $expense['freight'] = true;
        }
        if (isset($request->outhers_total) && !is_null($request->outhers_total)
            && isset($request->outhers_tax) && !is_null($request->outhers_tax)
            && isset($request->outhers_project) && !is_null($request->outhers_project)
            && isset($request->outhers_role) && !is_null($request->outhers_role)) {
            $expense['outhers'] = true;
        }
        if (isset($request->safe_total) && !is_null($request->safe_total)
            && isset($request->safe_tax) && !is_null($request->safe_tax)
            && isset($request->safe_project) && !is_null($request->safe_project)
            && isset($request->safe_role) && !is_null($request->safe_role)) {
            $expense['safe'] = true;
        }

        return $expense;
    }

    private
    function getItens($id)
    {
        return Items::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_items.idReceiptGoods')
            ->select('receipt_goods_items.itemCode', 'receipt_goods_items.quantity', 'receipt_goods_items.price', 'receipt_goods_items.lineSum', 'receipt_goods_items.codUse', 'receipt_goods_items.codProject', 'receipt_goods_items.codCost', 'receipt_goods_items.codCFOP')
            ->where('receipt_goods.id', '=', $id)
            ->get();

    }

    private
    function getExpenses($id)
    {
        $expenses = Expenses::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_expenses.idReceiptGoods')
            ->select('receipt_goods_expenses.expenseCode', 'receipt_goods_expenses.tax', 'receipt_goods_expenses.lineTotal', 'receipt_goods_expenses.project', 'receipt_goods_expenses.distributionRule', 'receipt_goods_expenses.comments')
            ->where('receipt_goods.id', '=', $id)
            ->get();
        if (count($expenses) > 0) {
            return $expenses;
        } else {
            return false;
        }
    }

    private
    function getTax($id)
    {
        return Tax::join('receipt_goods', 'receipt_goods.id', '=', 'receipt_goods_taxes.idReceiptGoods')
            ->select('receipt_goods_taxes.seqCode', 'receipt_goods_taxes.sequenceSerial', 'receipt_goods_taxes.seriesStr', 'receipt_goods_taxes.subStr', 'receipt_goods_taxes.sequenceModel')
            ->where('idReceiptGoods', '=', $id)
            ->get();
    }

    public
    function saveInSAP($obj, $comments = false, $xml = false, $idXML = false)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
            $copy = false;
            $item->DocDate = $obj->docDate;
            $item->DocDueDate = $obj->docDueDate;
            $item->TaxDate = $obj->taxDate;
            $item->CardCode = $obj->cardCode;
            $item->PaymentGroupCode = $obj->paymentTerms;
            $item->Comments = compressText($obj->comments, 200);
            $item->SequenceCode = isset($this->getTax($obj->id)[0]->seqCode) ? $this->getTax($obj->id)[0]->seqCode : '';
            $item->SequenceSerial = (String)$this->getTax($obj->id)[0]->sequenceSerial;
            $item->SeriesString = (String)$this->getTax($obj->id)[0]->seriesStr;
            $item->SubSeriesString = (String)$this->getTax($obj->id)[0]->subStr;
            $item->SequenceModel = (String)$this->getTax($obj->id)[0]->sequenceModel;

            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);

            if (!is_null($obj->valorDesconto)) {
                $item->DiscountPercent = $obj->discPrcnt;
            }

            $check = $this->getExpenses($obj->id);
            if ($check) {
                foreach ($check as $key => $value) {
                    $item->Expenses->ExpenseCode = (int)$value->expenseCode;
                    $item->Expenses->LineTotal = clearNumberDouble(number_format($value->lineTotal, 2, ',', '.'));
                    $item->Expenses->Project = (String)$value->project;
                    $item->Expenses->DistributionRule = (String)$value->distributionRule;
                    $item->Expenses->TaxCode = (String)$value->tax;
                    $item->Expenses->Remarks = (String)$value->comments;
                    $item->Expenses->add();
                }
            }

            $getItem = $this->getItens($obj->id);
            $j = 0;
            foreach ($getItem as $key => $value) {
                $item->Lines->setCurrentLine($j);
                $item->Lines->ItemCode = (String)$value->itemCode;
                $item->Lines->Quantity = (Double)$value->quantity;
                $item->Lines->UnitPrice = (Double)$value->price;
                $item->Lines->ProjectCode = (String)$value->codProject;
                $item->Lines->CostingCode = (String)$value->codCost;
                $item->Lines->Usage = (String)$value->codUse;
                $item->Lines->CFOPCode = (String)$this->validCFOP($value->codCFOP, $xml);
                $item->Lines->Add();
                $j++;
            }
            if ($item->Add() !== 0) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('E0002', 'Cadastro de item no SAP', $sap->GetLastErrorDescription());
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = true;
                $obj->save();
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = "Salvo no SAP com sucesso.";
                $obj->is_locked = false;
                $obj->save();

                if ($idXML) {
                    $import = Import::find($idXML);
                    $import->status = Import::STATUS_CLOSE;
                    $import->codSAP = $sap->GetNewObjectKey();
                    $import->document = 'oPurchaseDeliveryNotes';
                    $import->save();
                }
            }

        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0074', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
            throw new \Exception($e->getMessage());
        }
    }

    public
    function saveCopyXMLInSAP($obj, $idXML)
    {
        try {
            $company = NewCompany::getInstance();
            $sap = $company->getCompany();

            $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
            $item->DocDate = $obj->docDate;
            $item->DocDueDate = $obj->docDueDate;
            $item->TaxDate = $obj->taxDate;
            $item->CardCode = $obj->cardCode;
            $item->PaymentGroupCode = $obj->paymentTerms;
            $item->Comments = compressText($obj->comments, 200);
            $item->SequenceCode = isset($this->getTax($obj->id)[0]->seqCode) ? $this->getTax($obj->id)[0]->seqCode : '';
            $item->SequenceSerial = (String)$this->getTax($obj->id)[0]->sequenceSerial;
            $item->SeriesString = (String)$this->getTax($obj->id)[0]->seriesStr;
            $item->SubSeriesString = (String)$this->getTax($obj->id)[0]->subStr;
            $item->SequenceModel = (String)$this->getTax($obj->id)[0]->sequenceModel;

            $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
            $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);

            if (!is_null($obj->valorDesconto)) {
                $item->DiscountPercent = $obj->discPrcnt;
            }

            $check = $this->getExpenses($obj->id);
            if ($check) {
                foreach ($check as $key => $value) {
                    $item->Expenses->ExpenseCode = (int)$value->expenseCode;
                    $item->Expenses->LineTotal = clearNumberDouble(number_format($value->lineTotal, 2, ',', '.'));
                    $item->Expenses->Project = (String)$value->project;
                    $item->Expenses->DistributionRule = (String)$value->distributionRule;
                    $item->Expenses->TaxCode = (String)$value->tax;
                    $item->Expenses->Remarks = (String)$value->comments;
                    $item->Expenses->add();
                }
            }

            $getItem = $this->getItens($obj->id);
            $j = 0;
            foreach ($getItem as $key => $value) {
                $item->Lines->setCurrentLine($j);
                $item->Lines->ItemCode = (String)$value->itemCode;
                $item->Lines->Quantity = (Double)$value->quantity;
                $item->Lines->UnitPrice = (Double)$value->price;
                $item->Lines->ProjectCode = (String)$value->codProject;
                $item->Lines->CostingCode = (String)$value->codCost;
                $item->Lines->Usage = (String)$value->codUse;
                $item->Lines->CFOPCode = (String)$this->validCFOP($value->codCFOP, false);
                $item->Lines->Add();
                $j++;
            }
            if ($item->Add() !== 0) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('E0002', 'Cadastro de item no SAP', $sap->GetLastErrorDescription());
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = true;
                $obj->save();
                ReceiptGoodsCopyToSAP::dispatch($obj, $idXML)->delay(now()->addMinutes(5));
            } else {
                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = "Salvo no SAP com sucesso.";
                $obj->is_locked = false;
                $obj->save();

                if ($idXML) {
                    $import = Import::find($idXML);
                    $import->status = Import::STATUS_CLOSE;
                    $import->codSAP = $sap->GetNewObjectKey();
                    $import->document = 'oPurchaseDeliveryNotes';
                    $import->save();
                }
            }

        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0074', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
            ReceiptGoodsCopyToSAP::dispatch($obj, $idXML)->delay(now()->addMinutes(5));
        }
    }

    public
    function saveCopyInSAP($obj, $idXML = false)
    {
        $OPOR = PurchaseOrder::find($obj->idPurchaseOrders);
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $po = $sap->GetBusinessObject(BoObjectTypes::oPurchaseOrders);
            if ($po->GetByKey((int)$OPOR->codSAP)) {
                $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
                $item->CardCode = $po->CardCode;
                $item->CardName = $po->CardName;
                $item->DocDate = $obj->docDate;
                $item->DocDueDate = $obj->docDueDate;
                $item->TaxDate = $obj->taxDate;

                $item->SequenceCode = $this->getTax($obj->id)[0]->seqCode;
                $item->SequenceSerial = (String)$this->getTax($obj->id)[0]->sequenceSerial;
                $item->SeriesString = (String)$this->getTax($obj->id)[0]->seriesStr;
                $item->SubSeriesString = (String)$this->getTax($obj->id)[0]->subStr;
                $item->SequenceModel = (String)$this->getTax($obj->id)[0]->sequenceModel;
                $item->Comments = compressText($obj->comments, 200);

                $check = $this->getExpenses($obj->id);
                if ($check) {
                    foreach ($check as $key => $value) {
                        $item->Expenses->ExpenseCode = (int)$value->expenseCode;
                        $item->Expenses->LineTotal = clearNumberDouble($value->lineTotal);
                        $item->Expenses->Project = (String)$value->project;
                        $item->Expenses->DistributionRule = (String)$value->distributionRule;
                        $item->Expenses->TaxCode = (String)$value->tax;
                        $item->Expenses->Remarks = (String)$value->comments;
                        $item->Expenses->add();
                    }
                }

                $getItem = $this->getItens($obj->id);
                $j = 0;
                foreach ($getItem as $key => $value) {
                    $item->Lines->setCurrentLine($j);
                    $item->Lines->ItemCode = (String)$value->itemCode;
                    $item->Lines->Quantity = (Double)$value->quantity;
                    $item->Lines->UnitPrice = (Double)$value->price;
                    $item->Lines->ProjectCode = (String)$value->codProject;
                    $item->Lines->CostingCode = (String)$value->codCost;
                    $item->Lines->Usage = (String)$value->codUse;
                    $item->Lines->CFOPCode = (String)$value->codCFOP;
                    $item->Lines->BaseEntry = $po->DocNum;
                    $item->Lines->BaseType = BoAPARDocumentTypes::bodt_PurchaseOrder;
                    $item->Lines->BaseLine = $j;
                    $item->Lines->Add();
                    $j++;
                }

                $item->UserFields->fields->Item("U_R2W_CODE")->value = $OPOR->code;
                $item->UserFields->fields->Item("U_R2W_USERNAME")->value = auth()->user()->name;

                if ($item->Add() !== 0) {
                    $logsErrors = new LogsError();
                    $logsErrors->saveInDB('E0002', 'Cadastro de item no SAP', $sap->GetLastErrorDescription());
                    $obj->message = $sap->GetLastErrorDescription();
                    $obj->is_locked = true;
                    $obj->save();
                } else {
                    $obj->codSAP = $sap->GetNewObjectKey();
                    $obj->message = "Salvo no SAP com sucesso.";
                    $obj->is_locked = false;
                    $obj->save();
                    $OPOR->status = self::STATUS_CLOSE;
                    $OPOR->save();
                    if ($idXML) {
                        $import = Import::find($idXML);
                        $import->status = Import::STATUS_CLOSE;
                        $import->codSAP = $sap->GetNewObjectKey();
                        $import->document = 'oPurchaseDeliveryNotes';
                        $import->save();
                    }

                }
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0074', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
            throw new \Exception($e->getMessage());
        }

    }

    public
    function closedInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
            if (empty($obj->codSAP) || is_null($obj->codSAP)) {
                $obj->is_locked = false;
                $obj->dbUpdate = false;
                $obj->status = self::STATUS_CLOSE;
                $obj->save();
            } else {
                if ($opor->GetByKey((string)$obj->codSAP)) {
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

    public
    function canceledInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();
            $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
            if (empty($obj->codSAP) || is_null($obj->codSAP)) {
                $obj->is_locked = false;
                $obj->dbUpdate = false;
                $obj->status = self::STATUS_CANCEL;
                $obj->save();
            } else {
                if ($opor->GetByKey((string)$obj->codSAP)) {
                    if ($opor->Cancel === 0) {
                        $obj->is_locked = false;
                        $obj->dbUpdate = false;
                        $obj->status = self::STATUS_CANCEL;
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

    private
    function validCFOP($cfop, $xml)
    {
        $aux = $cfop;
        if ($xml) {
            switch (substr($cfop, 0, 1)) {
                case 5:
                    $aux = '1101';
                    break;
                case 6:
                    $aux = '2102';
                    break;
                case 7:
                    $aux = '3104';
                    break;
            }
        }
        return $aux;
    }

    private
    function getNameUser($id)
    {
        return User::find($id)->name;
    }

    private
    function checkEspenses($request, $codSAP)
    {
        if (isset($request->dividas) && (array_key_exists($codSAP, $request->dividas))) {
            return $request->dividas[$codSAP];
        } else {
            return false;
        }

    }

    public
    function createCode()
    {
        $busca = DB::select("select top 1 receipt_goods.code from receipt_goods order by receipt_goods.id desc");
        $codigo = '';
        if (empty($busca) || is_null($busca) || $busca == '') {
            $codigo = 'RG00001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }
}
