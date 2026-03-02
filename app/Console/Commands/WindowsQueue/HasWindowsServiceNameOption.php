<?php

namespace App\Console\Commands\WindowsQueue;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @see Command
 */
trait HasWindowsServiceNameOption
{
    private string $optionName = 'windowsServiceName';
    protected function addWindowsServiceNameOption(): void
    {
        $this->addOption($this->optionName, null, InputOption::VALUE_REQUIRED, 'Windows Service Name');
    }

    protected function getWindowsServiceNameOption()
    {
        return $this->option($this->optionName);
    }
}
