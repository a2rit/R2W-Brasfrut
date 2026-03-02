<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckPermission;
use App\Models\Porcionamento;
use App\Models\SAP;
use Auth;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Litiano\Sap\Company;
use Litiano\Sap\NewCompany;
use Log;
use Throwable;


class PorcionamentoController extends Controller
{
    function __construct()
    {
        $this->middleware(CheckPermission::class . ":porcionamento");
    }

    public function pesquisar(Request $request)
    {
        $sap = new Company(false);
        $notas = $sap->getDb()->table('OPCH')
                ->select("OPCH.DocNum", "OPCH.TaxDate", "OPCH.Serial", "OPCH.CardName");

        if(!empty($request->get('cardCode'))){
            $notas->where('OPCH.CardCode', '=', $request->get('cardCode'));
        }

        if(!empty($request->get('data_fist'))){
            $notas->where('OPCH.TaxDate', '>=', $request->get('data_fist'));
        }

        if(!empty($request->get('data_last'))){
            $notas->where('OPCH.TaxDate', '<=', $request->get('data_last'));
        }

        $notas = $notas->orderBy("OPCH.TaxDate", "DESC")->paginate(30);

        return view("porcionamento.pesquisar", compact("notas"));
    }

    public function getNotasFiscais(Request $request)
    {
        $codFornecedor = $request->get("codigoFornecedor");
        $sap = new Company(false);
        $notas = $sap->getDb()->table('OPCH')
                ->select("OPCH.DocNum", "OPCH.TaxDate", "OPCH.Serial", "OPCH.CardName")
                ->where("OPCH.CardCode", $codFornecedor)
                ->orderBy("OPCH.TaxDate", "DESC")
                ->paginate(30);

        // foreach ($notas as &$nota) {
        //     dd($nota);
        //     $nota["TaxDate"] = date("d-m-Y", strtotime($nota["TaxDate"]));
        // }
        return response()->json($notas);


    }

    public function getItensNotaFiscal(Request $request)
    {
        $docNum = $request->get("docNum");

        $itens = NewCompany::getDb()
            ->table('PCH1')
            ->leftJoin('OITW', 'OITW.ItemCode', 'PCH1.ItemCode')
            ->whereColumn('OITW.WhsCode', 'PCH1.WhsCode')
            ->where('PCH1.DocEntry', $docNum)
            ->get(['PCH1.ItemCode', 'PCH1.Dscription', 'PCH1.Quantity', 'PCH1.unitMsr', 'PCH1.WhsCode', 'PCH1.Price',
                'PCH1.DocEntry', 'PCH1.LineNum', 'OITW.OnHand']);

        foreach ($itens as &$item) {
            $item->porcionado = false;
            if ($porcionamento = Porcionamento::whereDocumentoId($docNum)->whereLinha($item->LineNum)->first()) {
                $item->porcionado = true;
                $item->porcionamentoId = $porcionamento->id;
            }
        }

        return response()->json($itens);


    }

    public function criar($docEntry, $lineNum, Request $request)
    {
        $sap = new SAP(true, false, false);

        $item = NewCompany::getDb()
            ->table('PCH1')
            ->leftJoin('OITW', 'OITW.ItemCode', 'PCH1.ItemCode')
            ->leftJoin('OPCH', 'PCH1.DocEntry', 'OPCH.DocEntry')
            ->whereColumn('OITW.WhsCode', 'PCH1.WhsCode')
            ->where('PCH1.DocEntry', $docEntry)
            ->where('PCH1.LineNum', $lineNum)
            ->first(['PCH1.ItemCode', 'PCH1.Dscription', 'PCH1.Quantity', 'PCH1.unitMsr', 'PCH1.WhsCode', 'PCH1.Price',
                'PCH1.DocEntry', 'PCH1.LineNum', 'OPCH.CardCode', 'OPCH.CardName', 'OPCH.Serial','OITW.OnHand']);

        $parametros['projetos'] = NewCompany::getInstance()->getProjectsQueryBuilder()->get(['PrjCode as valor', 'PrjName as nome']);
        $parametros['regrasDistribuicao'] = NewCompany::getInstance()->getCostCentersQueryBuilder()->get(['PrcCode as valor', 'PrcName as nome']);

        $depositos = "<option></option>";
        foreach ($sap->query("select WhsCode as valor, WhsName as nome from OWHS") as $deposito) {
            $depositos .= "<option value='$deposito[valor]'>$deposito[nome]</option>";
        }

        $porcionamento = Porcionamento::whereDocumentoId($docEntry)->whereLinha($lineNum)->first();
        if ($porcionamento) {
            return redirect()->route("porcionamento.ver", ["id" => $porcionamento->id]);
        }
        $pPerda = Porcionamento\PorcentagemPerda::whereCodigo($item->ItemCode)->first();

        return view("porcionamento.porcionamento", compact('item', 'depositos', 'parametros', 'pPerda'));
    }

    public function getItem(Request $request)
    {
        $codItem = $request->get("itemCode");
        $sap = new SAP(true, false, false);
        $item = $sap->query("select TOP 15 OITM.ItemCode, OITM.ItemName, OITW.AvgPrice, OITM.SalUnitMsr, 
                                OITM.DfltWH from OITM left join OITW on OITW.ItemCode = OITM.ItemCode and OITW.WhsCode = OITM.DfltWH
                                where OITM.ItemCode = :codigo", ["codigo" => $codItem]);

        if (isset($item[0])) {
            $pPerda = Porcionamento\PorcentagemPerda::whereCodigo($item[0]['ItemCode'])->first();
            $item[0]['pPerda'] = $pPerda;
            return response()->json($item[0]);
        }
        return response()->json([]);

    }

    public function salvar(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $_itens = $request->get("itens");
                $item = $request->get("item");
                $porcionamento = Porcionamento::findOrNew($item["DocEntry"] . $item["LineNum"]);
                if ($porcionamento->cod_item) {
                    return response()->json(["sucesso" => false, "msg" => "Porcionamento já cadastrado!"]);
                }
                $porcionamento->cod_item = $item["ItemCode"];
                $porcionamento->nome_item = $item["Dscription"];
                $porcionamento->quantidade = $item["Quantity"] - $request->get("devolvido");
                $porcionamento->deposito = $item["WhsCode"];
                $porcionamento->user_id = Auth::user()->id;
                $porcionamento->preco = $item["Price"];
                $porcionamento->documento_id = $item["DocEntry"];
                $porcionamento->linha = $item["LineNum"];
                $porcionamento->id = $item["DocEntry"] . $item["LineNum"];
                $porcionamento->unidade_medida = $item["unitMsr"];
                $porcionamento->nota_fiscal = $item["Serial"];
                $porcionamento->cod_fornecedor = $item["CardCode"];
                $porcionamento->nome_fornecedor = $item["CardName"];
                $porcionamento->projeto = $request->get("projeto");
                $porcionamento->regra_distribuicao = $request->get("regra_distribuicao");

                if ($request->get('justificativaSelect') === 'outra') {
                    $porcionamento->justificativa = $request->get("justificativa");
                } else {
                    $porcionamento->justificativa = $request->get('justificativaSelect');
                }
                $porcionamento->save();

                foreach ($_itens as $it) {
                    $pItem = new Porcionamento\Item();
                    $pItem->cod_item = $it["codSap"];
                    $pItem->nome_item = $it["nome"];
                    $pItem->quantidade_gasta = $it["utilizacao"];
                    $pItem->quantidade_produzida = $it["producao"];
                    $pItem->deposito = $it["deposito"];
                    $pItem->custo = $it["custo"];
                    $pItem->tipo = $it["tipo"];
                    $pItem->porcionamento_id = $porcionamento->id;
                    $pItem->save();
                }

                return response()->json(["sucesso" => true, "porcionamento_id" => $porcionamento->id]);
            });
        } catch (Throwable $e) {
            Log::alert($e->getMessage());

            return response()->json(["sucesso" => false, "msg" => $e->getMessage() . $e->getLine()]);
        }
    }

    public function ver($id)
    {
        $porcionamento = Porcionamento::find($id);
        return view("porcionamento.ver", compact("porcionamento"));
        //@TODO criar o ver e adicionar no SAP
    }

    public function salvarSap($id)
    {
        try {
            DB::beginTransaction();
            /** @var Porcionamento $porcionamento */
            $porcionamento = Porcionamento::lockForUpdate()->find($id);
            $porcionamento->salvarSAP();
            DB::commit();
        } catch (Throwable $e) {
            DB::commit(); // operação parcial deve ser commitada!
            Log::error($e);
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect()->back()->withSuccess('Porcionamento Salvo com Sucesso!');
    }

    public function getFornecedor(Request $request)
    {
        $query = "%" . $request->get("query") . "%";
        $sap = new SAP(true, false, false);
        $fornecedors = $sap->query("select TOP 15 CardName as value, CardCode as data from OCRD 
where CardType = 'S' and (CardName like :query or CardCode like :query2)", ["query" => $query, "query2" => $query]);

        return response()->json(["query" => $query, "suggestions" => $fornecedors]);
    }

    public function getItens(Request $request)
    {
        $query = "%" . $request->get("query") . "%";
        $sap = new SAP(true, false, false);
        $fornecedors = $sap->query("select ItemName as value, ItemCode as data from OITM 
where validFor = 'Y' and (ItemName like :query or ItemCode like :query2)", ["query" => $query, "query2" => $query]);

        return response()->json(["query" => $query, "suggestions" => $fornecedors]);
    }

    public function listar(Request $request)
    {
        $porcionamentos = DB::table("porcionamentos");

        if(!empty($request->get("fornecedor"))){
            $porcionamentos->where("cod_fornecedor", "like", "%{$request->get('fornecedor')}%");
        }
        if(!empty($request->get("item"))){
            $porcionamentos->where("cod_item", "like", "%{$request->get('item')}%")->orWhere("nome_item", "like", "%{$request->get('item')}%");
        }
        if(!empty($request->get("codeEntrada"))){
            $porcionamentos->where("cod_entrada", "like", "%{$request->get('codeEntrada')}%");
        }
        if(!empty($request->get("codeSaida"))){
            $porcionamentos->where("cod_saida", "like", "%{$request->get('codeSaida')}%");
        }
        if(!empty($request->get("codeReavaliacao"))){
            $porcionamentos->where("cod_reavaliacao", "like", "%{$request->get('codeReavaliacao')}%");
        }

        $porcionamentos = $porcionamentos->orderByRaw("(case when cod_entrada is null then 0 else 1 end), cod_entrada desc")->paginate(10);

        return view("porcionamento.listar", compact("porcionamentos"));
    }

    public function excluir($id)
    {
        $porcionamento = Porcionamento::find($id);

        //if($porcionamento->cod_entrada == "" && $porcionamento->cod_saida == "")
        //{
        $porcionamento->delete();
        //}
        return redirect()->back()->with('mensagem', ['class' => 'success', 'titulo' => 'Sucesso!', 'mensagem' => 'Porcionamento excluido!']);
    }

    public function porcentagemPerda()
    {
        return view("porcionamento.porcentagemPerda.adicionar");
    }

    public function porcentagemPerdaPost(Request $request)
    {
        Porcionamento\PorcentagemPerda::updateOrCreate(
            $request->only(["codigo"]),
            $request->except(["codigo"])
        );
        return redirect()->route("porcionamento.porcentagemPerdaListar")
            ->with('mensagem', ['class' => 'success', 'titulo' => 'Sucesso!', 'mensagem' => 'Porcentagem cadastrada com sucesso!']);
    }

    public function buscaItem(Request $request)
    {
        $sap = new Company(false);
        $result = $sap->getValidItemQueryBuilder('OITM')
            ->where(function (Builder $builder) use ($request) {
                $builder->where('OITM.ItemName', 'like', "%{$request->get('q')}%")
                    ->orWhere('OITM.ItemCode', 'like', "%{$request->get('q')}%");
            })
            ->limit(7)
            ->get(['OITM.ItemName', 'OITM.ItemCode']);

        foreach ($result as &$item) {
            $item->name = "{$item->ItemCode} - {$item->ItemName}";
            $item->value = $item->ItemCode;
        }
        return response()->json($result);
    }

    public function porcentagemPerdaListar(Request $request)
    {
        //$itens = Porcionamento\PorcentagemPerda::paginate();
        $itens = Porcionamento\PorcentagemPerda::with(['itemSap:ItemCode,ItemName'])->get();
        //dd($itens);
        return view("porcionamento.porcentagemPerda.listar", compact("itens"));
    }

    public function porcentagemPerdaExcluir($id)
    {
        Porcionamento\PorcentagemPerda::destroy($id);
        return redirect()->route("porcionamento.porcentagemPerdaListar")->withSuccess("Excluido com sucesso!");
    }

    public function autorizar(Porcionamento $porcionamento)
    {
        $porcionamento->data_autorizacao = Carbon::now();
        $porcionamento->usuario_autorizador_id = Auth::user()->id;
        $porcionamento->save();
        return redirect()->back()->withSuccess("Autorizado com sucesso!");
    }

    public function justificativasListar(Request $request)
    {
        $itens = Porcionamento\Justificativa::paginate();
        return view("porcionamento.justificativas.listar", compact("itens"));
    }

    public function justificativaAdicionar(Request $request)
    {
        $justificativa = new Porcionamento\Justificativa();
        $justificativa->justificativa = $request->get("justificativa");
        $justificativa->save();
        return redirect()->route("porcionamento.justificativas")->withSuccess("Adicionado com sucesso!");
    }

    public function justificativaExcluir(Porcionamento\Justificativa $justificativa)
    {
        $justificativa->delete();
        return redirect()->route("porcionamento.justificativas")->withSuccess("Excluido com sucesso!");
    }

    public function listarAltaPerda()
    {
        $porcionamentos = Porcionamento::whereNotNull('justificativa')
            ->orWhereNotNull('justificativa_id')->orderBy('data_autorizacao')
            ->paginate();

        return view("porcionamento.listarAltaPerda", compact("porcionamentos"));
    }

    public function editarPerda($codigo)
    {
        $data = Porcionamento\PorcentagemPerda::find($codigo);
        return view("porcionamento.porcentagemPerda.adicionar", compact('data'));
    }

}
