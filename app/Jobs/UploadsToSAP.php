<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\Queue;
use App\Upload;
use App\LogsError;
use Exception;

class UploadsToSAP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $obj;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
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
                self::dispatch($this->obj)->delay(now()->addMinutes($this->attempts()))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
                return;
            }
            $this->obj->saveInSAP();
        } catch (Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EF2269', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            self::dispatch($this->obj)
                ->delay(now()->addMinutes(5))->onQueue(Queue::QUEUE_PURCHASE_ORDERS);
        }
    }
}
