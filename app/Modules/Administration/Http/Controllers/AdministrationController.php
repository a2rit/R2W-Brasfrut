<?php

namespace App\Modules\Administration\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use App\SapUtilities;
use Litiano\Sap\Company;

class AdministrationController extends Controller
{
    use SapUtilities;
    
   /* public function __construct(){
      $this->middleware(function ($request, $next){
        if(!checkAccess('admin_config')){
        return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
        }else{
          return $next($request);
        }
      });
    }
  */
    // Cadastro de Usuarios
    public function index(){
        return view('administration::userRegistration.index');
    }
    public function create(){
        return view("administration::userRegistration.create", $this->options());
    }
    // Ajuda
    public function help(){
        return view("administration::userRegistration.help");
    }

    public function edit($id){
      $user = User::find($id);
      return view("administration::userRegistration.create",array_merge(['user' =>$user], $this->options()));
    }
    private function options(){
      $sap = new Company(false);
      $whs = $this->getWHSOptions($sap);
      $userClerk = $this->getUserClerkOptions($sap);
      return compact('whs','userClerk');
    }
    public function save(Request $request){
      try {
          $id = $request->get('id', false);
          if($id) {
            if(($request->password === $request->passwordCheck)){
              $user = \App\User::find($id);
              $user->updateInDB($request);
                return back()->withSuccess("Operação realizada com sucesso!");
            }else {
              return back()->withErrors("Ops! as senhas informadas não coencidem");
            }
        }else{
          if(($request->password === $request->passwordCheck)){
            $user = new \App\User();
            $user->saveInDB($request);
            return back()->withSuccess("Operação realizada com sucesso!");
          }else{
              return back()->withErrors("Ops! as senhas informadas não coencidem");
            }
        }
      }catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
      }
    }
    public function search(){
      return view('administration::userRegistration.search');
    }
    public function anyData(Request $request){
        $query = DB::table('users');
        $recordsTotal = $query->count();
        $columns = $request->get("columns");
        $columnsToSelect = ['id','name', 'email','tipo','status','admin'];

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("name", "like", "%{$search['value']}%")
                  ->orWhere("email", "like", "%{$search['value']}%");
        }
        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->get($columnsToSelect)
        ]);
    }

    public function relatory(){
      return view('administration::userRegistration.relatory');
    }
    public function relatoryData(Request $request){
      $query = DB::table('sessions')->join('users','users.id','=', 'sessions.user_id');
      $recordsTotal = $query->count();
      $columns = $request->get("columns");
      $columnsToSelect = ['user_id','name','ip_address', 'user_agent'];

      $search = $request->get('search');
      if ($search['value']) {
          $query->orWhere("name", "like", "%{$search['value']}%")
                ->orWhere("ip_address", "like", "%{$search['value']}%");
      }
      return response()->json([
          "draw" => $request->get("draw"),
          "recordsTotal" => $recordsTotal,
          "recordsFiltered" => $query->count(),
          "data" => $query->get($columnsToSelect)
      ]);
    }
}
