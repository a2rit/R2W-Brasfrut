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

Route::group(['prefix' => 'partners', 'middleware' => 'auth'], function () {
    Route::get('create', 'PartnersController@create')->name('partners.create');
    Route::post('store', 'PartnersController@store')->name('partners.store');
    Route::get('/', 'PartnersController@index')->name('partners.index');
    Route::get('index', 'PartnersController@index')->name('partners.index');
    Route::get('any-data', 'PartnersController@anyData')->name('partners.anyData');
    Route::get('edit/{code?}', 'PartnersController@edit')->name('partners.edit');
    Route::get('filter', 'PartnersController@filter')->name('partners.filter');
    Route::any('get/pais', 'PartnersController@getPais')->name('partners.get.pais');
    Route::any('get/contacts', 'PartnersController@getPartnersSAP')->name('partners.get.contacts');
    Route::any('get/type/of/address/{ibge?}', 'PartnersController@getTypeOfAddress')->name('partners.get.contacts.address');
    Route::any('get/all/{id?}', 'PartnerController@getCLient')->name('partners.get.all');
    Route::any('get/provider/{id?}', 'PartnerController@getProvider')->name('partners.get.provider');
    Route::any('get/partner/{cardCode?}', 'PartnersController@getPartner')->name('partners.get.partner');
    Route::get('get/contracts/{id?}', 'PartnersController@getPartnerContracts')->name('partners.get.contracts');
    Route::get('get/getContractUsageHistory', 'PartnersController@getContractUsageHistory')->name('partners.get.contracts.usage.history');
    Route::get('get/relatory', 'PartnersController@relatory')->name('partners.relatory');
    Route::get('remove/upload/{id?}/{idRef?}','PartnersController@removeUploads')->name('partners.remove.uploads');
    Route::post('update/upload', 'PartnersController@updateUploads')->name('partners.update.uploads');
    Route::post('remove-contract', 'PartnersController@removeContract')->name('partners.remove-contract');
});
