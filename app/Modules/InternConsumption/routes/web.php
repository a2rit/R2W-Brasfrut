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

Route::group(
    [
        'prefix' => 'intern-consumption',
        'middleware' => 'checkPermission:InternConsumption.create',
        'as' => 'intern-consumption.',
    ],
    function () {
        Route::get('/', 'EntryController@index')->name('index');
        Route::get('index-data', 'EntryController@indexData')->name('indexData');
        Route::get('create/{documentType?}', 'EntryController@create')->name('create');
        Route::get('show/{ic}', 'EntryController@show')->name('show');
        Route::get('edit/{internConsumption}', 'EntryController@edit')->name('edit');
        Route::get('print/{ic}', 'EntryController@print')->name('print');
        Route::get('cancel/{ic}', 'EntryController@cancel')->name('cancel');
        Route::get('search-item', 'EntryController@searchItem')->name('searchItem');
        Route::get('search-requester', 'EntryController@searchRequester')->name('searchRequester');
        Route::post('store', 'EntryController@store')->name('store');
        Route::post('store-comment/{id}', 'EntryController@storeComment')->name('storeComment');
        Route::post('update/{id}', 'EntryController@update')->name('update')
            ->middleware('checkPermission:InternConsumption.authorize');

        Route::group(['as' => 'report.', 'prefix' => 'report'], function() {
            Route::get('/', 'ReportController@index')->name('index');
            Route::post('/', 'ReportController@report');
        });
    });
