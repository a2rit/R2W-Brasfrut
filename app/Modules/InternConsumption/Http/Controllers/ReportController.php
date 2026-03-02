<?php

namespace App\Modules\InternConsumption\Http\Controllers;

use App\Models\PontoVenda;
use App\Modules\InternConsumption\Models\InternConsumption;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\Snappy\PdfWrapper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Litiano\Sap\NewCompany;
use App\JasperReport;

class ReportController extends Controller
{
    public function index()
    {
        $pos = PontoVenda::all(['id as value', 'nome as text']);
        $statuses = InternConsumption::getStatuses();
        $users = User::orderBy('name')->get(['id as value', 'name as text']);
        $fullNameRaw = \DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as text");
        $requesters = NewCompany::getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as value', $fullNameRaw])
        ;
        $projects = NewCompany::getInstance()
            ->getProjectsQueryBuilder()
            ->get(['PrjCode as value', 'PrjName as text'])
        ;
        $distributionRules = NewCompany::getInstance()
            ->getDistributionRulesQueryBuilder()
            ->get(['OOCR.OcrCode as value', 'OOCR.OcrName as text'])
        ;

        return view(
            'intern-consumption::report.index',
            compact('pos', 'statuses', 'users', 'requesters', 'projects', 'distributionRules')
        );
    }

    public function report(Request $request)
    {

        $data = [
            'pos_id' => !empty($request->pos_id) ? $request->pos_id : "NULL",
            'requester_sap_id' => !empty($request->requester_sap_id) ? $request->requester_sap_id : "NULL",
            'authorizer_user_id' => !empty($request->authorizer_user_id) ? $request->authorizer_user_id : "NULL", 
            'status' => !empty($request->status) ? $request->status : "NULL", 
            'distribution_rule' => !empty($request->distribution_rule) ? $request->distribution_rule : "NULL", 
            'project' => !empty($request->project) ? $request->project : "NULL", 
            'data_inicial' => !empty($request->start_date) ? $request->start_date : "NULL",
            'data_final' => !empty($request->end_date) ? $request->end_date : "NULL",
        ];

        if($request->input('type') !== 'analytic-excel'){
            $data['document_type'] = (String)$request->document_type ?? "NULL";
        }
        
        if($request->input('type') === 'synthetic') {
            $file_name = "InternComsumption-Sintetico";
            $output_file_extension = ["pdf"];
            $relatory_model = storage_path('app/public/relatorios_modelos')."/{$file_name}.jasper";
        } elseif ($request->input('type') === 'analytic') {
            $file_name = "InternConsumption-Analitico";
            $output_file_extension = ["pdf"];
            $relatory_model = storage_path('app/public/relatorios_modelos')."/{$file_name}.jasper";
        } elseif ($request->input('type') === 'analytic-excel') {
            $file_name = "InternConsumption-Excel";
            $output_file_extension = ["xlsx"];
            $relatory_model = storage_path('app/public/relatorios_modelos')."/{$file_name}.jasper";
        }

        if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/{$file_name}.jrxml";
        }
        
        $report = new JasperReport();
        $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'ConsumoInterno';
        $output = public_path('/relatorios'.'/'.$file_name);
        

        $report = $report->generateReport($relatory_model, $output, $output_file_extension, $data, 'pt_BR', 'r2w');
        return response()->file($report)->deleteFileAfterSend(true);

    }
}
