<?php

namespace App\Modules\ConsumoInterno\Http\Controllers;

use App\Models\PontoVenda;
use App\Models\SAP;
use App\Modules\ConsumoInterno\Models\Lancamento;
use App\Modules\ConsumoInterno\Models\Lancamento\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Litiano\Sap\Company;

class ConsumoInternoController extends Controller
{

    public function index()
    {
        $pvs = PontoVenda::all();
        return view("consumo-interno::index", compact("pvs"));
    }
    /**
     * @param $pvId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function lancamento($pvId)
    {
        $pv = PontoVenda::find($pvId);
        if(!\Auth::user()->admin && !in_array(\Auth::user()->id, $pv->ci_config["usuarios"])){
            return redirect()->back()->withErrors("Usuário não autorizado!");
        }
        $lancamento = Lancamento::wherePvId($pvId)->where("data", Carbon::now())->first();

        if($lancamento)
        {
            $items = $lancamento->itens;
        } else {
            $items = [];
        }
        $sap = new Company(false);
        $regrasDist = $sap->query("select PrcCode as valor, PrcName as nome from OPRC where (ValidTo is null or ValidTo > GETDATE()) and Active = 'Y' and DimCode = 1");
        $projetos = $sap->query("select PrjCode as 'valor', PrjName as 'nome' from OPRJ where (ValidTo is null or ValidTo > GETDATE()) and Active = 'Y'");
        return view("consumo-interno::lancamento", compact("pvId", "items", "pv", "regrasDist", "projetos"));
    }

    public function addItemPost(Request $request)
    {
        if(!$lancamento = Lancamento::whereData(Carbon::now()->toDateString())->where("pv_id", $request->get("pv_id"))->first()){
            $lancamento = new Lancamento();
            $lancamento->pv_id = $request->get("pv_id");
            $lancamento->data = Carbon::now()->toDateString();
            $lancamento->save();
        }
        $item = new Item();
        $item->ci_id = $lancamento->id;
        $item->cod_sap = $request->get("codigo");
        $item->descricao = $request->get("nome");
        $item->centro_custo = $request->get("centro_custo");
        $item->projeto = $request->get("projeto");
        $item->qtd = $request->get("qtd");
        $item->user_id = \Auth::user()->id;
        $item->save();
        return redirect()->back()->withSuccess("Adicionado com sucesso!");
    }

    public function delete($id)
    {
        Item::destroy($id);
        return redirect()->back()->withSuccess("Item removido com sucesso!");
    }

    public function listar()
    {
        $lancamentos = Lancamento::orderBy("id", "desc")->paginate(10);
        return view("consumo-interno::listar", compact("lancamentos"));
    }

    public function getItem(Request $request)
    {
        $sap = new Company(false);
        $query = "%" . $request->get("query") . "%";
        $result = $sap->getDb()->select("select top 10 ItemCode as data, ItemName as value from OITM where ItemName like :query or ItemCode like :query2", ["query"=> $query, "query2"=> $query]);

        return response()->json(["query" => $request->get("query"), "suggestions" => $result]);
    }

    public function getItemByCode(Request $request)
    {
        $sap = new Company(false);
        $result = $sap->getDb()->selectOne("select top 1 ItemCode, ItemName from OITM where ItemCode = :itemCode",
            ["itemCode"=>$request->get("itemCode")]);
        return response()->json($result);
    }

}
