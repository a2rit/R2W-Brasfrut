<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\CheckPermission;
use App\Models\SAP;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(User::all()->count() > 0){
            $this->middleware("auth");
            $this->middleware(CheckPermission::class . ":admin");
        }
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $groups = User\Group::all();
        return view('auth.register', compact('groups'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $obj = new User();
        
        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        $user['group_id'] = $data['group_id'] ?? null;
        $user['ativo'] = $data['ativo'];
        $user['tipo'] = $data['tipo'] ?? '';
        $user['userClerk'] = $data['userClerk'] ?? '';
        $user['whsDefault'] = $data['whsDefault'] ?? '';
        $user['password'] = bcrypt($data['password']);
        $user['permissions'] = $obj->createPermissions($data);
     

        return User::create($user);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));
        if(!empty($user->id)){
            return redirect()->route('usuarios.editar', $user->id)->withSuccess("Cadastro do usuário realizado com sucesso!");
        }else{
            return redirect()->back()->withErrros("Não foi possivel cadastrar o usuário. Atualize a página e tente novamente!");
        }

        /*$this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());*/
    }

    public function getUsers(Request $request)
    {
        $sap = new SAP(true, false, false);
        $query = "%".$request->get("query")."%";
        $usuarios = $sap->query("select ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')
 as value, ISNULL(email, '') as data from OHEM where Active = 'Y' and firstName is not NULL 
 and (firstName like :query or middleName like :query2 or lastName like :query3)",
            ["query"=>$query, "query2"=>$query, "query3"=>$query]);

        return response()->json(["query"=>$query, "suggestions"=>$usuarios]);

    }
}
