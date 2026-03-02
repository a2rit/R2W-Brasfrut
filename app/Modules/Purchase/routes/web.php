<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'purchase', 'middleware' => 'auth'], function () {

    /*Route::get('/', function () {
        dd('This is the Purchase module index page. Build something great!');
    });*/

    Route::get('dashboard','DashboardController@index')->name('purchase.dashboard.index');
    Route::get('dashboard/filter','DashboardController@filter')->name('purchase.dashboard.filter');

    Route::post('order/save','PurchaseController@save')->name('purchase.order.save');
    Route::get('order/index','PurchaseController@index')->name('purchase.order.index');
    Route::get('order/index-data', 'PurchaseController@indexData')->name('purchase.order.indexData');
    Route::get('order/show/{id?}', 'PurchaseController@show')->name('purchase.order.show');
    Route::get('order/preview/{id?}', 'PurchaseController@preview')->name('purchase.order.preview');
    Route::get('order/create', 'PurchaseController@create')->name('purchase.order.create');
    Route::get('order/filter', 'PurchaseController@filter')->name('purchase.order.filter');
    Route::get('order/read/from/request/{id?}', 'PurchaseController@readFromRequest')->name('purchase.order.read.from.request');
    Route::get('order/read/{id?}', 'PurchaseController@read')->name('purchase.order.read');
    Route::get('order/read/code/{code?}', 'PurchaseController@readCode')->name('purchase.order.read.code');
    Route::post('order/canceled','PurchaseController@canceled')->name('purchase.order.canceled');
    Route::get('order/remove/upload/{id?}/{idRef?}','PurchaseController@removeUpload')->name('purchase.order.remove.upload');
    Route::post('order/update/upload', 'PurchaseController@updateUploads')->name('purchase.order.updateUploads');
    Route::get('order/closed/{id?}','PurchaseController@closed')->name('purchase.order.closed');
    Route::get('order/print/{id?}/{type?}','PurchaseController@print')->name('purchase.order.print');
    Route::get('order/approve/{id?}','PurchaseController@approve')->name('purchase.order.approve');
    Route::get('order/duplicate/{id?}','PurchaseController@duplicate')->name('purchase.order.duplicate');
    Route::get('order/reprove/{id?}','PurchaseController@reprove')->name('purchase.order.reprove');
    Route::any('order/copy/request','PurchaseController@copyFromRequest')->name('puchase.order.copy.from.request');
    Route::any('order/copy/quotation','PurchaseController@copyFromQuotation')->name('puchase.order.copy.from.quotation');
    Route::post('order/editing-mass', 'PurchaseController@editingMass')->name('purchase.order.editingMass');
    // Route::get('order/get-budget-info', 'PurchaseController@getBudgetInfo')->name('purchase.order.getBudgetInfo');
    #Report
    Route::get('order/report','PurchaseController@report')->name('purchase.order.report');
    Route::post('order/gerar', 'PurchaseController@gerarReport')->name('purchase.order.gerar');
    Route::get('order/getPurchaseOrders', 'PurchaseController@getPurchaseOrders')->name('purchase.order.getPurchaseOrders');
    Route::get('order/listOrdersTopNav', 'PurchaseController@listOrdersTopNav')->name('purchase.order.listOrdersTopNav');


    Route::post('read/xml','XMLController@load')->name('purchase.order.read.xml');
    Route::get('import/xml','XMLController@import')->name('purchase.order.import.xml');
    Route::get('xml/join/order/{id?}', 'XMLController@join')->name('purchase.xml.order.join');
    Route::get('xml/get/items/{id?}', 'XMLController@getItems')->name('purchase.xml.get.items');
    Route::post('xml/read/save', 'XMLController@save')->name('purchase.xml.read.save');
    Route::post('xml/join/save', 'XMLController@store')->name('purchase.xml.join.save');
    Route::get('xml/get/items/modal/{id?}', 'XMLController@getItemsXML')->name('purchase.xml.get.item.modal');
    Route::post('xml/filter', 'XMLController@filter')->name('purchase.xml.filter');


    /* Recebimentos de mercadorias 
    Route::get('receipts/goods/index','ReceiptsGoodsController@index')->name('purchase.receipts.goods.index');
    Route::get('receipts/goods/create', 'ReceiptsGoodsController@create')->name('purchase.receipts.goods.create');
    Route::post('receipts/goods/save','ReceiptsGoodsController@save')->name('purchase.receipts.goods.save');
    Route::post('receipts/goods/xml/save','XMLController@saveReceipts')->name('purchase.receipts.goods.xml.save');
    Route::get('receipts/goods/read/{id?}', 'ReceiptsGoodsController@read')->name('purchase.receipts.goods.read');
    Route::get('receipts/goods/closed/{code?}','ReceiptsGoodsController@closed')->name('purchase.receipts.goods.closed');
    Route::get('receipts/goods/canceled/{code?}','ReceiptsGoodsController@canceled')->name('purchase.receipts.goods.canceled');
    Route::get('receipts/goods/print/{code?}','ReceiptsGoodsController@print')->name('purchase.receipts.goods.print');
    Route::get('receipts/goods/filter', 'ReceiptsGoodsController@filter')->name('purchase.receipts.goods.filter');
    Route::get('receipts/goods/copy/{code?}', 'ReceiptsGoodsController@copy')->name('purchase.receipts.goods.copy');
    Route::get('receipts/goods/copy/from/nfe/{code?}', 'ReceiptsGoodsController@cFromNFE')->name('purchase.receipts.goods.copy.from.nfe');
    // de cimara
    Route::post('receipts/goods/reports', 'ReceiptsGoodsController@reports')->name('purchase.receipts.goods.reports');
    Route::post('receipts/goods/filter/reports','ReceiptsGoodsController@filterReports')->name('purchase.receipts.good.filterReports');
    Route::get('receipts/goods/relatory/{id?}','ReceiptsGoodsController@gerar')->name('purchase.receipts.good.relatory');
    */
    /* Adiantamento para Fornecedor*/
    Route::get('advance/provider/index','AdvanceProviderController@index')->name('purchase.advance.provider.index');
    Route::get('advance/provider/create','AdvanceProviderController@create')->name('purchase.advance.provider.create');
    Route::post('advance/provider/save','AdvanceProviderController@save')->name('purchase.advance.provider.save');
    Route::get('advance/provider/refund/{id?}','AdvanceProviderController@refund')->name('purchase.advance.provider.refund');
    Route::get('advance/provider/search','AdvanceProviderController@search')->name('purchase.advance.provider.search');
    Route::get('advance/provider/read/{id?}','AdvanceProviderController@read')->name('purchase.advance.provider.read');
    Route::get('advance/provider/copy/{id?}','AdvanceProviderController@copyFromPurchaseOrder')->name('purchase.advance.provider.copy.from.purchase.order');
    Route::get('advance/provider/print/{id?}','AdvanceProviderController@print')->name('purchase.advance.provider.print');
    Route::get('advance/provider/anydata/{id?}','AdvanceProviderController@anyData')->name('purchase.advance.provider.anydata');
    Route::get('advance/provider/listAdvancesTopNav', 'AdvanceProviderController@listAdvancesTopNav')->name('purchase.advance.provider.listAdvancesTopNav');
    Route::post('advance/provider/updateUploads','AdvanceProviderController@updateUploads')->name('purchase.advance.provider.updateUploads');
    
    
    /*Nota fiscal de Entrada*/
    Route::get('ap/invoice/index','InvoiceController@index')->name('purchase.ap.invoice.index');
    Route::get('ap/invoice/create','InvoiceController@create')->name('purchase.ap.invoice.create');
    Route::post('ap/invoice/save','InvoiceController@save')->name('purchase.ap.invoice.save');
    Route::get('ap/invoice/filter','InvoiceController@filter')->name('purchase.ap.invoice.filter');
    Route::get('ap/invoice/read/{id?}','InvoiceController@read')->name('purchase.ap.invoice.read');
    Route::get('ap/invoice/print/{id?}','InvoiceController@print')->name('purchase.ap.invoice.print');
    Route::get('ap/invoice/canceled/{id?}','InvoiceController@canceled')->name('purchase.ap.invoice.canceled');
    Route::get('ap/invoice/copy/{id?}','InvoiceController@copy')->name('purchase.ap.invoice.copy');
    Route::post('ap/invoice/save/copy/nfe', 'InvoiceController@saveNFE')->name('purchase.ap.invoice.save.copy.nfe');
    Route::get('ap/invoice/remove/upload/{id?}/{idRef?}','InvoiceController@removeUpload')->name('purchase.ap.invoice.remove.upload');
    Route::get('ap/invoice/report', 'InvoiceController@report')->name('purchase.ap.invoice.report');
    Route::post('ap/invoice/gerar', 'InvoiceController@gerarReport')->name('purchase.ap.invoice.gerar');
    Route::get('ap/invoice/getwithheldtax', 'InvoiceController@getWithheldTax')->name('purchase.ap.invoice.getwithheldtax');
    Route::get('ap/invoice/getProductsSAP', 'InvoiceController@getProductsSAP')->name('purchase.ap.invoice.getproductssap');
    Route::get('ap/invoice/getAdvancePayments', 'InvoiceController@getAdvancePayments')->name('purchase.ap.invoice.getadvancepayments');
    Route::get('ap/invoice/duplicate/{id?}','InvoiceController@duplicate')->name('purchase.ap.invoice.duplicate');
    Route::post('ap/invoice/upload', 'InvoiceController@updateUploads')->name('purchase.ap.invoice.updateUploads');
    Route::get('ap/invoice/anydata/{id?}', 'InvoiceController@anyData')->name('purchase.ap.invoice.anyData');
    Route::get('ap/invoice/listInvoicesTopNav', 'InvoiceController@listInvoicesTopNav')->name('purchase.ap.invoice.listInvoicesTopNav');
    Route::get('ap/invoice/approve/{id?}','InvoiceController@approve')->name('purchase.ap.invoice.approve');
    Route::any('ap/invoice/reprove/{id?}','InvoiceController@reprove')->name('purchase.ap.invoice.reprove');


    Route::get('purchase/request', 'PurchaseRequestController@index')->name('purchase.request.index');
    Route::get('purchase/request/search', 'PurchaseRequestController@index')->name('purchase.request.search');
    Route::get('purchase/request/read/{id?}', 'PurchaseRequestController@read')->name('purchase.request.read');
    Route::get('purchase/request/code/{code?}', 'PurchaseRequestController@readCode')->name('purchase.request.read.code');
    Route::get('purchase/request/any-data/{id?}', 'PurchaseRequestController@anyDataPurchaseRequest')->name('purchase.request.anyData');

    Route::get('purchase/request/any-data2/{id?}', 'PurchaseRequestController@anyDataPurchaseRequest2')->name('purchase.request.anyData2');
    
    Route::get('purchase/request/any-data3', 'PurchaseRequestController@syncRequestToSAP')->name('purchase.request.syncrequesttosap');
    Route::get('purchase/request/sync-to-sap', 'PurchaseRequestController@anyDataPurchaseRequest3')->name('purchase.request.anyData3');
    Route::get('purchase/request/listRequestsTopNav', 'PurchaseRequestController@listRequestsTopNav')->name('purchase.request.listRequestsTopNav');
    Route::get('purchase/request/create', 'PurchaseRequestController@create')->name('purchase.request.create');
    Route::post('purchase/request/save', 'PurchaseRequestController@save')->name('purchase.request.save');
    Route::post('purchase/request/from/purchase', 'PurchaseRequestController@fromPurchase')->name('purchase.request.from.purchase');
    Route::post('purchase/request/from/quotation', 'PurchaseRequestController@fromQuotation')->name('purchase.request.from.quotation');
    Route::post('purchase/request/store', 'PurchaseRequestController@store')->name('purchase.request.store');
    Route::get('purchase/request/filter', 'PurchaseRequestController@filter')->name('purchase.request.filter');
    Route::get('purchase/request/duplicate/{id}', 'PurchaseRequestController@duplicate')->name('purchase.request.duplicate');
    Route::get('purchase/request/canceled/{id?}','PurchaseRequestController@canceled')->name('purchase.request.canceled');
    Route::get('purchase/request/print/{id?}/{type?}','PurchaseRequestController@print')->name('purchase.request.print');
    Route::get('purchase/remove/upload/{id?}/{idRef?}','PurchaseRequestController@removeUpload')->name('purchase.request.remove.upload');
    Route::post('purchase/update/upload', 'PurchaseRequestController@updateUploads')->name('purchase.request.updateUploads');
    Route::get('purchase/request/force/{id}', 'PurchaseRequestController@forceToSAP')->name('purchase.request.forcetopsap');   
    Route::get('purchase/request/getPurchaseRequests', 'PurchaseRequestController@getPurchaseRequests')->name('purchase.request.getPurchaseRequests')->middleware('auth');
    Route::post('purchase/request/editing-mass', 'PurchaseRequestController@editingMass')->name('purchase.request.editingMass');

    #Relarórios
    Route::get('purchase/request/report', 'PurchaseRequestController@report')->name('purchase.request.report');
    Route::post('purchase/request/gerar', 'PurchaseRequestController@gerarReport')->name('purchase.request.gerar');

    Route::get('purchase/quotation', 'PurchaseQuotationController@index')->name('purchase.quotation.index');
    Route::get('purchase/quotation/search', 'PurchaseQuotationController@index')->name('purchase.quotation.search');
    Route::get('purchase/quotation/read/{id?}', 'PurchaseQuotationController@read')->name('purchase.quotation.read');
    Route::get('purchase/quotation/any-data/{id?}', 'PurchaseQuotationController@anyData')->name('purchase.quotation.anyData');
    Route::get('purchase/quotation/create', 'PurchaseQuotationController@create')->name('purchase.quotation.create');
    Route::post('purchase/quotation/save', 'PurchaseQuotationController@save')->name('purchase.quotation.save');
    Route::post('purchase/quotation/from/purchase', 'PurchaseQuotationController@fromPurchase')->name('purchase.quotation.from.purchase');
    Route::post('purchase/quotation/store', 'PurchaseQuotationController@store')->name('purchase.quotation.store');
    Route::get('purchase/quotation/filter', 'PurchaseQuotationController@filter')->name('purchase.quotation.filter');
    Route::post('purchase/quotation/update/upload/','PurchaseQuotationController@updateUploads')->name('purchase.quotation.updateUploads');
    Route::get('purchase/quotation/remove/upload/{id?}/{idRef?}','PurchaseQuotationController@removeUpload')->name('purchase.quotation.remove.upload');
    Route::get('purchase/quotation/canceled/{id?}','PurchaseQuotationController@canceled')->name('purchase.quotation.canceled');
    Route::any('purchase/quotation/copy/request','PurchaseQuotationController@copyFromRequest')->name('puchase.quotation.copy.from.request');
    Route::get('purchase/quotation/duplicate/{id}','PurchaseQuotationController@duplicate')->name('purchase.quotation.duplicate');
    Route::get('purchase/quotation/getitem','PurchaseQuotationController@getItem')->name('purchase.quotation.getitem');
    Route::get('purchase/quotation/print/{id}/{type}','PurchaseQuotationController@print')->name('purchase.quotation.print');
    Route::post('purchase/quotation/send-to-partner/','PurchaseQuotationController@sendToPartner')->name('purchase.quotation.sendToPartner');
    Route::get('purchase/quotation/filter-best-items/','PurchaseQuotationController@filterBestItems')->name('purchase.quotation.filterBestItems');
    Route::get('purchase/request/listQuotationsTopNav', 'PurchaseQuotationController@listQuotationsTopNav')->name('purchase.quotation.listQuotationsTopNav');


    Route::get('suggestion','PurchaseSuggestionController@index')->name('purchase.suggestion.index');
    Route::get('suggestion/filter','PurchaseSuggestionController@filter')->name('purchase.suggestion.filter');
    Route::get('suggestion/sales-history/{itemCode?}','PurchaseSuggestionController@salesHistory')->name('purchase.suggestion.sales-history');
    Route::post('suggestion/save','PurchaseSuggestionController@save')->name('purchase.suggestion.save');
});

Route::group(['prefix' => 'purchase'], function () {
    Route::get('purchase/quotation/external-access/{external_id}','PurchaseQuotationController@externalAccess')->name('purchase.quotation.externalAccess');
    Route::post('purchase/quotation/save-external-quotation', 'PurchaseQuotationController@saveExternalQuotation')->name('purchase.quotation.saveExternalQuotation');
});
