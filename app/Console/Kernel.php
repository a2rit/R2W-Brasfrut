<?php

namespace App\Console;

use App\Models\NFCe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('nfce:load-files', ['1'])
            ->everyFiveMinutes()
            ->name('schedule-nfce-load-xml-bar')
            ->withoutOverlapping(60);

        $schedule->command('nfce:load-files', ['2'])
            ->hourly() //@todo reduzir o intervalo de tempo quando puder mover os XMLs de pasta.
            ->name('schedule-nfce-load-xml-rest')
            ->withoutOverlapping(60);

        $schedule->command('nfce:load-files', ['3'])
            ->everyThirtyMinutes()
            ->name('schedule-nfce-load-xml-eventos-rest')
            ->withoutOverlapping(60);

        $schedule->call(function () {
            NFCe::tarefaAtualizarItensSemCodigoSap();
        })
            ->name('check-items-without-sap-code')
            ->hourly()
            ->withoutOverlapping(120);

        $schedule->command('nfce:fallback-sync')
            ->name('nfce-fallback-sync')
            ->dailyAt('14:44')
            ->withoutOverlapping(120);

        $schedule->command('contract:update')
            ->name('contract-update')
            ->dailyAt('00:00')
            ->withoutOverlapping(120);

//        $schedule->command('nfce:production-orders')
//            ->name('nfc-production-orders')
//            ->daily()
//            ->withoutOverlapping(120);

//        $schedule->command('nfce:sync')
//            ->name('geral-nfce')
//            ->daily()
//            ->withoutOverlapping(120);

//        $schedule->command('nfce:sync-payments')
//            ->name('nfce-payments')
//            ->withoutOverlapping(60)
//            ->daily();

        $schedule->command('check-purchase-order-sap:r2w')
            ->name('purchase-order-sap-to-r2w')
            ->everyThirtyMinutes()
            ->withoutOverlapping(20);

        $schedule->command('sync:uploads')
            ->name('sync-uploads')
            ->everyThirtyMinutes()
            ->withoutOverlapping(10);

        $schedule->command('app:fill-budget-sap-table')
            ->name('app:fill-budget-sap-table')
            ->hourly();

        // $schedule->command('sync-advance-payments:r2w')
        //     ->name('sync-advance-payments')
        //     ->everyThirtyMinutes()
        //     ->withoutOverlapping(5);

        // Exclui arquivos de log antigos.
        //$path = '"C:\\ProgramData\\SAP\\SAP Business One\\Log\\SAP Business One\\administrador\\DIAPI"';
        $path = '"C:\\Users\\Todos os Usuários\\SAP\\SAP Business One\\Log\\SAP Business One\\ICBSAP01$\\BusinessOne"';
        $command = '"cmd /c del @path"';
        $schedule->exec("FORFILES /P {$path} /M php*.csv /D -30 /C {$command}")->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
