<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Config;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\DB;
use App\User;
use App\LogsError;

class SettingsController extends Controller
{ 

    public function index(){
      return view('settings::index');
    }

    public function geral(){
      return view('settings::geral.index');
    }

    public function general(){
      $sap = new Company(false);
      $itemSeries = $sap->getDb()->select("select Series as value, SeriesName as name from NNM1 where IsManual = 'N' and Locked = 'N' and ObjectCode = :objectCode", ['objectCode' => (string)BoObjectTypes::oItems]);
      $bpcSeries = $sap->getDb()->select("select Series as value, SeriesName as name from NNM1 where IsManual = 'N' and Locked = 'N' and DocSubType = 'C' and ObjectCode = :objectCode", ['objectCode' => (string)BoObjectTypes::oBusinessPartners]);
      $bpsSeries = $sap->getDb()->select("select Series as value, SeriesName as name from NNM1 where IsManual = 'N' and Locked = 'N' and DocSubType = 'S' and ObjectCode = :objectCode", ['objectCode' => (string)BoObjectTypes::oBusinessPartners]);
      $acctCode = $sap->getDb()->select("SELECT T0.AcctCode, T0.AcctName FROM OACT T0 WHERE T0.FrozenFor = 'N'");
      $acctCode = $sap->getDb()->select("SELECT T0.AcctCode, T0.AcctName FROM OACT T0 WHERE T0.FrozenFor = 'N'");
      $warehouses = $sap->getDb()->select("select WhsCode as value, WhsName as name from OWHS");
      $settings = Config::all();

      return view('settings::general', compact('warehouses','itemSeries', 'bpcSeries', 'bpsSeries', 'settings', 'acctCode'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        foreach ($request->except(['_token']) as $code => $value) {
            Config::updateOrCreate(['code' => $code], ['value' => $value]);
        }
        return redirect()->back()->withSuccess("Salvo com sucesso!");
    }

    public function getLogsErros(){
        $logs = LogsError::orderBy('logs_errors.id','desc')
                  ->join('users', 'users.id','=', 'logs_errors.idUser')
                  ->select('logs_errors.value', 'logs_errors.operation', 'logs_errors.created_at', 'logs_errors.message', 'users.name')
                  ->limit(200)
                  ->get();
        return view('settings::logs.errors',['items' => $logs]);
    }

    public function filterLogsErros(Request $request){
      try{
          $sql = LogsError::orderBy('logs_errors.id','desc')
            ->limit(100)
            ->join('users', 'users.id','=', 'logs_errors.idUser')
            ->select('logs_errors.value', 'logs_errors.operation', 'logs_errors.created_at', 'logs_errors.message', 'users.name');
       
        if(!is_null($request->code)) {
            $sql->where('logs_errors.value','=',$request->code);
        }
        if (!is_null($request->name)) {
            $sql->where('users.name','like',"%{$request->name}%");
        }
        if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
            $sql->where('created_at.value','>=',$request->data_fist);
            $sql->where('created_at.value','<=',$request->data_last);
        }
        return view('settings::logs.errors',['items' => $sql->get()]);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0029', 'Consultando logs de erros',$e->getMessage());
         return view('settings::logs.errors')->withErrors($e->getMessage());
      }

    }

}
