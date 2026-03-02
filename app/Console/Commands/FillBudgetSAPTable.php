<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Illuminate\Console\Command;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Illuminate\Support\Str;

class FillBudgetSAPTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-budget-sap-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pega dados da API AllStrategy e joga na tabela @A2RORCPC';

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
        try {
            $login = "webservice.987@allstrategy.com.br";
            $senha = "h2B4xbp$";
            $competencia_inicio = Carbon::now()->startOfMonth();
            $competencia_fim = Carbon::now()->endOfMonth();

            // $competencia_inicio = Carbon::createFromDate("2025", "06", "01")->startOfMonth();
            // $competencia_fim = Carbon::createFromDate("2025", "06", "01")->endOfMonth();

            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::create(2025, 6, 1);
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);
            // dd($period->getEndDate());
            foreach ($period as $startDate) {
                // dd($startDate->startOfMonth()->format("Y-m-d"));
                $startDate2 = $startDate->copy();
                $competencia_fim = $startDate->endOfMonth();
                $competencia_inicio = $startDate2->startOfMonth();
                $BR_formated_competencia_inicio = $competencia_inicio->format("d/m/Y");
                $BR_formated_competencia_fim = $competencia_fim->endOfMonth()->format("d/m/Y");

                // resetar a tabela de orçamento
                $url = "https://webservice-plano.allstrategy.com.br/api/dre?LOGIN=$login&SENHA=$senha&COMPETENCIA_INICIO=$BR_formated_competencia_inicio&COMPETENCIA_FIM=$BR_formated_competencia_fim";
    
                // Inicialize a sessão cURL
                $ch = curl_init();
    
                // Configure as opções do cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
                // Execute a requisição
                $response = curl_exec($ch);
    
                // Verifique se houve erro na execução
                if (curl_errno($ch)) {
                    die('Erro ao realizar a requisição: ' . curl_error($ch));
                }
    
                // Feche a sessão cURL
                curl_close($ch);
    
                // Converta a resposta JSON para um array PHP
                $data = json_decode($response, true);
    
                // Verifique se a conversão foi bem-sucedida
                if ($data === NULL) {
                    die('Erro ao decodificar a resposta JSON.');
                }
    
                $sap_query = new Company(false);
    
                // apaga todos os registros em que a competencia é igual ao inicio e fim
                $sap_query->getDb()->table("@A2RORCPC")
                    ->whereDate("U_A2RDIOPC", ">=", $competencia_inicio->format("Y-m-d"))
                    ->whereDate("U_A2RDFOPC", "<=", $competencia_fim->format("Y-m-d"))
                    ->delete();

                // continue;
    
                // pega o ultimo DocEntry para ser utilizado na inserção futuramente
                $cont = $sap_query->getDb()->table("@A2RORCPC")->select("DocEntry")->orderBy('DocEntry', 'desc')->first()->DocEntry ?? 0;
    
                // $filter_conta_externa = array_filter($data, function($item) {
                //     if (strpos($item['CONTA_EXTERNA'], "3.3.2.2") === 0) {
                //         return true;
                //     }else if((strpos($item['CONTA_EXTERNA'], "3.2.1.1.2") === 0 && $item["ESTRUTURA_CONTA"] == "4.2.1.1.0002")
                //         || (strpos($item['CONTA_EXTERNA'], "3.2.1.1.3") === 0 && $item["ESTRUTURA_CONTA"] == "4.2.1.1.0003")){
                //         return true;
                //     }
                // });
    
                // dd($filter_conta_externa);
                // dd()
                foreach ($data as $index => $budget_data) {

                    // if($budget_data["COD_CENTROCUSTO"] == '3.1' && $budget_data["CONTA_EXTERNA"] == '3.3.2.2.104'){
                    //     dd($budget_data);
                    // }
                    
                    $cont++;
    
                    $account = $budget_data["CONTA_EXTERNA"] ?? "99999999";
                    $code = "BGT." . str_replace(".", "", $budget_data["COD_CENTROCUSTO"]) . "." . str_replace(".", "", $budget_data["ESTRUTURA_CONTA"]) . str_replace(".", "", $competencia_inicio) . Str::random(5);
    
                    // if( $budget_data["COD_CENTROCUSTO"] == '2.2' && $budget_data["NOME_CONTA"] == "Consultoria e Auditoria"){
                    //     dd($budget_data, $account, $code);
                    // }
                    $data = [
                        'DocEntry' => $cont,
                        'Code' => (string) $code,
                        'Name' => (string) $account,
                        'U_A2RCC' => $budget_data["COD_CENTROCUSTO"],
                        'U_A2RDIOPC' => $competencia_inicio->format("Y-m-d"),
                        'U_A2RDFOPC' => $competencia_fim->format("Y-m-d"),
                        'U_A2RVLROPC' => (float)abs(number_format($budget_data["VALOR_ORCADO"], 5, ".", "")),
                        'U_A2RVLRORCU' => (float)abs(number_format($budget_data["VALOR_REALIZADO"], 5, ".", ""))
                    ];
    
                    $sap_query->getDb()->table("@A2RORCPC")->insert($data);
                }

                $this->info("MES: ". $startDate->format("d-m-Y"));
                // $startDate->addMonth(); // Incrementa para o próximo mês
            }


        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
}
