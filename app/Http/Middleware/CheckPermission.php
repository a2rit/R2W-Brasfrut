<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role = 'admin')
    {
        if(!\Auth::check()) {
            return redirect("login");
        }

        if(!\Auth::user()->ativo){
            return redirect("/")->withErrors("Usuário inativo!");
        }

        if(\Auth::user()->admin){
            return $next($request);
        }

        // if(!\Auth::user()->hasRole($role)) {
        //     return redirect("/")->withErrors("Acesso Negado! Verifique com o administrador suas permissões de usuário!");
        // }
        return $next($request);
        /* @see https://laravel-news.com/two-best-roles-permissions-packages */
    }
}
