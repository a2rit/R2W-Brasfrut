<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Litiano\Sap\Company;
use Illuminate\Support\Facades\Response;
use App\GrupoWhs;

class GrupoWHSController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('grupo.index',$this->option());
    }
	private function option(){
		$sap =  new Company(false);
        $warehouses = $sap->getDb()->table('OWHS')
        ->where('Inactive', '=', 'N')
        ->get(['WhsCode', 'WhsName']);
        $items = GrupoWhs::where('status','=', true)->get();
		$type = [
		   '1' => 'Uso e consumo',
		   '2' => 'Manutenção',
		   '3' => 'Eventos',
		];
		return compact('warehouses', 'items', 'type');
	}
    public function edit($id){
        $head = GrupoWhs::find($id);

        return view('grupo.index',array_merge(['head'=>$head], $this->option()));
    }
    public function delete($id){
        try {
            $head = GrupoWhs::find($id);
            $head->delete();
        
            return redirect()->route('grupo.deposito.index')->withSuccess('Removido com sucesso!');
        } catch (\Exception $e) {
          return redirect()->route('grupo.deposito.index')->withErrors($e->getMessage());
        }
    }
    public function store(Request $request){
        try {
            $sap =  new Company(false);
            $id = $request->get('id', false);
            $code = $request->get('whsCode');
            
            $whsName = $sap->getDb()->table('OWHS')
                    ->where('WhsCode', '=', $code)
                    ->get(['WhsName'])[0]->WhsName;
            if($id) {
                $gwhs = GrupoWhs::find($id);
                $gwhs->idUser = auth()->user()->id;
                $gwhs->type = $request->get('type');
                $gwhs->whsCode = $request->get('whsCode');
                $gwhs->whsName = $whsName;
                $gwhs->save();
            } else {
                $atributes = $request->all();
                $atributes['idUser'] = auth()->user()->id;
                $atributes['type'] = $request->get('type');
                $atributes['whsCode'] = $request->get('whsCode');
                $atributes['whsName'] = $whsName;
                $atributes['status'] = true;
                $gwhs= GrupoWhs::create($atributes);
            }
            return redirect()->route('grupo.deposito.index')->withSuccess('Salvo com sucesso!');
        } catch (\Exception $e) {
          return redirect()->route('grupo.deposito.index')->withErrors($e->getMessage());
        }
    }
}
