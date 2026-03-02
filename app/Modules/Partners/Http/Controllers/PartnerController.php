<?php

namespace App\Modules\Partners\Http\Controllers;

use App\Modules\Partners\Models\Partner;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;

class PartnerController extends Controller
{   
    public function getCLient(Request $request){
        $name  = '%'.$request->get('q').'%';
        $sap = new Company(false);
        return $sap->query("select CardCode as value, concat(CardCode, ' - ', CardName) as name from OCRD where CardName like '{$name}' and cardtype = 'c'");
    
    }
    public function getProvider(Request $request){

        $sap = new Company(false);

        if(!empty($request->get('query'))){
            $name  = '%'.$request->get('query').'%';
            $providers = $sap->query("SELECT TOP 10 CardName as value, CardCode as data from OCRD where CardName like '%{$name}%' and cardtype = 'S'");
            return response()->json(["query" => $name, "suggestions" => $providers]);
        }

        $name  = '%'.$request->get('q').'%';
        return $sap->query("SELECT TOP 10 CardCode as value, concat(CardCode, ' - ', CardName) as name from OCRD where CardName like '{$name}' and cardtype = 'S'");

    }
}
