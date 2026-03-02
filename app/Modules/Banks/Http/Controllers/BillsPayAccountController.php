<?php

namespace App\Modules\Banks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Upload;
use App\LogsError;
use App\Modules\Settings\Models\CashFlow;
use App\Modules\Banks\Models\BillsPayAccount\BillsPayAccount;
use App\Modules\Banks\Models\BillsPayAccount\Items;
use App\Modules\Banks\Models\BillsPayAccount\Payment;
use App\SapUtilities;
use App\Http\Requests;
use Litiano\Sap\Company;
use Litiano\Sap\Enum\BoObjectTypes;
use Litiano\Sap\Enum\BoRcptTypes;
use Litiano\Sap\Enum\BoPaymentsObjectType;

class BillsPayAccountController extends Controller {

  use SapUtilities;
  /*
  public function __construct(){
    $this->middleware(function ($request, $next){
        if(!checkAccess('bill_play_account')){
            return redirect()->route('home')->withErrors(auth()->user()->name.' você não possui acesso! consulte o Admin do Sistema');
        }else{
            return $next($request);
        }
    });
  }*/

  public function index(){
    $sap = new Company(false);
    $sql = "SELECT TOP 100 T0.id, T0.codSAP, T0.code, T0.taxDate,T0.comments,T1.name,T0.docTotal, T0.status
            from bills_pay_accounts as T0
            JOIN users T1 on T0.idUser = T1.id ";
    $sql.= " order by  T0.id desc ";
    $items = DB::SELECT($sql);
    return view("banks::billsPayAccount.index",compact('items'));
  }

  public function create(){
    return view("banks::billsPayAccount.create", $this->getOptions());
  }
  protected function getOptions(){
    $sap = new Company(false);
    $cartao = $sap->query("SELECT T0.CreditCard as code, T0.CardName as value FROM OCRC T0");
    $projeto = $this->getProjectOptions($sap);
    $role = $this->getDistributionRulesOptions($sap);
    $account = $this->getAccountOptions($sap);
    $cashFlow = CashFlow::where(['module' => 'C', 'status' => '1'])->select('id', 'description as value')->get();
    return compact('cartao', 'projeto', 'role','account', 'cashFlow');
  }

  public function getAccount(Request $request){
    $sap = new Company(false);
    $query = $sap->getDb()->table("OACT");
    $recordsTotal = $query->count();
    $query->offset($request->get("start"));
    $query->limit($request->get("length"));
    $columnsToSelect = ['AcctCode', 'AcctName'];
    $columns = $request->get("columns");

    $search = $request->get('search');
    if($search['value']) {
        $query->orWhere("AcctCode", "like", "%{$search['value']}%")
            ->orWhere("AcctName", "like", "%{$search['value']}%")
            ->where("PosTable", "=", "Y");
    }else{
      $query->where("PosTable", "=", "Y");

    }
    $order = $request->get('order');

    return response()->json([
        "draw" => $request->get("draw"),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $query->count(),
        "data" => $query->get($columnsToSelect)
    ]);

  }

  public function save(Request $request){
    try {
      DB::beginTransaction();
      $bra = new BillsPayAccount();
      $valid = $bra->saveInDB($request);
      DB::commit();
      $bra->saveInSap($bra);
      if($bra->is_locked){
        return redirect()->back()->withErrors($bra->message);
      }else{
        return redirect()->route('banks.bills.pay.account.index')->withSuccess('Operação realizada com sucesso!');
      }
    }catch (\Exception $e) {
      DB::rollBack();
      $logsError = new LogsError();
      $logsError->saveInDB('EX121',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      return redirect()->route('banks.bills.pay.account.index')->withErrors($e->getMessage());
    }
  }
  public function cancel($id){
    try {
      DB::beginTransaction();
      $BR = BillsPayAccount::find($id);
      $obj = new BillsPayAccount();
      $obj->cancelInSAP($BR);
      DB::commit();
      if($BR->is_locked){
        return redirect()->route('banks.bills.pay.account.index')->withErrors($BR->message);
      }else {
        return redirect()->route('banks.bills.pay.account.index')->withSuccess('Operação realizada com sucesso!');
      }
    } catch (\Exception $e) {
      DB::rollBack();
      $logsError = new LogsError();
      $logsError->saveInDB('E90FG2',$e->getFile().' | '.$e->getLine(),$e->getMessage());
      return redirect()->route('banks.bills.pay.account.index',$id)->withErrors($e->getMessage());
    }
  }
  public function filter(Request $request){
        try{
          $sap = new Company(false);
          $sql = "SELECT TOP 100 T0.id, T0.codSAP, T0.code, T0.taxDate,T0.comments,T1.name,T0.docTotal, T0.status
                  from bills_pay_accounts as T0
                  JOIN users T1 on T0.idUser = T1.id
                  where T0.id != '-1'";

          if (!is_null($request->codSAP)) {
              $sql .= " and T0.codSAP = {$request->codSAP}";
          }
          if (!is_null($request->code)) {
              $sql .= " and T0.code like '{$request->code}'";
          }
          if (!is_null($request->nameParceiro)) {
              $sql .= " and T1.name like '%{$request->nameParceiro}%'";
          }
          if ((!is_null($request->data_fist)) && (!is_null($request->data_last))) {
              $sql .= " and T0.taxDate >= '".$request->data_fist."' and T0.taxDate <= '".$request->data_last."'";
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
          return view('banks::billsPayAccount.index',['items' => $query]);
        }catch (\Throwable $e) {
           $logsErrors = new LogsError();
           $logsErrors->saveInDB('EF012', $e->getFile().' | '. $e->getLine(),$e->getMessage());
           return view('banks::billsPayAccount.index')->withErrors($e->getMessage());
        }
      }

  public function read($id){
    $head = BillsPayAccount::find($id);
    $payment = Payment::where('idBillsPayAccount','=',$id)->get();
	$body = Items::where('idBillsPayAccount','=',$id)->get();
    return view('banks::billsPayAccount.create',array_merge(['head'=>$head,'payment'=>$payment,'body'=>$body], $this->getOptions()));

  }
}
