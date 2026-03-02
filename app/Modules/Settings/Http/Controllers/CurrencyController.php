<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Config;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Modules\Settings\Models\CurrencyRate;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\DB;
use App\User;
use App\LogsError;
use Carbon\Carbon;

class CurrencyController extends Controller
{
/*
  public function __construct(){
    $this->middleware(function ($request, $next){
        if(!checkAccess('admin_config')){
            return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
        }else{
            return $next($request);
        }
    });
  } */
  
  public function index(){
    $errors = false;
    
    if(!is_null(Session::get('currency_sap'))){
      $errors = Session::get('currency_sap');
    }
    return view('settings::currencyQuote.create', array_merge(['error'=>$errors],$this->option()));
  }
  public function option(){
    $items = CurrencyRate::join('users','users.id','=','currency_rates.idUser')
    ->select('currency_rates.*', 'users.name')                    
    ->orderBy('currency_rates.id','desc')
    ->get();
    return compact("items");
  }
  public function store(Request $request){
    try {
        DB::beginTransaction();
        $sap = new Company(false);
        $value = clearNumberDouble($request->rate);
        $attributes = $request->except(['_token','rate']);
        $attributes["idUser"] = auth()->user()->id;
        $attributes["rate"] = $value;
        $id = $request->get('id',false);
        if($id){
          if($request->posting_date == DATE('Y-m-d')){
            $item = CurrencyRate::find($id);
            $item->fill($attributes);
            $item->save();
            $date = $request->posting_date." 00:00:00.000";
            $sap->getDb()->table('ORTT')
                ->where(['RateDate'=> $date, 'Currency'=>$request->coin])
                ->update(['RateDate'=> $request->posting_date, 'Currency'=> $request->coin, 'Rate'=> $attributes["rate"]]);
            DB::commit();
          }else{
            DB::commit();
            return redirect()->back()->withErrors("Opss! Data fora do intervalo permitido");
          }
        }else{
          if($this->checkDate($request)){
              CurrencyRate::create($attributes);
              $date = $request->posting_date." 00:00:00.000";
              $search = $sap->getDb()->table('ORTT')->where(['RateDate'=> $date, 'Currency'=>$request->coin])->get();
              if(count($search) > 0){
                Session::put('currency_sap',$search);
                DB::rollBack();
                return redirect()->route('settings.currency.quote.index', true)->withErrors('Ops! já existe uma cotaçao cadastrada manualmente no SAP para a data '.formatDate($request->posting_date). ' no valor de '. number_format($search[0]->Rate,2,',','.'));
              }else{
                $sap->getDb()->table('ORTT')->insert(['RateDate'=>$request->posting_date,'Currency'=>$request->coin,'Rate'=>$value,'DataSource'=>'I','UserSign' => 1]);
              }
            }else{
              DB::rollBack();
              return redirect()->route('settings.currency.quote.index')->withErrors("Opss! Operação não pode ser realizada pois já existe uma cotação para essa data!");
            }
        }
        DB::commit();
        return redirect()->route('settings.currency.quote.index')->withSuccess('Operação realizada com sucesso!');
      
    } catch (\Exception $e) {
      DB::rollBack();
      $logsErrors = new LogsError();
      $logsErrors->saveInDB('ASF#1', $e->getFile().'|'.$e->getLine(),$e->getMessage());
      return redirect()->back()->withErrors($e->getMessage());
    }
  }
  private function checkDate($request){
    $busca = DB::SELECT("SELECT count(T0.id) as items FROM currency_rates as T0 WHERE T0.posting_date = '{$request->posting_date}' and T0.coin = '{$request->coin}'")[0]->items;
    if($busca > 0){
      return false;
    }else{
      return true;
    }
  }
  public function read($id){
    $head = CurrencyRate::find($id);
    return view('settings::currencyQuote.create',array_merge(['head'=>$head],$this->option()));
  }
  public function response(){
    try {
      DB::beginTransaction();
      $aux = Session::get('currency_sap');
      $attributes = [];

      $attributes["idUser"] = auth()->user()->id;
      $attributes["posting_date"] = $aux[0]->RateDate;
      $attributes["coin"] = $aux[0]->Currency;
      $attributes["rate"] = $aux[0]->Rate;
      
      CurrencyRate::create($attributes);
      DB::commit();
      Session::forget('currency_sap');
      return redirect()->route('settings.currency.quote.index')->withSuccess('Operação realizada com sucesso!');
    
    } catch (\Exception $e) {
      DB::rollBack();
      $logsErrors = new LogsError();
      $logsErrors->saveInDB('ASF52#1', $e->getFile().'|'.$e->getLine(),$e->getMessage());
      return redirect()->back()->withErrors($e->getMessage());
    }

  }

}
