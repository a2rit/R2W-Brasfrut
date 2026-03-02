<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Upload;
use App\LogsError;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\StockLoan\StockLoan;
use App\Modules\Inventory\Models\StockLoan\Item;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Modules\Inventory\Models\Requisicao\Requests;
use Illuminate\Support\Facades\DB;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\Response;
use App\Modules\Inventory\Jobs\StockLoanToSAP;
use Carbon\Carbon;
use App\User;

use App\JasperReport;

class StockReportController extends Controller{

    public function index(){

        $sap = new Company(false);

        $warehouses = DB::select("SELECT  DISTINCT 
                                    T2.[WhsCode],
                                    T3.[WhsName]
                                FROM [SAPHOMOLOGACAO].[dbo].[OITW] T2   
                                INNER JOIN [SAPHOMOLOGACAO].[dbo].[OWHS] T3 ON T2.[WhsCode] = T3.[WhsCode]");

        $groups = DB::select("SELECT DISTINCT
                                T0.[ItmsGrpCod],
                                T1.[ItmsGrpNam]
                            FROM [SAPHOMOLOGACAO].[dbo].[OITM] T0
                            INNER JOIN [SAPHOMOLOGACAO].[dbo].[OITB] T1 ON T0.[ItmsGrpCod] = T1.[ItmsGrpCod]");

        $itemProperties = $sap->getDb()->table('OITG')->select('ItmsTypCod as value', 'ItmsGrpNam as name')->get();

        return view("inventory::report.index", compact('warehouses', 'groups', 'itemProperties'));
    }


    public function reportGenerate(Request $request){

        $data = [
            'warehouse' => $request->warehouse ?? 'NULL', 
            'itemGroup' => $request->group ?? 'NULL',
            'property' => $request->property ?? 'NULL'
        ];
        
        try{
            if($request->format == '2'){

                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryCountPerGroup.jasper";
                
                if(!file_exists($relatory_model)){
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryCountPerGroup.jrxml";
                }

                $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'por-grupo';
                $output = public_path('/relatorios'.'/'.$file_name);
                $report = $report->generateReport($relatory_model, $output, ['xls'], $data, 'pt_BR', 'sap');
        
                return response()->file($report)->deleteFileAfterSend(true);

            }else{
                if($request->type == '1'){
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryListToCount.jasper";
                    
                    if(!file_exists($relatory_model)){
                        $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryListToCount.jrxml";
                    }

                    $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'lista-para-contagem';
                    $output = public_path('/relatorios'.'/'.$file_name);
                    $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'sap');
            
                    return response()->file($report)->deleteFileAfterSend(true);
    
                } else {
                    $report = new JasperReport();
                    $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryCountPerGroup.jasper";
                    
                    if(!file_exists($relatory_model)){
                        $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryCountPerGroup.jrxml";
                    }

                    $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'por-grupo';
                    $output = public_path('/relatorios'.'/'.$file_name);
                    $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'sap');
            
                    return response()->file($report)->deleteFileAfterSend(true);
                }
            }
          
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('E0008', 'Listando o adiantamentos ao fornecedor',$e->getMessage());
           return view('inventory::stockLoan.index')->withErrors($e->getMessage());
        }
    }

    public function relatorioPerdas(Request $request){
        
        $report = new JasperReport();
        $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryPerdas.jasper";
        
        if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/InventoryPerdas.jrxml";
        }

        $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'perdas';
        $output = public_path('/relatorios'.'/'.$file_name);
        $report = $report->generateReport($relatory_model, $output, ['pdf'], ['itemCode'=>$request->itemCode, 'initialDate'=>$request->initialDate, 'lastDate'=>$request->lastDate, 'warehouse'=>$request->warehouse, 'group'=>$request->group], 'pt_BR', 'sap');
        
        return response()->file($report)->deleteFileAfterSend(true);

    }
}
