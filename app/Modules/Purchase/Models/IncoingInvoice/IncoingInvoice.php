<?php

namespace App\Modules\Purchase\Models\IncoingInvoice;

use App\Modules\Partners\Models\Partner\Contract;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\ReceiptGoods\ReceiptGoods;
use App\Modules\Purchase\Models\ReceiptGoods\Items as ItemRG;
use App\Modules\Purchase\Models\ReceiptGoods\Expenses as ExpensesRG;
use App\Modules\Purchase\Models\ReceiptGoods\Tax as TaxRG;
use App\Upload;
use App\Jobs\LinkUploadsInDocument;
use App\Jobs\Queue;
use Illuminate\Http\Request;
use App\LogsError;
use App\CFItems;
use App\Modules\Settings\Models\Lofted;
use App\User;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Company;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoAPARDocumentTypes;
use \Datetime;
use \Exception;
use Illuminate\Database\Query\Builder;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice
 *
 * @property int $id
 * @property string $idUser
 * @property string|null $codSAP
 * @property string|null $idPurchaseOrder
 * @property string $code
 * @property string $cardCode
 * @property string $cardName
 * @property string|null $identification
 * @property string $docDate
 * @property string $docDueDate
 * @property string $taxDate
 * @property float|null $quotation
 * @property float|null $docTotal
 * @property int $paymentTerms
 * @property string|null $freightDocument
 * @property float|null $discountPercent
 * @property string|null $branch
 * @property string|null $coin
 * @property string|null $comments
 * @property string $status
 * @property string|null $message
 * @property bool $is_locked
 * @property bool $dbUpdate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $idReceiptGoods
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCodSAP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereDbUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereDocDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereDocDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereDocTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereFreightDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereIdPurchaseOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereIdReceiptGoods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereQuotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereTaxDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Purchase\Models\IncoingInvoice\Installment[] $installments
 * @property-read int|null $installments_count
 */
class IncoingInvoice extends Model
{
    protected $table = 'incoing_invoices';
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;
    const STATUS_CANCEL = 2;
    const STATUS_PENDING = 3;

    const STATUS_TEXT = [
        '0' => 'FECHADO',
        '1' => 'ABERTO',
        '2' => 'CANCELADO',
        '3' => 'PENDENTE'
    ];

    protected $cast = [
        "is_locked" => "boolean"
    ];

    public function items()
    {
        return $this->hasMany(Items::class, 'idIncoingInvoice', 'id');
    }

    public function taxes()
    {
        return $this->hasOne(Tax::class, 'idIncoingInvoice', 'id');
    }

    public function getContract()
    {
        return $this->items()->where('contract', '!=', null)->first()->contract ?? null;
    }

    public function getNextAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '>', $this->id)->orderBy('id', 'asc')->first() : $this->select('id')->orderBy('id', 'desc')->first();
    }

    public function getPreviousAttribute()
    {
        return !empty($this->id) ? $this->select('id')->where('id', '<', $this->id)->orderBy('id', 'desc')->first() : $this->select('id')->orderBy('id', 'asc')->first();
    }

    public function saveInDB(Request $request)
    {
        try {
            $partner = getProviderData($request->cardCode);
            $this->idUser = auth()->user()->id;
            $this->code = $this->createCode();
            $this->cardCode = $request->cardCode;
            $this->cardName = $partner['CardName'];
            $this->identification = $partner['TaxId0'] ?? $partner['TaxId4'];
            $this->docDate = $request->dataLancamento;
            $this->docDueDate = $request->dataVencimento;
            $this->taxDate = $request->dataDocumento;
            $this->paymentTerms = $request->condPagamentos;
            $this->freightDocument = is_null($request->valorFrete) ? 0 : $request->valorFrete;
            $this->discountPercent = is_null($request->valorDesconto) ? 0 : $request->valorDesconto;
            $this->branch = is_null($request->valorDesconto) ? 1 : $request->valorDesconto;
            $this->is_locked = true;
            $this->docTotal = round(clearNumberDouble($request->docTotal), 2);
            $this->impostos_r = clearNumberDouble($request->impostos_r);
            $this->total_a_pagar = round(clearNumberDouble($request->total_a_pagar), 2);
            $this->contract = $request->contract;

            if (isset($request->idPurchaseOrder) && !is_null($request->idPurchaseOrder))
                $this->idPurchaseOrder = $request->idPurchaseOrder;

            if (isset($request->idReceiptGoods)) {
                $this->idReceiptGoods = $request->idReceiptGoods;
            }

            if (workCoin()) {
                $this->coin = $request->coin;
            }
            #$this->paindSum = creatPaindSum($request);
            $this->comments = mb_convert_encoding(is_null($request->obsevacoes) ? ' ' : $request->obsevacoes, 'UTF-8');
            $this->JrnlMemo = mb_convert_encoding($request->jrnlmemo ?: "$request->cardCode " . substr(getPartnerName($this->cardCode), 0, 10) . ' NF ' . ($request->number_nf ?: '-'), 'UTF-8');
            $this->status = self::STATUS_OPEN;
            if (workQuotation()) {
                $this->quotation = clearNumberDouble($request->cotacao);
            }

            if ($this->save()) {
                if (isset($request->advancePayments)) {
                    foreach ($request->advancePayments as $payment) {
                        $adPayments = new AdvancePayments();
                        $payment['idIncoingInvoice'] = $this->id;
                        $adPayments->saveInDB($payment);
                    }
                }
                if (workCashFlow()) {
                    $CFItems = new CFItems(); //fluxo de caixa;
                    $CFItems->saveInDB($request->cashFlow, $this->id, 'incoing_invoices', $this->docTotal);
                }
                $tax = new Tax();
                $tax->saveInDB($request, $this->id);

                foreach ($request->requiredProducts as $key => $value) {

                    $item = new Items();
                    $item->saveInDB($value, $this->id);
                    if (!empty($value['withheldTaxes'])) {
                        foreach ($value['withheldTaxes'] as $index => $value) {
                            $withheld_taxes = new WithheldTax();
                            $withheld_taxes->saveInDB($value, $item->id);
                        }
                    }
                }


                $contract = Contract::where('code', '=', $this->contract)->first();
                if (!empty($contract)) {

                    if ($this->docTotal > (float)$contract->residualAmount) {
                        $this->message = "O valor total do documento não deve exceder o valor residual do contrato";
                        throw new Exception("O valor total do documento não deve exceder o valor residual do contrato");
                    }

                    $residualAmount = (float)$contract->residualAmount - $this->docTotal;
                    Contract::where('code', $this->contract)->update(['residualAmount' => $residualAmount]);
                }

                // $this->checkIfNeedApprove();
                $this->save();
            }
        } catch (\Exception $e) {
            $logsError = new LogsError();
            $logsError->saveInDB('E0104E', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function updateInDB($obj, Request $request)
    {
        $old_items = Items::where('idIncoingInvoice', $obj->id)->get();

        AdvancePayments::where('idIncoingInvoice', $obj->id)->delete();

        CFItems::where([
            'idTransation' => $obj->id,
            'transation' => 'incoing_invoices'
        ])->delete();

        Tax::join('incoing_invoices', 'incoing_invoices.id', '=', 'incoing_invoice_taxes.idIncoingInvoice')
            ->where('incoing_invoices.id', $obj->id)
            ->delete();

        Items::join('incoing_invoices', 'incoing_invoices.id', '=', 'incoing_invoice_items.idIncoingInvoice')
            ->where('incoing_invoices.id', $obj->id)
            ->delete();

        WithheldTax::where('itemId', $old_items->pluck('id'))->delete();

        Expenses::join('incoing_invoices', 'incoing_invoices.id', '=', 'incoing_invoice_expenses.idIncoingInvoice')
            ->where('incoing_invoices.id', $obj->id)
            ->delete();

        $oldDocTotal = (float)$obj->docTotal;
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
        $obj->comments = mb_convert_encoding(is_null($request->obsevacoes) ? ' ' : $request->obsevacoes, 'UTF-8');
        $obj->JrnlMemo = mb_convert_encoding($request->jrnlmemo ?: "$request->cardCode " . substr(getPartnerName($this->cardCode), 0, 10) . ' NF ' . ($request->number_nf ?: '-'), 'UTF-8');

        $obj->status = self::STATUS_OPEN;

        if (workQuotation()) {
            $obj->quotation = clearNumberDouble($request->cotacao);
        }
        $obj->is_locked = true;
        $obj->docTotal = (float)(is_numeric($request->docTotal) ? $request->docTotal : clearNumberDouble($request->docTotal));
        $obj->impostos_r = (float)(is_numeric($request->impostos_r) ? $request->impostos_r : clearNumberDouble($request->impostos_r));
        $obj->total_a_pagar = (float)(is_numeric($request->total_a_pagar) ? $request->total_a_pagar : clearNumberDouble($request->total_a_pagar));

        if ($obj->save()) {

            if (isset($request->advancePayments)) {
                $adPayments = new AdvancePayments();
                foreach ($request->advancePayments as $payment) {
                    $adPayments = new AdvancePayments();
                    $payment['idIncoingInvoice'] = $obj->id;
                    $adPayments->saveInDB($payment);
                }
            }

            if (workCashFlow()) {
                $CFItems = new CFItems(); //fluxo de caixa;
                $CFItems->saveInDB($request->cashFlow, $obj->id, 'incoing_invoices', $obj->docTotal);
            }
            $tax = new Tax();
            $tax->saveInDB($request, $obj->id);

            $new_items = [];
            foreach ($request->requiredProducts as $key => $value) {
                $item = new Items();
                $item->saveInDB($value, $obj->id);
                if (isset($value['withheldTaxes'])) {
                    foreach ($value['withheldTaxes'] as $irf) {
                        $withheld_tax = new WithheldTax();
                        $withheld_tax->saveInDB($irf, $item->id);
                    }
                }
                array_push($new_items, $item->id);
            }

            // $obj->saveExpenses($obj->id, $request);

            $contract = Contract::where('code', '=', $obj->getContract())->first();
            if (!empty($contract) && $obj->docTotal > (float)$contract->residualAmount) {
                $obj->message = "O valor total do documento não deve exceder o valor residual do contrato";
                throw new Exception("O valor total do documento não deve exceder o valor residual do contrato");
            }

            // $obj->checkIfNeedApprove();
            $obj->save();
        }
    }

    public function saveExpenses($id, $request)
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
                $expenses->idIncoingInvoice = $id;
                $expenses->expenseCode = 4;
                $expenses->lineTotal = clearNumberDouble($request->di_total);
                $expenses->project = $request->di_project;
                $expenses->distributionRule = $request->di_costCenter;
                $expenses->costCenter = $request->di_costCenter;
                $expenses->costCenter2 = $request->di_costCenter2;
                $expenses->tax = $request->di_tax;
                $expenses->comments = mb_convert_encoding((string)$request->di_comments, 'UTF-8');
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['freight']) && ($check['freight']) && ($freight)) {
                $freight = false;
                $expenses->idIncoingInvoice = $id;
                $expenses->expenseCode = 1;
                $expenses->lineTotal = clearNumberDouble($request->freight_total);
                $expenses->project = $request->freight_project;
                $expenses->distributionRule = $request->freight_costCenter;
                $expenses->costCenter = $request->freight_costCenter;
                $expenses->costCenter2 = $request->freight_costCenter2;
                $expenses->tax = $request->freight_tax;
                $expenses->comments = mb_convert_encoding((string)$request->freight_comments, 'UTF-8');
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['outhers']) && ($check['outhers']) && ($outhers)) {
                $outhers =  false;
                $expenses->idIncoingInvoice = $id;
                $expenses->expenseCode = 3;
                $expenses->lineTotal = clearNumberDouble($request->outhers_total);
                $expenses->project = $request->outhers_project;
                $expenses->distributionRule = $request->outhers_costCenter;
                $expenses->costCenter = $request->outhers_costCenter;
                $expenses->costCenter2 = $request->outhers_costCenter2;
                $expenses->tax = $request->outhers_tax;
                $expenses->comments = mb_convert_encoding((string)$request->outhers_comments, 'UTF-8');
                $expenses->save();
                $expenses = new Expenses();
            }
            if (isset($check['safe']) && ($check['safe']) && ($safe)) {
                $safe = false;
                $expenses->idIncoingInvoice = $id;
                $expenses->expenseCode = 2;
                $expenses->lineTotal = clearNumberDouble($request->safe_total);
                $expenses->project = $request->safe_project;
                $expenses->distributionRule = $request->safe_costCenter;
                $expenses->costCenter = $request->safe_costCenter;
                $expenses->costCenter2 = $request->safe_costCenter2;
                $expenses->tax = $request->safe_tax;
                $expenses->comments = mb_convert_encoding((string)$request->safe_comments, 'UTF-8');
                $expenses->save();
                $expenses = new Expenses();
            }
        }
    }

    private function checkExpenses($request)
    {
        $expense = [];
        if (
            isset($request->di_total) && !is_null($request->di_total)
            && isset($request->di_tax) && !is_null($request->di_tax)
            && isset($request->di_project) && !is_null($request->di_project)
            // && isset($request->di_role) && !is_null($request->di_role)
            && isset($request->di_costCenter) && !is_null($request->di_costCenter)
            && isset($request->di_costCenter2) && !is_null($request->di_costCenter2)

        ) {
            $expense['despImport'] = true;
        }
        if (
            isset($request->freight_total) && !is_null($request->freight_total)
            && isset($request->freight_tax) && !is_null($request->freight_tax)
            && isset($request->freight_project) && !is_null($request->freight_project)
            // && isset($request->freight_role) && !is_null($request->freight_role)
            && isset($request->freight_costCenter) && !is_null($request->freight_costCenter)
            && isset($request->freight_costCenter2) && !is_null($request->freight_costCenter2)

        ) {
            $expense['freight'] = true;
        }
        if (
            isset($request->outhers_total) && !is_null($request->outhers_total)
            && isset($request->outhers_tax) && !is_null($request->outhers_tax)
            && isset($request->outhers_project) && !is_null($request->outhers_project)
            // && isset($request->outhers_role) && !is_null($request->outhers_role)
            && isset($request->outhers_costCenter) && !is_null($request->outhers_costCenter)
            && isset($request->outhers_costCenter2) && !is_null($request->outhers_costCenter2)

        ) {
            $expense['outhers'] = true;
        }
        if (
            isset($request->safe_total) && !is_null($request->safe_total)
            && isset($request->safe_tax) && !is_null($request->safe_tax)
            && isset($request->safe_project) && !is_null($request->safe_project)
            // && isset($request->safe_role) && !is_null($request->safe_role)
            && isset($request->safe_costCenter) && !is_null($request->safe_costCenter)
            && isset($request->safe_costCenter2) && !is_null($request->safe_costCenter2)

        ) {
            $expense['safe'] = true;
        }

        return $expense;
    }

    private function createCode()
    {
        $busca = IncoingInvoice::orderBy('id', 'desc')->get(['code']);
        $codigo = '';
        if (count($busca) <= 0) {
            $codigo = 'II0001';
        } else {
            $codigo = $busca[0]->code;
            $codigo++;
        }
        return $codigo;
    }

    public function getCashFlowLabel($id)
    {
        try {
            $item = CFItems::join('cash_flows', 'cash_flows.id', '=', 'cash_flow_items.idCashFlow')
                ->where([
                    'cash_flow_items.idTransation' => $id,
                    'cash_flow_items.transation' => 'incoing_invoices'
                ])
                ->select('cash_flows.id')
                ->get();
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

    private function getItens($id)
    {
        return Items::select(
            'id',
            'itemCode',
            'taxCode',
            'quantity',
            'price',
            'lineSum',
            'costCenter',
            'costCenter2',
            'codUse',
            'codProject',
            'codCost',
            'codCFOP',
            'contract'
        )
            ->where('idIncoingInvoice', '=', $id)
            ->get();
    }

    private function getItemTaxes($id)
    {

        return DB::table('incoing_invoice_withheldtaxes')
            ->where('itemId', '=', $id)
            ->get();
    }

    private function getNameUser($id)
    {
        return User::find($id)->name;
    }

    private function getTax($id)
    {
        return Tax::where('idIncoingInvoice', '=', $id)->get();
    }

    private function getExpenses($id)
    {
        return Expenses::where('idIncoingInvoice', '=', $id)->get();
    }

    /**
     * @param IncoingInvoice $obj
     */
    public function saveInSAP($obj)
    {
        try {

            /** @var NewCompany $company */
            $sap = NewCompany::getInstance()->getCompany();
            $update = false;

            /** @var IDocuments $item */
            $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseInvoices);
            if ($obj->codSAP) {
                $item->GetByKey((string)$obj->codSAP);
                $update = true;
                $item->DocDueDate = $obj->docDueDate;
                $item->Comments = compressText($obj->comments, 200);
                $item->JournalMemo = $obj->JrnlMemo;

                $i = 0;
                // dd($obj->installments);
                foreach ($obj->installments as $installment) {
                    $item->Installments->SetCurrentLine($i);
                    if ($installment->value) {
                        $item->Installments->Total = $installment->value;
                    }
                    if ($installment->due_date) {
                        $item->Installments->DueDate = $installment->due_date; //->toDateString();
                    }
                    $item->Installments->Add();
                    $i++;
                }
            } else {

                if (!empty($obj->idPurchaseOrder)) {
                    $purchase_order = PurchaseOrder::find($obj->idPurchaseOrder);
                }

                $item->DocDueDate = $obj->docDueDate;
                $item->DocDate = $obj->docDate;
                $item->TaxDate = $obj->taxDate;
                $item->DocDueDate = $obj->docDueDate;
                $item->CardCode = $obj->cardCode;
                $item->PaymentGroupCode = $obj->paymentTerms;
                //item->Usage = '';
                $item->Comments = trim($obj->comments);
                $item->JournalMemo = trim($obj->JrnlMemo);
                $item_taxes = $this->getTax($obj->id);
                if (count($item_taxes) > 0) {
                    $item->SequenceSerial = $item_taxes[0]->sequenceSerial;
                    $item->SequenceCode = $item_taxes[0]->seqCode;
                    $item->SeriesString = $item_taxes[0]->seriesStr ?? 0;
                    $item->SequenceModel = $item_taxes[0]->sequenceModel;
                    if (isset($item_taxes[0]->subStr)) {
                        $item->SubSeriesString = $item_taxes[0]->subStr;
                    }
                }
                $adPayments = DB::table('incoing_invoice_advance_payments')->Where('idIncoingInvoice', '=', $obj->id)->get()->pluck('codSAP');
                if (!empty($adPayments)) {
                    foreach ($adPayments as $value) {
                        $payment = getAdvanceProviderSAP($value);
                        $item->DownPaymentsToDraw->DocEntry = (int)$payment['DocNum'];
                        $item->DownPaymentsToDraw->AmountToDraw = (float)$payment['DocTotal'] - $payment['DpmAppl'];
                    }
                }

                $item->UserFields->fields->Item("U_R2W_CODE")->value = $obj->code;
                $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($obj->idUser);

                $i = 0;
                foreach ($obj->installments as $installment) {
                    $item->Installments->SetCurrentLine($i);
                    if ($installment->value) {
                        $item->Installments->Total = $installment->value;
                    }
                    if ($installment->due_date) {
                        $item->Installments->DueDate = $installment->due_date; //->toDateString();
                    }
                    $item->Installments->Add();
                    $i++;
                }

                if (!is_null($obj->valorDesconto)) {
                    $item->DiscountPercent = $obj->discPrcnt;
                }

                $getItem = $obj->items()->get();
                $j = 0;
                foreach ($getItem as $key => $value) {

                    $item->Lines->setCurrentLine($j);
                    $item->Lines->ItemCode = (string)$value->itemCode;
                    $item->Lines->Quantity = clearNumberDouble(number_format($value->quantity, 2, ',', '.'));
                    $item->Lines->UnitPrice = (float)$value->price;
                    $item->Lines->MeasureUnit = (string)$value->itemUnd;
                    $item->Lines->ProjectCode = (string)$value->codProject;
                    $item->Lines->CostingCode = (string)$value->costCenter;
                    $item->Lines->CostingCode2 = (string)$value->costCenter2;
                    $item->Lines->Usage = (string)$value->codUse;
                    $item->Lines->CFOPCode = (string)$this->validCFOP($value->codCFOP, false);
                    $item->Lines->TaxCode = (string)$value->taxCode;
                    $item->Lines->UserFields->Fields->Item("U_A2R_CONTRATOPN")->Value = (string)$value->contract;
                    $item->Lines->UserFields->Fields->Item("U_ContaOrcameto")->Value = (String)$value->accounting_account;

                    if (!empty($purchase_order)) {
                        $purchase_order_item = $purchase_order->items()->find($value->idItemPurchaseOrder);
                        if (!empty($purchase_order_item)) {
                            $item->Lines->BaseEntry = $purchase_order->codSAP;
                            $item->Lines->BaseType = BoAPARDocumentTypes::bodt_PurchaseOrder;
                            $item->Lines->BaseLine = $purchase_order_item->lineNum;
                        }
                    }

                    foreach ($this->getItemTaxes($value->id) as $index => $withheld) {
                        $item->Lines->WithholdingTaxLines->WTCode = (string)$withheld->WTCode; // codigo do imposto - add no foreach
                        $item->Lines->WithholdingTaxLines->WTAmount = (float)$withheld->Value;
                        $item->Lines->WithholdingTaxLines->Add();
                    }
                    $item->Lines->Add();
                    $j++;
                }
                $item->TaxExtension->MainUsage = $getItem[0]->codUse;
            }

            if ($update) {
                $ret = $item->Update();
            } else {
                $ret = $item->Add();
            }

            if ($ret !== 0) {
                $logsErrors = new LogsError();
                $logsErrors->saveInDB('E0002', 'Cadastro de item no SAP', $sap->GetLastErrorDescription());
                $obj->message = $sap->GetLastErrorDescription();
                $obj->is_locked = false;
                $obj->save();
            } else {

                $obj->codSAP = $sap->GetNewObjectKey();
                $obj->message = null;
                $obj->is_locked = false;
                $obj->sync_at = new DateTime();
                $obj->save();

                if (isset($purchase_order)) {
                    $purchase_order->status = $purchase_order::STATUS_CLOSE;
                    $purchase_order->save();
                }

                $uploads = Upload::where('idReference', $obj->id)->where('reference', 'incoing_invoices')->first();
                if (!empty($uploads)) {
                    LinkUploadsInDocument::dispatch($uploads)->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                }

                if ((int)$obj->status === (int)self::STATUS_CANCEL) {
                    $obj->canceledInSAP($obj);
                }
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0074', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = false;
            $obj->save();
        }
    }

    public function duplicate($obj)
    {

        $this->idUser = auth()->user()->id;
        $this->code = $this->createCode();
        $this->cardCode = $obj->cardCode;
        $this->cardName = $obj->cardName;
        $this->identification = $obj->identification;
        $this->docDate = $obj->docDate;
        $this->docDueDate = $obj->docDueDate;
        $this->taxDate = $obj->taxDate;
        $this->paymentTerms = $obj->paymentTerms;
        $this->contract = $obj->contract;
        $this->freightDocument = is_null($obj->freightDocument) ? 0 : $obj->freightDocument;
        $this->discountPercent = is_null($obj->discountPercent) ? 0 : $obj->discountPercent;
        $this->branch = is_null($obj->valorDesconto) ? 1 : $obj->valorDesconto;

        if (workCoin()) {
            $this->coin = $obj->coin;
        }

        $this->comments = mb_convert_encoding(utf8_encode(is_null($obj->comments) ? ' ' : $obj->comments), 'UTF-8');
        $this->JrnlMemo = mb_convert_encoding($obj->jrnlmemo ?: "$obj->cardCode " . substr(getPartnerName($obj->cardCode), 0, 10) . ' NF ' . ($obj->number_nf ?: '-'), 'UTF-8');
        $this->status = self::STATUS_OPEN;

        if (workQuotation()) {
            $this->quotation = clearNumberDouble($obj->cotacao);
        }

        $this->is_locked = false;
        $this->docTotal = (float)(is_numeric($obj->docTotal) ? $obj->docTotal : clearNumberDouble($obj->docTotal));
        $this->impostos_r = (float)(is_numeric($obj->impostos_r) ? $obj->impostos_r : clearNumberDouble($obj->impostos_r));
        $this->total_a_pagar = (float)(is_numeric($obj->total_a_pagar) ? $obj->total_a_pagar : clearNumberDouble($obj->total_a_pagar));

        if ($this->save()) {

            $advancePayments = AdvancePayments::where('idIncoingInvoice', $obj->id)->get();
            if (!empty($advancePayments)) {
                foreach ($advancePayments as $payment) {
                    $adPayments = new AdvancePayments();
                    $payment['idIncoingInvoice'] = $this->id;
                    $adPayments->saveInDB($payment);
                }
            }

            $taxes = Tax::where('idIncoingInvoice', $obj->id)->get();
            if (!empty($taxes)) {
                foreach ($taxes as $value) {
                    $tax = new Tax();
                    $tax->duplicate($value, $this->id);
                }
            }

            $items = Items::where('idIncoingInvoice', $obj->id)->get();
            foreach ($items as $key => $value) {

                $item = new Items();
                $item->duplicate($value, $this->id);

                $withheld_taxes = WithheldTax::where('itemId', $value->id)->get();
                if (!empty($withheld_taxes)) {
                    foreach ($withheld_taxes as $index => $value) {
                        $withheld_tax = new WithheldTax();
                        $withheld_tax->saveInDB($value, $item->id);
                    }
                }
            }
            // $this->checkIfNeedApprove();
            $this->save();
        }
    }

    public function saveCopyRGInSAP($obj)
    {
        try {
            $sap = NewCompany::getInstance()->getCompany();


            $OPOR = ReceiptGoods::find($obj->idReceiptGoods);
            $po = $sap->GetBusinessObject(BoObjectTypes::oPurchaseDeliveryNotes);
            if ($po->GetByKey((int)$OPOR->codSAP)) {
                /** @var IDocuments $item */
                $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseInvoices);
                $item->DocDate = $obj->docDate;
                $item->DocDueDate = $obj->docDueDate;
                $item->TaxDate = $obj->taxDate;
                $item->CardCode = $obj->cardCode;
                $item->PaymentGroupCode = $obj->paymentTerms;
                $item->Comments = 'Baseado na Nota Fiscal de Entrada na WEB code: ' . $obj->code;
                if (count($this->getTax($obj->id)) > 0) {
                    $item->SequenceCode = $this->getTax($obj->id)[0]->seqCode;
                }
                if (count($this->getTax($obj->id)) > 0) {
                    $item->SequenceSerial = $this->getTax($obj->id)[0]->sequenceSerial;
                }
                if (count($this->getTax($obj->id)) > 0) {
                    $item->SeriesString = $this->getTax($obj->id)[0]->seriesStr ?: 123;
                }
                if (count($this->getTax($obj->id)) > 0 && isset($this->getTax($obj->id)[0]->subStr)) {
                    $item->SubSeriesString = $this->getTax($obj->id)[0]->subStr;
                }
                if (count($this->getTax($obj->id)) > 0) {
                    $item->SequenceModel = $this->getTax($obj->id)[0]->sequenceModel;
                }
                $check = $this->getExpenses($obj->id);
                if ($check) {
                    foreach ($check as $key => $value) {
                        $item->Expenses->ExpenseCode = (int)$value->expenseCode;
                        $item->Expenses->LineTotal = clearNumberDouble($value->lineTotal);
                        $item->Expenses->Project = (string)$value->project;
                        $item->Expenses->DistributionRule = (string)$value->distributionRule;
                        $item->Expenses->TaxCode = (string)$value->tax;
                        $item->Expenses->Remarks = (string)$value->comments;
                        $item->Expenses->add();
                    }
                }
                $getItem = $this->getItens($obj->id);
                $j = 0;
                foreach ($getItem as $key => $value) {
                    $item->Lines->setCurrentLine($j);
                    $item->Lines->ItemCode = (string)$value->itemCode;
                    $item->Lines->Quantity = (float)$value->quantity;
                    $item->Lines->UnitPrice = (float)$value->price;
                    $item->Lines->ProjectCode = (string)$value->codProject;
                    $item->Lines->CostingCode = (string)$value->codCost;
                    $item->Lines->Usage = (string)$value->codUse;
                    $item->Lines->CFOPCode = (string)$this->validCFOP($value->codCFOP, false);
                    $item->Lines->TaxCode = (string)$value->taxCode;
                    $item->Lines->BaseEntry = $po->DocNum;
                    $item->Lines->BaseType = BoAPARDocumentTypes::bodt_PurchaseDeliveryNote;
                    $item->Lines->BaseLine = $j;
                    $item->Lines->Add();
                    $j++;
                }
                $item->UserFields->fields->Item("U_R2W_CODE")->value = $OPOR->code;
                $item->UserFields->fields->Item("U_R2W_USERNAME")->value = $this->getNameUser($OPOR->idUser);
                if ($this->getTax($obj->id)[0]->NFEKey) {
                    $item->UserFields->fields->Item("U_invoice_key")->value = $this->getTax($obj->id)[0]->NFEKey;
                }

                if ($item->Add() !== 0) {
                    $logsErrors = new LogsError();
                    $logsErrors->saveInDB('E0FA92', 'Cadastro de item no SAP', $sap->GetLastErrorDescription());
                    $obj->message = $sap->GetLastErrorDescription();
                    $obj->is_locked = true;
                    $obj->save();
                } else {
                    $OPOR->status = self::STATUS_CLOSE;
                    $OPOR->save();
                    $obj->codSAP = $sap->GetNewObjectKey();
                    $obj->message = "Salvo no SAP com sucesso.";
                    $obj->is_locked = false;
                    $obj->save();
                }
            }
        } catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E4FRG', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            $obj->message = $e->getMessage();
            $obj->is_locked = true;
            $obj->save();
        }
    }

    private function validCFOP($cfop, $xml)
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

    public function canceledInSAP($obj)
    {
        try {
            if (is_null($obj->codSAP)) {
                $obj->is_locked = false;
                $obj->dbUpdate = false;
                $obj->status = self::STATUS_CANCEL;
                $obj->save();
            } else {
                $sap = new Company(false);
                $sapQuery = $sap->query("SELECT DocStatus, CANCELED FROM OPCH WHERE DocNum = $obj->codSAP");

                if (!empty($sapQuery) && $sapQuery[0]['CANCELED'] == 'Y' && $sapQuery[0]['DocStatus'] == 'C') {
                    $obj->is_locked = false;
                    $obj->dbUpdate = false;
                    $obj->status = self::STATUS_CANCEL;
                    $obj->save();
                    return;
                }

                $sap = NewCompany::getInstance()->getCompany();
                /** @var IDocuments $opor */
                NewCompany::transaction(function () use ($obj, $sap) {
                    /** @var IDocuments $opor */
                    $opor = $sap->GetBusinessObject(BoObjectTypes::oPurchaseInvoices);
                    if ($opor->GetByKey((int) $obj->codSAP)) {
                        $opor->sequenceModel = 0;
                        if ($opor->Update() === 0) {
                            $cancellationDocument = $opor->CreateCancellationDocument();
                            if (!is_null($cancellationDocument)) {
                                $ret = $cancellationDocument->Add();
                                if ($ret === 0) {
                                    $obj->is_locked = false;
                                    $obj->dbUpdate = false;
                                    $obj->status = self::STATUS_CANCEL;
                                    $obj->sync_at = new DateTime();
                                    $obj->message = '';
                                } else {
                                    $obj->message = $sap->GetLastErrorDescription();
                                    $obj->is_locked = false;
                                }
                                $obj->save();
                            } else {
                                $obj->is_locked = false;
                                $obj->message = "Não foi possivel cancelar o documento. Já está cancelado ou ocorreu algum erro no processo";
                                $obj->dbUpdate = true;
                                $obj->save();
                            }
                        }
                    }
                });
            }

            if (!empty($obj->contract)) {
                $partner_contract = Contract::where('code', $obj->contract)->first();
                $residualAmount = (float)$partner_contract->residualAmount + $obj->docTotal;
                Contract::where('code', $obj->contract)->update(['residualAmount' => $residualAmount]);
            }
        } catch (\Exception $e) {
            $obj->is_locked = false;
            $obj->message = $e->getMessage();
            $obj->dbUpdate = true;
            $obj->save();
        }
    }

    public function installments()
    {
        return $this->hasMany(Installment::class, 'invoice_id', 'id');
    }

    public function getAdvancePayments($cardCode)
    {
        $sap = new Company(false);
        $query = "SELECT T0.DocNum, T0.Comments, T0.DocDate, T0.DocTotal, T0.DpmAppl FROM SAPHOMOLOGACAO.dbo.ODPO T0 
                    WHERE T0.[CardCode] = :cardCode
                    AND T0.[CANCELED] = 'N'
                    AND T0.[DpmStatus] = 'O'
                    ORDER BY T0.DocNum DESC";
        $adPayments = DB::select($query, ['cardCode' => $cardCode]);
        if (!empty($adPayments)) {
            return $adPayments;
        }
        return false;
    }

    public function updateUpload()
    {

        $attachment = Upload::where('reference', '=', 'incoing_invoices')
            ->where('idReference', '=', $this->id)
            ->first();

        if (!is_null($attachment)) {
            $sap = NewCompany::getInstance()->getCompany();
            $item = $sap->GetBusinessObject(BoObjectTypes::oPurchaseInvoices);
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

    public function updateFromSAP($excludeItems)
    {
        try {
            $sap = new Company(false);
            $invoice_sap = $sap->getDb()->table('OPCH')
                ->select('DocDate', 'TaxDate', 'DocDueDate', 'GroupNum', 'Comments', 'JrnlMemo', 'DocTotal')
                ->where('DocNum', $this->codSAP)
                ->first();

            if (!empty($invoice_sap)) {
                $this->docDate = $invoice_sap->DocDate;
                $this->docDueDate = $invoice_sap->DocDueDate;
                $this->taxDate = $invoice_sap->TaxDate;
                $this->paymentTerms = $invoice_sap->GroupNum;
                $this->docTotal = $invoice_sap->DocTotal;
                // $this->freightDocument = is_null($invoice_sap->valorFrete) ? 0 : $invoice_sap->valorFrete;
                // $this->discountPercent = is_null($invoice_sap->valorDesconto) ? 0 : $invoice_sap->valorDesconto;
                $this->comments = $invoice_sap->Comments;
                $this->JrnlMemo = $invoice_sap->JrnlMemo;
                $this->is_locked = false;
                // $this->docTotal = (Double)$invoice_sap->DocTotal;
                // $this->impostos_r = (Double)(is_numeric($invoice_sap->impostos_r) ? $invoice_sap->impostos_r : clearNumberDouble($invoice_sap->impostos_r));
                // $this->total_a_pagar = (Double)(is_numeric($invoice_sap->total_a_pagar) ? $invoice_sap->total_a_pagar : clearNumberDouble($invoice_sap->total_a_pagar));

                if ($this->save()) {

                    $items_sap = $sap->getDb()->table('PCH1')
                        ->select(
                            'ItemCode',
                            'Dscription',
                            'Price',
                            'Quantity',
                            'Usage',
                            'Project',
                            'OcrCode',
                            'OcrCode2',
                            'CFOPCode',
                            'TaxCode'
                        )
                        ->where('DocEntry', $this->codSAP)
                        ->get();

                    if ($excludeItems) {
                        $this->items()->delete();
                        foreach ($items_sap as $index => $item_sap) {
                            $attributes = [];
                            $attributes['codSAP'] = $item_sap->ItemCode;
                            $attributes['itemName'] = $item_sap->Dscription;
                            $attributes['preco'] = $item_sap->Price;
                            $attributes['qtd'] = $item_sap->Quantity;
                            $attributes['use'] = $item_sap->Usage;
                            $attributes['projeto'] = $item_sap->Project;
                            $attributes['costCenter'] = $item_sap->OcrCode;
                            $attributes['costCenter'] = $item_sap->OcrCode;
                            $attributes['costCenter2'] = $item_sap->OcrCode2;
                            $attributes['cfop'] = $item_sap->CFOPCode;
                            $attributes['taxCode'] = $item_sap->TaxCode;
                            $attributes['idItemPurchaseOrder'] = $item_sap->idItemPurchaseOrder ?? NULL;

                            $item = new Items;
                            if ($item->saveInDB($attributes, $this->id)) {
                                $withheld_taxes_sap = $sap->getDb()->table('PCH5')
                                    ->select('WTCode', 'Rate', 'Category', 'WTAmnt')
                                    ->where('AbsEntry', $this->codSAP)
                                    ->get();

                                foreach ($withheld_taxes_sap as $irf) {
                                    $attributes = [];
                                    $withheld_tax = new WithheldTax();
                                    $attributes['WTCode'] = $irf['WTCode'];
                                    $attributes['Rate'] = $irf['Rate'];
                                    $attributes['Value'] = $irf['WTAmnt'];
                                    $withheld_tax->saveInDB($attributes, $item->id);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage(), $e->getFile());
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
            "form_url" => route('purchase.ap.invoice.listInvoicesTopNav'),
            "read_document_url" => route('purchase.ap.invoice.read'),
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
            "back_page_url" => route('purchase.ap.invoice.index'),
            "previous_record_url" => !empty($previousRecord) ? route('purchase.ap.invoice.read', $previousRecord) : "",
            "create_record_url" => route('purchase.ap.invoice.create'),
            "next_record_url" => !empty($nextRecord) ? route('purchase.ap.invoice.read', $nextRecord) : "",
            "print_urls" => $this->getPrintUrls(),
        ];
    }

    public function getPrintUrls(): array
    {
        if ($this->id && $this->codSAP) {
            return [
                "PDF" => route('purchase.ap.invoice.print', $this->id)
            ];
        }
        return [];
    }

    // private function checkIfNeedApprove()
    // {
    //     $groups_items = $this->items->groupBy(["costCenter", "costCenter2", "accounting_account"]);
    //     foreach ($groups_items as $key => $group_items) {
    //         if ($key == "1.0") {
    //             foreach ($group_items as $cost_center_code2 => $group_item) {
    //                 foreach ($group_item as $acct => $group_account) {
    //                     $this->checkAproveBudgetCCenter($key, $cost_center_code2, $group_account, $acct);
    //                 }
    //             }
    //         } else {
    //             foreach ($group_items as $cost_center_code => $group_item) {
    //                 foreach ($group_item as $acct => $group_account) {
    //                     $this->checkAproveBudgetCCenter($cost_center_code, null, $group_account, $acct);
    //                 }
    //             }
    //         }
    //     }
    // }

    // private function checkAproveBudgetCCenter(String $cost_center_code, String $cost_center_code2 = null, $items, $acct)
    // {
    //     $sap = new Company(false);
    //     $budgetCostCenterSearch = $cost_center_code2 ?? $cost_center_code;
    //     $budgetSAP = $sap->getDb()->table("@A2RORCPC")
    //         ->select("U_A2RVLROPC", "U_A2RVLRORCU")
    //         ->where("Name", "=", $acct)
    //         ->where("U_A2RCC", "=", "{$budgetCostCenterSearch}")
    //         ->where(function (Builder $builder) {
    //             $builder->whereDate("U_A2RDIOPC", "<=", date("Y-m-d"))
    //                 ->whereDate("U_A2RDFOPC", ">=", date("Y-m-d"));
    //         })
    //         ->orderBy("U_A2RDFOPC",  "ASC")
    //         ->first();
    //     dd($budgetSAP, $acct);

    //     $residualAmount = $budgetSAP->U_A2RVLROPC - $budgetSAP->U_A2RVLRORCU;
    //     $group_items_sum = $items->sum("lineSum");
    //     dd((float)$group_items_sum > (float)$residualAmount);
    //     if (!empty($budgetSAP) && (float)$group_items_sum > (float)$residualAmount) {

    //         if (!empty($cost_center_code2)) {
    //             $valid_approver = Lofted::where("cost_center_2_id", $cost_center_code2)
    //                 ->where('docNum', '=', Lofted::BUDGET_INCOING_INVOICE)
    //                 ->first();
    //         } else {
    //             $valid_approver = Lofted::where("cost_center_id", $cost_center_code)
    //                 ->where('docNum', '=', Lofted::BUDGET_INCOING_INVOICE)
    //                 ->first();
    //         }

    //         if (!empty($valid_approver)) {
    //             foreach ($items as $item) {
    //                 $item->lofted_approveds_id = $valid_approver->id;
    //                 $item->save();
    //             }

    //             $search = Lofted::join('approver_documents', 'approver_documents.idLoftedApproveds', '=', 'lofted_approveds.id')
    //                 ->where('lofted_approveds.id', '=', $item->lofted_approveds_id)
    //                 ->where('docNum', '=', Lofted::BUDGET_INCOING_INVOICE)
    //                 ->where('lofted_approveds.status', '=', Lofted::STATUS_OPEN)
    //                 ->select('approver_documents.*', 'lofted_approveds.quantity', 'lofted_approveds.id as idLofted')
    //                 ->orderby('nivel')
    //                 ->get();

    //             if (count($search) > 0) {
    //                 foreach ($search as $value) {
    //                     $attributes['idPurchaseOrder'] = $this->id;
    //                     $attributes['idLofted'] = $value->idLofted;
    //                     $attributes['idApproverDocuments'] = $value->id;
    //                     $attributes['nivel'] = $value->nivel;
    //                     $attributes['idUser'] = $value->approverUser;
    //                     $attributes['status'] = Approve::STATUS_CLOSE;

    //                     Approve::create($attributes);
    //                 }
    //             }
    //             dd(123);
    //             $this->status = self::STATUS_PENDING;
    //             $this->is_locked = false;
    //         }
    //     };
    // }
}
