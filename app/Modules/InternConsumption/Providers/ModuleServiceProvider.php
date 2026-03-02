<?php

namespace App\Modules\InternConsumption\Providers;

use App\Jobs\Queue;
use App\Modules\InternConsumption\Jobs\InterConsumptionCron;
use App\Modules\InternConsumption\Models\InternConsumption;
use App\Modules\InternConsumption\Models\InternConsumption\Item;
use Caffeinated\Modules\Support\ServiceProvider;
use DB;
use Illuminate\Console\Scheduling\Schedule;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'intern-consumption');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'intern-consumption');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->booted(function () {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function () {
                $queue = Queue::QUEUE_LOW;
                $qty = DB::table('jobs')
                    ->where('queue', $queue)
                    ->where('payload', 'like', "%InterConsumptionCron%")
                    ->count();
                if ($qty === 0) {
                    InterConsumptionCron::dispatch()->onQueue($queue);
                }
            })
                ->everyFiveMinutes()
                ->name("intern-consumption")
                ->withoutOverlapping(60);
        });
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
