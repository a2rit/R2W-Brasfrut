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

Route::group(['prefix' => 'administration', 'middleware' => 'auth'], function () {
    /*Route::get('/', function () {
        dd('This is the Administration module index page. Build something great!');
    });*/

     /* Cadastrar Usuario*/
    Route::get('help','AdministrationController@help')->name('administration.user.registration.help');
    Route::get('anyData','AdministrationController@anyData')->name('administration.anyData');
    Route::get('user/registration/index','AdministrationController@index')->name('administration.user.registration.index');
    Route::get('user/registration/create', 'AdministrationController@create')->name('administration.user.registration.create');
    Route::post('user/registration/create', 'AdministrationController@save')->name('administration.user.registration.save');
    Route::get('user/registration/search', 'AdministrationController@search')->name('administration.user.registration.search');
    Route::get('user/registration/edit/{id?}', 'AdministrationController@edit')->name('administration.user.registration.edit');
    Route::get('user/registration/relatory', 'AdministrationController@relatory')->name('administration.user.registration.relatory');
    Route::any('user/registration/relatory/data', 'AdministrationController@relatoryData')->name('administration.user.registration.relatory.data');
    Route::any('user/registration/approver', 'ApproverController@create')->name('administration.user.registration.approver');
    Route::any('user/registration/approver/index', 'ApproverController@index')->name('administration.user.registration.approver.index');
    Route::any('get/users', 'ApproverController@getUsers')->name('administration.user.get');
    Route::post('user/approver/save', 'ApproverController@store')->name('administration.user.approver.save');
    Route::get('user/approver/edit/{id?}', 'ApproverController@edit')->name('administration.user.approver.edit');
    Route::get('user/approver/remove/{id?}', 'ApproverController@remove')->name('administration.user.approver.remove');
    
});
