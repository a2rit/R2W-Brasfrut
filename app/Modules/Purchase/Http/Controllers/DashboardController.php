<?php

namespace App\Modules\Purchase\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Purchase\Models\PurchaseOrder\PurchaseOrder;
use App\Modules\Purchase\Models\PurchaseQuotation\PurchaseQuotation;
use App\Modules\Purchase\Models\PurchaseRequest\PurchaseRequest;
use App\Modules\Purchase\Models\IncoingInvoice\IncoingInvoice;
use App\Modules\Purchase\Models\AdvanceProvider\AdvanceProvider;
use Litiano\Sap\Company;
use App\User\CostCenter;
use App\User;
use Redirect;

class DashboardController extends Controller
{
    
    public function index(Request $request){

        $user = User::find(Auth::user()->id);
        $selectedCostCenter = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode')->pluck('costCenterCode')->first();
    
        if(empty($selectedCostCenter)){
            return Redirect::back()->withErrors('É necessário informar os centros de custos do usuário na tela de configuração de usuários');
        }
        
        $selectedCostCenter2 = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode2')->pluck('costCenterCode2')->first();

        $fist_date = Carbon::now()->subYear();
        $last_date = Carbon::now();
        
        $purchase_requests = PurchaseRequest::select('purchase_requests.id', 'purchase_requests.codStatus',
                (DB::raw("MONTH(purchase_requests.requriedDate) as month, YEAR(purchase_requests.requriedDate) as year")))
            ->join('purchase_request_items', 'purchase_request_items.idPurchaseRequest', 'purchase_requests.id')
            ->where('purchase_requests.code', 'like', '%SLC%')
            ->where('purchase_requests.codSAP', '!=', null)
            ->where('purchase_request_items.distrRule', $selectedCostCenter)
            ->whereBetween('purchase_requests.requriedDate', [$fist_date, $last_date]);
        
        $purchase_quotations = PurchaseQuotation::select('purchase_quotation.created_at',
                'purchase_quotation.id', 'purchase_quotation.data_i as data_i', 'purchase_quotation.status',
                (DB::raw("MONTH(purchase_quotation.data_i) as month, YEAR(purchase_quotation.data_i) as year")))
            ->join('purchase_quotation_items', 'purchase_quotation_items.idPurchaseQuotation', 'purchase_quotation.id')
            ->join('purchase_request_items', 'purchase_request_items.id', '=', 'purchase_quotation_items.idItemPurchaseRequest')
            ->where('purchase_request_items.distrRule', $selectedCostCenter)
            ->whereBetween('purchase_quotation.data_i', [$fist_date, $last_date]);

        $purchase_orders = PurchaseOrder::select('taxDate', 'docTotal', 'purchase_orders.status', 'purchase_orders.created_at',
                (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year")))
            ->join('purchase_order_items', 'purchase_order_items.idPurchaseOrders', 'purchase_orders.id')
            ->where('purchase_order_items.costCenter', $selectedCostCenter)
            ->where('purchase_orders.codSAP', '!=', null)
            ->whereBetween('taxDate', [$fist_date, $last_date]);
        
        $incoing_invoices = IncoingInvoice::select('incoing_invoices.id',
            'incoing_invoices.docTotal', 'incoing_invoices.status', 'incoing_invoices.created_at',
            (DB::raw("MONTH(incoing_invoices.taxDate) as month, YEAR(incoing_invoices.taxDate) as year")))
            ->join('incoing_invoice_items', 'incoing_invoice_items.idIncoingInvoice', 'incoing_invoices.id')
            ->where('incoing_invoice_items.costCenter', $selectedCostCenter)
            ->where('incoing_invoices.codSAP', '!=', null)
            ->whereBetween('incoing_invoices.taxDate', [$fist_date, $last_date]);

        $advance_providers = AdvanceProvider::select("advance_provider.id", 'taxDate', 'status',
                (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year")))
            ->join('advance_provider_items', 'advance_provider.id', '=', 'advance_provider_items.idAdvanceProvider')
            ->where('advance_provider_items.distrRule', $selectedCostCenter)
            ->where('advance_provider.codSAP', '!=', null)
            ->whereBetween('taxDate', [$fist_date, $last_date]);

        
        if($selectedCostCenter === '1.0'){
            $purchase_requests->where('purchase_request_items.distriRule2', $selectedCostCenter2);
            $purchase_quotations->where('purchase_request_items.distriRule2', $selectedCostCenter2);
            $purchase_orders->where('purchase_order_items.costCenter2', $selectedCostCenter2);
            $incoing_invoices->where('incoing_invoice_items.costCenter2', $selectedCostCenter2);
            $advance_providers->where('advance_provider_items.distrRule2', $selectedCostCenter2);
        }

        $purchase_requests = $purchase_requests->get();
        $purchase_quotations = $purchase_quotations->get();
        $purchase_orders = $purchase_orders->get();
        $incoing_invoices = $incoing_invoices->get();
        $advance_providers = $advance_providers->get();
        
        return view("purchase::dashboard", compact('purchase_requests', 'purchase_quotations', 'purchase_orders', 'incoing_invoices', 'advance_providers', 'selectedCostCenter', 'selectedCostCenter2', 'fist_date', 'last_date'), $this->options());
    }

    public function filter(Request $request){
        $user = User::find(Auth::user()->id);
        $selectedCostCenter = $request->get('costCenter');
        $selectedCostCenter2 = $request->get('costCenter2');
        $fist_date = $request->data_fist ?? Carbon::now()->subYear();
        $last_date = $request->data_last ?? Carbon::now();
        $purchase_requests = PurchaseRequest::select('purchase_requests.codStatus',
        (DB::raw("MONTH(purchase_requests.requriedDate) as month, YEAR(purchase_requests.requriedDate) as year")))
            ->join('purchase_request_items', 'purchase_request_items.idPurchaseRequest', 'purchase_requests.id')
            ->where('purchase_request_items.distrRule', $selectedCostCenter)
            ->whereDate('purchase_requests.created_at', '>=', $fist_date)
            ->whereDate('purchase_requests.created_at', '<=', $last_date);

        $purchase_quotations = PurchaseQuotation::select('purchase_quotation.created_at', 'purchase_quotation.data_i as data_i', 'purchase_quotation.status',
                (DB::raw("MONTH(purchase_quotation.data_i) as month, YEAR(purchase_quotation.data_i) as year")))
            ->join('purchase_quotation_items', 'purchase_quotation_items.idPurchaseQuotation', 'purchase_quotation.id')
            ->join('purchase_request_items', 'purchase_request_items.id', '=', 'purchase_quotation_items.idItemPurchaseRequest')
            ->where('purchase_request_items.distrRule', $selectedCostCenter)
            ->whereDate('purchase_quotation.data_i', '>=', $fist_date)
            ->whereDate('purchase_quotation.data_i', '<=', $last_date);

        $purchase_orders = PurchaseOrder::select('taxDate', 'docTotal', 'purchase_orders.status', 'purchase_orders.created_at',
                (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year")))
            ->leftJoin('purchase_order_items', 'purchase_order_items.idPurchaseOrders', 'purchase_orders.id')
            ->where('purchase_order_items.costCenter', $selectedCostCenter)
            ->where('purchase_orders.codSAP', '!=', null)
            ->whereDate('purchase_orders.TaxDate', '>=', $fist_date)
            ->whereDate('purchase_orders.TaxDate', '<=', $last_date);

        $incoing_invoices = IncoingInvoice::select('incoing_invoices.docTotal', 'incoing_invoices.status', 'incoing_invoices.created_at',
                (DB::raw("MONTH(incoing_invoices.taxDate) as month, YEAR(incoing_invoices.taxDate) as year")))
            ->join('incoing_invoice_items', 'incoing_invoice_items.idIncoingInvoice', 'incoing_invoices.id')
            ->where('incoing_invoice_items.costCenter', $selectedCostCenter)
            ->where('incoing_invoices.codSAP', '!=', null)
            ->whereDate('incoing_invoices.taxDate', '>=', $fist_date)
            ->whereDate('incoing_invoices.taxDate', '<=', $last_date);

        $advance_providers = AdvanceProvider::select("advance_provider.id", 'taxDate', 'status',
                (DB::raw("MONTH(taxDate) as month, YEAR(taxDate) as year")))
            ->join('advance_provider_items', 'advance_provider.id', '=', 'advance_provider_items.idAdvanceProvider')
            ->where('advance_provider_items.distrRule', $selectedCostCenter)
            ->where('advance_provider.codSAP', '!=', null)
            ->whereDate('advance_provider.taxDate', '>=', $fist_date)
            ->whereDate('advance_provider.taxDate', '<=', $last_date);

        if($selectedCostCenter == '1.0'){
            $purchase_requests->where('purchase_request_items.distriRule2', $selectedCostCenter2);
            $purchase_quotations->where('purchase_request_items.distriRule2', $selectedCostCenter2);
            $purchase_orders->where('purchase_order_items.costCenter2', $selectedCostCenter2);
            $incoing_invoices->where('incoing_invoice_items.costCenter2', $selectedCostCenter2);
            $advance_providers->where('advance_provider_items.distrRule2', $selectedCostCenter2);
        }

        $purchase_requests = $purchase_requests->groupBy('purchase_requests.codStatus', 'requriedDate')->get();
        $purchase_quotations = $purchase_quotations->groupBy('purchase_quotation.created_at',
            'purchase_quotation.id', 'purchase_quotation.data_i', 'purchase_quotation.status')->get();
        $purchase_orders = $purchase_orders->groupBy('codSAP', 'taxDate', 'docTotal', 'purchase_orders.status', 'purchase_orders.created_at')->get();
        $incoing_invoices = $incoing_invoices->groupBy('incoing_invoices.id',
            'incoing_invoices.docTotal', 'incoing_invoices.status', 'incoing_invoices.created_at', 'taxDate')->get();
        $advance_providers = $advance_providers->groupBy("advance_provider.id", 'taxDate', 'status')->get();
        
        return view("purchase::dashboard", compact('purchase_requests', 'purchase_quotations', 'purchase_orders', 'incoing_invoices', 'advance_providers', 'selectedCostCenter', 'selectedCostCenter2', 'fist_date', 'last_date'), $this->options());
    }

    private function options(){
        $sap = new Company();
        $user = User::find(Auth::user()->id);
        $user_CostCenters = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode')->pluck('costCenterCode');
        $user_CostCenters2 = CostCenter::where('user_id', $user->id)->whereNotNull('costCenterCode2')->pluck('costCenterCode2');
        
        $costCenters = $sap->getDb()
                        ->table('OPRC')
                        ->where('Active', '=', 'Y')
                        ->where('DimCode', 1)
                        ->where('VALIDTO', NULL)
                        ->whereIn('PrcCode', $user_CostCenters)
                        ->get(['PrcCode as value', 'PrcName as name']);

        $costCenters2 = $sap->getDb()
                        ->table('OPRC')
                        ->where('Active', '=', 'Y')
                        ->where('DimCode', 2)
                        ->where('VALIDTO', NULL)
                        ->whereIn('PrcCode', $user_CostCenters2)
                        ->get(['PrcCode as value', 'PrcName as name']);
        return compact('costCenters', 'costCenters2');
    }
}
