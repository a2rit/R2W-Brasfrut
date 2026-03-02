<?php

namespace App\Console\Commands\NFCe;

use App\Models\PontoVenda;
use Exception;
use Throwable;

class LoadFilesCommand extends BaseCommand
{
    protected $signature = 'nfce:load-files {pvId? : ID do ponto de venda}
                           {--all : ler arquivos de todos os pontos de venda}';

    protected $description = 'Load NFCe XML files';

    public function __construct()
    {
        parent::__construct(app('NFCeLogger'));
    }

    public function handle()
    {
        $this->start();
        try {
            if ($this->option('all')) {
                $pvs = PontoVenda::all();
            } elseif ($this->argument('pvId')) {
                $pvs = PontoVenda::where('id', $this->argument('pvId'))->get();
            } else {
                throw new Exception('Informe o pvId ou a opção --all');
            }
            foreach ($pvs as $pv) {
                $this->logAndOutputInfo("Processando arquivos do PV {$pv->id} - {$pv->nome}");
                $pv->processarArquivos();
                $pv->processarArquivosContingencia();
                $this->logAndOutputInfo("Processado arquivos do PV {$pv->id} - {$pv->nome} {$this->partialTimeDiff()}");
            }
        } catch (Throwable $e) {
            $this->logAndOutputError("Erro ao processar arquivos: {$e->getMessage()}");
        }

        $this->end();
    }
}
