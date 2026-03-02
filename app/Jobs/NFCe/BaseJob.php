<?php

namespace App\Jobs\NFCe;

use App\Jobs\Queue;
use App\Models\Erro;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $itemId;
    protected int $attempt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $itemId, int $attempt = 1)
    {
        $this->itemId = $itemId;
        $this->attempt = $attempt;
    }

    protected function retryLater(string $queue = Queue::QUEUE_VERY_LOW, int $afterMinutes = 10)
    {
        static::dispatch($this->itemId, $this->attempt + 1)
            ->onQueue($queue)
            ->delay(Carbon::now()->addMinutes($afterMinutes));
    }

    protected function createOrUpdateError(string $modelClass, Throwable $e, int $pvId, Carbon $docDate)
    {
        Erro::updateOrCreate(
            ['model' => $modelClass, 'model_id' => $this->itemId],
            [
                'mensagem' => $e->getMessage(),
                'pv_id' => $pvId,
                'doc_date' => $docDate,
                'attempt' => $this->attempt,
                'exception' => get_class($e),
                'exception_code' => $e->getCode(),
            ]
        );
    }

    protected function logError(Throwable $e, bool $debug = true)
    {
        app('NFCeLogger')
            ->error(
                $e->getMessage(),
                [
                    'id' => $this->itemId,
                    'attempt' => $this->attempt,
                    'class' => get_called_class(),
                    'exception' => get_class($e),
                    'error_code' => $e->getCode(),
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                ]
            );

        if ($debug) {
            app('NFCeLogger')->error($e);
        }
    }
}
