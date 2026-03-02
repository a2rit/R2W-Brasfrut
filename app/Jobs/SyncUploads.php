<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Upload;
use App\logsError;

class SyncUploads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $obj;
    private $boType;
    private $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($obj, $boType, $model)
    {
        $this->obj = $obj;
        $this->boType = $boType;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $this->obj->syncUploads($this->obj, $this->boType, $this->model);
        }catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('UPS210',$e->getFile().'|'.$e->getLine(),$e->getMessage());
        }
    }
}
