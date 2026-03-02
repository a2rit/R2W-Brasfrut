<?php

namespace App;

use App\Models\Erro;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Throwable;

trait ErrorTrait
{
    public function destroyError()
    {
        if ($this->erro_id) {
            Erro::destroy($this->erro_id);
            $this->erro_id = null;
            $this->save();
        }
        Erro::where('model_id', $this->id)->where('model', static::class)->delete();
    }

    public function createOrUpdateError(Throwable $e, $docDate = null, $pvId = null, int $attempt = null)
    {
        Erro::updateOrCreate(
            ['model' => static::class, 'model_id' => $this->id],
            [
                'mensagem' => $e->getMessage(),
                'exception' => get_class($e),
                'exception_code' => $e->getCode(),
                'pv_id' => $pvId,
                'doc_date' => $docDate,
                'attempt' => $attempt,
            ]
        );
    }

    public function erro(): MorphOne
    {
        return $this->morphOne(Erro::class, 'erro', 'model', 'model_id');
    }

    public function recentError()
    {
        return $this->erro()->where('updated_at', '>=', Carbon::now()->subDay());
    }
}
