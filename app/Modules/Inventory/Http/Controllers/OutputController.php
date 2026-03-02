<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LogsError;
use App\Upload;
use Litiano\Sap\Company;
use App\Modules\Inventory\Models\Output\Output;
use App\Modules\Inventory\Models\Output\Item;
use Illuminate\Support\Facades\DB;
use App\Modules\Inventory\Jobs\OutputToSAPNew;
use App\Modules\Inventory\Models\Requisicao\Requests;
use App\User;
use App\SapUtilities;
use Barryvdh\Snappy\Facades\SnappyPdf;

use App\JasperReport;

class OutputController extends Controller
{
    use SapUtilities;


    public function index()
    {
        $items = Output::join('users', 'users.id', '=', 'outputs.idUser')
            ->select("outputs.id", "outputs.message", "outputs.is_locked", "outputs.TaxDate", "outputs.code", "outputs.codSAP", 'users.name')
            ->orderBy('outputs.id', 'desc')
            ->paginate(30);

        return view("inventory::output.index", array_merge(['items' => $items], $this->getOption()));
    }

    public function create()
    {
        return view("inventory::output.create", $this->getOption());
    }

    private function getOption()
    {
        $sap = new Company(false);
        //$centroCusto = $this->getCostCenterOptions($sap);
        $centroCusto = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 1 and Active = 'Y'");
        $centroCusto2 = $sap->query("SELECT OPRC.PrcCode as value, OPRC.PrcName as name FROM OPRC WHERE OPRC.VALIDTO IS NULL AND OPRC.DimCode = 2 and Active = 'Y'");
        $projeto = $this->getProjectOptions($sap);
        $warehouses = $this->getWHSOptions($sap);
        $role = $this->getDistributionRulesOptions($sap);
        $acct = $this->getAccountOptions($sap);
        return compact('centroCusto', 'projeto', 'warehouses', 'role', 'acct', 'centroCusto2');
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $otp = new Output();
            $otp->saveInDB($request);
            saveUpload($request, 'outputs', $otp->id);

            DB::commit();
            OutputToSAPNew::dispatch($otp);
            if ($otp->is_locked) {
                return redirect()->route('inventory.output.index')->withErrors($otp->message);
            } else {
                return redirect()->route('inventory.output.edit', $otp->id)->withSuccess("Operação realizada com sucesso!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('EMX98', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('inventory.output.index')->withErrors($e->getMessage());
        }
    }

    public function search()
    {
        return view('inventory::output.search');
    }

    public function anyData(Request $request)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table("OIGE");
        $recordsTotal = $query->count();
        $query->offset($request->get("start"));
        $query->limit($request->get("length"));
        $columns = $request->get("columns");
        $columnsToSelect = ['DocNum', 'DocDate', 'TaxDate', 'Comments', 'U_R2W_USERNAME', 'U_R2W_CODE'];

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("DocNum", "like", "%{$search['value']}%")
                ->orWhere("DocDate", "like", "%{$search['value']}%")
                ->orWhere("TaxDate", "like", "%{$search['value']}%")
                ->orWhere("Comments", "like", "%{$search['value']}%")
                ->orWhere("U_R2W_USERNAME", "like", "%{$search['value']}%")
                ->orWhere("U_R2W_CODE", "like", "%{$search['value']}%");
        }

        $order = $request->get('order');
        $query->orderBy($columns[$order[0]['column']]['name'], $order[0]['dir']);

        return response()->json([
            "draw" => $request->get("draw"),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $query->count(),
            "data" => $query->distinct()->get($columnsToSelect)
        ]);
    }

    public function searchData($code)
    {
        $sap = new Company(false);
        $query = $sap->getDb()->table('IGE1')
            ->leftJoin('OPRJ', 'IGN1.Project', '=', 'OPRJ.PrjCode')
            ->leftJoin('OOCR', 'IGE1.OcrCode', '=', 'OOCR.OcrCode')
            ->join('OWHS', 'IGE1.WhsCode', '=', 'OWHS.WhsCode')
            ->join('OIGE', 'IGE1.DocEntry', '=', 'OIGE.DocEntry')
            ->where('OIGE.DocNum', $code)
            ->select('OIGE.DocNum', 'IGE1.ItemCode', 'IGE1.Dscription', 'IGE1.Quantity', 'IGE1.WhsCode', 'OPRJ.PrjName', 'OOCR.OcrName', 'OWHS.WhsCode', 'OWHS.WhsName')
            ->get();
        return response()->json([
            "recordsTotal" => count($query),
            "recordsFiltered" => count($query),
            "data" => $query
        ]);
    }

    public function filter(Request $request)
    {
        try {
            $request->flash();

            $items = Output::join('users', 'users.id', '=', 'outputs.idUser')
                ->select("outputs.id", "outputs.message", "outputs.is_locked", "outputs.TaxDate", "outputs.code", "outputs.codSAP", 'users.name');

            if (!is_null($request->codSAP)) {
                $items->where('codSAP', 'like', "%{$request->codSAP}%");
            }
            if (!is_null($request->codWEB)) {
                $items->where('code', 'like', "%{$request->codWEB}%");
            }
            if (!is_null($request->usuario)) {
                $items->where('idUser', '=', "{$request->usuario}");
            }
            if ((!is_null($request->warehouse))) {
                $items->where('warehouse', $request->warehouse);
            }
            if ((!is_null($request->data_fist))) {
                $items->where('TaxDate', '>=', $request->data_fist);
            }
            if ((!is_null($request->data_last))) {
                $items->where('TaxDate', '<=', $request->data_last);
            }
            if ((!is_null($request->status)) && ($request->status > 0)) {
                switch ($request->status) {
                    case 1:
                        $items->where('is_locked', 0);
                        $items->whereNull('message');
                        break;
                    case 2:
                        $items->where('is_locked', 0);
                        $items->whereNotNull('message');
                        break;
                    case 3:
                        $items->where('is_locked', 1);
                        break;
                    case 4:
                        $items->where(['dbUpdate' => 1, 'is_locked' => 0]);
                        break;
                }
            }

            $items = $items->orderBy('outputs.id', 'desc')->paginate(30)->appends(request()->query());
            return view('inventory::output.index', compact('items'), $this->getOption());
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0007', 'Listando a saída de mercadoria', $e->getMessage());
            return view('inventory::output.index')->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        $head = Output::find($id);
        $upload = Upload::where('reference', 'outputs')->where('idReference', $id)->get();

        return view("inventory::output.edit", array_merge($this->getOption(), ['head' => $head, 'body' => $this->geDiscribre($head->items()->get()), 'upload' => $upload]));
    }

    public function removeUpload($id, $idReference)
    {
        try {
            DB::beginTransaction();
            $upload = Upload::where('id', $id);
            $diretory = public_path($upload->get()->first()->diretory);
            if (file_exists($diretory)) {
                unlink($diretory);
            };
            $upload->delete();
            $output = Output::find($idReference);
            DB::commit();

            return redirect()->route('inventory.output.edit', $output->id)->withSuccess("Anexo excluido com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErro = new logsError();
            $logsErro->saveInDB('EE081', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function geDiscribre($body)
    {
        $new = [];
        foreach ($body as $key => $value) {

            $sap = new Company(false);

            $und = $sap->query("SELECT T0.[ItemCode], T0.[InvntryUom], T0.[BuyUnitMsr] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0];

            $sap = new Company(false);
            $new[] = [
                'id' => $value->id,
                'idOutputs' => $value->idOutputs,
                'itemCode' => $value->itemCode,
                'quantity' => $value->quantity,
                'price' => $value->price,
                'projectCode' => $value->projectCode,
                'costCenter' => $value->costCenter,
                'costCenter2' => $value->costCenter2,
                'accountCode' => $value->accountCode,
                'accountName' => $sap->getDb()->table('OACT')->where('OACT.AcctCode', $value->accountCode)->first()->AcctName,
                'itemName' => $sap->getDb()->table('OITM')->where('OITM.ItemCode', $value->itemCode)->first()->ItemName,
                'wareHouseCode' => $value->wareHouseCode,
                'wareHouseName' => $sap->getDb()->table('OWHS')->where('OWHS.WhsCode', $value->wareHouseCode)->first()->WhsName,
                'lot' => $value->lot,
                'itemUnd' => ((!is_null($und['BuyUnitMsr']) ? $und['BuyUnitMsr'] : $und['InvntryUom'])),
            ];
        }
        return $new;
    }


    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            Item::where('idOutputs', '=', $request->get('id'))->delete();
            foreach ($request->items as $key => $value) {
                $item = new Item();
                $value['idOutputs'] = $request->get('id');
                $item->updateInDB($value, $item);
            }
            $obj = new Output();
            $out = Output::find($request->get('id'));
            $out->DocDate = $request->data;
            $out->dbUpdate = 1;
            $out->is_locked = false;
            $out->save();
            saveUpload($request, 'outputs', $out->id);

            #SetInSAPOutput::dispatch($out->id);
            DB::commit();
            OutputToSAPNew::dispatch($out);

            if ($out->is_locked) {
                return redirect()->route('inventory.output.index')->withErrors($out->message);
            } else {
                return redirect()->route('inventory.output.index')->withSuccess('Operação realizada com sucesso');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            try {
                $logsError = new LogsError();
                $logsError->saveInDB('E0aAF', $e->getFile(), $e->getLine(), $e->getMessage());
                return redirect()->back()->withErrors($e->getMessage());
            } catch (\Exception $t) {

                $mensagem = $e->getMessage() . ' - ' . $t->getMessage();
                return redirect()->back()->withErrors($mensagem);
            }
        }
    }

    public function print($id)
    {
        try {
            $output = Output::find($id);
            if (!empty($output)) {
                $report = new JasperReport();
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/OutputInventory.jasper";

                if (!file_exists($relatory_model)) {
                    $relatory_model = storage_path('app/public/relatorios_modelos') . "/OutputInventory.jrxml";
                }

                $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'output';
                $output = public_path('/relatorios' . '/' . $file_name);
                $report = $report->generateReport($relatory_model, $output, ['pdf'], ['id' => $id], 'pt_BR', 'r2w');

                return response()->file($report)->deleteFileAfterSend(true);
            }
        } catch (\Throwable $e) {
            $logsError = new logsError();
            $logsError->saveInDB('E0013kf', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    // private function getItemName($array)
    // {
    //     $newArray = [];
    //     foreach ($array as $key => $value) {
    //         $sap = new Company(false);
    //         $newArray[] = [
    //             'id' => $value->id,
    //             'itemCode' => $value->itemCode,
    //             'itemName' => $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
    //             'quantity' => $value->quantity,
    //             'price' => $value->price,
    //             'accountCode' => $value->accountCode,
    //             'wareHouseCode' => $sap->query("SELECT concat(T0.[WhsCode],'-', T0.[WhsName]) as [WhsName]  FROM OWHS T0 WHERE T0.[WhsCode]= '{$value->wareHouseCode}'")[0]['WhsName'],
    //             'projectCode' => $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->projectCode}'")[0]['PrjName'],
    //             'costingCode' => $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costingCode}'")[0]['OcrName']
    //         ];
    //     }
    //     return $newArray;
    // }
    private function getItemName($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            $sap = new Company(false);
            $codProject = $sap->query("SELECT T0.[PrjName] FROM OPRJ T0 WHERE T0.[PrjCode] = '{$value->projectCode}'");
            $codCost = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter}'");
            $codCost2 = $sap->query("SELECT T0.[OcrName] FROM OOCR T0 WHERE T0.[OcrCode] = '{$value->costCenter2}'");

            $codWhs = $sap->query("SELECT concat(T0.[WhsCode],'-', T0.[WhsName]) as [WhsName]  FROM OWHS T0 WHERE T0.[WhsCode]= '{$value->wareHouseCode}'");

            $codAcct = $sap->query("SELECT concat(T0.[AcctCode],'-', T0.[AcctName]) as [AcctName] FROM OACT T0 WHERE T0.[AcctCode] = '{$value->accountCode}'");


            $newArray[] = [
                'id' => $value->id,
                'idOutputs' => $value->idOutputs,
                'itemCode' => $value->itemCode,
                'itemName' => $sap->query("SELECT T0.[ItemName] FROM OITM T0 WHERE T0.[ItemCode] = '{$value->itemCode}'")[0]['ItemName'],
                'quantity' => $value->quantity,
                'price' => $value->price,
                'accountCode' => (!empty($codAcct) ? $codAcct[0]['AcctName'] : ''),
                'wareHouseCode' => (!empty($codWhs) ? $codWhs[0]['WhsName'] : ''),
                'projectCode' => (!empty($codProject) ? $codProject[0]['PrjName'] : ''),
                'costingCode' => (!empty($codCost) ? $codCost[0]['OcrName'] : ''),
                'costingCode2' => (!empty($codCost2) ? $codCost2[0]['OcrName'] : '')
            ];
        }

        return $newArray;
    }

    public function report(Request $request)
    {
        return view("inventory::output.report");
    }

    public function reportGenerate(Request $request)
    {

        $data = [
            'code' => $request->code ?? 'NULL',
            'idUser' => $request->name ?? 'NULL',
            'initialDate' => $request->data_ini ?? '2015-01-01',
            'lastDate' => $request->data_fim ?? date('Y-m-d'),
            'docStatus' => $request->status ?? 'NULL'
        ];

        if ($request->tipo == 1) {

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos') . "/InventoryOutput-Sintetico.jasper";
            $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
            $output = public_path('/relatorios' . '/' . $file_name);

            if (!file_exists($relatory_model)) {
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/InventoryOutput-Sintetico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        } else if ($request->tipo == 2) {

            $report = new JasperReport();
            $relatory_model = storage_path('app/public/relatorios_modelos') . "/InventoryOutput-Analitico.jasper";
            $file_name = str_random(5) . "-" . date('his') . "-" . str_random(3) . "=" . 'purchase_order';
            $output = public_path('/relatorios' . '/' . $file_name);

            if (!file_exists($relatory_model)) {
                $relatory_model = storage_path('app/public/relatorios_modelos') . "/InventoryOutput-Analitico.jrxml";
            }

            $report = $report->generateReport($relatory_model, $output, ['pdf'], $data, 'pt_BR', 'r2w');
            return response()->file($report)->deleteFileAfterSend(true);
        }
    }
}
