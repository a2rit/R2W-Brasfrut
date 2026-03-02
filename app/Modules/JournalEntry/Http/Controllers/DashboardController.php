<?php

namespace App\Modules\JournalEntry\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use App\User;
use App\User\CostCenter;
use Carbon\Carbon;
use Auth;

class DashboardController extends Controller
{
    public function index(){
        $user = User::find(Auth::user()->id);
        $selectedCostCenter = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode')->pluck('costCenterCode')->first();

        if(empty($selectedCostCenter)){
            return Redirect::back()->withErrors('É necessário informar os centros de custos do usuário na tela de configuração de usuários');
        }
        
        $selectedCostCenter2 = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode2')->pluck('costCenterCode2')->first();
        $fist_date = Carbon::now()->subYear();
        $last_date = Carbon::now();
        $sap = new Company(false);

        $contas_a_pagar = $sap->getDb()->table('VW_CONTAS_A_PAGAR')
                            ->select("VW_CONTAS_A_PAGAR.VALOR EM ABERTO", "VW_CONTAS_A_PAGAR.GroupCode", "OCRG.GroupName")
                            ->join("OCRG", 'VW_CONTAS_A_PAGAR.GroupCode', "=", "OCRG.GroupCode")
                            ->whereBetween('VW_CONTAS_A_PAGAR.DT VENCTO', [$fist_date, $last_date])
                            ->where('OcrCode', $selectedCostCenter);
                            
        $contas_pagas = $sap->getDb()->table('VW_CONTAS_PAGAS')
            ->select("VW_CONTAS_PAGAS.VALOR PAGO", "VW_CONTAS_PAGAS.GroupCode", "OCRG.GroupName")
            ->join("OCRG", 'VW_CONTAS_PAGAS.GroupCode', "=", "OCRG.GroupCode")
            ->whereBetween('VW_CONTAS_PAGAS.DT VENCTO', [$fist_date, $last_date]);

        if($selectedCostCenter === '1.0'){
            $contas_a_pagar->where('VW_CONTAS_A_PAGAR.OcrCode2', $selectedCostCenter2);
        }

        $contas_a_pagar = $contas_a_pagar->get();
        $contas_pagas = $contas_pagas->get();

        return view("journal-entry::dashboard", compact('contas_a_pagar', 'contas_pagas', 'selectedCostCenter', 'selectedCostCenter2', 'fist_date', 'last_date'), $this->options());
    }

    public function filter(Request $request){
        $sap = new Company(false);
        $user = User::find(Auth::user()->id);
        $selectedCostCenter = $request->get('costCenter');
        $selectedCostCenter2 = $request->get('costCenter2');
        $fist_date = $request->data_fist ?? Carbon::now()->subYear();
        $last_date = $request->data_last ?? Carbon::now();

        $contas_a_pagar = $sap->getDb()->table('VW_CONTAS_A_PAGAR')
            ->select("VW_CONTAS_A_PAGAR.VALOR EM ABERTO", "VW_CONTAS_A_PAGAR.GroupCode", "OCRG.GroupName")
            ->join("OCRG", 'VW_CONTAS_A_PAGAR.GroupCode', "=", "OCRG.GroupCode")
            ->where('OcrCode', $selectedCostCenter)
            ->whereBetween('VW_CONTAS_A_PAGAR.DT VENCTO', [$fist_date, $last_date]);

        $contas_pagas = $sap->getDb()->table('VW_CONTAS_PAGAS')
            ->select("VW_CONTAS_PAGAS.VALOR PAGO", "VW_CONTAS_PAGAS.GroupCode", "OCRG.GroupName")
            ->join("OCRG", 'VW_CONTAS_PAGAS.GroupCode', "=", "OCRG.GroupCode")
            ->whereBetween('VW_CONTAS_PAGAS.DT VENCTO', [formatdate($fist_date), formatdate($last_date)]);

        if($selectedCostCenter == '1.0'){
            $contas_a_pagar->where('VW_CONTAS_A_PAGAR.OcrCode2', $selectedCostCenter2);
        }

        $contas_a_pagar = $contas_a_pagar->get();
        $contas_pagas = $contas_pagas->get();

        return view("journal-entry::dashboard", compact('contas_a_pagar', 'contas_pagas', 'selectedCostCenter', 'selectedCostCenter2', 'fist_date', 'last_date'), $this->options());
    }

    private function options(){
        $sap = new Company();
        $user = User::find(Auth::user()->id);
        $user_CostCenters = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode')->pluck('costCenterCode');
        $user_CostCenters2 = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode2')->pluck('costCenterCode2');
        
        $costCenters = $sap->getDb()
                        ->table('OPRC')
                        ->where('Active', '=', 'Y')
                        ->where('DimCode', 1)
                        ->where('VALIDTO', NULL)
                        ->whereIn('PrcCode', $user_CostCenters)
                        ->get(['PrcCode as value', 'PrcName as name']);

        $costCenters2 = $sap->getDb()
                        ->table('OPRC')
                        ->where('Active', '=', 'Y')
                        ->where('DimCode', 2)
                        ->where('VALIDTO', NULL)
                        ->whereIn('PrcCode', $user_CostCenters2)
                        ->get(['PrcCode as value', 'PrcName as name']);
        return compact('costCenters', 'costCenters2');
    }
}
