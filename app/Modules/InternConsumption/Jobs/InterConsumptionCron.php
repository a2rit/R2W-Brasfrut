<?php

namespace App\Modules\InternConsumption\Jobs;

use App\Modules\InternConsumption\Models\InternConsumption;
use App\Modules\InternConsumption\Models\InternConsumption\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InterConsumptionCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Item::cron();
        InternConsumption::cron();
    }
}
