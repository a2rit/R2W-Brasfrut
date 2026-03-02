<?php

namespace App\Console\Commands;

use App\Models\Porcionamento\PorcentagemPerda;
use Illuminate\Console\Command;

class CargaPerdas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perdas:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $file = file_get_contents(__DIR__ . '/carga-perdas.csv');
        $lines = explode("\n", $file);
        foreach ($lines as $line) {
            $data = explode(',', $line);
            /** @var PorcentagemPerda $perda */
            $perda = PorcentagemPerda::firstOrNew(['codigo' => $data[0]]);
            $perda->codigo = $data[0];
            $perda->porcentagem_base = $data[1] * 100;
            $perda->porcentagem_aceita = $data[2] * 100;
            $perda->comentario = 'Importado da planilha';
            $perda->save();
        }
    }
}
