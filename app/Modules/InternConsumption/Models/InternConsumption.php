<?php

namespace App\Modules\InternConsumption\Models;

use App\ErrorTrait;
use App\Exceptions\SapIntegrationException;
use App\Models\PontoVenda;
use App\Modules\InternConsumption\Models\InternConsumption\Comment;
use App\Modules\InternConsumption\Models\InternConsumption\Item;
use App\Modules\Inventory\Models\Output\Output;
use App\SapUtilities;
use App\User;
use Auth;
use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IJournalEntries;
use Litiano\Sap\IdeHelper\IStockTransfer;
use Litiano\Sap\NewCompany;
use Log;
use Throwable;

/**
 * App\Modules\InternConsumption\Models\InternConsumption
 *
 * @property int $id
 * @property int $creator_user_id
 * @property int|null $authorizer_user_id
 * @property \Carbon\Carbon|null $definition_date
 * @property int $requester_sap_id
 * @property string $requester_name
 * @property \Carbon\Carbon $date
 * @property string|null $distribution_rule
 * @property string|null $project
 * @property string $status
 * @property string|null $comment
 * @property int $pos_id
 * @property int|null $sales_order_code
 * @property int|null $stock_transfer_code
 * @property string|null $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Collection|Comment[] $comments
 * @property-read mixed $distribution_rule_name
 * @property-read mixed $project_name
 * @property-read mixed $status_label
 * @property-read Collection|Item[] $items
 * @property-read Collection|Item[] $notFinalizedItems
 * @property-read PontoVenda $pos
 * @method static Builder|InternConsumption whereAuthorizerUserId($value)
 * @method static Builder|InternConsumption whereComment($value)
 * @method static Builder|InternConsumption whereCreatedAt($value)
 * @method static Builder|InternConsumption whereCreatorUserId($value)
 * @method static Builder|InternConsumption whereDate($value)
 * @method static Builder|InternConsumption whereDefinitionDate($value)
 * @method static Builder|InternConsumption whereDistributionRule($value)
 * @method static Builder|InternConsumption whereId($value)
 * @method static Builder|InternConsumption whereMessage($value)
 * @method static Builder|InternConsumption wherePosId($value)
 * @method static Builder|InternConsumption whereProject($value)
 * @method static Builder|InternConsumption whereRequesterName($value)
 * @method static Builder|InternConsumption whereRequesterSapId($value)
 * @method static Builder|InternConsumption whereSalesOrderCode($value)
 * @method static Builder|InternConsumption whereStatus($value)
 * @method static Builder|InternConsumption whereStockTransferCode($value)
 * @method static Builder|InternConsumption whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string|null $requester_branch
 * @property string|null $observation
 * @property string|null $delivery_location
 * @property-read mixed $total
 * @method static Builder|InternConsumption whereDeliveryLocation($value)
 * @method static Builder|InternConsumption whereObservation($value)
 * @method static Builder|InternConsumption whereRequesterBranch($value)
 */
class InternConsumption extends Model
{
    use ErrorTrait;
    use SapUtilities;

    const STATUS_NEW = 'new';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_UNAUTHORIZED = 'unauthorized';
    const STATUS_FINALIZED = 'finalized';
    const STATUS_JOURNAL_PENDING = 'journal-pending';
    const STATUS_CANCELED = 'canceled';
    //const STATUS_WAITING_PRODUCTION_ORDER = 'waiting_production_order';

    const DOCUMENT_TYPE_TEXT = [
        0 => 'PADRÃO',
        1 => 'PERDAS',
        2 => 'EVENTOS',
    ];

    protected $table = 'intern_consumption';

    protected $fillable = [
        'creator_user_id', 'requester_sap_id', 'requester_name', 'date', 'distribution_rule', 'project',
        'status', 'pos_id', 'observation', 'requester_branch', 'delivery_location', 'comment', 'distribution_rule2',
        'document_type', 'dest_whs_code'
    ];

    protected $appends = ['status_label', 'edit_url', 'show_url', 'distribution_rule_name', 'distribution_rule2_name',
        'project_name'];

    protected $dates = ['date', 'definition_date', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'pos_id' => 'int',
        'order_total' => 'float',
        'document_type' => 'int',
    ];


    public static function cron()
    {
        $items = InternConsumption::whereIn(
            'status',
            [
                InternConsumption::STATUS_AUTHORIZED,
                InternConsumption::STATUS_JOURNAL_PENDING,
                InternConsumption::STATUS_NEW
            ]
        )
            ->where(function (Builder $query) {
                $query->doesntHave('notFinalizedItems')
                    ->orWhere('document_type', '!=', '0');
            })
            ->where('date', '<=', now()->toDateString())
            ->get();

        /** @var InternConsumption $item */
        foreach ($items as $item) {
            if (
                ($item->status === $item::STATUS_NEW
                    && $item->updated_at->getTimestamp() > \Carbon\Carbon::now()->subHour()->getTimestamp())
                ||
                (!empty($item->document_type) && $item->document_type !== 0
                    && $item->status != InternConsumption::STATUS_AUTHORIZED)
            ) {
                continue;
            }
            try {
                DB::transaction(function () use ($item) {
                    /** @var InternConsumption $ic */
                    $ic = InternConsumption::lockForUpdate()->find($item->id);
                    $ic->stockTransfer();
                });
                DB::transaction(function () use ($item) {
                    /** @var InternConsumption $ic */
                    $ic = InternConsumption::lockForUpdate()->find($item->id);
                    $ic->sendToSap();
                });
                DB::transaction(function () use ($item) {
                    /** @var InternConsumption $ic */
                    $ic = InternConsumption::lockForUpdate()->find($item->id);
                    $ic->manualAccountEntryToSap();
                });
            } catch (Throwable $e) {
                $item->message = utf8_encode($e->getMessage());
                $item->save();
                $item->createOrUpdateError($e, $item->date, $item->pos_id);
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * @throws SapIntegrationException
     */
    protected function stockOutput(): bool
    {
        $sap = new Company(false);
        $items = $this->items()->get();
        $itemsOutput = [];
        $whsAccount = getAccFromWhs($this->pos->deposito);
        if(empty($whsAccount)){
            throw new SapIntegrationException(
                "Erro ao processar a saída de mercadorias: Não há uma conta definida no depósito {$this->pos->deposito}",
                8993
            );
        }
        foreach ($items as $index => $item) {
            $itemsOutput[$index] = [
                "itemCode" => $item->code,
                "qtd" => $item->qty,
                "price" => $item->value,
                "projectCode" => $this->project,
                "centroCusto" => $this->distribution_rule,
                "centroCusto2" => $this->distribution_rule2,
                "conta" => getAccFromWhs($this->pos->deposito)->DecreasAc,
                "whsCode" => $this->pos->deposito,
                "intern_consumption" => true,
            ];
        }

        $output = new Output;
        $output->saveInDB((Object)[
            "creator_user_id" => $this->creator_user_id,
            "data" => $this->date->format('Y-m-d'),
            "obsevacoes" => "Documento gerado através do lançamento de ". self::DOCUMENT_TYPE_TEXT[$this->document_type],
            "items" => $itemsOutput
        ]);
        
        if(!empty($output->id)){
            $output_to_sap = $output->saveInSAP($output);
            if($output_to_sap === true){
                $this->message = "Saída de mercadorias processada com sucesso!";
                $this->stock_output_id = $output->id;
                $this->save();
                $this->destroyError();
                return true;
            }else{
                throw new SapIntegrationException(
                    "Erro ao processar a saída de mercadorias: {$output_to_sap}",
                    8994
                );
                return false;
            }
        }
    }

    /**
     * @throws SapIntegrationException
     */
    protected function stockTransfer(): bool
    {
        if ($this->stock_transfer_code) {
            return false;
        }
        
        if($this->document_type != '0'){
            $items = $this->items()->where("production_order_code", NULL)->get();
        }else{
            $items = $this->items()->where('type', 'IV')->get();
        }
        
        if ($items->count() === 0) {
            $this->stock_transfer_code = -1;
            $this->message = "Transferido de estoque não necessária!";
            $this->save();
            $this->destroyError();

            return true;
        }
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IStockTransfer $stockTransfer */
        $stockTransfer = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
        $stockTransfer->FromWarehouse = $this->pos->deposito;
        $stockTransfer->JournalMemo = "Transferido via aplicação web.";
        $stockTransfer->Comments = "Transferido por consumo interno id: {$this->id}.";
        foreach ($items as $item) {
            $stockTransfer->Lines->ItemCode = $item->code;
            $stockTransfer->Lines->Quantity = (float)$item->qty;
            $stockTransfer->Lines->WarehouseCode = $this->dest_whs_code ?? $this->pos->ci_config["deposit"];
            $stockTransfer->Lines->Add();
        }

        if ($stockTransfer->Add() !== 0) {
            throw new SapIntegrationException(
                "Erro ao transferir estoque: {$sap->GetLastErrorDescription()}",
                $sap->GetLastErrorCode()
            );
        }
        $this->message = "Transferido com sucesso!";
        $this->stock_transfer_code = $sap->GetNewObjectKey();
        $this->save();
        $this->destroyError();

        return true;
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'intern_consumption_id', 'id');
    }

    public function output(): HasOne
    {
        return $this->hasOne(Output::class, 'id', 'stock_output_id');
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function sendToSap(): bool
    {
        if ($this->sales_order_code || !$this->stock_transfer_code) {
            return false;
        }

        $sap = NewCompany::getInstance()->getCompany();

        /**
         * @var $order IDocuments
         */
        $order = $sap->GetBusinessObject(BoObjectTypes::oOrders);
        $order->CardCode = $this->pos->ci_config["card_code"];
        $order->DocDueDate = $this->date->format('d/m/Y');
        $order->DocDate = $this->date->format('d/m/Y');
        $order->Comments = "Baseado no consumo interno {$this::DOCUMENT_TYPE_TEXT[$this->document_type]} id: {$this->id} : {$this->date->format('d-m-Y')} - {$this->observation}";

        if($this->document_type !== 0){
            if($this->document_type === 1){
                $order->SalesPersonCode = (int)($this->pos_id == 1 ? 34 : 35); // 34 = PERDAS BAR / 35 = PERDAS REST
            }elseif($this->document_type === 2){
                $order->SalesPersonCode = (int)($this->pos_id == 1 ? 36 : 37); // 36 = EVENTOS BAR / 37 = EVENTOS REST
            }else{
                $order->SalesPersonCode = (int)($this->pos->ci_config['seller_code'] ?? -1);
            }
        }else{
            $order->SalesPersonCode = (int)($this->pos->ci_config['seller_code'] ?? -1);
        }
        foreach ($this->items as $item) {
            $order->Lines->ItemCode = $item->code;
            $order->Lines->UnitPrice = $item->item_cost;
            $order->Lines->Quantity = (float)$item->qty;
            $order->Lines->Usage = $this->pos->ci_config["utilization"];
            $order->Lines->FreeText = $item->comments ?? "";
            //$order->Lines->CostingCode = $this->distribution_rule;
            
            if ($this->pos_id == 1) {
                $order->Lines->CostingCode = '3.2';
            } else if ($this->pos_id == 2) {
                $order->Lines->CostingCode = '3.1';
            }

            // if ($this->distribution_rule2) {
            //     $order->Lines->CostingCode2 = $this->distribution_rule2;
            // }
            if ($this->project) {
                $order->Lines->ProjectCode = $this->project;
            }

            $order->Lines->TaxCode = $this->pos->ci_config["tax_code"];
            
            if($this->document_type !== 0){
                if($this->document_type === 1){
                    $order->Lines->SalesPersonCode = (int)($this->pos_id === 1 ? 34 : 35); // 34 = PERDAS BAR / 35 = PERDAS REST
                }elseif($this->document_type === 2){
                    $order->Lines->SalesPersonCode = (int)($this->pos_id === 1 ? 36 : 37); // 36 = EVENTOS BAR / 37 = EVENTOS REST
                }
            }else{
                $order->Lines->SalesPersonCode = (int)($this->pos->ci_config['seller_code'] ?? -1);
            }

            $order->Lines->WarehouseCode = $this->dest_whs_code ?? $this->pos->ci_config["deposit"];
            $order->Lines->Add();
        }

        if ($order->Add() !== 0) {
            throw new SapIntegrationException(
                "Erro ao cadastrar pedido de venda! {$sap->GetLastErrorDescription()}",
                $sap->GetLastErrorCode()
            );
        }
        $this->sales_order_code = $sap->GetNewObjectKey();
        $this->message = "Pedido de venda criado com sucesso!";
        $this->status = self::STATUS_JOURNAL_PENDING;
        $this->save();
        $this->destroyError();
        Log::info("Total:");
        Log::info($order->DocTotal);

        return true;
    }

    /**
     * @throws SapIntegrationException
     */
    protected function manualAccountEntryToSap(): bool
    {
        if (!$this->sales_order_code || $this->manual_account_entry_id) {
            return false;
        }

        $sap = NewCompany::getInstance()->getCompany();

        /** @var IJournalEntries $doc */
        $doc = $sap->GetBusinessObject(BoObjectTypes::oJournalEntries);
        $doc->DueDate = $this->date->format('d/m/Y');
        $doc->TaxDate = $this->date->format('d/m/Y');
        $doc->ReferenceDate = $this->date->format('d/m/Y');
        $doc->Memo = "Consumo Interno {$this->id} PV {$this->sales_order_code}";
        if ($this->project) {
            $doc->ProjectCode = $this->project;
        }

        // Begin credit
        $doc->Lines->AccountCode = '3.3.2.2.27';
        $doc->Lines->Credit = $this->total;
        if ($this->project) {
            $doc->Lines->ProjectCode = $this->project;
        }

        if ($this->pos_id == 1) {
            $doc->Lines->CostingCode = '3.2';
        } elseif ($this->pos_id == 2) {
            $doc->Lines->CostingCode = '3.1';
        }

        $doc->Lines->Add();
        // End credit

        // Begin debit
        $doc->Lines->AccountCode = '3.3.2.2.27';
        $doc->Lines->Debit = $this->total;
        if ($this->project) {
            $doc->Lines->ProjectCode = $this->project;
        }

        if ($this->distribution_rule) {
            $doc->Lines->CostingCode = $this->distribution_rule;
        }

        if ($this->distribution_rule2) {
            $doc->Lines->CostingCode2 = $this->distribution_rule2;
        }

        $doc->Lines->Add();
        // End debit

        if ($doc->Add() !== 0) {
            throw new SapIntegrationException(
                "Erro ao criar lançamento contábil manual! " . $sap->GetLastErrorDescription(),
                $sap->GetLastErrorCode(),
            );
        }

        $this->manual_account_entry_id = $sap->GetNewObjectKey();
        $this->message = 'Lançamento contábil criado com sucesso.';
        $this->status = self::STATUS_FINALIZED;
        $this->save();
        $this->destroyError();

        foreach ($this->items as $item) {
            $item->destroyError();
        }

        return true;
    }

    public static function getStatuses(): array
    {
        return [
            ['value' => self::STATUS_FINALIZED, 'text' => 'Finalizado'],
            ['value' => self::STATUS_NEW, 'text' => 'Pendente'],
            ['value' => self::STATUS_AUTHORIZED, 'text' => 'Autorizado'],
            ['value' => self::STATUS_CANCELED, 'text' => 'Cancelado'],
            ['value' => self::STATUS_UNAUTHORIZED, 'text' => 'Não autorizado'],
            ['value' => self::STATUS_JOURNAL_PENDING, 'text' => 'Pendente de lançamento contábil'],
        ];
    }

    public function notFinalizedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'intern_consumption_id', 'id')
            ->where('type', '=', 'IP')
            ->where(function (Builder $builder) {
                $builder->orWhere('production_order_status', '!=', Item::PO_STATUS_CLOSED)
                    ->orWhereNull('production_order_status');
            });
    }

    public function hasItemWithProductionOrder(): bool
    {
        return $this->items()
            ->where('type', '=', 'IP')
            ->whereNotNull('production_order_code')
            ->exists();
    }

    public function pos(): BelongsTo
    {
        return $this->belongsTo(PontoVenda::class, 'pos_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->attributes['status']) {
            case self::STATUS_NEW:
                return 'Pendente de autorização';
            case self::STATUS_AUTHORIZED:
                return 'Liberado';
            case self::STATUS_UNAUTHORIZED:
                return 'Não liberado';
            /*case self::STATUS_WAITING_PRODUCTION_ORDER:
                return 'Aguardando ordem de produção';*/
            case self::STATUS_FINALIZED:
                return 'Finalizado';
            case self::STATUS_JOURNAL_PENDING:
                return 'Pendente de lançamento contábil';
            case self::STATUS_CANCELED:
                return 'Cancelado';
            default:
                return '???';
        }
    }

    public function getDistributionRuleNameAttribute()
    {
        return NewCompany::getInstance()->getDistributionRuleName($this->attributes['distribution_rule']);
    }

    public function getDistributionRule2NameAttribute()
    {
        if (!empty($this->attributes['distribution_rule2'])) {
            return NewCompany::getInstance()->getDistributionRuleName($this->attributes['distribution_rule2'], 2);
        }

        return '';
    }

    public function getProjectNameAttribute()
    {
        return NewCompany::getInstance()->getProjectName($this->attributes['project']);
    }

    public function setStatus(bool $approved, ?string $comment = null): string
    {
        if ($this->status !== InternConsumption::STATUS_NEW) {
            throw new Exception('Este pedido já foi definido ou ainda não pode ser avaliado!');
        } elseif ($approved) {
            $this->status = InternConsumption::STATUS_AUTHORIZED;
            $this->authorizer_user_id = Auth::user()->id;
            $this->definition_date = Carbon::now();
            $this->save();
            $this->addComment("Pedido aprovado", $this->status);
            $message = 'Pedido aprovado com sucesso!';
        } else {
            $this->status = InternConsumption::STATUS_UNAUTHORIZED;
            $this->authorizer_user_id = Auth::user()->id;
            $this->definition_date = Carbon::now();
            $this->save();
            $this->addComment("Pedido reprovado", $this->status);
            $message = 'Pedido reprovado com sucesso!';
        }

        if ($comment) {
            $this->addComment($comment, $this->status);
        }

        return $message;
    }

    public function addComment($comment, $status)
    {
        $userId = Auth::user()->id;
        $this->comments()->create(['comment' => $comment, 'status' => $status, 'user_id' => $userId]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'intern_consumption_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function getTotalAttribute(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total = bcadd($total, $item->total, 2);
        }
        return (float)$total;
    }

    public function getEditUrlAttribute(): string
    {
        return route('intern-consumption.edit', $this);
    }

    public function getShowUrlAttribute(): string
    {
        return route('intern-consumption.show', $this);
    }

    public function getAuthorizerNameAttribute()
    {
        return User::find($this->authorizer_user_id)->name ?? "";
    }

    public function getDestWhsCodeNameAttribute()
    {
        $sap = new Company(false);
        return $sap->getDb()->table("OWHS")->select("WhsName")->where("WhsCode", "=", $this->dest_whs_code)->first()->WhsName ?? "";
    }

    public function canUpdate(): bool
    {
        return !$this->hasItemWithProductionOrder()
            && $this->status === self::STATUS_NEW
            && !$this->stock_transfer_code;
    }
}
