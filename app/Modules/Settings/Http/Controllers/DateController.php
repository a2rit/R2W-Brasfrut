<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Base;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\LogsError;
use App\Modules\Settings\Models\Config_date;
use Litiano\Sap\Company;

class DateController extends Controller
{   
    // public function __construct(){
    //   $this->middleware(function ($request, $next){
    //       if(!checkAccess('admin_config')){
    //           return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
    //       }else{
    //           return $next($request);
    //       }
    //   });
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
      try{
        $sap = new Company(false);
        $cDate = Config_date::join('users','users.id','=', 'config_dates.idUser')
        ->select('users.name','config_dates.id','config_dates.codSAP','config_dates.description','config_dates.amount')->orderBy('id', 'desc')->get();
        $paymentConditions = $sap->getDb()->table('OCTG')->select('GroupNum as value', 'PymntGroup as name')->get();
        return view('settings::date.create', compact('paymentConditions', 'cDate'));
      } catch (\Exception $e) {
        $logErros = new LogsError();
        $logErros->saveInDB('E983*', $e->getFile().'|'.$e->getLine(), $e->getMessage());
        return redirect()->back()->withErrors($e->getMessage());
      }

    }

    public function save(Request $request){
      try{
        $sap = new Company(false);
        $date = new Config_date();
        $attributes = $attributes = $request->except(['_token']);
        $attributes['idUser'] = auth()->user()->id;
        $attributes['description'] = $sap->getDb()->table('OCTG')->select('PymntGroup as name')->where('GroupNum', '=', $request->codSAP)->get()[0]->name;
        $date->saveInDB($attributes);
        return redirect()->route('settings.date.index')->withSuccess('Operação realizada com successo!');
      }catch (\Exception $e) {
        $logErros = new LogsError();
        $logErros->saveInDB('E0061',$e->getFile().'|'.$e->getLine(), $e->getMessage());
        return redirect()->route('settings.date.index')->withErrors( $e->getMessage());
      }
    }
    public function remove($id){
      try{
        $cdate = Config_date::find($id);
        $cdate->delete();
        return redirect()->route('settings.date.index')->withSuccess('Operação realizada com successo!');
      } catch (\Exception $e) {
        $logErros = new LogsError();
        $logErros->saveInDB('E98A34*', $e->getFile().'|'.$e->getLine(), $e->getMessage());
        return redirect()->back()->withErrors($e->getMessage());
      }
    }
}
