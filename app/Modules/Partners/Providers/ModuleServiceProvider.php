<?php

namespace App\Modules\Partners\Providers;

use App\Modules\Partners\Models\Partner;
use Caffeinated\Modules\Support\ServiceProvider;
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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'partners');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'partners');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->booted(function () {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function(){
                /** @todo mover para uma job */
                Partner::cron();
            })
                ->name("sync-partners")
                ->everyThirtyMinutes()
                ->withoutOverlapping(30);
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
