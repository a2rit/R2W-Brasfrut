<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class Permissao
 * @package App\Http\Middleware
 * @deprecated Use CheckPermission
 */
class Permissao
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $grupo
     * @return mixed
     */
    public function handle($request, Closure $next, $grupo = "admin")
    {
        if(!\Auth::user()->ativo){
            return redirect("/")->with('mensagem', ['class'=>'danger', 'titulo'=>'Erro!', 'mensagem'=>"Usuário inativo!"]);
        }

        if(!\Auth::user()->$grupo){
            return redirect("/")->with('mensagem', ['class'=>'danger', 'titulo'=>'Erro!', 'mensagem'=>"Acesso Negado!"]);
        }
        return $next($request);
    }
}
