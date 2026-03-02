<?php

namespace App\Modules\Settings\Http\Controllers;

use App\Modules\Settings\Models\CashFlow;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Illuminate\Support\Facades\DB;
use App\User;
use App\LogsError;

class CashFlowController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!checkAccess('admin_config')) {
                return redirect()->route('home')->withErrors(auth()->user()->name . ' você não possui acesso! consulte o Admin do Sistema');
            } else {
                return $next($request);
            }
        });
    } */

    public function index()
    {
        return view('settings::cashFlow.create', $this->option());
    }

    private function option()
    {
        $busca = CashFlow::orderBy('id', 'desc')
            ->join('users','users.id', '=','cash_flows.idUser')
            ->select('cash_flows.*','users.name')->get();
        return compact('busca');
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $cash = new CashFlow();
            $id = $request->get('id', false);
            if ($id) {
                $cf = CashFlow::find($id);
                $attributes = $request->except('_token', 'id');
                $attributes['description'] = clearString($request->description);
                $attributes['module'] = clearString($request->module);
                $cf->fill($attributes);
                $cf->save();
            } else {
                $attributes = $request->except('_token', 'id');
                $attributes['idUser'] = auth()->user()->id;
                $attributes['description'] = clearString($request->description);
                $attributes['module'] = clearString($request->module);
                CashFlow::create($attributes);
            }
            DB::commit();
            return view('settings::cashFlow.create', $this->option());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('settings.cash.flow.index')->withErrors($e->getMessage());
        }
    }

    public function status($id)
    {
        $cash = new CashFlow();
        $obj = CashFlow::find($id);
        $cash->updateStatus($obj);
        return redirect()->route('settings.cash.flow.index')->withSuccess('Operação realizada com sucesso!');
    }

    public function read($id)
    {
        $head = CashFlow::find($id);
        return view('settings::cashFlow.create', array_merge(['head' => $head], $this->option()));
    }


}
