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

Route::group(['prefix' => 'journal-entry', 'middleware' => 'auth'], function () {
    /*Route::get('/', function () {
        dd('This is the JournalEntry module index page. Build something great!');
    });*/
    Route::get('edit/{id?}', 'JournalEntryController@edit')->name('journal-entry.edit');
    Route::get('create', 'JournalEntryController@create')->name('journal-entry.create');
    Route::post('save', 'JournalEntryController@save')->name('journal-entry.save');
    Route::post('store', 'JournalEntryController@store')->name('journal-entry.store');
    Route::get('canceled/{id?}', 'JournalEntryController@canceled')->name('journal-entry.canceled');
    Route::get('index', 'JournalEntryController@index')->name('journal-entry.index');
    Route::get('search', 'JournalEntryController@index')->name('journal-entry.search');
    Route::any('searchTable', 'JournalEntryController@searchTable')->name('journal-entry.searchTable');
    Route::get('searchItem', 'JournalEntryController@searchItem')->name('journal-entry.searchItem');
    Route::get('reports', 'JournalEntryController@reports')->name('journal-entry.reports');
    Route::get('help', 'JournalEntryController@help')->name('journal-entry.help');
    Route::post('reports/save', 'JournalEntryController@reportsSave')->name('journal-entry.reports/save');
    Route::post('reports/filter', 'JournalEntryController@filter')->name('journal-entry.filter');
    Route::get('getCashFlowJE/{id?}', 'JournalEntryController@getCashFlow')->name('journal-entry.getCashFlowJE');


    Route::get('dashboard', 'DashboardController@index')->name('journal-entry.dashboard.index');
    Route::get('dashboard/filter', 'DashboardController@filter')->name('journal-entry.dashboard.filter');
});
