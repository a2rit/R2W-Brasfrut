<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckPermission;
use App\Models\FormasPagamento;
use App\Models\PontoVenda;
use App\Models\SAP;
use Illuminate\Http\Request;
use Throwable;

class PontoVendaController extends Controller
{
    public function __construct()
    {
        $this->middleware(CheckPermission::class . ":nfce");
    }

    public function cadastro()
    {
        $parametros = $this->getParametros();

        return view('pontoVenda.criar', compact('parametros'));
    }

    protected function getParametros()
    {
        $sap = new SAP(true, false);

        $parametros['vendedores'] = $sap->query("select SlpCode as 'valor', SlpName as 'nome' from OSLP where Active = 'Y'");
        $parametros['clientes'] = $sap->query("select CardCode as 'valor', CardName as 'nome' from OCRD where CardType = 'C' order by CardName asc");
        $parametros['modelosNf'] = $sap->query("select AbsEntry as 'valor', NfmName as 'nome', NfmDescrip as 'titulo' from ONFM");
        $parametros['regrasDistribuicao'] = $sap->query("select PrcCode as valor, PrcName as nome from OPRC where (ValidTo is null or ValidTo > GETDATE()) and Active = 'Y' and DimCode = 1");
        $parametros['codigosImposto'] = $sap->query("select Code as 'valor', Name as 'nome' from OSTC");
        $parametros['utilizacoes'] = $sap->query("select ID as 'valor', Usage as 'nome' from OUSG");
        $parametros['projetos'] = $sap->query("select PrjCode as 'valor', PrjName as 'nome' from OPRJ where (ValidTo is null or ValidTo > GETDATE()) and Active = 'Y'");
        $parametros['nomesCartoes'] = $sap->query("select CreditCard as 'valor', CardName as 'nome' from OCRC");
        $parametros['formasCartoes'] = $sap->query("select CrTypeCode as 'valor', CrTypeName as 'nome' from OCRP order by CreditCard asc");
        $parametros['formasPagamento'] = $sap->query("select CrTypeCode, CrTypeName as 'nome', CreditCard from OCRP order by CreditCard asc");
        $parametros['contasContabeis'] = $sap->query("select AcctCode as valor, AcctName as nome from OACT where Finanse = 'Y'");
        $parametros['contasContabeis3'] = $sap->query("select AcctCode as valor, AcctName as nome from OACT where GroupMask = '3' and Postable = 'Y'");
        $parametros['contasContabeisCheque'] = $sap->query("select AcctCode as valor, AcctName as nome from OACT where AcctName like '%cheque%'");
        $parametros['depositos'] = $sap->query("select WhsCode as valor, WhsName as nome from OWHS");
        $parametros["codigoOV"] = $sap->query("select ExpnsCode as valor, ExpnsName as nome from OEXD");
        $parametros["grupos_item"] = $sap->query("SELECT [ItmsGrpCod] as valor ,[ItmsGrpNam] as nome FROM [OITB] ORDER BY ItmsGrpNam DESC");
        $parametros["prices_list"] = $sap->query("select ListNum as value, ListName as name from OPLN order by ListName asc");
        $parametros["items_gorjeta"] = $sap->query("select ItemCode as value, ItemName as name from OITM where ItemName like '%gorjeta%'");
        $parametros["contas_gorjeta"] = $sap->query("select AcctCode as valor, AcctName as nome from OACT where AcctName like '%gorjeta%'");

        return $parametros;
    }

    public function cadastroPost(Request $request)
    {
        $dados = $request->all();
        if (isset($dados['id'])) {
            $pv = PontoVenda::find($dados['id']);
        } else {
            $pv = new PontoVenda();
        }
        $pv->nome = $dados['nome'];
        $pv->vendedor = $dados['vendedor'];
        $pv->cliente = $dados['cliente'];
        $pv->modelo_nf = $dados['modelo_nf'];
        $pv->regra_distribuicao = $dados['regra_distribuicao'] ?? "";
        $pv->regra_distribuicao_ov = $dados['regra_distribuicao_ov'] ?? "";
        $pv->codigo_imposto = $dados['codigo_imposto'] ? $dados['codigo_imposto'] : "";
        $pv->codigo_imposto_ov = $dados['codigo_imposto_ov'] ? $dados['codigo_imposto_ov'] : "";
        $pv->utilizacao = $dados['utilizacao'];
        $pv->pasta_xml = $dados['pasta_xml'];
        $pv->pasta_xml_contingencia = $dados['pasta_xml_contingencia'];
        $pv->projeto = $dados['projeto'];
        $pv->projeto_ov = $dados['projeto_ov'];
        $pv->conta_dinheiro = $dados['conta_dinheiro'];
        $pv->deposito = $dados['deposito'];
        $pv->codigo_ov = $dados["codigo_ov"];
        $pv->conta_troco = $dados["conta_troco"];
        $pv->conta_cheque = $dados["conta_cheque"];
        $pv->conta_pix = $dados["conta_pix"];
        $pv->grupo_servico = $dados["grupo_servico"];
        $pv->deposito_servico = $dados["deposito_servico"];
        $pv->ci_config = $dados["intern_consumption"];
        $pv->serie = $dados["serie"];
        $pv->item_gorjeta_sap = $dados["item_gorjeta_sap"];
        $pv->item_gorjeta_colibri = $dados["item_gorjeta_colibri"];
        $pv->conta_gorjeta_debito = $dados["conta_gorjeta_debito"];
        $pv->conta_gorjeta_credito = $dados["conta_gorjeta_credito"];
        $pv->save();

        return redirect()->route("pv.editar", ["id" => $pv->id])->with('mensagem', ['class' => 'success', 'titulo' => 'Sucesso!', 'mensagem' => 'Ponto de venda salvo com sucesso!']);
    }

    public function editar($id)
    {
        $parametros = $this->getParametros();
        $pv = PontoVenda::find($id);

        return view('pontoVenda.editar', compact('parametros', 'pv'));
    }

    public function listar()
    {
        $pontosVenda = PontoVenda::all();
        return view('pontoVenda.listar', compact('pontosVenda'));
    }

    public function adicionarFormaPag(Request $request)
    {
        try {
            $formaPag = new FormasPagamento();
            $formaPag->valor = trim($request->get("valor"));
            $formaPag->pv_id = trim($request->get("pv_id"));
            $formaPag->chave_colibri = trim($request->get("chave"));
            $formaPag->codigo_unico = trim($request->get("chave")) . "-" . trim($request->get("pv_id"));
            $formaPag->save();
            return response()->json(["success" => true]);
        } catch (Throwable $e) {
            return response()->json(["success" => false, "msg" => $e->getMessage()]);
        }
    }

    public function excluirFormaPagamento($id)
    {
        FormasPagamento::destroy($id);
        return redirect()->back()->with('mensagem', ['class' => 'success', 'titulo' => 'Sucesso!', 'mensagem' => 'Excluido com sucesso!']);
    }
}
