<?php

namespace App\Console\Commands\WindowsQueue;

use Illuminate\Queue\Console\RestartCommand as BaseRestartCommand;

class RestartCommand extends BaseRestartCommand
{
    use HasWindowsServiceNameOption;
    public function __construct()
    {
        parent::__construct();
        $this->addWindowsServiceNameOption();
    }

    public function handle()
    {
        $windowsServiceName = $this->getWindowsServiceNameOption();
        if ($windowsServiceName) {
            $cacheKey = 'windows:service:queue:restart:' . $windowsServiceName;
            $this->laravel['cache']->forever($cacheKey, $this->currentTime());
            $this->info('Windows Queue restart signal.');
        } else {
            parent::handle();
        }
    }
}
