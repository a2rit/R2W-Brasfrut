<?php

namespace App\Modules\Banks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\LogsError;

use App\Modules\Banks\Models\BillsReceiveAccount\BillsReceiveAccount;
use App\Modules\Banks\Models\BillsReceiveAccount\Payment;
use App\Modules\Banks\Models\BillsReceiveAccount\Items;
use App\Modules\Settings\Models\CashFlow;
use App\SapUtilities;
use App\Http\Requests;
use Litiano\Sap\Company;


class BillsReceiveAccountController extends Controller {
  use SapUtilities;

/*  public function __construct(){
    $this->middleware(function ($request, $next){
        if(!checkAccess('receive_accounts')){
            return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
        }else{
            return $next($request);
        }
    });
  }*/
  public function index(){
      $items = BillsReceiveAccount::orderBy('id','desc')->get();
      return view('banks::billsReceiveAccount.index',['items'=>$items]);
  }

  public function create(){
      return view("banks::billsReceiveAccount.create",$this->getOptions());
  }

  public function read($id){
    $head = BillsReceiveAccount::find($id);
    $payment = Payment::where('idBillsReceiveAccount','=',$id)->get();
    $body = Items::where('idBillsReceiveAccount','=',$id)->get();
    return view('banks::billsReceiveAccount.create',array_merge(['head'=>$head,'payment'=>$payment,'body'=>$body], $this->getOptions()));
  }

  public function save(Request $request){
    try {
      DB::beginTransaction();
      $bra = new BillsReceiveAccount();
      $valid = $bra->saveInDB($request);
      DB::commit();
      $bra->saveInSap($bra);
      if($bra->is_locked){
        return redirect()->route('banks.bills.receive.account.index')->withErrors($bra->message);
      }else{
        return redirect()->route('banks.bills.receive.account.index')->withSuccess('Operação realizada com sucesso!');
      }
    }catch (\Exception $e) {
      DB::rollBack();
      $logsError = new LogsError();
      $logsError->saveInDB('EX121',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      return redirect()->route('banks.bills.receive.account.index')->withErrors($e->getMessage());
    }

  }
  public function cancel($id){
    try {
      DB::beginTransaction();
      $BR = BillsReceiveAccount::find($id);
      $obj = new BillsReceiveAccount();
      $obj->cancelInSAP($BR);
      DB::commit();
      if($BR->is_locked){
        return redirect()->route('banks.bills.receive.account.index')->withErrors($BR->message);
      }else {
        return redirect()->route('banks.bills.receive.account.index')->withSuccess('Operação realizada com sucesso!');
      }
    } catch (\Exception $e) {
      DB::rollBack();
      $logsError = new LogsError();
      $logsError->saveInDB('E903G2',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      return redirect()->route('banks.bills.receive.account.read',$id)->withErrors($e->getMessage());
    }

  }

  protected function getOptions(){
    $sap = new Company(false);
    $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
    $projeto = $this->getProjectOptions($sap);
    $role = $this->getDistributionRulesOptions($sap);
    $account = $this->getAccountOptions($sap);
    $cashFlow = CashFlow::where(['module' => 'V', 'status' => '1'])->select('id', 'description as value')->get();
    return compact('cartao', 'projeto', 'role','account','cashFlow');
  }

  public function filter(Request $request){
      try{
        $sap = new Company(false);
        $sql = "SELECT TOP 100 T0.id, T0.codSAP, T0.code, T0.taxDate,T0.comments,T1.name,T2.docTotal,T0.status
                from bills_receive_accounts as T0
                JOIN users T1 on T0.idUser = T1.id
                JOIN bills_receive_account_payments T2 on T0.id = T2.idBillsReceiveAccount
                where T0.id != '-1'";

        if (!is_null($request->codSAP)) {
            $sql .= " and T0.codSAP = {$request->codSAP}";
        }
        if (!is_null($request->codWEB)) {
            $sql .= " and T0.code = '{$request->codWEB}'";
        }
        if (!is_null($request->nameParceiro)) {
            $sql .= " and T1.name like '%{$request->nameParceiro}%'";
        }
        if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
            $sql .= " and T0.taxDate >=  '".$request->data_fist."' and T0.taxDate <= '".$request->data_last."'";
        }
        if ((!is_null($request->value_fist)) && (!is_null($request->value_last))) {
            $value_fist = clearNumberDouble($request->value_fist);
            $value_last = clearNumberDouble($request->value_last);
            $sql .= " and T0.docTotal >=  '".$value_fist."' and T0.docTotal <=  '".$value_last."'";
        }
        if(!is_null($request->status)){
          $sql .= " and T0.status = '{$request->status}'";
        }
        $sql.= " order by  T0.id desc ";
        $query = DB::SELECT($sql);
        return view('banks::billsReceiveAccount.index',['items' => $query]);
      }catch (\Throwable $e) {
         $logsErrors = new LogsError();
         $logsErrors->saveInDB('E0012', 'Consultando recebimento de mercadoria',$e->getMessage());
         return view('banks::billsReceiveAccount.index')->withErrors($e->getMessage());
      }
    }
}
