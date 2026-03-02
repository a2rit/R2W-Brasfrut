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

Route::group(['prefix' => 'consumo-interno'], function () {
    Route::get("/", "ConsumoInternoController@index")->name("consumo-interno.index");
    Route::get("listar", "ConsumoInternoController@listar")->name("consumo-interno.listar");
    Route::get("lancamento/{pvId}", "ConsumoInternoController@lancamento")->name("consumo-interno.lancamento");

    Route::get("getItem", "ConsumoInternoController@getItem")->name("consumo-interno.getItem");
    Route::get("getItemByCode", "ConsumoInternoController@getItemByCode")->name("consumo-interno.getItemByCode");
    Route::post("addItem", "ConsumoInternoController@addItemPost")->name("consumo-interno.addItem");
    Route::get("excluir/{id}", "ConsumoInternoController@delete")->name("consumo-interno.excluir");


});
