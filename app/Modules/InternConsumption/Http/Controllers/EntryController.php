<?php

namespace App\Modules\InternConsumption\Http\Controllers;

use App\Exceptions\SapIntegrationException;
use App\Http\Controllers\Controller;
use App\Models\PontoVenda;
use App\Modules\InternConsumption\Models\InternConsumption;
use App\Modules\InternConsumption\Models\InternConsumption\Item;
use App\Notifications\NewInternConsumption;
use App\User;
use Auth;
use DB;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoProductionOrderStatusEnum;
use Litiano\Sap\IdeHelper\IDocuments;
use Litiano\Sap\IdeHelper\IJournalEntries;
use Litiano\Sap\IdeHelper\IProductionOrders;
use Litiano\Sap\IdeHelper\IStockTransfer;
use Litiano\Sap\NewCompany;
use Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Notification;
use Throwable;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view("intern-consumption::index");
    }

    public function indexData(Request $request)
    {
        $columns = $request->get('columns');

        $length = max($request->get('length'), 100);
        $order = $request->get('order');
        $order = $order[0];
        $orderBy = $columns[$order['column']];
        $columnsSelect = ['id', 'document_type', 'requester_name', 'status', 'date', 'distribution_rule', 'distribution_rule2',
            'project', 'observation',];
        $query = InternConsumption::orderBy($orderBy['name'], $order['dir']);

        if ($request->get('status')) {
            $query->where('status', '=', $request->get('status'));
        }

        if ($request->get('request_date')) {
            $date = str_replace("/", "-", explode(" - ", $request->get('request_date')));
            $query->whereDate('date', '>=', date_format(date_create($date[0]),"Y-m-d"))
                ->whereDate('date', '<=', date_format(date_create($date[1]),"Y-m-d"));
        }

        if ($request->get('requester')) {
            $requesterSapId = User::find($request->get('requester'));
            $query->where('requester_sap_id', '=', $requesterSapId->userClerk);
        }
        
        if (isset($request->document_type)) {
            $query->where('document_type', '=', $request->get('document_type'));
        }

        $search = $request->get('search');
        if ($search['value']) {
            $query->orWhere("id", "like", "%{$search['value']}%");
        }
        
        $recordsFiltered = $query->count();
        $query->offset($request->get('start'));
        $query->limit($length);

        $return = [];
        $return['recordsTotal'] = InternConsumption::count();
        $return['recordsFiltered'] = $recordsFiltered;
        $return['draw'] = $request->get('draw');
        $return['data'] = $query->get($columnsSelect);

        return response()->json($return);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|Response|View
     */
    public function create($documentType = null)
    {
        $sellingPoints = PontoVenda::all();
        $sap = NewCompany::getInstance();
        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);
        $projects = $sap->getProjectsQueryBuilder(true);
        $distributionRules = $sap->getDistributionRulesQueryBuilder(true);
        //@TODO move to NewCompany
        $distributionRules2 = $this->getDistributionRulesQueryBuilder(true, 2);

        $attributes = $this->getDocumentAttributes((Int)$documentType);

        $whsCodes = (int)$documentType == 2 ? ["28", "29"] : ["27"];
        $whs = $sap->getDb()->table('OWHS')
            ->select('WhsCode as value', 'WhsName as name')
            ->whereIn('WhsCode', $whsCodes)
            ->get();

        return view("intern-consumption::create",
            compact('sellingPoints', 'projects', 'distributionRules', 'requesters', 'distributionRules2', 'documentType', 'attributes', 'whs'));
    }

    protected function getDistributionRulesQueryBuilder($toArray = false, $dimCode = 1)
    {
        $sap = NewCompany::getInstance();
        $query = $sap->getDb()
            ->table('OOCR')
            ->join('OCR1', 'OOCR.OcrCode', '=', 'OCR1.OcrCode')
            ->where('OOCR.Active', '=', 'Y')
            ->where(function (Builder $builder) {
                $builder->whereNull('OCR1.ValidFrom')
                    ->orWhereDate('OCR1.ValidFrom', '<=', \Carbon\Carbon::now());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('OCR1.ValidTo')
                    ->orWhereDate('OCR1.ValidTo', '>=', Carbon::now());
            })
            ->where('OOCR.DimCode', '=', $dimCode)
            ->orderBy('OOCR.OcrCode')
            ->distinct();
        if ($toArray) {
            return $query->get(['OOCR.OcrCode as value', 'OOCR.OcrName as name']);
        }
        return $query;
    }

    public function searchItem(Request $request)
    {
        $sap = NewCompany::getInstance();
        $pos = PontoVenda::find($request->get('pos_id'));

        if (!$pos) {
            $pos = PontoVenda::first();
        }

        $priceList = $pos->ci_config["price_list"];
        $priceList = $priceList ? $priceList : 1;
        $columnsToSelect = ['OITM.ItemName as name', 'OITM.ItemCode as code', 'OITT.Code as type'];
        
        $q = "%{$request->get('q')}%";
        $items = $sap->getValidItemQueryBuilder('OITM')
            ->leftJoin('OITT', 'OITM.ItemCode', '=', 'OITT.Code')
            ->leftJoin('ITM1', 'OITM.ItemCode', '=', 'ITM1.ItemCode')
            ->where('ITM1.PriceList', '=', $priceList)
            ->where(function (Builder $where) use ($q) {
                $where->where('OITM.ItemCode', 'like', $q)
                    ->orWhere('OITM.ItemName', 'like', $q);
            })->limit(8);

        if(!empty($request->documentType) && $request->documentType !== '0'){
            $items->leftJoin('OITW', 'OITW.ItemCode', 'OITM.ItemCode')
                ->where('OITW.WhsCode', '=', $pos->deposito);

            array_push($columnsToSelect, 'OITW.AvgPrice as value');
        }else{
            $items->where('OITM.InvntItem', '=', 'Y')
                ->whereRaw('ISNUMERIC(OITM.ItemCode) = 1');

            array_push($columnsToSelect, 'ITM1.Price as value');
        }

        $items = $items->get($columnsToSelect);

        foreach ($items as &$item) {
            $item->type = $item->type ? 'IP' : 'IV';
            //$item->value = json_encode($item);
            $item->value = (float)$item->value;
            $item->text = "{$item->code} - {$item->name}";
        }

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function store(Request $request)
    {
        try {
            $ic = DB::transaction(function () use ($request) {
                $data = $request->all();

                $data['status'] = InternConsumption::STATUS_NEW;

                $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
                $requester = NewCompany::getDb()->table('OHEM')
                    ->where('empID', $request->input('requester_sap_id'))
                    ->orderBy('firstName')
                    ->first(['empID as id', $fullNameRaw]);
                $data['requester_name'] = $requester->name;
                $data['requester_sap_id'] = $requester->id;

                $data['creator_user_id'] = Auth::user()->id;
                $data['date'] = Carbon::parse($request->input('date'));
                if ($request->input('id')) {
                    /** @var InternConsumption $ic */
                    $ic = InternConsumption::lockForUpdate()->find($request->input('id'));
                    if ($ic->canUpdate()) {
                        throw new Exception('Este consumo interno não pode ser alterado!');
                    }
                    $ic->update($data);
                    $ic->addComment('Solicitação alterada com sucesso!', $ic->status);
                } else {
                    $ic = InternConsumption::create($data);
                    $ic->addComment('Solicitação criada com sucesso!', $ic->status);
                }
                $ic->items()->delete();
                $ic->items()->createMany($request->get('items'));

                if ($request->get('comment')) {
                    $ic->addComment($request->get('comment'), $ic->status);
                }

                Notification::send(
                    $this->getNotifiableUsers(),
                    new NewInternConsumption('Novo consumo interno de ' . Auth::user()->name)
                );

                return $ic;
            });
        } catch (Throwable $e) {
            return response()->json(['error' => true, 'msg' => $e->getMessage()], 500);
        }
        return response()->json(['msg' => 'Solicitação salva com sucesso!', 'ic' => $ic]);
    }

    protected function getNotifiableUsers()
    {
        return User::whereHas('roles', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->whereIn('code', ['admin', 'InternConsumption.authorize']);
        })->get();
    }

    public function storeComment(Request $request, $id)
    {
        $ic = InternConsumption::find($id);
        $ic->addComment($request->get('comment'), $ic->status);

        return redirect()->back()->withSuccess("Comentário adicionado com sucesso!");
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Factory|Response|View
     */
    public function show(InternConsumption $ic)
    {
        $attributes = $this->getDocumentAttributes($ic->document_type);
        return view("intern-consumption::show", compact('ic', 'attributes'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param InternConsumption $internConsumption
     * @return Factory|Response|View
     * @throws Exception
     */
    public function edit(InternConsumption $internConsumption)
    {
        if (!$internConsumption->canUpdate()) {
            return redirect()->back()->withErrors('Este consumo interno não pode ser editado!');
        }

        $internConsumption->load('items');
        $sellingPoints = PontoVenda::all();
        $sap = NewCompany::getInstance();
        $fullNameRaw = DB::raw("(ISNULL(firstName, '') + ' ' + ISNULL(middleName, '') + ' ' + ISNULL(lastName, '')) as name");
        $requesters = $sap->getDb()->table('OHEM')
            ->where('Active', 'Y')
            ->orderBy('firstName')
            ->get(['empID as id', $fullNameRaw]);
        $projects = $sap->getProjectsQueryBuilder(true);
        $distributionRules = $sap->getDistributionRulesQueryBuilder(true);
        $distributionRules2 = $this->getDistributionRulesQueryBuilder(true, 2);
        $attributes = $this->getDocumentAttributes($internConsumption->document_type);

        return view(
            "intern-consumption::create",
            compact(
                'sellingPoints',
                'projects',
                'distributionRules',
                'requesters',
                'distributionRules2',
                'internConsumption',
                'attributes'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse|Response
     */
    public function update(Request $request, $id)
    {
        //@TODO check if user is authorized
        try {
            $message = DB::transaction(function () use ($request, $id) {
                /** @var InternConsumption $ic */
                $ic = InternConsumption::lockForUpdate()->find($id);

                return $ic->setStatus((bool)$request->get('approve'), $request->get('comment'));
            });

            return response()->json(['message' => $message, 'success' => true]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'success' => false]);
        }
    }

    public function cancel(InternConsumption $ic)
    {
        if ((!auth()->user()->admin && $ic->authorizer_user_id) || $ic->status === $ic::STATUS_CANCELED) {
            return redirect()->back()->withErrors('Este pedido não pode ser cancelado!');
        }

        NewCompany::transaction(function () use ($ic) {
            /** @var InternConsumption $icModel */
            $icModel = InternConsumption::lockForUpdate()->find($ic->id);
            $sap = NewCompany::getInstance()->getCompany();

            if ($icModel->sales_order_code) {
                /** @var IDocuments $order */
                $order = $sap->GetBusinessObject(BoObjectTypes::oOrders);
                if ($order->GetByKey((int) $icModel->sales_order_code)) {
                    // BoStatus.bost_Close
                    if ($order->DocumentStatus === 1) {
                        throw new SapIntegrationException('Pedido fechado não pode ser cancelado.');
                    }
                    if ($order->Cancel() !== 0) {
                        throw new SapIntegrationException(
                            'Erro ao cancelar pedido de venda. ' . $sap->GetLastErrorDescription()
                        );
                    }
                }
            }

            if ($icModel->stock_transfer_code) {
                /** @var IStockTransfer $stockTransfer */
                $stockTransfer = $sap->GetBusinessObject(BoObjectTypes::oStockTransfer);
                if ($stockTransfer->GetByKey((int) $icModel->stock_transfer_code)) {
                    if ($stockTransfer->Cancel() !== 0) {
                        throw new SapIntegrationException(
                            'Erro ao cancelar transferência de estoque. ' . $sap->GetLastErrorDescription()
                        );
                    }
                }
            }

            if ($icModel->manual_account_entry_id) {
                /** @var IJournalEntries $doc */
                $doc = $sap->GetBusinessObject(BoObjectTypes::oJournalEntries);
                if ($doc->GetByKey((int) $icModel->manual_account_entry_id)) {
                    if ($doc->Cancel() !== 0) {
                        throw new SapIntegrationException(
                            'Erro ao cancelar lançamento contábil. ' . $sap->GetLastErrorDescription()
                        );
                    }
                }
            }

            foreach ($icModel->notFinalizedItems as $item) {
                if (in_array($item->production_order_status, [Item::PO_STATUS_PLANNED, Item::PO_STATUS_RELEASED])) {
                    /** @var IProductionOrders $po */
                    $po = $sap->GetBusinessObject(BoObjectTypes::oProductionOrders);
                    $po->GetByKey($item->production_order_code);

                    if (
                        $po->ProductionOrderStatus !== BoProductionOrderStatusEnum::boposCancelled
                        && $po->Cancel() !== 0
                    ) {
                        throw new SapIntegrationException(
                            "Erro ao cancelar ordem de produção {$item->production_order_code}"
                        );
                    }

                    $item->production_order_status = Item::PO_STATUS_CANCELED;
                    $item->save();
                }
            }

            $ic->addComment('Cancelado', $ic::STATUS_CANCELED);

            $ic->status = $ic::STATUS_CANCELED;
            $ic->save();

            foreach ($ic->items as $item) {
                $item->destroyError();
            }
            $ic->destroyError();
        });

        return redirect()->back()->withSuccess('Pedido cancelado com sucesso!');
    }

    public function print(InternConsumption $ic)
    {
        try {
            if (!isset($ic->pos->ci_config['printer_ip']) || !isset($ic->pos->ci_config['printer_port'])) {
                return redirect()->back()->withErrors("Informe IP e porta da impressora na configuração!");
            }
            $connector = new NetworkPrintConnector($ic->pos->ci_config['printer_ip'], $ic->pos->ci_config['printer_port'], 5);
            $printer = new Printer($connector);
            //Header
            $printer->setFont(Printer::FONT_A);
            $printer->setEmphasis();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("Yacht Clube da Bahia\n");
            $printer->setTextSize(1, 1);
            $printer->feed();
            $printer->text("Consumo Interno {$ic->pos->nome}\n");
            $printer->text("Nº {$ic->id}\n");
            $printer->feed();

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis();
            $printer->text("Código     Descrição    Qtd.  V.Unt. Total\n");

            $printer->setEmphasis(false);
            $printer->setFont(Printer::FONT_B);
            foreach ($ic->items as $item) {
                $name = mb_strlen($item->name) > 21 ? mb_substr($item->name, 0, 21) : $item->name;

                $printer->text($this->mbStrPad($item->code, 8) . ' ');
                $printer->text($this->mbStrPad($name, 21) . ' ');
                $printer->text($this->mbStrPad(number_format($item->qty, 3, ',', '.'), 7) . ' ');
                $printer->text($this->mbStrPad(number_format($item->value, 2, ',', '.'), 8) . ' ');
                $printer->text($this->mbStrPad(number_format($item->total, 2, ',', '.'), 8) . ' ');
                $printer->text("\n");
            }
            $printer->feed();
            $printer->feed();

            $printer->setFont(Printer::FONT_A);
            $printer->setEmphasis(true);
            $printer->text('Total: ');
            $printer->setEmphasis(false);
            $total = number_format($ic->total, 2, ',', '.');
            $printer->text("R$ {$total}\n");

            $printer->setEmphasis(true);
            $printer->text('Solicitante: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->requester_name}\n");

            $printer->setEmphasis(true);
            $printer->text('Ramal: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->requester_branch}\n");

            $printer->setEmphasis(true);
            $printer->text('Regra de distribuição: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->distribution_rule_name}\n");

            $printer->setEmphasis(true);
            $printer->text('Regra de distribuição 2: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->distribution_rule2_name}\n");

            $printer->setEmphasis(true);
            $printer->text('Projeto: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->project_name}\n");

            $printer->setEmphasis(true);
            $printer->text('Data: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->date->format('d-m-Y')}\n");

            $printer->setEmphasis(true);
            $printer->text('Local de entrega: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->delivery_location}\n");

            $printer->setEmphasis(true);
            $printer->text('Comentário: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->comment}\n");

            $printer->setEmphasis(true);
            $printer->text('Observações: ');
            $printer->setEmphasis(false);
            $printer->text("{$ic->observation}\n");

            $printer->feed(3);

            $printer->setEmphasis(true);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed(1);
            $y = date('Y');
            $printer->text("© {$y} A2R Inovação em Tecnologia");

            $printer->feed(1);
            $printer->cut();
            $printer->close();
        } catch (Exception $e) {
            Log::error($e);

            return redirect()->back()->withErrors('Erro ao imprimir!');
        }

        return redirect()->back()->withSuccess('Impressão realizada com sucesso!');
    }

    protected function mbStrPad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT): string
    {
        $diff = strlen($input) - mb_strlen($input);

        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

    private function getDocumentAttributes($documentType){
        switch ((Int)$documentType) {
            case 0:
                $attributes = [
                    'pageTitle' => 'Lançamento de Consumo Interno',
                    'label-date' => 'Data de consumo',
                    'label_ponto_venda' => "Ponto de venda",
                    'show_local_entrega' => true,
                    'show_item_comments' => false,
                    'show_dest_whs' => false,
                ];
                break;
            case 1:
                $attributes = [
                    'pageTitle' => 'Lançamento de Perdas',
                    'label-date' => 'Data de lançamento',
                    'label_ponto_venda' => "Depósito",
                    'show_local_entrega' => false,
                    'show_item_comments' => true,
                    'show_dest_whs' => true,
                ];
                break;
            case 2:
                $attributes = [
                    'pageTitle' => 'Lançamento de Eventos',
                    'label-date' => 'Data de lançamento',
                    'label_ponto_venda' => "Depósito",
                    'show_local_entrega' => false,
                    'show_item_comments' => true,
                    'show_dest_whs' => true,
                ];
                break;
            default:
                $attributes = [
                    'pageTitle' => 'Lançamento de Consumo Interno',
                    'label-date' => 'Data de consumo',
                    'label_ponto_venda' => "Ponto de venda",
                    'show_local_entrega' => true,
                    'show_item_comments' => false,
                    'show_dest_whs' => false,
                ];
                break;
        }
        return $attributes;
    }
}