<?php

namespace App\Modules\Banks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Upload;
use App\LogsError;
use Illuminate\Support\Facades\Response;
use App\Modules\Banks\Models\BillsPay\BillsPay;
use App\Modules\Banks\Models\BillsPay\Invoice;
use App\Modules\Banks\Models\BillsPay\Payment;
use App\Http\Requests;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;

use Litiano\Sap\Enum\DownPaymentTypeEnum;
use Litiano\Sap\Enum\BoORCTPaymentTypeEnum;
use Litiano\Sap\Enum\BoPaymentsObjectType;
use Litiano\Sap\Enum\BoRcptInvTypes;
use App\SapUtilities;

class BillsToPayController extends Controller
{

    use SapUtilities;

  /*  public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!checkAccess('bill_plays')) {
                return redirect()->route('home')->withErrors(auth()->user()->name . ' você não possui acesso! consulte o Admin do Sistema');
            } else {
                return $next($request);
            }
        });
    }*/

    public function index()
    {
        $items = BillsPay::orderBy('id', 'desc')->get();

        return view('banks::billsToPay.index', compact('items'));
    }

    public function create()
    {
        return view('banks::billsToPay.create', $this->getOptions());
    }

    private function getOptions()
    {
        $sap = new Company(false);
        $obpl = $sap->query("SELECT T0.BPLId as code, T0.BPLName as value FROM OBPL T0");
        $paymentConditions = $sap->query("SELECT T0.GroupNum, T0.PymntGroup FROM OCTG T0");
        $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
        $typeOut = $sap->query("SELECT T0.ExpnsCode as code, T0.ExpnsName as value FROM OEXD T0 order by code");
        $account = $this->getAccountOptions($sap);
        return compact('obpl', 'paymentConditions', 'cartao', 'typeOut', 'account');
    }

    public function read($id)
    {
        $head = BillsPay::find($id);
        $invoice = Invoice::where('idBillspay', '=', $id)->get();
        $payment = Payment::where('idBillspay', '=', $id)->get();
        return view('banks::billsToPay.create', array_merge(['head' => $head, 'invoice' => $invoice, 'payment' => $payment], $this->getOptions()));

    }

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $billsPay = new BillsPay();
            DB::commit();
            $billsPay->saveInDB($request);
            $billsPay->saveInSap($billsPay);
            if ($billsPay->is_locked) {
                return redirect()->back()->withErrors($billsPay->message);
            } else {
                return redirect()->back()->withSuccess('Operação realizada com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0047',$e->getFile(). ' | '.$e->getLine(), $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            $BR = BillsPay::find($id);
            $obj = new BillsPay();
            $obj->cancelInSAP($BR);
            DB::commit();
            if ($BR->is_locked) {
                return redirect()->route('banks.bills.pay.index')->withErrors($BR->message);
            } else {
                return redirect()->route('banks.bills.pay.index')->withSuccess('Operação realizada com sucesso!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $logsError = new LogsError();
            $logsError->saveInDB('E90FG2', $e->getFile() . ' | ' . $e->getLine(), $e->getMessage());
            return redirect()->route('banks.bills.pay.index', $id)->withErrors($e->getMessage());
        }
    }

    public function getAllAccounts($cardCode)
    {
        $bp = new BillsPay();
        return Response::json($bp->getAccounts($cardCode));

    }

    public function filter(Request $request)
    {
        try {
            $sap = new Company(false);
            $sql = "SELECT  T0.id, T0.codSAP,T0.identification, T0.code, T0.taxDate,T0.comments,T0.docTotal,T0.cardName,T0.status, T1.name
                  from bills_pays as T0
                  JOIN users T1 on T1.id = T0.idUser
                  where T0.id != '-1'";

            if (!is_null($request->codSAP)) {
                $sql .= " and T0.codSAP = {$request->codSAP}";
            }
            if (!is_null($request->code)) {
                $sql .= " and T0.code = '{$request->code}'";
            }
            if (!is_null($request->cardName)) {
                $sql .= " and T0.cardName like '%{$request->cardName}%'";
            }
            if (!is_null($request->cpf_cnpj)) {
                $sql .= " and T0.identification = '{$request->cpf_cnpj}'";
            }
            if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
                $sql .= " and T0.taxDate >=  '" . $request->data_fist . "' and T0.taxDate <= '" . $request->data_last . "'";
            }
            if ((!is_null($request->value_fist)) && (!is_null($request->value_last))) {
                $value_fist = clearNumberDouble($request->value_fist);
                $value_last = clearNumberDouble($request->value_last);
                $sql .= " and T0.docTotal >=  '" . $value_fist . "' and T0.docTotal <=  '" . $value_last . "'";
            }
            if (!is_null($request->status)) {
                $sql .= " and T0.status = {$request->status}";
            }
            $sql .= " order by  T0.id desc ";
            $query = DB::SELECT($sql);

            return view('banks::billsToPay.index', ['items' => $query]);
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E0012', 'Consultando recebimento de mercadoria', $e->getMessage());
            return view('banks::billsToPay.index')->withErrors($e->getMessage());
        }
    }

    public function indexRelatory()
    {
        return view('banks::billsToPay.relatory');
    }

    public function relatory(Request $request)
    {
        try {
            $sap = new Company(false);
            $company = \App\Modules\Settings\Models\Company::orderBy('id', 'des')->first();
            $page = $request->get('type');

            if ($page == 1) {
                $body = $sap->getDb()->table('U_R2W_BILLS_PAY');
            } else {
                $body = $sap->getDb()->table('U_R2W_BILLS_PAYD');
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
            $body->orderBy('FORNECEDOR');
            $body = $body->get();

            $img = Upload::where(['reference' => 'companies', 'idReference' => $company->id])->orderBy('id', 'desc')->first();
            return \PDF1::setOptions(['uplouds' => true])->loadView('relatory.layouts.billsPlay', compact('body', 'img', 'company','page'))->setPaper('a4', 'portrait')->stream('pdf.pdf');
        } catch (\Throwable $e) {
            $logsErrors = new LogsError();
            $logsErrors->saveInDB('E9HsFA', $e->getFile() . '|' . $e->getLine(), $e->getMessage());
            return view('banks::billsToPay.index')->withErrors($e->getMessage());
        }
    }
}
