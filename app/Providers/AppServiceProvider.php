<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('pt_BR');
        setlocale(LC_TIME, "pt_BR");
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        require_once __DIR__ . '/../Http/Helpers/Navigation.php';

        $this->app->singleton('NFCeLogger', function() {
            $logger = new Logger('NFCe');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/NFCe.log'), config('log_max_files', 90)));

            return $logger;
        });

        $this->app->singleton('PurchaseRequestJobLogger', function() {
            $logger = new Logger('PurchaseRequestJobLogger');
            $logger->pushHandler(
                new RotatingFileHandler(storage_path('logs/purchase-request.log'), config('log_max_files', 90))
            );

            return $logger;
        });
    }
}
