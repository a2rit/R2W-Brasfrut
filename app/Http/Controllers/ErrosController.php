<?php

namespace App\Http\Controllers;

use App\Modules\InternConsumption\Models\InternConsumption;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\CheckPermission;
use App\Models\Erro;
use App\Models\NFCe;
use App\Models\PontoVenda;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Litiano\Sap\NewCompany;
use Log;
use stdClass;
use App\logsError;
use App\Jobs\SalesOrderToSAP;

use App\JasperReport;

class ErrosController extends Controller
{
    function __construct()
    {
        $this->middleware(CheckPermission::class . ":nfce");
    }

    public function listarOld()
    {
        $erros = Erro::paginate(10);

        return view('erros.listar', compact('erros'));
    }

    public function listar()
    {
        $modelos = [
            ['value' => '1', 'text' => 'NFC-e'],
            ['value' => '2', 'text' => 'NFCe\Item'],
            ['value' => '3', 'text' => 'Consumo Interno'],
            ['value' => '4', 'text' => 'Consumo Interno\Item'],
            ['value' => '0', 'text' => 'Outro'],
        ];
        $erros = Erro::all();
        $pvs = PontoVenda::all(['id as value', 'nome as text']);

        $internConsumptionTypes = [];
        foreach (InternConsumption::DOCUMENT_TYPE_TEXT as $value => $text) {
            $internConsumptionTypes[] = compact('value', 'text');
        }

        return view('erros.listar2', compact('erros', 'pvs', 'modelos', 'internConsumptionTypes'));
    }

    public function ver($id)
    {
        $erro = Erro::find($id);
        if (!$erro) {
            return redirect()
                ->back()
                ->with('mensagem', ['class' => 'warning', 'titulo' => 'Atenção!', 'mensagem' => 'Erro já resolvido!']);
        }

        $nf = null;
        if ($erro->model == "App\Models\NFCe") {
            $nf = NFCe::find($erro->model_id);
        }
        return view('erros.ver', compact('erro', 'nf'));
    }

    public function contingencia()
    {
        $xmls = [];
        foreach (PontoVenda::all() as $pv) {
            $baseDir = $pv->pasta_xml_contingencia;
            if (!is_dir($baseDir)) {
                Log::alert("O caminho fornecido em Pasta de Xmls não é um diretorio.");
                continue;
            }
            $dir = scandir($baseDir);
            foreach ($dir as $file) {
                $file = $baseDir . DIRECTORY_SEPARATOR . $file;
                if (!is_file($file)) {
                    continue;
                }
                $xml = simplexml_load_file($file);
                if (!isset($xml->NFe)) {
                    continue;
                }
                $chave = $xml->NFe->infNFe->attributes()["Id"];
                $data = $xml->NFe->infNFe->ide->dhEmi;
                $data = Carbon::createFromFormat(Carbon::ATOM, $data);
                $numero = $xml->NFe->infNFe->ide->nNF;

                $item = new stdClass();
                $item->ponto_venda = $pv->nome;
                $item->chave = $chave;
                $item->data = $data;
                $item->numero = $numero;

                $xmls[] = $item;
            }
        }

        return view("erros.contingencia", compact("xmls"));
    }

    public function forceSync()
    {
        $cacheKey = 'nfce-force-sync';
        $lastForceSync = \Cache::get($cacheKey, false);
        $cacheInMinutes = 90;
        if (empty($lastForceSync)) {
            app('NFCeLogger')->info('Force sync disabled');
//            \Artisan::call('nfce:production-orders');
//            \Artisan::call('nfce:sync');
            \Cache::add($cacheKey, Carbon::now()->toAtomString(), $cacheInMinutes);

            return redirect()->back()->withSuccess('Aguarde o processamento.');
        }

        $minutosForWait = $cacheInMinutes - Carbon::parse($lastForceSync)->diffInMinutes(Carbon::now());
        app('NFCeLogger')->info("Force sync blocked by {$minutosForWait} minutes");

        return redirect()->back()->withErrors("Aguarde {$minutosForWait} minutos antes de tentar novamente.");
    }

    public function toExcel(Request $request){
        $report = new JasperReport();
        $relatory_model = storage_path('app/public/relatorios_modelos')."/ErrosSyncToExcel.jasper";
        $data = [
            "pv_id" => $request->pv_id ?? "NULL",
            "startDate" => $request->startDate ?? '2015-01-01',
            "endDate" => $request->endDate ?? date('Y-m-d'),
            "tipo_modelo" => $request->tipo_modelo ?? "NULL",
        ];

        switch ($request->tipo_modelo) {
            case '1':
                $data['tipo_modelo'] = NFCe::class;
                break;
            case '2':
                $data['tipo_modelo'] = NFCe\Item::class;
                break;
            case '3':
                $data['tipo_modelo'] = InternConsumption::class;
                break;
            case '4':
                $data['tipo_modelo'] = InternConsumption\Item::class;
                break;
            default:
                $data['tipo_modelo'] = 'NULL';
                break;
        }
        
        if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/ErrosSyncToExcel.jrxml";
        }

        $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'erros_sync';
        $output = public_path('/relatorios'.'/'.$file_name);
        $report = $report->generateReport($relatory_model, $output, ['xls'], $data, 'pt_BR', 'r2w');
        
        return response()->download($report)->deleteFileAfterSend(true);
    }

    public function estoqueNFCe(Request $request)
    {
        $onHandSubQuery = $this->getOnHandSubQuery('nfc_itens.codigo_sap', 'ponto_venda.deposito');
        $sapDb = NewCompany::getDb()->getDatabaseName();
        $qtdNecessariaSql = "sum(nfc_itens.quantidade) - ({$onHandSubQuery->toSql()})";
        $query = DB::table('nfc_itens')
            ->addSelect(['ponto_venda.nome', 'nfc_itens.codigo_sap'])
            ->selectSub($this->getItemNameSubQuery('nfc_itens.codigo_sap'), 'item')
            ->selectSub($this->getIsProductionItemSubQuery('nfc_itens.codigo_sap'), 'is_ip')
            ->selectSub("sum(nfc_itens.quantidade)", 'quantidade_solicitada')
            ->selectSub($onHandSubQuery, 'estoque_atual')
            ->selectSub($this->getWhsNameSubQuery('ponto_venda.deposito'), 'deposito')
            ->selectSub($qtdNecessariaSql, 'quantidade_necessaria')
            ->selectSub($this->getInProductionSubQuery('nfc_itens.codigo_sap', 'ponto_venda.deposito'), 'em_producao')
            ->join('nfc', 'nfc.id', 'nfc_itens.nfc_id')
            ->join('ponto_venda', 'nfc.pv_id', 'ponto_venda.id')
            ->join("{$sapDb}.dbo.OITM", function (JoinClause $joinClause) {
                $joinClause->on('OITM.ItemCode', 'nfc_itens.codigo_sap')
                    ->where('OITM.InvntItem', 'Y')
                ;
            })
            ->whereNull('nfc.codigo_sap')
            ->groupBy(['nfc.pv_id', 'ponto_venda.deposito', 'ponto_venda.nome', 'nfc_itens.codigo_sap'])
            ->havingRaw("sum(nfc_itens.quantidade) > ({$onHandSubQuery->toSql()})")
            ->orderByRaw($qtdNecessariaSql . ' desc')
        ;

        if (!empty($request->pv_id)) {
            $query->where('pv_id', $request->pv_id);
        }
        if (!empty($request->item)) {
            $query->where(function (Builder $builder) use ($sapDb, $request) {
                $builder->where('nfc_itens.codigo_sap', $request->item)
                    ->orWhereIn('nfc_itens.codigo_sap', $this->getLikeItemNameSubQuery("%{$request->item}%"))
                ;
            });
        }

        if (!empty($request->deposito)) {
            $query->where('ponto_venda.deposito', $request->deposito);
        }

        $items = $query->paginate(30)->appends(request()->query());

        return view('erros.estoque-nfce', compact('items'), $this->filterOptions());
    }

    public function estoqueOP(Request $request)
    {
        $onHandSubQuery = $this->getOnHandSubQuery('WOR1.ItemCode', 'WOR1.wareHouse');
        $query = NewCompany::getDb()->table('WOR1')
            ->join('OWOR', 'OWOR.DocEntry', 'WOR1.DocEntry')
            ->join('OWHS', 'OWHS.WhsCode', 'WOR1.wareHouse')
            ->whereIn('OWOR.Status', ['R', 'P'])
            ->whereNotNull('OWOR.U_A2R_CUPOM_FISCAL')
            ->where('OWOR.U_A2R_CUPOM_FISCAL', '!=', '')
            ->selectSub($this->getItemNameSubQuery('WOR1.ItemCode'), 'ItemName')
            ->selectSub($this->getItemNameSubQuery('OWOR.ItemCode'), 'MainItemName')
            ->selectSub($onHandSubQuery, 'estoque')
            ->selectSub($this->getWhsNameSubQuery('WOR1.wareHouse'), 'WhsName')
            ->addSelect(['OWOR.ItemCode as MainItemCode', 'WOR1.ItemCode', 'WOR1.wareHouse', 'OWOR.DocNum', 'OWOR.StartDate'])
            ->selectSub('sum(WOR1.PlannedQty)', 'quantidade_necessaria')
            ->groupBy(['OWOR.ItemCode', 'WOR1.ItemCode', 'WOR1.wareHouse', 'OWOR.DocNum', 'OWOR.StartDate' ])
            ->havingRaw("sum(WOR1.PlannedQty) > ({$onHandSubQuery->toSql()})")
            ->orderBy('OWOR.StartDate', 'DESC')
            ->orderBy('OWOR.ItemCode');

        if (!empty($request->itemPai)) {
            $query->where(function (Builder $builder) use ($request) {
                $builder->where('OWOR.ItemCode', $request->itemPai)
                    ->orWhereIn('OWOR.ItemCode', $this->getLikeItemNameSubQuery("%{$request->itemPai}%"));
            });
        }

        if (!empty($request->item)) {
            $query->where(function (Builder $builder) use ($request) {
                $builder->where('WOR1.ItemCode', $request->item)
                    ->orWhereIn('WOR1.ItemCode', $this->getLikeItemNameSubQuery("%{$request->item}%"))
                ;
            });
        }

        if (!empty($request->deposito)) {
            $query->where('OWHS.WhsCode', $request->deposito);
        }

        $items = $query->paginate(30)->appends(request()->query());
        return view('erros.estoque-op', compact('items'), $this->filterOptions());
    }

    public function estoqueOPToExcel(Request $request){

        $data = [
            "ItemPai" => $request->itemPai ?? 'NULL',
            "Item" => $request->item ?? 'NULL',
            "Deposito" => $request->deposito ?? 'NULL',
        ];

        $report = new JasperReport();
        $relatory_model = storage_path('app/public/relatorios_modelos')."/EstoqueInsuficienteOPExcel.jasper";
        
        if(!file_exists($relatory_model)){
            $relatory_model = storage_path('app/public/relatorios_modelos')."/EstoqueInsuficienteOPExcel.jrxml";
        }

        $file_name = str_random(5)."-".date('his')."-".str_random(3)."=".'estoque_insuficiente_op';
        $output = public_path('/relatorios'.'/'.$file_name);
        $report = $report->generateReport($relatory_model, $output, ['xls'], $data, 'pt_BR', 'sap');
        
        return response()->download($report)->deleteFileAfterSend(true);
    }

    public function gerarPedidoVenda($id){

        try {
            $so = new SalesOrder;
            $so->saveInDBFromNfceError($id);
            if(!empty($so->id)){
                SalesOrderToSAP::dispatch($so);
                return redirect()->route('erros.ver', $id)->withSuccess('Pedido de venda em processamento!');
            }
        } catch (\Exception $e) {
            $logsError = new logsError();
            $logsError->saveInDB('SO879', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
        }
    }

    private function getItemNameSubQuery(string $itemCodeColumn)
    {
        $prefix = NewCompany::getDb()->getDatabaseName();
        return NewCompany::getDb()
            ->table("{$prefix}.dbo.OITM")
            ->whereColumn('OITM.ItemCode', $itemCodeColumn)
            ->select(['ItemName']);
    }

    private function getLikeItemNameSubQuery(string $value)
    {
        $databaseName = NewCompany::getDb()->getDatabaseName();

        return NewCompany::getDb()
            ->table("{$databaseName}.dbo.OITM")
            ->where('OITM.ItemName', 'like', $value)
            ->select(['ItemCode']);
    }

    private function getWhsNameSubQuery(string $whsCodeColumn)
    {
        $databaseName = NewCompany::getDb()->getDatabaseName();

        return NewCompany::getDb()
            ->table("{$databaseName}.dbo.OWHS")
            ->whereColumn('OWHS.WhsCode', $whsCodeColumn)
            ->select(['WhsName']);
    }

    private function getInProductionSubQuery(string $itemCodeColumn, string $whsCodeColumn)
    {
        $databaseName = NewCompany::getDb()->getDatabaseName();

        return NewCompany::getDb()
            ->table("{$databaseName}.dbo.OWOR")
            ->whereColumn('OWOR.ItemCode', $itemCodeColumn)
            ->whereColumn('OWOR.Warehouse', $whsCodeColumn)
            ->whereIn('OWOR.Status', ['R', 'P'])
            ->whereNotNull('OWOR.U_A2R_CUPOM_FISCAL')
            ->where('OWOR.U_A2R_CUPOM_FISCAL', '!=', '')
            ->selectSub('sum(OWOR.PlannedQty)', 'PlannedQty');
    }

    private function getOnHandSubQuery(string $itemCodeColumn, string $whsCodeColumn)
    {
        $prefix = NewCompany::getDb()->getDatabaseName();
        return NewCompany::getDb()
            ->table("{$prefix}.dbo.OITW")
            ->whereColumn('OITW.ItemCode', $itemCodeColumn)
            ->whereColumn('OITW.WhsCode', $whsCodeColumn)
            ->select(['OnHand']);
    }

    private function getIsProductionItemSubQuery(string $itemCodeColumn)
    {
        $databaseName = NewCompany::getDb()->getDatabaseName();

        return NewCompany::getDb()
            ->table("{$databaseName}.dbo.OITT")
            ->whereColumn('OITT.Code', $itemCodeColumn)
            ->selectRaw('1')
            ->limit(1);
    }

    private function filterOptions()
    {
        $ponto_venda = PontoVenda::all();
        $deposito = NewCompany::getDb()->table('OWHS')->get(['WhsCode', 'WhsName']);

        return compact('ponto_venda', 'deposito');
    }
}
