<?php

namespace App\Modules\Inventory\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{


    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'inventory');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'inventory');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        /*
                $this->app->booted(function () {
                    $schedule = $this->app->make(Schedule::class);
                    $schedule->call(function(){
                        Item::cron();
                    })->name("sync-items")->everyMinute()->withoutOverlapping(30);
                });*/
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
