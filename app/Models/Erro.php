<?php

namespace App\Models;

use App\Modules\InternConsumption\Models\InternConsumption;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use phpDocumentor\Reflection\Types\Integer;

/**
 * App\Models\Erro
 *
 * @property int $id
 * @property string $model
 * @property int $model_id
 * @property string $mensagem
 * @property bool $lido
 * @property string|null $pv_id
 * @property Carbon|null $doc_date
 * @property int|null $attempt
 * @property string|null $exception
 * @property string|null $exception_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read null|Carbon $data_nf
 * @property-read mixed $ponto_venda_label
 * @method static Builder|Erro whereCreatedAt($value)
 * @method static Builder|Erro whereId($value)
 * @method static Builder|Erro whereLido($value)
 * @method static Builder|Erro whereMensagem($value)
 * @method static Builder|Erro whereModel($value)
 * @method static Builder|Erro whereModelId($value)
 * @method static Builder|Erro whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|Erro newModelQuery()
 * @method static Builder|Erro newQuery()
 * @method static \Illuminate\Data
 * base\Eloquent\Builder|\App\Models\Erro query()
 */
class Erro extends Model
{
    protected $table = 'erros';

    protected $dates = ['doc_date'];

    protected $appends = ['tipo', 'ver_url', 'tipo_modelo', 'numero_nfce', 'intern_consumption_type'];

    protected $fillable = [
        'model',
        'model_id',
        'mensagem',
        'pv_id',
        'doc_date',
        'attempt',
        'exception',
        'exception_code',
    ];

    protected $casts = [
        'model_id' => 'int',
        'attempt' => 'int',
    ];

    public static function fixData()
    {
        $erros = Erro::whereNull('doc_date');
        /** @var Erro $i */
        $erros->each(function ($i) {
            $i->pv_id = $i->modelo->pv_id ?? $i->modelo->nfc->pv_id;
            $i->doc_date = $i->modelo->data_emissao ?? $i->modelo->nfc->data_emissao;
            $i->save();
        });
    }

    public function getTipoAttribute(): string
    {
        if ($this->doc_date && $this->doc_date->isToday()) {
            return 'Advertência';
        }
        return 'Erro';
    }

    public function getVerUrlAttribute(): string
    {
        if ($this->model === InternConsumption::class) {
            return route('intern-consumption.show', $this->model_id);
        }
        if ($this->model === InternConsumption\Item::class && $this->modelo) {
            return route('intern-consumption.show', $this->modelo->internConsumption);
        }

        return route('erros.ver', ['id' => $this->id]);
    }

    public function modelo(): MorphTo
    {
        return $this->morphTo('modelo', 'model', 'model_id');
    }

    public function pv(): BelongsTo
    {
        return $this->belongsTo(PontoVenda::class, 'pv_id');
    }

    public function nfce(): BelongsTo
    {
        return $this->belongsTo(NFCe::class, 'model_id');
    }
    
    public function getNumeroNfceAttribute(): string
    {
        return $this->nfce->numero ?? "";
    }

    public function getTipoModeloAttribute(): string
    {
        switch ($this->model) {
            case NFCe::class:
                return '1';
            case NFCe\Item::class:
                return '2';
            case InternConsumption::class:
                return '3';
            case InternConsumption\Item::class:
                return '4';
        }
        return '0';
    }

    public function getInternConsumptionTypeAttribute(): ?int
    {
        if ($this->model === InternConsumption::class) {
            return $this->modelo->document_type;
        } elseif ($this->model === InternConsumption\Item::class) {
            return $this->modelo->internConsumption->document_type;
        }

        return null;
    }
}
