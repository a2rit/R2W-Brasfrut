<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContaController extends Controller
{
    public function mudarSenha()
    {
        return view("auth.senha");
    }

    public function mudarSenhaPost(Request $request)
    {
        if(\Hash::check($request->get("password"), \Auth::user()->password)){
            if(strlen($request->get("new_password")) < 6){
                return redirect()->back()->with('mensagem', ['class'=>'danger', 'titulo'=>'Erro!', 'mensagem'=>'A senha deve ter no mínimo 6 caracteres!']);
            }
            if($request->get("new_password") === $request->get("password_confirmation")){
                $user = \Auth::user();
                $user->password = bcrypt($request->get("new_password"));
                $user->save();
                return redirect()->back()->with('mensagem', ['class'=>'success', 'titulo'=>'Sucesso!', 'mensagem'=>'Senha alterada com sucesso!']);
            }
            return redirect()->back()->with('mensagem', ['class'=>'danger', 'titulo'=>'Erro!', 'mensagem'=>'Senhas não conferem!']);
        }
        return redirect()->back()->with('mensagem', ['class'=>'danger', 'titulo'=>'Erro!', 'mensagem'=>'Senha atual inválida!']);
    }
}
