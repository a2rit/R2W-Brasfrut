<?php

namespace App;

use JasperPHP;
use Exception;

class JasperReport
{

    public function generateReport($model_relatory, $output, $output_extension, $data = [], $language = 'pt_BR', $database)
    {
        
        /**
         * Caso de algum erro durante a geração do relatório, troque a funcao 'execute()' por 'output()'
        */

        try {
            $file = pathinfo($model_relatory);
            if($file['extension'] == 'jrxml'){
                JasperPHP::compile($model_relatory)->execute();
                $model_relatory = str_replace($file['extension'], 'jasper', $model_relatory);
            }

            $report = JasperPHP::process(
                $model_relatory, // (Obrigatório) caminho e arquivo de modelo que será utilizado.
                $output, // (Obrigatório) caminho completo para onde o arquivo será enviado.
                $output_extension, // (Obrigatório) array de extensões de arquivos que irão ser geradas
                $data, // (Opcional) array de dados que serão enviados para o template
                $this->getDatabaseConfig($database), // (Opcional) array de conexão com o banco de dados
                $language, // (Opcional) linguagem utilizada. 
            )->output();

            dd(str_replace("^^^", '', $report));

            $file = $output.'.'.$output_extension[0];
            
            if(!file_exists($file)){
                throw new Exception("The file $file not exists", 1);
            }

            return $file; // retorna o caminho do arquivo
        } catch (\Throwable $e) {
            throw new Exception("JasperReport: ".$e->getMessage(). "-". $e->getLine(), 1);
        }
    }

    private function getDatabaseConfig($database)
    {
        switch ($database) {
            case 'sap':
                return config('jasper.sap');
                break;

            case 'r2w':
                return config('jasper.r2w');
                break;
        }
    }
}
