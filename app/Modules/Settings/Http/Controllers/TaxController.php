<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Tax;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\DB;
use App\User;
use App\SapUtilities;
use App\LogsError;
use App\Modules\Invoice\Models\Nfe\Tax as LTax;

class TaxController extends Controller
{   
  use SapUtilities;

    // public function __construct(){
    //   $this->middleware(function ($request, $next){
    //       if(!checkAccess('admin_config')){
    //           return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
    //       }else{
    //           return $next($request);
    //       }
    //   });
    // }
    public function index(){
      return view('settings::tax.index');     
    }
    public function createWebMania(){
      $sap = new Company(false);
      $tax = $this->getTaxOptions($sap);
      $ltax = LTax::where('enabled', LTax::STATUS_OPEN)->get();
      return view('settings::tax.web_mania',compact('tax','ltax'));
    }
    public function removeWebMania($id){
      try {
        DB::beginTransaction();
        $ltax = LTax::find($id);
        $ltax->enabled = LTax::STATUS_CLOSE;
        $ltax->save();
        DB::commit();  
        return redirect()->route('settings.tax.create.web.mania')->withSuccess('Salvo com sucesso!');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('EX89F54',$e->getFile(). ' | '.$e->getLine() ,$e->getMessage());
        return redirect()->route('settings.tax.create.web.mania')->withErrors($e->getMessage());
      }  
    } 
    public function create(){
      $sap = new Company(false);
      $tax = $this->getTaxOptions($sap);
      return view('settings::tax.create',compact('tax'));
    }
    public function saveWebMania(Request $request){
      try {
        DB::beginTransaction();
        $attributes = $request->except(['_token']);
        $attributes['enabled'] = LTax::STATUS_OPEN;
        LTax::create($attributes);
        DB::commit();  
        return redirect()->route('settings.tax.create.web.mania')->withSuccess('Salvo com sucesso!');
      } catch (\Exception $e) {
        DB::rollBack();
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('EX8954',$e->getFile(). ' | '.$e->getLine() ,$e->getMessage());
        return redirect()->route('settings.tax.create.web.mania')->withErrors($e->getMessage());
      }  
    }
    public function store(Request $request){
      try {
        $sap = new Company(false); 
        $id = $request->get('id', false);
        $code = $request->get('codSAP');

        $name = $sap->getDb()->table('OSTC')->select('name')->get()[0]->name;

        if($id) {
            $tax = Tax::find($id);
            $tax->idUser = auth()->user()->id;
            $tax->codSAP = $request->get('codSAP');
            $tax->name = $name;
            $tax->IPI = number_format(clearNumberDouble($request->get('IPI')),4,'.','');
            $tax->ICMS = number_format(clearNumberDouble($request->get('ICMS')),4,'.','');
            $tax->PIS = number_format(clearNumberDouble($request->get('PIS')),4,'.','');
            $tax->COFINS = number_format(clearNumberDouble($request->get('COFINS')),4,'.','');
            $tax->status = true;
            $tax->save();
        } else {
            $atributes = $request->all();
            $atributes['idUser'] = auth()->user()->id;
            $atributes['name'] = $name;
            $atributes['status'] = true;
            $atributes['IPI'] = number_format(clearNumberDouble($request->get('IPI')),4,'.','');
            $atributes['ICMS'] = number_format(clearNumberDouble($request->get('ICMS')),4,'.','');
            $atributes['PIS'] = number_format(clearNumberDouble($request->get('PIS')),4,'.','');
            $atributes['COFINS'] = number_format(clearNumberDouble($request->get('COFINS')),4,'.','');
            $tax = Tax::create($atributes);
        }
        return redirect()->route('settings.tax.index')->withSuccess('Salvo com sucesso!');
      } catch (\Exception $e) {
        return redirect()->route('settings.tax.index')->withErrors($e->getMessage());
      }
    }
    public function getTaxAll(Request $request){
      $query = Tax::select('id','name','codSAP', 'ICMS', 'IPI', 'PIS', 'COFINS');

      $recordsTotal = $query->count();

      $query->offset($request->get("start"));
      $query->limit($request->get("length"));
      $search = $request->get('search');

      if($search['value']) {
          $query->where(function (Builder $where) use ($search) {
              $where->orWhere("codSAP", "like", "%{$search['value']}%");
          });
      }

      return response()->json([
          "draw" => $request->get("draw"),
          "recordsTotal" => $recordsTotal,
          "recordsFiltered" => $query->count(),
          "data" => $query->get()
      ]);
     }

    public function remove($id){
      try{
        $tax = Tax::find($id);
        $tax->delete();
        return redirect()->route('settings.tax.index')->withSuccess('Removido com sucesso!');
      } catch (\Exception $e) {
        return redirect()->route('settings.tax.index')->withErrors($e->getMessage());
      }
    }
    public function edit($id){
      $head = Tax::find($id);
      $sap = new Company(false);
      $tax = $this->getTaxOptions($sap);
      return view('settings::tax.create', compact('head', 'tax'));
    }
}
