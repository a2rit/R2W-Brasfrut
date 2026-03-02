<?php

namespace App\Console\Commands\WindowsQueue;

use App\Jobs\WindowsQueueWorker;
use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;

class WorkCommand extends BaseWorkCommand
{
    use HasWindowsServiceNameOption;
    public function __construct(WindowsQueueWorker $worker)
    {
        parent::__construct($worker);
        $this->addWindowsServiceNameOption();
    }

    public function handle()
    {
        $windowsServiceName = $this->getWindowsServiceNameOption();
        if ($windowsServiceName) {
            $this->worker->setWindowsServiceName($windowsServiceName);
        }
        $this->setWindowsCtrlEventHandler();

        parent::handle();
    }

    protected function setWindowsCtrlEventHandler(): void
    {
        if (!function_exists('sapi_windows_set_ctrl_handler')) {
            return;
        }

        sapi_windows_set_ctrl_handler(function (int $event) {
            $serviceName = $this->getWindowsServiceNameOption();

            $this->laravel['log']->info(
                'call sapi_windows_set_ctrl_handler',
                compact('serviceName', 'event')
            );
            $this->info('call sapi_windows_set_ctrl_handler ' . $event);

            $arguments = $serviceName ? ['windowsServiceName' => $serviceName] : [];
            $this->call('queue:restart', $arguments);
        });
    }
}
