<?php

namespace App\Console\Commands\NFCe;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;

abstract class BaseCommand extends Command
{
    protected ?Carbon $startedAt;
    protected ?Carbon $checkPoint;
    protected int $mutexMinutes = 60;

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    protected function start()
    {
        $this->addMutex();

        $this->startedAt = Carbon::now();
        $this->checkPoint = Carbon::now();
        $this->logAndOutputInfo("Iniciou comando {$this->name}");
    }

    protected function end()
    {
        if (isset($this->startedAt)) {
            $tempo = Carbon::now()->diffForHumans($this->startedAt);
            $this->logAndOutputInfo("Finalizou comando {$this->name} {$tempo}");
        }

        $this->removeMutex();
    }

    protected function logAndOutputError(string $message, array $context = [])
    {
        $this->logger->error($message, $context);
        $this->error($message);
    }

    protected function logAndOutputInfo(string $message, array $context = [])
    {
        $this->logger->info($message, $context);
        $this->info($message);
    }

    protected function partialTimeDiff(): string
    {
        $diffForHumans = Carbon::now()->diffForHumans($this->checkPoint);
        $this->checkPoint = Carbon::now();

        return $diffForHumans;
    }

    /**
     * @throws Exception
     */
    private function addMutex()
    {
        if (!Cache::add($this->name, true, $this->mutexMinutes)) {
            $msg = "Mutex fail on {$this->name}";
            $this->logAndOutputError($msg);

            throw new Exception($msg);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function removeMutex()
    {
        Cache::delete($this->name);
    }
}
