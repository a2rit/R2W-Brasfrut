<?php

namespace App\Modules\Purchase\Jobs;

use App\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\LogsError;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use Throwable;
use App\Jobs\Queue;

class PurchaseRequestToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var PurchaseRequest */
    private $pa;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pa)
    {
        $this->pa = $pa;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->attempts() > 1) {
                $this->logAttempt();
                self::dispatch($this->pa)->delay(now()->addMinutes($this->attempts()))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                return;
            }

            $obj = new PurchaseRequest();
            $obj->saveInSAP($this->pa);
        } catch (Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('ERF228', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            PurchaseRequestToSAP::dispatch($this->pa)->delay(now()->addMinutes(3))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }

    private function logAttempt()
    {
        $itemsCount = $this->pa->items()->count();
        $uploadsCount = Upload::where('reference', 'purchase_requests')
            ->where('idReference', $this->pa->id)
            ->count();
        $id = $this->pa->id;
        $attempts = $this->attempts();

        app('PurchaseRequestJobLogger')
            ->info(
                'Job PurchaseRequestToSAP Delay',
                compact('id', 'attempts', 'itemsCount', 'uploadsCount')
            )
        ;
    }
}
