<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Auth::routes();
Route::view('/changelogs', 'layouts.changelog')->middleware('auth');

Route::get('home', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@index')->name('initial');
Route::get('get-notifications', 'Auth\UserController@getNotifications')->name('getNotifications');
Route::get('get/dueDate/{date?}/{id?}', 'HomeController@getDueDate')->name('home/get/dueDate')->middleware('auth');
Route::get('getPNJ/{id?}', 'HomeController@requireTablePNJ')->middleware('auth');
//Route::get('pdf','ReportsController@gerarRelatorio')->name('pdf')->middleware('auth');
Route::get('getExpenses/{id?}', 'HomeController@requireExpenses')->middleware('auth');
Route::get('getProductsSAP/{id?}', 'HomeController@requireTable')->name('requireTable')->middleware('auth');
Route::get('getProductsProvider/{id?}', 'HomeController@getLastProvider')->middleware('auth');
Route::get('getPNF/{id?}', 'HomeController@requireTablePNF')->middleware('auth');
Route::get('getPNJ/{id?}', 'HomeController@requireTablePNJ')->middleware('auth');
Route::get('getPN/{id?}', 'HomeController@requireTablePN')->middleware('auth');//get all pn
Route::get('getNamePN/{id?}', 'HomeController@requirePN')->middleware('auth');
Route::get('getTableUsers/{id?}', 'HomeController@getTablesUsers')->middleware('auth');
Route::get('getStatusPO/{id?}', 'HomeController@getStatusPO')->middleware('auth');
Route::get('journal-searchTableItem/{id?}', 'HomeController@searchTableItem')->middleware('auth');
Route::any('/getCashFlowJE/{id?}', 'HomeController@getCashFlow')->name('/getCashFlowJE/')->middleware('auth');//Lista os Lançamentos do parceiro em contas a receber
Route::post('register-frontend-error', 'HomeController@registerFrontendError')->name('registerFrontendError')->middleware('auth');

//Route::get('journal-searchItem/{id?}', 'JEController@getHeadModal')->middleware('auth');
//Route::get('journal-getBody/{id?}', 'JEController@getTableModal')->middleware('auth');
Route::get('error/404', 'HomeController@error404')->middleware('auth');


Route::get('getProductsSAP/{id?}', 'HomeController@requireTable')->name('requireTable')->middleware('auth');
Route::get('getProductsFromWhs/{id}', 'HomeController@getProductsFromWhs')->middleware('auth');
Route::get('grupo/deposito/index', 'GrupoWHSController@index')->name('grupo.deposito.index')->middleware('auth');
Route::post('grupo/deposito/save', 'GrupoWHSController@store')->name('grupo.deposito.store')->middleware('auth');
Route::get('grupo/deposito/edit/{id?}', 'GrupoWHSController@edit')->name('grupo.deposito.edit')->middleware('auth');
Route::get('grupo/deposito/delete/{id?}', 'GrupoWHSController@delete')->name('grupo.deposito.delete')->middleware('auth');

Route::group(['prefix' => 'ponto-venda', 'middleware' => 'auth'], function () {
    Route::get('cadastro', 'PontoVendaController@cadastro')->name('pv.cadastro');
    Route::post('cadastro', 'PontoVendaController@cadastroPost')->name('pv.cadastroPost');
    Route::get('editar/{id}', 'PontoVendaController@editar')->name('pv.editar');
    Route::get('listar', 'PontoVendaController@listar')->name('pv.listar');
    Route::post('add-forma-pagamento', 'PontoVendaController@adicionarFormaPag')->name('pv.addFormaPag');
    Route::get('excluir-forma-pagamento/{id}', 'PontoVendaController@excluirFormaPagamento')->name('pv.excluirFormaPag');
});

Route::group(['prefix' => 'erros', 'middleware' => 'auth'], function () {
    Route::get('listar', 'ErrosController@listar')->name('erros.listar');
    Route::get('ver/{id}', 'ErrosController@ver')->name('erros.ver');
    Route::post('toExcel', 'ErrosController@toExcel')->name('erros.toExcel');
    Route::get('contingencia', 'ErrosController@contingencia')->name('erros.contingencia');
    Route::get('force-sync', 'ErrosController@forceSync')->name('erros.force-sync');
    Route::get('gerar-pedido-venda/{id}', 'ErrosController@gerarPedidoVenda')->name('erros.gerar-pedido-venda');
    Route::get('estoque-nfce', 'ErrosController@estoqueNFCe')->name('erros.estoque-nfce');
    Route::get('estoque-op', 'ErrosController@estoqueOP')->name('erros.estoque-op');
    Route::get('estoque-op/to-excel', 'ErrosController@estoqueOPToExcel')->name('erros.estoque-op.to-excel');
});
Route::group(['prefix' => 'alertas', 'middleware' => 'auth'], function () {
    Route::get('listar/{type?}', 'AlertasController@listar')->name('alertas.listar');
    Route::get('abrir/{id}', 'AlertasController@abrir')->name('alertas.abrir');

});

Route::group(['prefix' => 'porcionamento', 'middleware' => 'auth'], function () {
    Route::get('pesquisar', 'PorcionamentoController@pesquisar')->name('porcionamento.pesquisar');
    Route::post('get-notas', 'PorcionamentoController@getNotasFiscais')->name('porcionamento.getNotas');
    Route::post('get-itens', 'PorcionamentoController@getItensNotaFiscal')->name('porcionamento.getItensNF');
    Route::get('get-item', 'PorcionamentoController@getItem')->name('porcionamento.getItem');
    Route::post('salvar', 'PorcionamentoController@salvar')->name('porcionamento.salvar');
    Route::get('criar/{docEntry?}/{lineNum?}', 'PorcionamentoController@criar')->name('porcionamento.criar');
    Route::get('ver/{id?}', 'PorcionamentoController@ver')->name('porcionamento.ver');
    Route::get('excluir/{id}', 'PorcionamentoController@excluir')->name('porcionamento.excluir');
    Route::get('salvar-sap/{id}', 'PorcionamentoController@salvarSap')->name('porcionamento.salvarSap');
    Route::get('getFornecedor', 'PorcionamentoController@getFornecedor')->name('porcionamento.getFornecedor');
    Route::get('getItens', 'PorcionamentoController@getItens')->name('porcionamento.getItens');
    Route::get('listar', 'PorcionamentoController@listar')->name('porcionamento.listar');

    Route::get('autorizar/{porcionamento}', 'PorcionamentoController@autorizar')->name('porcionamento.autorizar')
        ->middleware('checkPermission:Porcionamento.autorizar');

    Route::get('porcentagem-perda', 'PorcionamentoController@porcentagemPerda')->name('porcionamento.porcentagem-perda')
        ->middleware('checkPermission:Porcionamento.porcentagemPerda');
    Route::post('porcentagem-perda', 'PorcionamentoController@porcentagemPerdaPost')->name('porcionamento.porcentagem-perda-post')
        ->middleware('checkPermission:Porcionamento.porcentagemPerda');
    Route::get('porcentagens-perda-listar', 'PorcionamentoController@porcentagemPerdaListar')->name('porcionamento.porcentagemPerdaListar');
    Route::get('porcentagens-perda-excluir/{codigo}', 'PorcionamentoController@porcentagemPerdaExcluir')->name('porcionamento.porcentagemPerdaExcluir')
        ->middleware('checkPermission:Porcionamento.porcentagemPerda');
    Route::get('item-search', 'PorcionamentoController@buscaItem')->name('porcionamento.buscaItem');

    Route::get('porcionamento-justificativas', 'PorcionamentoController@justificativasListar')
        ->name('porcionamento.justificativas')
        ->middleware('checkPermission:porcionamento');
    Route::get('porcionamento-justificativa-excluir/{justificativa}', 'PorcionamentoController@justificativaExcluir')
        ->name('porcionamento.justificativa.excluir')
        ->middleware('checkPermission:porcionamento');
    Route::post('porcionamento-justificativa-adicionar', 'PorcionamentoController@justificativaAdicionar')
        ->name('porcionamento.justificativas.adicionar')
        ->middleware('checkPermission:porcionamento');

    Route::get('listar-alta-perda', 'PorcionamentoController@listarAltaPerda')->name('porcionamento.listar-alta-perda');
    Route::get('editar-perda/{codigo?}', 'PorcionamentoController@editarPerda')->name('porcionamento.listar-editar');



});

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

//Mudar senha
Route::group(["middleware" => "auth"], function () {
    Route::get('mudar-senha', 'Auth\ContaController@mudarSenha')->name('mudarSenha');
    Route::post('mudar-senha', 'Auth\ContaController@mudarSenhaPost')->name("mudarSenhaPost");
});


// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::group(["prefix" => "usuarios", 'middleware' => 'auth'], function () {
    Route::get("listar", "Auth\\UserController@index")->name("usuarios.listar");
    Route::post("filtrar", "Auth\\UserController@filtrar")->name("usuarios.filtrar");
    Route::get("editar/{id}", "Auth\\UserController@editar")->name("usuarios.editar");
    Route::post("salvar/{id}", "Auth\\UserController@salvar")->name("usuarios.salvar");
    Route::get("getUsers", "Auth\\RegisterController@getUsers")->name("usuarios.get");
});

Route::group(["prefix" => "tomticket", 'middleware' => 'auth'], function () {
    Route::get("geraracessorapido", "HomeController@gerarAcessoRapido")->name("tomticket.geraracessorapido");
});

Route::group(['prefix' => 'user/groups'], function () {
    Route::get('/', 'Auth\GroupController@index')->name('user.groups.index');
    Route::get('create', 'Auth\GroupController@create')->name('user.groups.create');
    Route::post('store', 'Auth\GroupController@store')->name('user.groups.store');
    Route::get('edit/{id}', 'Auth\GroupController@edit')->name('user.groups.edit');
    Route::post('update/{id}', 'Auth\GroupController@update')->name('user.groups.update');
    Route::get('destroy/{id}', 'Auth\GroupController@destroy')->name('user.groups.destroy');
});

Route::group(['as' => 'report.', 'prefix' => 'report'], function () {
    Route::get('footer', 'ReportController@footer')->name('footer');
});
