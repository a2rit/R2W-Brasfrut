<?php

namespace App\Jobs;

use Illuminate\Queue\Worker;
use Exception;

class WindowsQueueWorker extends Worker
{
    protected string $windowsServiceName;

    public function setWindowsServiceName(string $windowsServiceName): void
    {
        if (!empty($this->windowsServiceName)) {
            throw new Exception('Cannot set Windows service name');
        }
        $this->windowsServiceName = $windowsServiceName;
    }

    protected function getTimestampOfLastQueueRestart()
    {
        if ($this->cache && !empty($this->windowsServiceName)) {
            $cacheKey = "windows:service:queue:restart:{$this->windowsServiceName}";

            return $this->cache->get($cacheKey);
        }

        return parent::getTimestampOfLastQueueRestart();
    }
}
