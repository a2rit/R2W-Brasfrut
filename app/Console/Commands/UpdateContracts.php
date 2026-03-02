<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Partners\Models\Partner\Contract;
use Carbon\Carbon;

class UpdateContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o valor residual dos contratos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $contracts = Contract::where("endDate", '>=', Carbon::now()->format('Y-m-d'))->get();
        foreach($contracts as $key => $contract){
            $contract->updateResidualValue(Carbon::now()->format('Y-m-d'));
        }
    }
}
