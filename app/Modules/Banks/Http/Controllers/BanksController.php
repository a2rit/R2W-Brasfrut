<?php

namespace App\Modules\Banks\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Litiano\Sap\Company;

class BanksController extends Controller
{

    // Contas a receber
    public function indexReceive(){
        return view("banks::billsAReceive.index");
    }

    public function createReceive(){
        return view("banks::billsAReceive.create");
    }
    public function list(){
      return view("banks::billsAReceive.list");
    }

    //Contas a Receber por Conta
    public function indexReceiveBills(){
        return view("banks::billsAReceive.indexBills");
    }
    //---- cadastrar -----

    public function createReceiveBills(){

        return view ("banks::billsAReceive.createBills");
    }
 //------ Lista -----
    public function listBills(){

      return view("banks::billsAReceive.listBills");
    }

    //Contas a Pagar

    public function indexPay(){
        return view("banks::billsToPay.index");
    }
    public function createPay(){
        return view ("banks::billsToPay.create");
    }

    //Contas a Pagar por Contas

    public function indexPayBills(){
        return view("banks::billsToPay.indexBills ");

    }
    public function createPayBills(){
        return view("banks::billsToPay.createBills");
    }


    public function getBanksSAP(Request $request){
      $sap = new Company(false);
      $busca = $sap->query("SELECT T0.BankCode as value, T0.BankName as name FROM ODSC T0 WHERE T0.BankName like '%{$request->get("q")}%'");
      return response()->json($busca);
    }

}
