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

Route::group(['prefix' => 'settings', 'middleware' => ['auth', 'checkPermission:admin']], function () {
    Route::get('/', 'SettingsController@index')->name('settings.index');

    //General Configs
    Route::group(['prefix' => 'general'], function () {
        Route::get('index', 'SettingsController@general')->name('settings.general.index');
        Route::post('store', 'SettingsController@store')->name('settings.boot.store');
    });
    
    Route::group(['prefix' => 'logs'], function () {
        Route::get('erros/index', 'SettingsController@getLogsErros')->name('settings.logs.errors.index');
        Route::post('erros/filter', 'SettingsController@filterLogsErros')->name('settings.logs.errors.filter');
    });

    Route::get('index', 'GeneralController@index')->name('settings.geral.index');
    Route::post('save', 'GeneralController@save')->name('settings.geral.save');

    //CashFlow
    Route::get('cash/flow/index', 'CashFlowController@index')->name('settings.cash.flow.index');
    Route::post('cash/flow/save', 'CashFlowController@save')->name('settings.cash.flow.save');
    Route::get('cash/flow/status/update/{id?}', 'CashFlowController@status')->name('settings.cash.flow.status.update');
    Route::get('cash/flow/status/update/{id?}', 'CashFlowController@status')->name('settings.cash.flow.status.update');
    Route::get('cash/flow/read/{id?}', 'CashFlowController@read')->name('settings.cash.flow.read');

    //date
    Route::get('date/index', 'DateController@index')->name('settings.date.index');
    Route::post('date/save', 'DateController@save')->name('settings.date.save');
    Route::get('date/remove/{id?}', 'DateController@remove')->name('settings.date.remove');


    //cotação da moeda atual
    Route::get('currency/quote/index', 'CurrencyController@index')->name('settings.currency.quote.index');
    Route::post('currency/quote/create', 'CurrencyController@store')->name('settings.currency.quote.create');
    Route::get('currency/read/{id?}', 'CurrencyController@read')->name('settings.currency.quote.read');
    Route::get('currency/response/{id?}', 'CurrencyController@response')->name('settings.currency.quote.response');
    
    //impostos
    Route::get('tax/index', 'TaxController@index')->name('settings.tax.index');
    Route::get('tax/create', 'TaxController@create')->name('settings.tax.create');
    Route::get('tax/create/web/mania', 'TaxController@createWebMania')->name('settings.tax.create.web.mania');
    Route::post('tax/web/mania/save', 'TaxController@saveWebMania')->name('settings.tax.web.mania.save');
    Route::get('tax/remove/web/mania/{id?}', 'TaxController@removeWebMania')->name('settings.tax.remove.web.mania');
    Route::post('tax/create', 'TaxController@store')->name('settings.tax.store');
    Route::get('tax/remove/{id?}', 'TaxController@remove')->name('settings.tax.remove');
    Route::get('tax/edit/{id?}', 'TaxController@edit')->name('settings.tax.edit');
    Route::get('tax/all', 'TaxController@getTaxAll')->name('settings.tax.all');


    Route::get('lofted/create', 'LoftedController@index')->name('settings.lofted.index');
    Route::post('lofted/save', 'LoftedController@store')->name('settings.lofted.save');
    Route::get('lofted/edit/{id?}', 'LoftedController@edit')->name('settings.lofted.edit');
    Route::get('lofted/remove/{id?}', 'LoftedController@remove')->name('settings.lofted.remove');

});
