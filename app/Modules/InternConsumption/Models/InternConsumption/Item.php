<?php

namespace App\Modules\InternConsumption\Models\InternConsumption;

use App\ErrorTrait;
use App\Exceptions\SapIntegrationException;
use App\Exceptions\StockErrorException;
use App\logsError;
use App\Modules\InternConsumption\Models\InternConsumption;
use Carbon\Carbon;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoProductionOrderStatusEnum;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IProductionOrders;
use Litiano\Sap\NewCompany;
use Litiano\Sap\Company;
use Throwable;

/**
 * App\Modules\InternConsumption\Models\InternConsumption\Item
 *
 * @property int $id
 * @property int $intern_consumption_id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property float $qty
 * @property int|null $production_order_code
 * @property int|null $delivery_code
 * @property string|null $production_order_status
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read InternConsumption $internConsumption
 * @method static Builder|Item whereCode($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereDeliveryCode($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereInternConsumptionId($value)
 * @method static Builder|Item whereMessage($value)
 * @method static Builder|Item whereName($value)
 * @method static Builder|Item whereProductionOrderCode($value)
 * @method static Builder|Item whereProductionOrderStatus($value)
 * @method static Builder|Item whereQty($value)
 * @method static Builder|Item whereType($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read mixed $po_status_label
 * @property float $value
 * @property-read mixed $total
 * @method static Builder|Item whereValue($value)
 */
class Item extends Model
{
    use ErrorTrait;

    const PO_STATUS_PLANNED = 'planned';
    const PO_STATUS_RELEASED = 'released';
    const PO_STATUS_DELIVERED = 'delivered';
    const PO_STATUS_CLOSED = 'closed';
    const PO_STATUS_CANCELED = 'canceled';
    protected $table = 'intern_consumption_items';
    protected $fillable = ['intern_consumption_id', 'code', 'name', 'type', 'qty', 'value', 'comments'];
    protected $dates = ['created_at', 'updated_at', 'date'];
    protected $appends = ['po_status_label'];
    protected $casts = [
        'qty' => 'float',
        'value' => 'float',
    ];

    public static function cron()
    {
        $new = Item::getOPBaseBuilder()->whereNull('production_order_status')->get();
        /** @var Item $item */
        foreach ($new as $item) {
            try {
                $ic = $item->internConsumption;
                if ($ic->date->getTimestamp() > now()->getTimestamp()) {
                    continue;
                }
                if ($ic->documentType != '0' && str_contains($item->code, "IP")) {
                    $sap = new Company(false);
                    $query = $sap->getDb()
                        ->table("OITM")
                        ->select("ItmsGrpCod")
                        ->where("ItemCode", $item->code)
                        ->first()
                    ;
                    if ($query->ItmsGrpCod == '108') { // Grupo "PORCIONAMENTO"
                        continue;
                    }
                }
                if (
                    $ic->status === $ic::STATUS_NEW
                    && $ic->updated_at->getTimestamp() > Carbon::now()->subHour()->getTimestamp()
                ) {
                    continue;
                }
                $item->createProductionOrder();
            } catch (Throwable $e) {
                $item->createOrUpdateError($e, $item->internConsumption->date, $item->internConsumption->pos_id);
//                Log::error("Production order error on create: {$e->getMessage()}", $item->toArray());
            }
        }

        $planned = Item::baseToGetProductionOrdersInProcess()
            ->whereProductionOrderStatus(self::PO_STATUS_PLANNED)
            ->get();
        /** @var Item $item */
        foreach ($planned as $item) {
            try {
                $item->releaseProductionOrder();
            } catch (Throwable $e) {
                $item->createOrUpdateError($e, $item->internConsumption->date, $item->internConsumption->pos_id);
//                Log::error("Production order error on release: {$e->getMessage()}", $item->toArray());
            }
        }

        $released = Item::baseToGetProductionOrdersInProcess()
            ->whereProductionOrderStatus(self::PO_STATUS_RELEASED)
            ->get();
        /** @var Item $item */
        foreach ($released as $item) {
            try {
                $item->checkProductionOrderStock();
                $item->deliveryProductionOrder();
            } catch (Throwable $e) {
                $item->createOrUpdateError($e, $item->internConsumption->date, $item->internConsumption->pos_id);
//                Log::error("Production order error on delivery: {$e->getMessage()}", $item->toArray());
            }
        }

        $delivered = Item::baseToGetProductionOrdersInProcess()
            ->whereProductionOrderStatus(self::PO_STATUS_DELIVERED)
            ->get();
        /** @var Item $item */
        foreach ($delivered as $item) {
            try {
                $item->closeProductionOrder();
            } catch (Throwable $e) {
                $item->createOrUpdateError($e, $item->internConsumption->date, $item->internConsumption->pos_id);
//                Log::error("Production order error on close: {$e->getMessage()}", $item->toArray());
            }
        }
    }

    protected static function getOPBaseBuilder()
    {
        return Item::whereType('IP')
            ->whereHas('internConsumption', function (Builder $builder) {
                $builder->whereIn(
                    'status',
                    [
                        InternConsumption::STATUS_NEW,
                        InternConsumption::STATUS_AUTHORIZED,
                        InternConsumption::STATUS_JOURNAL_PENDING,
                    ]
                );
            });
    }

    protected static function baseToGetProductionOrdersInProcess()
    {
        return Item::whereType('IP')
            ->whereHas('internConsumption', function (Builder $builder) {
                $builder->whereIn(
                    'status',
                    [
                        InternConsumption::STATUS_NEW,
                        InternConsumption::STATUS_AUTHORIZED,
                        InternConsumption::STATUS_JOURNAL_PENDING,
                        InternConsumption::STATUS_FINALIZED,
                    ]
                )->where('created_at', '>=', '2024-01-01');
            });
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function createProductionOrder(): bool
    {
        if ($this->type != "IP" || !$this->code) {
            return false;
        }

        if (!empty($this->production_order_code)) {
            $logsError = new logsError();
            $logsError->saveInDB('APE0001', 'Producao Consumo Interno', 'Item ja foi produzido');
        }

        $sap = NewCompany::getInstance()->getCompany();
        /** @var IProductionOrders $order */
        $order = $sap->GetBusinessObject(BoObjectTypes::oProductionOrders);
        /** Cabeçalho do Documento */
        $order->ItemNo = $this->code; // Código do Item
        $order->PlannedQuantity = (double)$this->qty; // Quantidade a ser produzida
        $order->DueDate = $this->internConsumption->date->format("d/m/Y"); // Vencimento
        $order->PostingDate = $this->internConsumption->date->format("d/m/Y"); // Data do Pedido
        $order->Warehouse = $this->internConsumption->pos->deposito;
        $order->Remarks = "Adicionado via aplicação web. Consumo interno {$this->intern_consumption_id}";

        /** Campos definidos pelo Usuário */
        //$order->UserFields->Fields->Item("U_field")->Value = "";

        if ($order->Add() != "0") {
            throw new SapIntegrationException($sap->GetLastErrorDescription(), $sap->GetLastErrorCode());
        }

        $this->message = "Production order created successfully";
        $this->production_order_code = $sap->GetNewObjectKey();
        $this->production_order_status = self::PO_STATUS_PLANNED;
        $this->save();
        $this->destroyError();

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function releaseProductionOrder(): bool
    {
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IProductionOrders $order */
        $order = $sap->GetBusinessObject(BoObjectTypes::oProductionOrders);
        $order->GetByKey($this->production_order_code);
        $order->ProductionOrderStatus = BoProductionOrderStatusEnum::boposReleased; //BoProductionOrderStatusEnum.boposReleased; // 1
        if ($order->Update() != "0") {
            throw new SapIntegrationException($sap->GetLastErrorDescription(), $sap->GetLastErrorCode());
        }

        $this->message = "Production order released";
        $this->production_order_status = self::PO_STATUS_RELEASED;
        $this->save();
        $this->destroyError();

        return true;
    }

    /**
     * @throws StockErrorException
     */
    protected function checkProductionOrderStock()
    {
        if (empty($this->production_order_code) || $this->production_order_status !== self::PO_STATUS_RELEASED) {
            return;
        }

        $onHandQuery = NewCompany::getDb()
            ->table('OITW')
            ->whereColumn('OITW.ItemCode', 'WOR1.ItemCode')
            ->whereColumn('OITW.WhsCode', 'WOR1.wareHouse')
            ->select(['OITW.OnHand'])
        ;
        $hasStockError = NewCompany::getDb()
            ->table('WOR1')
            ->where('WOR1.DocEntry', $this->production_order_code)
            ->groupBy(['WOR1.ItemCode', 'WOR1.wareHouse'])
            ->havingRaw("SUM(WOR1.PlannedQty) > ({$onHandQuery->toSql()})")
            ->exists();

        if ($hasStockError) {
            throw new StockErrorException(
                "Ordem de produção {$this->production_order_code} com itens abaixo do estoque necessário."
            );
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function deliveryProductionOrder(): bool
    {
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IDocuments $document */
        $document = $sap->GetBusinessObject(BoObjectTypes::oInventoryGenEntry);
        $document->DocDate = $this->internConsumption->date->format("d/m/Y");
        $document->Comments = "Adicionado via aplicação web.";
        //$document->UserFields->Fields->Item("U_A2R_CUPOM_FISCAL")->Value;

        $document->Lines->BaseEntry = (int)$this->production_order_code;
        $document->Lines->WarehouseCode = $this->internConsumption->dest_whs_code ?? $this->internConsumption->pos->ci_config["deposit"];

        if ($document->Add() != "0") {
            throw new SapIntegrationException($sap->GetLastErrorDescription(), $sap->GetLastErrorCode());
        }

        $this->message = "Production order delivered";
        $this->production_order_status = self::PO_STATUS_DELIVERED;
        $this->delivery_code = $sap->GetNewObjectKey();
        $this->save();
        $this->destroyError();

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function closeProductionOrder(): bool
    {
        $sap = NewCompany::getInstance()->getCompany();
        /** @var IProductionOrders $order */
        $order = $sap->GetBusinessObject(BoObjectTypes::oProductionOrders);
        $order->GetByKey($this->production_order_code);

        if ($order->ProductionOrderStatus === BoProductionOrderStatusEnum::boposClosed) {
            return $this->setProductionOrderClosed();
        }

        $order->ProductionOrderStatus = BoProductionOrderStatusEnum::boposClosed;
        if ($order->Update() != "0") {
            throw new SapIntegrationException($sap->GetLastErrorDescription(), $sap->GetLastErrorCode());
        }

        return $this->setProductionOrderClosed();
    }

    protected function setProductionOrderClosed(): bool
    {
        $this->message = "Production order closed successfully";
        $this->production_order_status = self::PO_STATUS_CLOSED;
        $this->save();
        $this->destroyError();

        return true;
    }

    public function internConsumption(): BelongsTo
    {
        return $this->belongsTo(InternConsumption::class, 'intern_consumption_id', 'id');
    }

    public function getPoStatusLabelAttribute(): string
    {
        if ($this->type !== 'IP') {
            return 'N/A';
        }
        switch ($this->production_order_status) {
            case self::PO_STATUS_PLANNED:
                return 'Planejada';
            case self::PO_STATUS_RELEASED:
                return 'Liberada';
            case self::PO_STATUS_DELIVERED:
                return 'Entregue';
            case self::PO_STATUS_CLOSED:
                return 'Fechada';
            default:
                return 'Não iniciada';
        }
    }

    public function getTotalAttribute(): float
    {
        return (float)bcmul($this->value, $this->qty, 2);
    }

    public function getValueAttribute(): float
    {
        if (bccomp($this->attributes['value'], 0, 2) <= 0) {
            try {
                $this->attributes['value'] = $this->getItemPriceAttribute();
                $this->save();
            } catch (Throwable $e) {
            }
        }
        return (float)$this->attributes['value'];
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getItemPriceAttribute(): float
    {
        $priceList = $this->internConsumption->dest_whs_code ?? $this->internConsumption->pos->ci_config["deposit"];

        $price = NewCompany::getDb()->table('ITM1')
            ->where('ItemCode', '=', $this->code)
            ->where('PriceList', '=', $priceList)
            ->first(['Price']);

        if (!$price) {
            throw new Exception("Preço do item {$this->code} não encontrado, verifique se o mesmo está ativo!");
        }

        return (float)$price->Price;
    }

    /**
     * @throws Exception
     */
    public function getItemCostAttribute(): float
    {
        $deposit = $this->internConsumption->dest_whs_code ?? $this->internConsumption->pos->ci_config["deposit"];

        $cost = NewCompany::getDb()->table('OITW')
            ->where('ItemCode', '=', $this->code)
            ->where('WhsCode', '=', $deposit)
            ->first(['AvgPrice']);

        if (!$cost) {
            throw new Exception("Custo do item {$this->code} não encontrado, verifique se o mesmo está ativo!");
        }

        return (float)$cost->AvgPrice;
    }
}
