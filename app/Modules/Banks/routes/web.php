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

Route::group(['prefix' => 'banks', 'middleware' => 'auth'], function () {
    /*Route::get('/', function () {
        dd('This is the Banks module index page. Build something great!');
    });*/

    //* Contas a Receber
    Route::get('bills/receive/index','BillsAReceiveController@index')->name('banks.bills.receive.index');
    Route::get('bills/receive/create','BillsAReceiveController@create')->name('banks.bills.receive.create');
    Route::get('bills/receive/invoices/{id?}','BillsAReceiveController@getAllAccounts')->name('banks.bills.receive.invoices');
    Route::post('bills/receive/save','BillsAReceiveController@save')->name('banks.bills.receive.save');
    Route::get("bills/receive/search",'BillsAReceiveController@index')->name('banks.bills.receive.search');
    Route::get("bills/receive/read/{id?}",'BillsAReceiveController@read')->name('banks.bills.receive.read');
    Route::get("bills/receive/cancel/{id?}",'BillsAReceiveController@cancel')->name('banks.bills.receive.cancel');
    Route::post("bills/receive/filter",'BillsAReceiveController@filter')->name('banks.bills.receive.filter');
    Route::get("bills/receive/relatory",'BillsAReceiveController@indexRelatory')->name('banks.bills.receive.relatory');
    Route::post("bills/receive/get/relatory/",'BillsAReceiveController@relatory')->name('banks.bills.receive.get.relatory');

    //* Contas a Receber por conta
    Route::get('bills/receive/account/index','BillsReceiveAccountController@index')->name('banks.bills.receive.account.index');
    Route::get('bills/receive/account/create','BillsReceiveAccountController@create')->name('banks.bills.receive.account.create');
    Route::post('bills/receive/account/save', 'BillsReceiveAccountController@save')->name('banks.bills.receive.account.save');
    Route::post('bills/receive/account/filter', 'BillsReceiveAccountController@filter')->name('banks.bills.receive.account.filter');
    Route::get('bills/receive/account/read/{id?}', 'BillsReceiveAccountController@read')->name('banks.bills.receive.account.read');
    Route::get('bills/receive/account/cancel/{id?}', 'BillsReceiveAccountController@cancel')->name('banks.bills.receive.account.cancel');

    //* Contas a Pagar
    Route::get('bills/pay/index','BillsToPayController@index')->name('banks.bills.pay.index');
    Route::get('bills/pay/search','BillsToPayController@index')->name('banks.bills.pay.search');
    Route::get('bills/pay/create','BillsToPayController@create')->name('banks.bills.pay.create');
    Route::post('bills/pay/filter','BillsToPayController@filter')->name('banks.bills.pay.filter');
    Route::post('bills/pay/save','BillsToPayController@save')->name('banks.bills.pay.save');
    Route::get('bills/pay/invoices/{id?}','BillsToPayController@getAllAccounts')->name('banks.bills.pay.invoices');
    Route::get('bills/pay/read/{id?}','BillsToPayController@read')->name('banks.bills.pay.read');
    Route::get('bills/pay/cancel/{id?}','BillsToPayController@cancel')->name('banks.bills.pay.cancel');
    Route::get("bills/pay/relatory",'BillsToPayController@indexRelatory')->name('banks.bills.pay.relatory');
    Route::post("bills/pay/get/relatory/",'BillsToPayController@relatory')->name('banks.bills.pay.get.relatory');

    //* Contas a pagar por contas
    Route::get('bills/pay/account/index','BillsPayAccountController@index')->name('banks.bills.pay.account.index');
    Route::get('bills/pay/account/create','BillsPayAccountController@create')->name('banks.bills.pay.account.create');
    Route::post('bills/pay/account/save','BillsPayAccountController@save')->name('banks.bills.pay.account.save');
    Route::get('bills/pay/account/get','BillsPayAccountController@getAccount')->name('banks.bills.pay.account.get');
    Route::post('bills/pay/account/filter','BillsPayAccountController@filter')->name('banks.bills.pay.account.filter');
    Route::get('bills/pay/account/read/{id?}','BillsPayAccountController@read')->name('banks.bills.pay.account.read');
    Route::get('bills/pay/account/cancel/{id?}','BillsPayAccountController@cancel')->name('banks.bills.pay.account.cancel');


    /*auxliliares*/
    Route::get('get/all','BanksController@getBanksSAP')->name('banks.get.all');
});
