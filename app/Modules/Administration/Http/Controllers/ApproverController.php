<?php

namespace App\Modules\Administration\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Modules\Administration\Models\Approver;
use  App\Modules\Settings\Models\Lofted;

class ApproverController extends Controller
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
    }
    */
    public function create(){
      return view("administration::approver.create", $this->option());
    }
    public function index(){
      return view('administration::approver.index');
  }
    private function option(){
      $items = Approver::all();
      $lofted = Lofted::all();
      return compact('items','lofted');
    }
    public function edit($id){
      try {
        $head = Approver::find($id);
        return view("administration::approver.create", array_merge(['head'=>$head]), $this->option());
 
      } catch (\Throwable $th) {
        return redirect()->route('administration.user.registration.approver')->withErrors($th->getMessage());
        
      }
    }
    public function remove($id){
      try {
        $head = Approver::find($id);
        $head->delete();
        
        return redirect()->route('administration.user.registration.approver')->withSuccess('Operação realizada com sucesso!');
 
      } catch (\Throwable $th) {
        return redirect()->route('administration.user.registration.approver')->withErrors($th->getMessage());
        
      }
    }
    public function getUsers(Request $request){

      if(!empty($request->get('query'))){
        $name = $request->get('query');
        if($request->get('type') === 'name'){
          $users = User::select("name AS data", "name AS value")->where('name', 'LIKE', '%'.$name.'%')->limit(50)->get();
        }else if($request->get('type') === 'email'){
          $users = User::select("email AS data", "email AS value")->where('email', 'LIKE', '%'.$name.'%')->limit(50)->get();
        }
        return response()->json(["query" => $name, "suggestions" => $users]);
      }

      $query = User::where('ativo','=', 1);
      

      if (!is_null($request->get('q'))) {
          $query->where("name", "like", "%{$request->get('q')}%");
      }

      $query->take(10)->select('id as value', 'name');
      return response()->json($query->get());
    }

    public function store(Request $request){
        try {

          $attributes = $request->except(['prices', '_token', 'id', 'first', 'last']);
         if ($request->get('id')) {
              $attributes["approverUser"] = $request->get('user');
              $attributes["nivel"] = $request->get('nivel');
              $attributes["nameApproverUser"] = User::find($request->get('user'))->name;
              $attributes["idUser"] = auth()->user()->id;
              $attributes["idLoftedApproveds"] = $request->get('idLoftedApproveds');
              $attributes["nameLoftedApproveds"] = Lofted::find($request->get('idLoftedApproveds'))->name;
              
              $item = Approver::find($request->get('id'));
              $item->fill($attributes);
              $item->save();
          } else {
            $lofted = Lofted::find($request->get('idLoftedApproveds'));
            if(count(Approver::where('idLoftedApproveds', $lofted->id)->get()) > $lofted->quantity){
              throw new \Exception("Apenas é possível adicionar". (Int)$lofted->quantity ."usuários na alçada selecionada!");
            }

            $attributes["approverUser"] = $request->get('user');
            $attributes["nivel"] = $request->get('nivel');
            $attributes["nameApproverUser"] = User::find($request->get('user'))->name;
            $attributes["idUser"] = auth()->user()->id;
            $attributes["idLoftedApproveds"] = $request->get('idLoftedApproveds');
            $attributes["nameLoftedApproveds"] = $lofted->name;
            
            $item = Approver::create($attributes);
          }

          return redirect()->route('administration.user.registration.approver')->withSuccess('Operação realizada com Sucesso!');
        } catch (\Throwable $th) {
          return redirect()->route('administration.user.registration.approver')->withErrors($th->getMessage());
        }
    }
    public function data(Request $request){
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
