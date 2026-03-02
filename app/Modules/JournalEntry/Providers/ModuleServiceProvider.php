<?php

namespace App\Modules\JournalEntry\Providers;

use App\Modules\JournalEntry\Models\JournalEntry;
use Caffeinated\Modules\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ModuleServiceProvider extends ServiceProvider
{
    /* @return void
  */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'journal-entry');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'journal-entry');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
/*
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->call(function(){
                JournalEntry::cron();
            })->name("sync-journal-entries")->everyMinute()->withoutOverlapping(30);
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
