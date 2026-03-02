<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\CheckPermission;
use App\User;
use App\User\CostCenter;
use App\User\Warehouses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\logsError;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Litiano\Sap\Company;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware(CheckPermission::class . ":admin");
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = User::paginate(10);

        return view("usuarios.listar", compact("usuarios"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function editar($id)
    {
        $usuario = User::with(['costCenters', 'warehouses'])->find($id);
        
        $groups = User\Group::all();
        $sap = new Company(false);
        $warehouses = $sap->getDb()->table('OWHS')
            ->where('Inactive', '=', 'N')
            ->get(['WhsCode', 'WhsName']);

        $employees = $sap->getDb()->table('OHEM')
            ->where('Active', '=', 'Y')
            ->get(['empID', 'firstName', 'middleName', 'lastName']);

        $costCenter = $sap->getDb()->table('OPRC')
                        ->where(['VALIDTO' => NULL, 'DimCode' => 1, 'Active' => 'Y'])
                        ->get(['PrcCode as value', 'PrcName as name']);
        $costCenter2 = $sap->getDb()->table('OPRC')
                        ->where(['VALIDTO' => NULL, 'DimCode' => 2, 'Active' => 'Y'])
                        ->get(['PrcCode as value', 'PrcName as name']);

        $type = [
            '1' => 'Uso e consumo',
            '2' => 'Manutenção',
            '3' => 'Eventos',
        ];

        return view("usuarios.editar", compact("usuario","type", 'groups', 'warehouses', 'employees', 'costCenter', 'costCenter2'));
    }
    
    public function filtrar(Request $request){
        $user = User::orderBy('id','desc');
        if(isset($request->name) && !is_null($request->name)){
            $user->where('name','like', '%'.$request->name.'%');
        }

        if(isset($request->email) && !is_null($request->email)){
            $user->where('email','=',$request->email);
        }
        $usuarios= $user->paginate(10);
        $request->flash();
        return view("usuarios.listar", compact("usuarios"));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     //Salva os usuários editados
    public function salvar($id, Request $request)
    {

        try {
            
            $data = $request->all();
    
            $usuario = User::find($id);
            $usuario->name = $request->get("name");
            $usuario->email = $request->get("email");
            $usuario->ativo = $request->get("ativo");
            $usuario->group_id = $request->get("group_id");
            $usuario->tipo = $request->get("tipo");
            $usuario->tipoTransf = $request->get("tipoTransf");
            $usuario->tipoCompra = $request->get("tipoCompra");
            $usuario->freeCompra = $request->get("freeCompra");
            $usuario->userClerk = $request->get("userClerk");
            $usuario->whsDefault = $request->get("whsDefault");
            $usuario->whsGroup = $request->get("whsGroup");
            $usuario->permissions = $usuario->createPermissions($data);

            if(!empty($request->get("password"))){
                $usuario->password = bcrypt($request->get("password"));
            }
            
            if($usuario->save()){
                $usuario->costCenters()->delete();

                if(!empty($request->costCenter)){
                    $costCenter = new CostCenter;
                    foreach($request->costCenter as $index => $value){
                        $costCenter->create([
                            'user_id' => $usuario->id,
                            'costCenterCode' => $value
                        ]);
                    }
                }
                
                if(!empty($request->costCenter2)){
                    $costCenter = new CostCenter;
                    foreach($request->costCenter2 as $index => $value){
                        $costCenter->create([
                            'user_id' => $usuario->id,
                            'costCenterCode2' => $value
                        ]);
                    }
                }
                if(!empty($request->associatedWhs)){
                    $usuario->warehouses()->delete();
                    $warehouses = new Warehouses;
                    foreach($request->associatedWhs as $index => $value){
                        $warehouses->create([
                            'user_id' => $usuario->id,
                            'whsCode' => $value
                        ]);
                    }
                }
            };
            return redirect()->back()->with('mensagem', ['class'=>'success', 'titulo'=>'Sucesso!', 'mensagem'=>'Usuário salvo com sucesso!']);
        } catch (\Exception $e) {
            $logsErrors = new logsError();
            $logsErrors->saveInDB('E0118', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function getNotifications()
    {
        /** @var DatabaseNotificationCollection $notifications */
        $notifications = \Auth::user()->unreadNotifications;
        $notifications->markAsRead();
        return response()->json($notifications);

    }
}
