<?php

namespace App\Http\Controllers;


use App\Models\Alertas;

class AlertasController extends Controller
{
 
    public function listar($type)
    {
        $alertas = new Alertas;
        $all = Alertas::where('id_user',auth()->user()->id)
            ->where('type_document',$type)
            ->orderBy('id', 'desc')
            ->paginate(30);
        
        return view('alertas.index', compact('alertas', 'all'));
    }

    public function abrir($id)
    {
        $alerta = Alertas::find($id);
        $url = $alerta->getUrl($alerta->type_document);
        $alerta->status = '0';
        $alerta->save();

        return redirect()->route($url,$alerta->id_document);
    }

}
