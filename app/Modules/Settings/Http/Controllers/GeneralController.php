<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\LogsError;
use App\Upload;

use App\Http\Controllers\Controller;
use App\User;

class GeneralController extends Controller
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
      $settings = DB::SELECT("SELECT * FROM companies order by id desc");
      if(!empty($settings) && isset($settings[0]->id)){
        $id = $settings[0]->id;
          $img = DB::SELECT("SELECT TOP 1 diretory FROM uploads WHERE idReference = '{$id}' order by id desc");
          return view('settings::geral.index', compact('settings', 'img'));
      }else{

        return view('settings::geral.index', compact('settings'));
      }
    }

    public function save(Request $request){
        try{
              $comp = Company::find($request->get('id', false));

              if(is_null($comp)){
                Company::create($request->all());
                saveUpload($request,'companies',$comp->id);
              }else{
                  $comp->company =$request->get('company');
                  $comp->cnpj =$request->get('cnpj');
                  $comp->address =$request->get('address');
                  $comp->number =$request->get('number');
                  $comp->neighborhood =$request->get('neighborhood');
                  $comp->cep =$request->get('cep');
                  $comp->city =$request->get('city');
                  $comp->telephone = $request->get('telephone');
                  $comp->telephone2 = $request->get('telephone');
                  $comp->email =$request->get('email');
                  $comp->save();
                  saveUpload($request,'companies', $request->get('id'));
                }

            return redirect()->route('settings.geral.index')->withSuccess('Operação realizada com sucesso!');
          }catch (\Exception $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('EYU*&92', $e->getFile().' | '.$e->getLine(),$e->getMessage());
            return redirect()->route('settings.geral.index')->withErrors($e->getMessage());

          }
    }
}
