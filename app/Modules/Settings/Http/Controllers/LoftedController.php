<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\Lofted;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\DB;
use App\User;
use App\SapUtilities;
use App\LogsError;

class LoftedController extends Controller
{   
  use SapUtilities;

    public function index(){
      return view('settings::lofted.create',$this->option());
     
    }

    private function option(){
      $sap = new Company(false);

      $doc = [
        ["value" => 0, "name" => "Pedido de Compras"],
        ["value" => 1, "name" => "NFE Serviços"]
      ];

      $items = Lofted::orderBy('id','desc')->get();
      
      $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
      $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");

      return compact('doc','items', 'centroCusto', 'centroCusto2');
    }

    public function store(Request $request){
      try {
        $attributes = $request->except(['prices', '_token', 'id', 'first', 'last']);
        
       if ($request->get('id')) {
            $attributes["docNum"] = $request->get('document');
            $attributes["docName"] = Lofted::DOCUMENTS_TEXTS[$request->get('document')];

            $attributes["quantity"] = $request->get('quantity');
            $attributes["first"] = clearNumberDouble($request->get('first'));
            $attributes["last"] = clearNumberDouble($request->get('last'));
            $attributes["cost_center_id"] = $request->get('cost_center_id');
            $attributes["cost_center_2_id"] = $request->get('cost_center_2_id');
            $item = Lofted::find($request->get('id'));
            $item->fill($attributes);
            $item->save();
        } else {
            $attributes["docNum"] = $request->get('document');
            $attributes["docName"] = Lofted::DOCUMENTS_TEXTS[$request->get('document')];
            $attributes["idUser"] = auth()->user()->id;
            $attributes["quantity"] = $request->get('quantity');
            $attributes["first"] = clearNumberDouble($request->get('first'));
            $attributes["last"] = clearNumberDouble($request->get('last'));
            $attributes["cost_center_id"] = $request->get('cost_center_id');
            $item = Lofted::create($attributes);
        }
        return redirect()->route('settings.lofted.index')->withSuccess('Operação realizada com Sucesso!');
      } catch (\Throwable $e) {
        $logsErrors = new LogsError();
        $logsErrors->saveInDB('Ef05572', $e->getFile().' | '.$e->getLine(),$e->getMessage());
        return redirect()->route('settings.lofted.index')->withErrors($e->getMessage());
      }
    }

    public function remove($id){
     try{
        Lofted::find($id)->delete();
        return redirect()->route('settings.lofted.index')->withSuccess('Removido com sucesso!');
      } catch (\Exception $e) {
        return redirect()->route('settings.lofted.index')->withErrors($e->getMessage());
      }
    }
    
    public function edit($id){
      $head = Lofted::find($id);
     return view('settings::lofted.create', array_merge(['head'=>$head]), $this->option());
    }
}
