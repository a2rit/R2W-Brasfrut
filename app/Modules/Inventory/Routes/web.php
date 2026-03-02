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

Route::group(['prefix' => 'inventory', 'middleware' => 'auth'], function () {

    //request
    Route::get('request', 'RequestsController@index')->name('inventory.request.index');
    Route::get('request/create', 'RequestsController@create')->name('inventory.request.create');
    Route::post('save', 'RequestsController@save')->name('inventory.request.save');
    Route::get('request/list', 'RequestsController@anyData')->name('inventory.request.list');
    Route::get('request/list/whs', 'RequestsController@anyDataWhs')->name('inventory.request.list.whs');
    Route::any('get/acc/from/whs/{whs?}', 'RequestsController@getAccFromWhs')->name('inventory.request.acc.whs');
    Route::get('request/search', 'RequestsController@index')->name('inventory.request.search');
    Route::get('request/searching/{id}', 'RequestsController@searching')->name('inventory.request.searching');
    Route::get('any-data/{id?}', 'RequestsController@anyDataRequest')->name('inventory.anyData');
    Route::post('request/connectar', 'RequestsController@connectar')->name('inventory.request.connectar');
    Route::post('request/store', 'RequestsController@store')->name('inventory.request.store');
    Route::get('request/reject/{id?}', 'RequestsController@rejectRequest')->name('inventory.request.reject');
    Route::get('request/print/{id?}', 'RequestsController@print')->name('inventory.request.print');
    Route::get('request/filter', 'RequestsController@filter')->name('inventory.request.filter');
    Route::get('request/force/{id?}', 'RequestsController@forceExit')->name('inventory.request.force.exit');
    Route::get('request/report', 'RequestsController@report')->name('inventory.request.report');
    Route::post('request/report/gerar', 'RequestsController@reportGenerate')->name('inventory.request.gerar');
    Route::get('request/cancel/{id}', 'RequestsController@cancel')->name('inventory.request.cancel');
   
    //output$
    Route::get('output/index', 'OutputController@index')->name('inventory.output.index');
    Route::post('output/save', 'OutputController@save')->name('inventory.output.save');
    Route::get('output/search', 'OutputController@index')->name('inventory.output.search');
    Route::post('output/filter', 'OutputController@filter')->name('inventory.output.filter');
    Route::get('output/edit/{id?}', 'OutputController@edit')->name('inventory.output.edit');
    Route::post('output/store', 'OutputController@store')->name('inventory.output.store');
    Route::post('output/check', 'OutputController@check')->name('inventory.output.check');
    Route::get('output/create', 'OutputController@create')->name('inventory.output.create');
    Route::get('output/reports', 'OutputController@f')->name('inventory.output.reports');
    Route::get('output/any-data', 'OutputController@anyData')->name('inventory.output.anyData');
    Route::get('output/searchData/{code?}', 'OutputController@searchData')->name('inventory.output.searchData');
    Route::get('output/print/{code?}', 'OutputController@print')->name('inventory.output.print');
    Route::get('output/remove/upload/{id?}/{idRef?}','OutputController@removeUpload')->name('inventory.output.remove.upload');
    Route::get('output/report', 'OutputController@report')->name('inventory.output.report');
    Route::post('output/gerar', 'OutputController@reportGenerate')->name('inventory.output.gerar');

    //input
    Route::get('input/index', 'InputController@index')->name('inventory.input.index');
    Route::post('input/save', 'InputController@save')->name('inventory.input.save');
    Route::get('input/filter', 'InputController@filter')->name('inventory.input.filter');
    Route::get('input/edit/{id?}', 'InputController@edit')->name('inventory.input.edit');
    Route::post('input/check', 'InputController@check')->name('inventory.input.check');
    Route::post('input/store', 'InputController@store')->name('inventory.input.store');
    Route::get('input/create', 'InputController@create')->name('inventory.input.create');
    Route::get('input/search', 'InputController@index')->name('inventory.input.search');
    Route::get('input/any-data', 'InputController@anyData')->name('inventory.input/anyData');
    Route::get('input/searchData/{code?}', 'InputController@searchData')->name('input/searchData');
    Route::get('input/print/{code?}', 'InputController@print')->name('inventory.input.print');
    Route::get('input/remove/upload/{id?}/{idRef?}','InputController@removeUpload')->name('inventory.input.remove.upload');
    Route::post('input/update/upload', 'InputController@updateUploads')->name('inventory.input.updateUploads');
    #Rotas de relatórios
    Route::get('input/report', 'InputController@report')->name('inventory.input.report');
    Route::post('input/gerar', 'InputController@gerarReport')->name('inventory.input.gerar');

    //Pedido de transferencia de estoque
    Route::get('transferTaking/index', 'TransferTakingController@index')->name('inventory.transferTaking.index');
    Route::get('transferTaking/filter', 'TransferTakingController@filter')->name('inventory.transferTaking.filter');
    Route::post('transferTaking/save', 'TransferTakingController@save')->name('inventory.transferTaking.save');
    Route::get('transferTaking/cancel/{id?}', 'TransferTakingController@cancel')->name('inventory.transferTaking.cancel');
    Route::post('transferTaking/check', 'TransferTakingController@check')->name('inventory.transferTaking.check');
    Route::get('transferTaking/create', 'TransferTakingController@create')->name('inventory.transferTaking.create');
    Route::get('transferTaking/search', 'TransferTakingController@index')->name('inventory.transferTaking.search');
    Route::get('transferTaking/edit/{id?}', 'TransferTakingController@edit')->name('inventory.transferTaking.edit');
    Route::post('transferTaking/store', 'TransferTakingController@store')->name('inventory.transferTaking.store');
    Route::post('transferTaking/go/transfer', 'TransferTakingController@goTransfer')->name('inventory.transferTaking.go.transfer');
    Route::get('transferTaking/any-data', 'TransferTakingController@anyData')->name('inventory.transferTaking.anyData');
    Route::get('transferTaking/searchData/{code?}', 'TransferTakingController@searchData')->name('inventory.transferTaking.searchData');
    Route::get('transferTaking/print/{code?}', 'TransferTakingController@print')->name('inventory.transferTaking.print');
    Route::get('transferTaking/remove/upload/{id?}/{idRef?}','TransferTakingController@removeUpload')->name('inventory.transferTaking.remove.upload');
    Route::post('transferTaking/update/upload', 'TransferTakingController@updateUploads')->name('inventory.transferTaking.updateUploads');
    Route::get('transferTaking/report', 'TransferTakingController@report')->name('inventory.transferTaking.report');
    Route::post('transferTaking/gerar', 'TransferTakingController@reportGenerate')->name('inventory.transferTaking.gerar');



    //transferencia de estoque
    Route::get('transfer/index', 'TransferController@index')->name('inventory.transfer.index');
    Route::get('transfer/filter', 'TransferController@filter')->name('inventory.transfer.filter');
    Route::post('transfer/save', 'TransferController@save')->name('inventory.transfer.save');
    Route::post('transfer/check', 'TransferController@check')->name('inventory.transfer.check');
    Route::get('transfer/create', 'TransferController@create')->name('inventory.transfer.create');
    Route::get('transfer/search', 'TransferController@index')->name('inventory.transfer.search');
    Route::get('transfer/edit/{id?}', 'TransferController@edit')->name('inventory.transfer.edit');
    Route::post('transfer/store', 'TransferController@store')->name('inventory.transfer.store');
    Route::get('transfer/any-data', 'TransferController@anyData')->name('inventory.transfer.anyData');
    Route::get('transfer/searchData/{code?}', 'TransferController@searchData')->name('inventory.transfer.searchData');
    Route::get('transfer/print/{code?}', 'TransferController@print')->name('inventory.transfer.print');
    Route::get('transfer/remove/upload/{id?}/{idRef?}','TransferController@removeUpload')->name('inventory.transfer.remove.upload');
    Route::get('transfer/report', 'TransferController@report')->name('inventory.transfer.report');
    Route::post('transfer/gerar', 'TransferController@reportGenerate')->name('inventory.transfer.gerar');
    Route::get('transfer/cancel/{id?}', 'TransferController@cancel')->name('inventory.transfer.cancel');


    //Emprestimo de estoque
    Route::get('stockLoan/index', 'StockLoanController@index')->name('inventory.stockloan.index');
    Route::get('stockLoan/filter', 'StockLoanController@filter')->name('inventory.stockloan.filter');
    Route::post('stockLoan/save', 'StockLoanController@save')->name('inventory.stockloan.save');
    Route::post('stockLoan/devolution', 'StockLoanController@devolution')->name('inventory.stockloan.devolution');
    Route::post('stockLoan/check', 'StockLoanController@check')->name('inventory.stockloan.check');
    Route::get('stockLoan/create', 'StockLoanController@create')->name('inventory.stockloan.create');
    Route::get('stockLoan/search', 'StockLoanController@index')->name('inventory.stockloan.search');
    Route::get('stockLoan/edit/{id?}', 'StockLoanController@edit')->name('inventory.stockloan.edit');
    Route::post('stockLoan/store', 'StockLoanController@store')->name('inventory.stockloan.store');
    Route::get('stockLoan/any-data', 'StockLoanController@anyData')->name('inventory.stockloan.anyData');
    Route::get('stockLoan/searchData/{code?}', 'StockLoanController@searchData')->name('inventory.stockloan.searchData');
    Route::get('stockLoan/print/{code?}', 'StockLoanController@print')->name('inventory.stockloan.print');
    Route::get('stockLoan/remove/upload/{id?}/{idRef?}','StockLoanController@removeUpload')->name('inventory.stockLoan.remove.upload');
    Route::get('stockLoan/report', 'StockLoanController@report')->name('inventory.stockloan.reports');
    Route::post('stockLoan/report/gerar', 'StockLoanController@reportGenerate')->name('inventory.stockloan.reports.gerar');
    Route::post('stockLoan/update/upload', 'StockLoanController@updateUploads')->name('inventory.stockloan.updateUploads');

    Route::get("item/index",'ItemsController@index')->name("inventory.items.index");
    Route::get('item/search', 'ItemsController@index')->name('inventory.items.search');
    Route::get("item/create", "ItemsController@create")->name("inventory.items.create");
    Route::get("item/edit/{code?}", "ItemsController@edit")->name("inventory.items.edit");
    Route::post("item/store", "ItemsController@store")->name("inventory.items.store");
    Route::get("item/ncm-search", "ItemsController@ncmSearch")->name("inventory.items.ncmSearch");
    Route::get("item/cest-search", "ItemsController@cestSearch")->name("inventory.items.cestSearch");
    Route::get("item/items-search", "ItemsController@itemsSearch")->name("inventory.items.itemsSearch");
    Route::get("item/suppliers-search", "ItemsController@suppliersSearch")->name("inventory.items.suppliersSearch");
    Route::get("item/relatory", "ItemsController@relatory")->name("inventory.items.relatory");
    Route::post("item/relatory/filter", "ItemsController@relatoryFilter")->name("inventory.items.relatory.filter");
    Route::get("item/relatory/filter/all", "ItemsController@relatoryAll")->name("inventory.items.relatory.filter.all");
    Route::post("item/export/filter/excel", "ItemsController@exportFilterExcel")->name("inventory.items.export.filter.excel");
    Route::get("item/filter", "ItemsController@filter")->name("inventory.items.filter");
    Route::get('item/any-data', 'ItemsController@anyData')->name('inventory.items.anyData');
    Route::get('item/remove/{id?}', 'ItemsController@remove')->name('inventory.items.remove');
    Route::get('item/get/whs/{id?}', 'ItemsController@getWHS')->name('inventory.get.whs');
    Route::get('item/get/whs/emprestimo/{id?}', 'ItemsController@getWHSEmprestimo')->name('inventory.get.whs.emprestimo');
    Route::get("item/movement", "ItemsController@printMovement")->name("inventory.items.movement");

    // Rotas relatórios
    Route::get('report/index', 'StockReportController@index')->name('inventory.report.index');
    Route::get('report/gerar', 'StockReportController@reportGenerate')->name('inventory.report.gerar');
    Route::get('report/gerarPerdas', 'StockReportController@relatorioPerdas')->name('inventory.report.relatorioPerdas');


});
