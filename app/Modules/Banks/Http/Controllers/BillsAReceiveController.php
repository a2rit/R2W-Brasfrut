<?php

namespace App\Modules\Banks\Http\Controllers;

use App\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use App\LogsError;
use App\Modules\Banks\Models\BillsReceive\BillsReceive;
use App\Modules\Banks\Models\BillsReceive\Invoice;
use App\Modules\Banks\Models\BillsReceive\Payment;

use App\Http\Requests;
use Litiano\Sap\Company;
use App\SapUtilities;

class BillsAReceiveController extends Controller
{
    use SapUtilities;

   /* public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!checkAccess('bill_receives')) {
                return redirect()->route('home')->withErrors(auth()->user()->name . ' você não possui acesso! consulte o Admin do Sistema');
            } else {
                return $next($request);
            }
        });
    }
*/
    public function index()
    {

        $items = BillsReceive::orderBy('id', 'desc')->get();

        return view('banks::billsAReceive.index', compact('items'));
    }

    public function search()
    {
        return view('banks::billsAReceive.search');
    }

    public function create()
    {
        return view('banks::billsAReceive.create', $this->getOption());
    }

    private function getOption()
    {
        $sap = new Company(false);
        $obpl = $sap->query("SELECT T0.BPLId as code, T0.BPLName as value FROM OBPL T0");
        $account = $this->getAccountOptions($sap);
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
        $typeOut = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        return compact('obpl', 'account', 'paymentConditions', 'cartao', 'typeOut');
    }

    /*Contas a receber por contas*/
    public function getAllAccounts($cardCode)
    {
        $br = new BillsReceive();
        return Response::json($br->getAccounts($cardCode));
    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $br = new BillsReceive();
            $valid = $br->saveInDB($request);
            DB::commit();
            $br->saveInSap($br);
            if ($br->is_locked) {
                return redirect()->back()->withErrors($br->message);
            } else {
                return redirect()->route('banks.bills.receive.index')->withSuccess('Operação realizada com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('E903G', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('banks.bills.receive.index')->withErrors($e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            $BR = BillsReceive::find($id);
            $obj = new BillsReceive();
            $obj->cancelInSAP($BR);

            DB::commit();
            if ($BR->is_locked) {
                return redirect()->route('banks.bills.receive.index')->withErrors($BR->message);
            } else {
                return redirect()->route('banks.bills.receive.index')->withSuccess('Operação realizada com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('E903G2', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('banks.bills.receive.read', $id)->withErrors($e->getMessage());
        }

    }

    public function read($id)
    {
        $head = BillsReceive::find($id);
        $invoice = Invoice::where('idBillsReceive', '=', $id)->get();
        $payment = Payment::where('idBillsReceive', '=', $id)->get();
        return view('banks::billsAReceive.create', array_merge(['head' => $head, 'invoice' => $invoice, 'payment' => $payment], $this->getOption()));
    }

    public function filter(Request $request)
    {
        try {
            $sql = BillsReceive::orderBy('id', 'desc');


            if (!is_null($request->codSAP)) {
                $sql->where('codSAP', $request->codSAP);
            }
            if (!is_null($request->code)) {
                $sql->where('code', $request->code);
            }
            if (!is_null($request->cardName)) {
                $sql->where('cardName', 'like', "%{$request->cardName}%");
            }
            if (!is_null($request->cpf_cnpj)) {
                $sql->where('identification', $request->cpf_cnpj);
            }
            if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
                $sql->where('taxDate', ' >=', $request->data_fist);
                $sql->where('taxDate', ' <=', $request->data_last);
            }
            if ((!is_null($request->value_fist)) && (!is_null($request->value_last))) {
                $value_fist = clearNumberDouble($request->value_fist);
                $value_last = clearNumberDouble($request->value_last);
                $sql->where('docTotal', ' >=', $value_fist);
                $sql->where('docTotal', ' <=', $value_last);
            }
            if (!is_null($request->status)) {
                $sql->where('status', $request->status);
            }
            $items = $sql->get();

            return view('banks::billsAReceive.index', compact('items'));
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0012', 'Consultando recebimento de mercadoria', $e->getMessage());
            return view('banks::billsAReceive.index')->withErrors($e->getMessage());
        }
    }

    public function indexRelatory()
    {
        return view('banks::billsAReceive.relatory');
    }

    public function relatory(Request $request)
    {
        try {
            $sap = new Company(false);
            $company = \App\Modules\Settings\Models\Company::orderBy('id', 'desc')->first();
            $br = new BillsReceive();
            $page =  $request->get('type');
            if ($page == 1) {
                $body = $sap->getDb()->table('U_R2W_BILLS_RECEIVE');
            } else {
                $body = $sap->getDb()->table('U_R2W_BILLS_RECEIVED');
            }

            if(!is_null($request->get('cardCode'))){
                $body->where('CardCode', $request->cardCode);
            }
            if(!is_null($request->get('data_fist')) && !is_null($request->get('data_last'))){
                $data_fist = str_replace('-','/',$request->data_fist);
                $data_last = str_replace('-','/',$request->data_last);
                $body->where('DTEMISSAO', '>=',$data_fist);
                $body->where('DTEMISSAO', '<=',$data_last);
            }
            if(!is_null($request->get('data_fist_venc')) && !is_null($request->get('data_last_venc'))){
                $data_fist_venc = str_replace('-','/',$request->data_fist_venc);
                $data_last_venc = str_replace('-','/',$request->data_last_venc);
                $body->where('DTVENCTO', '>=',$data_fist_venc);
                $body->where('DTVENCTO', '<=',$data_last_venc);
            }

            $body->orderBy('DTVENCTO');
            $body->orderBy('CLIENTE');
            $body = $body->get();
            $img = Upload::where(['reference' => 'companies', 'idReference' => $company->id])->orderBy('id', 'desc')->first();
            //return view('relatory.layouts.billsReceive', compact('page','body', 'img', 'company'));
            return \PDF1::setOptions(['uplouds' => true])
                ->loadView('relatory.layouts.billsReceive', compact('page','body', 'img', 'company'))->setPaper('a4', 'portrait')->stream('pdf.pdf');

        } catch (\Throwable $e) {

            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E9HsFA', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return view('banks::billsAReceive.index')->withErrors($e->getMessage());
        }
    }
}
