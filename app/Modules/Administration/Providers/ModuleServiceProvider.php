<?php

namespace App\Modules\Administration\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'administration');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'administration');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations', 'administration');
        if(!$this->app->configurationIsCached()) {
            $this->loadConfigsFrom(__DIR__.'/../config');
        }
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
