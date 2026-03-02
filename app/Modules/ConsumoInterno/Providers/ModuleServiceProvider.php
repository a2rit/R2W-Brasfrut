<?php

namespace App\Modules\ConsumoInterno\Providers;

use App\Modules\ConsumoInterno\Models\Lancamento;
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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'consumo-interno');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'consumo-interno');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'consumo-interno');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function () {
                $lancamento = new Lancamento();
                $lancamento->cron();
            })->cron("00 * * * *")->name("consumo-interno")->withoutOverlapping(10);
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
