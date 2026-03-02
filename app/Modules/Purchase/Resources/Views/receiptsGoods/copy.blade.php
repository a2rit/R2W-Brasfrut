@extends('layouts.main')
@section('title', 'Recebimento de Mercadoria')
@section('content')
    <div class="wrapper wrapper-content">
        <form action="{{route('purchase.receipts.goods.save')}}" method="post" id="needs-validation"
              onkeydown='keyShowModel(event.keyCode)' enctype="multipart/form-data"
              onsubmit="waitingDialog.show('Salvando...')">
            {!! csrf_field() !!}
            <div class="row" id='form'>
                <div class="col-lg-12">
                    <div class="ibox">
                        <ol class="breadcrumb">
                            <li>
                                <a href={{route('home')}}><i class="fa fa-dashboard"></i>Inicio</a>
                            </li>
                            <li class="active">
                                <a href="{{route('purchase.receipts.goods.index')}}">
                                    <i class="fa fa-shopping-cart"></i>
                                    Recebimento de mercadoria
                                </a>
                            </li>
                            <li class="active">
                                    @if(isset($head))
                                        Visualizar
                                    @else
                                        Cadastrar
                                    @endif
                            </li>
                          </ol>
                        <div class="ibox-title input-group-btn">
                            
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-2">
                                <a href="{{route('purchase.receipts.goods.index')}}"><img
                                            src="{{asset('images/searchDocument.png')}}"
                                            class=" img-responsive -align-right right pull-right right"
                                            style="width: 24%;"></a>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-content">
                                            <h1 class="no-margins">@if(isset($head)) {{formatDate($head['taxDate'])}} @else {{DATE('d/m/Y')}}@endif</h1>
                                            <div class="stat-percent font-bold text-danger"><i class="fa fa-calendar"
                                                                                               style="font-size:36px"></i>
                                            </div>
                                            <small>Criação</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-content">
                                            <h1 class="no-margins">@if(isset($head->status) && ($head->status == 0))
                                                    Fechado @endif @if(isset($head->status) && ($head->status == 1))
                                                    Aberto @endif @if(isset($head->status) && ($head->status == 2))
                                                    Cancelado @endif @if(!isset($head->status)) Pendente @endif</h1>
                                            <div class="stat-percent font-bold text-navy"><i class="fa fa-tag"
                                                                                             style="font-size:36px"></i>
                                            </div>
                                            <small>Status</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-content">
                                            <h1 class="no-margins"
                                                id="totalHeader">@if(isset($head)) {{number_format($head['docTotal'],2,',','.')}} @else
                                                    0,00 @endif</h1>
                                            <div class=" no-margins stat-percent font-bold text-info ">
                                                <i class="fa fa-dollar" style="font-size:36px"></i>
                                            </div>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Parceiro de Negócio</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"
                                               @if(isset($head)) value="{{$head['cardName']}}"
                                               @endif id="parceiroNegocio" required name="parceiroNegocio"
                                               placeholder="Clique na lupa para pesquisar o parceiro de negócio"
                                               disabled>
                                        @if(isset($head))
                                            <input type="hidden" name="codPN" value="{{$head->cardCode}}">
                                            <input type="hidden" name="cardName" value="{{$head->cardName}}">
                                            <input type="hidden" name="identification"
                                                   value="{{$head->identification}}">
                                            <input type="hidden" name="idPurchaseOrders" value="{{$head->id}}">
                                    @endif
                                    <!-- Abertura do modal-->
                                        <div class="input-group-addon">
                                            <a href="" data-toggle="modal" data-target="#pnModal"><i
                                                        class="glyphicon glyphicon-search"></i></a>
                                        </div>
                                    </div>
                                    @if(!isset($head))
                                        <div class="modal inmodal" id="pnModal" tabindex="-1" role="dialog"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="ibox-content">
                                                    <button type="button" class="close" data-dismiss="modal"><span
                                                                aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                                                    </button>
                                                    <div>
                                                        <h4 class="modal-title">Pesquisa de Parceiros de Negócio</h4>
                                                    </div>
                                                    <div class="ibox-content">
                                                        <label>Pesquisar</label>
                                                        <input type="text" id="seachPN" class="form-control">
                                                    </div>
                                                    <span class="btn btn-success" onclick="loadTablePN()"
                                                          data-toggle="collapse">Pesquisar</span>
                                                    <div class="ibox-content">
                                                        <div class="table-responsive">
                                                            <div id="resulSearch" style="display:none;">
                                                                <table id="tableResult" class="table table-striped">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Codigo SAP</th>
                                                                        <th>Nome</th>
                                                                        <th>CNPJ/CPF</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                @if(workCashFlow())
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Fluxo de caixa</label>
                                            <select class="form-control selectpicker" @if(isset($head) ) disabled
                                                    @endif data-live-search="true" data-size="5" name="cashFlow"
                                                    required='required'>
                                                <option value=''>SELECIONE</option>
                                                @foreach($cashFlow as $key => $value)
                                                    <option value="{{$value->id}}"
                                                            @if(isset($head) && ($head->getCashFlowLabel()) && ($head->getCashFlowLabel() == $value->id)) selected @endif>{{$value->value}}</option>
                                                @endForeach
                                            </select>
                                        </div>
                                        @if(isset($head))
                                            <input type="hidden" name="cashFlow" value="{{$head->getCashFlowLabel()}}">
                                        @endif
                                    </div>
                                @endif
                                @if(workCoin())
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Moeda</label>
                                            <select class="form-control selectpicker" data-live-search="true"
                                                    @if(isset($head) ) disabled @endif name="coin">
                                                <option value="EUR"
                                                        @if(isset($head->coin) && ($head->coin == 'EUR'))  selected @endif>
                                                    Euro
                                                </option>
                                                <option value="R$"
                                                        @if(isset($head->coin) && ($head->coin == 'R$'))  selected @endif>
                                                    Real
                                                </option>
                                                <option value="$"
                                                        @if(isset($head->coin) && ($head->coin == '$'))  selected @endif>
                                                    Dólar
                                                </option>
                                            </select>
                                        </div>
                                        @if(isset($head))
                                            <input type="hidden" name="coin" value="{{$head->coin}}">
                                        @endif
                                    </div>
                                @endif
                                @if(workQuotation())
                                    <div class="col-md-2">
                                        <label>Cotação - EUR</label>
                                        <input type="text" class="form-control money" name="cotacao"
                                               @if(isset($head)) disabled @else value="{{getCoin()}}"
                                               @endif @if(isset($head->quotation)) value='{{number_format($head->quotation,2,',','.')}}' @endif>
                                    </div>
                                @endif
                                @if(isset($head))
                                    <input type="hidden" name="cotacao"
                                           value="{{number_format($head->quotation,2,',','.')}}">
                                @endif
                            </div>
                            <div class="row" style="padding-top:1%">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data do Documento</label>
                                        <input type="date" name="dataDocumento" id="dataDocumento"
                                               @if(isset($head)) disabled @endif class="form-control"
                                               @if(isset($head->taxDate)) value="{{$head->taxDate}}"
                                               @else value="{{DATE('Y-m-d')}}" @endif required>
                                    </div>
                                    @if(isset($head))
                                        <input type="hidden" name="dataDocumento" id="dataDocumento"
                                               value="{{$head->taxDate}}">
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data de Lançamento</label>
                                        <input type="date" name="dataLancamento" @if(isset($head)) disabled
                                               @endif class="form-control"
                                               @if(isset($head->docDate)) value="{{$head->docDate}}"
                                               @else value="{{DATE('Y-m-d')}}" @endif required>
                                    </div>
                                    @if(isset($head))
                                        <input type="hidden" name="dataLancamento" value="{{$head->docDate}}">
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Data de Vencimento</label>
                                        <input type="date" name="dataVencimento" id="dataVencimento"
                                               @if(isset($head)) disabled @endif class="form-control"
                                               @if(isset($head->docDueDate)) value="{{$head->docDueDate}}"
                                               @else value="{{DATE('Y-m-d')}}" @endif required>
                                    </div>
                                    @if(isset($head))
                                        <input type="hidden" name="dataVencimento" id="dataVencimento"
                                               value="{{$head->docDueDate}}">
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Condição de Pagamento</label>
                                        <select class="form-control selectpicker" id="conditionPagamentos"
                                                @if(isset($head) ) disabled @endif data-live-search="true" required
                                                data-size="10" required name="condPagamentos" onChange="getDueDate()">
                                            <option disabled selected>Selecione</option>
                                            @foreach($paymentConditions as $key => $value)
                                                <option value="{{$value['GroupNum']}}"
                                                        @if(isset($head->paymentTerms) && ($head->paymentTerms == $value['GroupNum'] )) selected @endif>{{$value['PymntGroup']}}</option>
                                            @endForeach
                                        </select>
                                    </div>
                                    @if(isset($head))
                                        <input type="hidden" name="condPagamentos" value="{{$head->paymentTerms}}">
                                    @endif
                                </div>
                                <div class="col-md-4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="tabs-container">
                        <ul class="nav nav-tabs" id="myTabs">
                            <li class="active"><a data-toggle="tab" href="#tab-1">Geral</a></li>
                            <li><a data-toggle="tab" href="#tab-2">Despesas</a></li>
                            <li><a data-toggle="tab" href="#tab-3">Impostos</a></li>
                            <li><a data-toggle="tab" href="#tab-4">Anexos</a></li>
                        </ul>
                        <fieldset>
                            <div class="tab-content">
                                <div class="tab-pane active liti" id="tab-1">
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table id="requiredTable"
                                                   class="table table-striped table-bordered table-hover dataTables-example"
                                                   style="width: 100%;">
                                                <thead>
                                                <tr>
                                                    <th style="width: 2%"><img src="{{asset('images/add.png')}}"
                                                                               data-toggle="modal"
                                                                               onclick="loadingItems();"
                                                                               data-target="#itensModal"/></th>
                                                    <th style="width: 15%">Cod. SAP</th>
                                                    <th style="width: 15%">Descrições</th>
                                                    <th style="width: 10%">Quantidade</th>
                                                    <th style="width: 15%">Preço Unitário</th>
                                                    <th>Total</th>
                                                    <th>Utilização</th>
                                                    <th>Projeto</th>
                                                    <th>Centro de Custo</th>
                                                    <th>CFOP</th>
                                                    <th>Código de Imposto</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(isset($body))
                                                    <?php $cont = 1;?>
                                                    @foreach($body as $key => $value)
                                                        <tr>
                                                            <td>{{$cont}}</td>
                                                            <td style='width: 5%'>{{$value['itemCode']}}<input
                                                                        type="hidden" value="{{$value['itemCode']}}"
                                                                        name="requiredProducts[{{$cont}}][codSAP]"></td>
                                                            <td style='width: 15%'>{{$value['itemName']}}<input
                                                                        type="hidden" value="{{$value['itemName']}}"
                                                                        name="requiredProducts[{{$cont}}][itemName]">
                                                            </td>
                                                            <td style='width: 10%'><input style='width: 100%'
                                                                                          value="{{number_format($value['quantity'],2,',','.')}}"
                                                                                          onclick='setMaskMoney()'
                                                                                          onblur="gerarTotal({{$cont}})"
                                                                                          id='qtd-{{$cont}}' type='text'
                                                                                          class='form-control money'
                                                                                          name='requiredProducts[{{$cont}}][qtd]'/>
                                                            </td>
                                                            <td style='width: 10%'><input style='width: 100%' required
                                                                                          onclick='setMaskMoney()'
                                                                                          onblur='gerarTotal({{$cont}})'
                                                                                          id='price-{{$cont}}'
                                                                                          value='{{number_format(($value['price']),2,',','.')}}'
                                                                                          type='text'
                                                                                          class='form-control money'
                                                                                          name='requiredProducts[{{$cont}}][preco]'
                                                                                          min='1'></td>
                                                            <td style='width: 10%'><input style='width: 100%'
                                                                                          value='{{number_format(($value['quantity'] * $value['price']),2,',','.')}}'
                                                                                          onclick='setMaskMoney()'
                                                                                          class='form-control money'
                                                                                          id='totalLinha-{{$cont}}'
                                                                                          onblur='gerarPrecoUnitario({{$cont}})'
                                                                                          type='text'></td>
                                                            <td style='width: 10%'>
                                                                <select class='form-control' style='width: 100%'
                                                                        id='use-{{$cont}}'
                                                                        name='requiredProducts[{{$cont}}][use]'
                                                                        required>
                                                                    <option value=''>Selecione</option>
                                                                    @foreach($use as $keys => $values)
                                                                        <option value='{{$values['code']}}'
                                                                                @if($value['codUse'] == $values['code']) selected @endif>{{$values['code']}}
                                                                            - {{compressText($values['value'],15)}}</option>
                                                                    @endForeach
                                                                </select>
                                                            </td>
                                                            <td style='width: 10%'>
                                                                <select class='form-control' style='width: 100%'
                                                                        id='projeto-{{$cont}}'
                                                                        name='requiredProducts[{{$cont}}][projeto]'
                                                                        required>
                                                                    <option value=''>Selecione</option>
                                                                    @foreach($projeto as $keys => $values)
                                                                        <option value='{{$values['value']}}'
                                                                                @if($value['codProject'] == $values['value']) selected @endif>{{$values['value']}}
                                                                            - {{compressText($values['name'],15)}}</option>
                                                                    @endForeach
                                                                </select>
                                                            </td>
                                                            <td style='width: 10%'>
                                                                <select class='form-control' style='width: 100%'
                                                                        id='role-{{$cont}}'
                                                                        name='requiredProducts[{{$cont}}][role]'
                                                                        required>
                                                                    <option value=''>Selecione</option>
                                                                    @foreach($centroCusto as $keys => $values)
                                                                        <option value='{{$values['value']}}'
                                                                                @if($value['codCost'] == $values['value']) selected @endif>{{$values['value']}}
                                                                            - {{compressText($values['name'],15)}}</option>
                                                                    @endForeach
                                                                </select>
                                                            </td>
                                                            <td style='width: 10%'>
                                                                <select class='form-control' style='width: 100%'
                                                                        id='cfop-{{$cont}}'
                                                                        name='requiredProducts[{{$cont}}][cfop]'
                                                                        required="true"/>
                                                                <option value=''>Selecione</option>
                                                                @foreach($cfop as $keys => $values)
                                                                    <option value='{{$values['value']}}'
                                                                            @if($value['codCFOP'] == $values['value']) selected @endif>{{$values['value']}}
                                                                        - {{compressText($values['name'],15)}}</option>
                                                                    @endForeach
                                                                    </select>
                                                            </td>
                                                            <td style='width: 10%'>
                                                                <select class='form-control' style='width: 100%'
                                                                        id='taxCode-{{$cont}}'
                                                                        name='requiredProducts[{{$cont}}][taxCode]'
                                                                        required="true"/>
                                                                <option value=''>Selecione</option>
                                                                @foreach($tax as $keys => $values)
                                                                    <option value="{{$values->value}}"
                                                                            @if($value['taxCode'] == $values->value) selected @endif>{{$values->value}}
                                                                        - {{compressText($values->name,20)}}</option>
                                                                    @endForeach
                                                                    </select>
                                                            </td>
                                                            <?php $cont++; ?>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                            <div class="col-md-2">
                                                <label>Utilização Principal</label>
                                                <select class='form-control' data-live-search='true' name='use'
                                                        id='useGlobal' onchange="setUseFull()">
                                                    <option value=''>Selecione</option>
                                                    @foreach($use as $keys => $values)
                                                        <option value='{{$values['code']}}'>{{$values['code']}}
                                                            - {{$values['value']}}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Projeto Principal</label>
                                                <select class='form-control' data-live-search='true' name='project'
                                                        id='projectGlobal' onchange="setProjecFull()">
                                                    <option value=''>Selecione</option>
                                                    @foreach($projeto as $keys => $values)
                                                        <option value='{{$values['value']}}'>{{$values['value']}}
                                                            - {{$values['name']}}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label>C. de Custo Principal</label>
                                                <select class='form-control' data-live-search='true' name='role'
                                                        id='roleGlobal' onchange="setRoleFull()">
                                                    <option value=''>Selecione</option>
                                                    @foreach($centroCusto as $keys => $values)
                                                        <option value='{{$values['value']}}'>{{$values['value']}}
                                                            - {{$values['name']}}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label>CFOP Principal</label>
                                                <select class="form-control" data-live-search='true' name='cfop'
                                                        id='cfopGlobal' onchange="setCFOPFull()">
                                                    <option value=''>Selecione</option>
                                                    @foreach($cfop as $keys => $values)
                                                        <option value='{{$values['value']}}'> {{$values['value']}}
                                                            - {{compressText($values['name'], 20)}}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label>Código de Imposto</label>
                                                <select class="form-control" data-live-search='true' name='cfop'
                                                        id='taxCodeGlobal' onchange="setTaxFull()">
                                                    <option value=''>Selecione</option>
                                                    @foreach($tax as $keys => $values)
                                                        <option value="{{$values->value}}">{{compressText($values->name, 20)}}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Total</label>
                                                    <input type="text" class="form-control" name="docTotal"
                                                           @if(isset($head)) value="{{number_format($head['docTotal'],2,',','.')}}"
                                                           @else value="0" @endif   readonly id="totalNota">
                                                    <input type="hidden" class="form-control"
                                                           @if(isset($head)) value="{{number_format($head['DocTotal'],2,',','.')}}"
                                                           @else value="0" @endif  id="docTotal">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane liti" id="tab-2">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table id="tableExpense"
                                                           class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                        <tr>
                                                            <th>Nome da Despesa</th>
                                                            <th>Valor</th>
                                                            <th>Código Imposto</th>
                                                            <th>Observação</th>
                                                            <th>Projeto</th>
                                                            <th>Regra de Distribuição</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <?php $DI = false; ?>
                                                            <td>Desp. Importação</td>
                                                            @if(isset($expenses))
                                                                @for($i =0; $i< count($expenses); $i++)
                                                                    @if($expenses[$i]->expenseCode == '4')
                                                                        <?php $DI = true; ?>
                                                                        <td style="width: 10%"><input
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '4') value='{{number_format($expenses[$i]->lineTotal,2,',','.')}}'
                                                                                    @endif @if(isset($head) ) disabled
                                                                                    @endif style="width: 100%"
                                                                                    onblur="sumAllValues()"
                                                                                    id='di_total' type="text"
                                                                                    class="form-control money"
                                                                                    name="di_total"></td>
                                                                        <td style="width: 20%">
                                                                            <select style="width: 100%"
                                                                                    class="form-control" name="di_tax"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($tax as $item)
                                                                                    <option value="{{$item->value}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '4' && $expenses[$i]->tax == $item->value) selected @endif>{{$item->value}}
                                                                                        - {{$item->name}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="width: 20%"><input
                                                                                    style="width: 100%"
                                                                                    @if(isset($expenses[$i]->comments) && $expenses[$i]->expenseCode == '4') value='{{$expenses[$i]->comments}}'
                                                                                    @endif type="text"
                                                                                    class="form-control"
                                                                                    name="di_comments"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="di_project"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($projeto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->project) && $expenses[$i]->expenseCode == '4' && $expenses[$i]->project == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control" name="di_role"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($centroCusto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->distributionRule) && $expenses[$i]->expenseCode == '4' && $expenses[$i]->distributionRule == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                            @if(!isset($expenses) || (!$DI))
                                                                <td style="width: 10%"><input style="width: 100%"
                                                                                              onblur="sumAllValues()"
                                                                                              id='di_total' type="text"
                                                                                              value='0,00'
                                                                                              class="form-control money"
                                                                                              name="di_total"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="di_tax" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($tax as $item)
                                                                            <option value="{{$item->value}}">{{$item->value}}
                                                                                - {{$item->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="width: 20%"><input style="width: 100%"
                                                                                              type="text"
                                                                                              class="form-control"
                                                                                              name="di_comments"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="di_project" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($projeto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="di_role" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($centroCusto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <td>Frete</td>
                                                            <?php $frete = false; ?>
                                                            @if(isset($expenses))
                                                                @for($i =0; $i< count($expenses); $i++)
                                                                    @if($expenses[$i]->expenseCode == '1')
                                                                        <?php $frete = true; ?>
                                                                        <td style="width: 10%"><input
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '1') value='{{number_format($expenses[$i]->lineTotal,2,',','.')}}'
                                                                                    @endif style="width: 100%"
                                                                                    onblur="sumAllValues()" value='0,00'
                                                                                    id='freight_total' type="text"
                                                                                    class="form-control money"
                                                                                    name="freight_total"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="freight_tax"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($tax as $item)
                                                                                    <option value="{{$item->value}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '1' && $expenses[$i]->tax == $item->value) selected @endif>{{$item->value}}
                                                                                        - {{$item->name}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="width: 20%"><input
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '1') value='{{$expenses[$i]->comments}}'
                                                                                    @endif style="width: 100%"
                                                                                    type="text" class="form-control"
                                                                                    name="freight_comments"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="freight_project"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($projeto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->project) && $expenses[$i]->expenseCode == '1' && $expenses[$i]->project == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="freight_role"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($centroCusto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->distributionRule) && $expenses[$i]->expenseCode == '1' && $expenses[$i]->distributionRule == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                            @if(!isset($head) || (!$frete))
                                                                <td style="width: 10%"><input style="width: 100%"
                                                                                              onblur="sumAllValues()"
                                                                                              value='0,00'
                                                                                              id='freight_total'
                                                                                              type="text"
                                                                                              class="form-control money"
                                                                                              name="freight_total"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="freight_tax" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($tax as $item)
                                                                            <option value="{{$item->value}}">{{$item->value}}
                                                                                - {{$item->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="width: 20%"><input style="width: 100%"
                                                                                              type="text"
                                                                                              class="form-control"
                                                                                              name="freight_comments">
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="freight_project"
                                                                            data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($projeto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="freight_role" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($centroCusto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <?php $outros = false; ?>
                                                            <td>Outros</td>
                                                            @if(isset($expenses))
                                                                @for($i =0; $i< count($expenses); $i++)
                                                                    @if($expenses[$i]->expenseCode == '3')
                                                                        <?php $outros = true; ?>
                                                                        <td style="width: 10%"><input
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '3') value='{{number_format($expenses[$i]->lineTotal,2,',','.')}}'
                                                                                    @endif onblur="sumAllValues()"
                                                                                    value='0,00' id='outhers_total'
                                                                                    style="width: 100%" type="text"
                                                                                    class="form-control money"
                                                                                    name="outhers_total"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="outhers_tax"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($tax as $item)
                                                                                    <option value="{{$item->value}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '3' && $expenses[$i]->tax == $item->value) selected @endif>{{$item->value}}
                                                                                        - {{$item->name}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="width: 20%"><input
                                                                                    style="width: 100%"
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '3') value='{{($expenses[$i]->comments)}}'
                                                                                    @endif type="text"
                                                                                    class="form-control"
                                                                                    name="outhers_comments"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="outhers_project"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($projeto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '3' && $expenses[$i]->project == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="outhers_role"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($centroCusto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '3' && $expenses[$i]->distributionRule == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                            @if(!isset($head) || (!$outros))
                                                                <td style="width: 10%"><input onblur="sumAllValues()"
                                                                                              value='0,00'
                                                                                              id='outhers_total'
                                                                                              style="width: 100%"
                                                                                              type="text"
                                                                                              class="form-control money"
                                                                                              name="outhers_total"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="outhers_tax" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($tax as $item)
                                                                            <option value="{{$item->value}}">{{$item->value}}
                                                                                - {{$item->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="width: 20%"><input style="width: 100%"
                                                                                              type="text"
                                                                                              class="form-control"
                                                                                              name="outhers_comments">
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="outhers_project"
                                                                            data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($projeto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="outhers_role" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($centroCusto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <td>Seguro</td>
                                                            <?php $seguro = false; ?>
                                                            @if(isset($expenses))
                                                                @for($i =0; $i< count($expenses); $i++)
                                                                    @if($expenses[$i]->expenseCode == '2')
                                                                        <?php $seguro = true; ?>
                                                                        <td style="width: 10%"><input
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '2') value='{{number_format($expenses[$i]->lineTotal,2,',','.')}}'
                                                                                    @endif style="width: 100%"
                                                                                    onblur="sumAllValues()" value='0,00'
                                                                                    id='safe_total'
                                                                                    onblur="sumAllValues();" type="text"
                                                                                    class="form-control money"
                                                                                    name="safe_total"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control" name="safe_tax"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($tax as $item)
                                                                                    <option value="{{$item->value}}"
                                                                                            @if(isset($expenses[$i]->tax) && $expenses[$i]->expenseCode == '2' && $expenses[$i]->tax == $item->value) selected @endif>{{$item->value}}
                                                                                        - {{$item->name}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="width: 20%"><input
                                                                                    style="width: 100%"
                                                                                    @if(isset($expenses[$i]->expenseCode) && $expenses[$i]->expenseCode == '2') value='{{$expenses[$i]->comments}}'
                                                                                    @endif type="text"
                                                                                    class="form-control"
                                                                                    name="safe_comments"></td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="safe_project"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($projeto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->project) && $expenses[$i]->expenseCode == '2' && $expenses[$i]->project == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select style="width: 100%"
                                                                                    class="form-control"
                                                                                    name="safe_role"
                                                                                    data-live-search='true'>
                                                                                <option selected disabled>Selecione
                                                                                </option>
                                                                                @foreach($centroCusto as $item)
                                                                                    <option value="{{$item['value']}}"
                                                                                            @if(isset($expenses[$i]->distributionRule) && $expenses[$i]->expenseCode == '2' && $expenses[$i]->distributionRule == $item['value']) selected @endif>{{$item['value']}}
                                                                                        - {{$item["name"]}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                    @endif
                                                                @endfor
                                                            @endif
                                                            @if(!isset($head) || (!$seguro))
                                                                <td style="width: 10%"><input style="width: 100%"
                                                                                              onblur="sumAllValues()"
                                                                                              value='0,00'
                                                                                              id='safe_total'
                                                                                              onblur="sumAllValues();"
                                                                                              type="text"
                                                                                              class="form-control money"
                                                                                              name="safe_total"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="safe_tax" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($tax as $item)
                                                                            <option value="{{$item->value}}">{{$item->value}}
                                                                                - {{$item->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td style="width: 20%"><input style="width: 100%"
                                                                                              type="text"
                                                                                              class="form-control"
                                                                                              name="safe_comments"></td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="safe_project" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($projeto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select style="width: 100%" class="form-control"
                                                                            name="safe_role" data-live-search='true'>
                                                                        <option selected disabled>Selecione</option>
                                                                        @foreach($centroCusto as $item)
                                                                            <option value="{{$item['value']}}">{{$item['value']}}
                                                                                - {{$item["name"]}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane liti" id="tab-3">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Tipo</label>
                                                    <select class="form-control" required name="type_tax">
                                                        <option value="-2"
                                                                @if(isset($taxes[0]->seqCode) && ($taxes[0]->seqCode == '-2')) selected @endif>
                                                            Externo
                                                        </option>
                                                        <option value="-1"
                                                                @if(isset($taxes[0]->seqCode) && ($taxes[0]->seqCode == '-1')) selected @endif>
                                                            Manual
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Número NF</label>
                                                    <input type="text" class="form-control"
                                                           @if(isset($taxes[0]->sequenceSerial)) value="{{$taxes[0]->sequenceSerial}}"
                                                           @endif  required name="number_nf" id="number_nf">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Serie</label>
                                                    <input type="text" class="form-control"
                                                           @if(isset($taxes[0]->seriesStr)) value="{{$taxes[0]->seriesStr}}"
                                                           @endif  required name="serie" id="serie">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Subserie</label>
                                                    <input type="text" class="form-control"
                                                           @if(isset($taxes[0]->subStr)) value="{{$taxes[0]->subStr}}"
                                                           @endif   name="sserie" id="sserie">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Modelo</label>
                                                    <select class="form-control" data-live-search="true" data-size="10"
                                                            required name="model" id="model">
                                                        <option value=''>Selecione</option>
                                                        @foreach($model as $key => $value)
                                                            <option value="{{$value['value']}}"
                                                                    @if(isset($taxes[0]->sequenceModel) && ($taxes[0]->sequenceModel == $value['value'])) selected @endif>{{$value['name']}}</option>
                                                        @endForeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Chave da NFE</label>
                                                    <input type="text" name="key_NFE"
                                                           @if(isset($taxes[0]->NFEKey)) value="{{$taxes[0]->NFEKey}}"
                                                           @endif id="key_NFE" class="form-control" min="44" max="44"
                                                           onblur="checkKeyNFE()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane liti" id="tab-4">
                                    <div class="panel-body">
                                        <div class="col-md-12">
                                            <label>Observação</label>
                                            <textarea class="form-control" rows="5"
                                                      name="obsevacoes">@if(isset($head)) {{$head['comments']}} @endif</textarea>
                                        </div>
                                        @if(false)
                                            <div class="col-md-6" style="padding-top: 5%">
                                                <!-- image-preview-filename input [CUT FROM HERE]-->
                                                <div class="input-group image-preview">
                                                    <input type="text" class="form-control image-preview-filename"
                                                           disabled="disabled">
                                                    <!-- don't give a name === doesn't send on POST/GET -->
                                                    <span class="input-group-btn">
                                 <!-- image-preview-clear button -->
                                 <button type="button" class="btn btn-default image-preview-clear"
                                         style="display:none;">
                                 <span class="glyphicon glyphicon-remove"></span> Remover
                                 </button>
                                                        <!-- image-preview-input -->
                                 <div class="btn btn-default image-preview-input">
                                    <span class="glyphicon glyphicon-folder-open"></span>
                                    <span class="image-preview-input-title">Abrir</span>
                                    <input type="file" multiple name="input-file-preview[]"/> <!-- rename it -->
                                 </div>
                              </span>
                                                </div>
                                                <!-- /input-group image-preview [TO HERE]-->
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="col-md-6  -align-right right pull-right right">
                    <div class="form-group pull-right">
                        <div class="hr-line-dashed"></div>
                        <button class="btn btn-primary" type="submit" onclick="validateTabs()">Salvar</button>
                        <div class="hr-line-dashed"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @if(!isset($head))
        <div class="modal inmodal" id="itensModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Adicionar Itens</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table id="table" class="table table-striped table-bordered table-hover"
                                   style="width: 100%;">
                                <thead>
                                <tr>
                                    <th style="width: 10%">Cod. SAP</th>
                                    <th style="width: 45%">Descrições</th>
                                    <th style="width: 10%">Opções</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal inmodal" id="gastosExtras" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                    aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title">Adicionar Despesas</h4>
                    </div>
                    <div class="modal-body">
                        <form onsubmit="exPensesAdditional(); return false;" id="expensesForm">
                            <input type="hidden" name="line">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tipo</label>
                                        <select class="form-control selectpicker" data-live-search="true" data-size="10"
                                                required id="cPagamentos" name="condPagamentos">
                                            <option>Selecione</option>
                                            @foreach($typeOut as $key => $value)
                                                <option value="{{$value['code']}}">{{$value['value']}}</option>
                                            @endForeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>Valor</Label>
                                    <input type="text" class="form-control money" id="vFrete" name="valorFrete">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" form="expensesForm" class="btn btn-white" data-dismiss="modal">Cancelar
                        </button>
                        <button type="submit" form="expensesForm" class="btn btn-primary">Adicionar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
    <script type="text/javascript">
        var selectpicker = $('.selectpicker').selectpicker();
        selectpicker.filter('.with-ajax-bank').ajaxSelectPicker(getAjaxSelectPickerOptions("{{route('banks.get.all')}}"));

        $(document).ready(function () {
            setMaskMoney();
        });

        function getDueDate() {
            var codition = $('#conditionPagamentos').val();
            var date = $('#dataDocumento').val();
            $.get("{{route('home/get/dueDate')}}" + '/' + date + '/' + codition, function (items) {
                
                document.getElementById('dataVencimento').value = items;
            });
        }

        function changeClassification() {
            // Service
            if ($("[name='classification']:checked").val() === '1') {
                $("#info-transfer").show();
                $("#info-money").hide();
                $("[name='source']").prop('required', false);
                $("[name='material_type']").prop('required', false);
            } else {
                $("#info-transfer").hide();
                $("#info-money").show();
                $("[name='source']").prop('required', true);
                $("[name='material_type']").prop('required', true);
            }
        }

        $(document).on('click', '#close-preview', function () {
            $('.image-preview').popover('hide');
            // Hover befor close the preview
            $('.image-preview').hover(
                function () {
                    $('.image-preview').popover('show');
                },
                function () {
                    $('.image-preview').popover('hide');
                }
            );
        });
        $(function () {
            // Create the close button
            var closebtn = $('<button/>', {
                type: "button",
                text: 'x',
                id: 'close-preview',
                style: 'font-size: initial;',
            });
            closebtn.attr("class", "close pull-right");
            // Set the popover default content
            $('.image-preview').popover({
                trigger: 'manual',
                html: true,
                title: "<strong>Preview</strong>" + $(closebtn)[0].outerHTML,
                content: "There's no image",
                placement: 'bottom'
            });
            // Clear event
            $('.image-preview-clear').click(function () {
                $('.image-preview').attr("data-content", "").popover('hide');
                $('.image-preview-filename').val("");
                $('.image-preview-clear').hide();
                $('.image-preview-input input:file').val("");
                $(".image-preview-input-title").text("Browse");
            });
            // Create the preview image
            $(".image-preview-input input:file").change(function () {
                var img = $('<img/>', {
                    id: 'dynamic',
                    width: 250,
                    height: 200
                });
                var file = this.files[0];
                var reader = new FileReader();
                // Set preview image into the popover data-content
                reader.onload = function (e) {
                    $(".image-preview-input-title").text("Change");
                    $(".image-preview-clear").show();
                    $(".image-preview-filename").val(file.name);
                    img.attr('src', e.target.result);
                    $(".image-preview").attr("data-content", $(img)[0].outerHTML).popover("show");
                }
                reader.readAsDataURL(file);
            });
        });
        $('.dataTables-example').DataTable({
            language: dataTablesPtBr,
            paging: false,
            "lengthChange": false,
            "ordering": false,
            "bFilter": false,
            "bInfo": false,
            "searching": false,
            "paging": false
        });

        function setMaskMoney() {
            $('.money').maskMoney({thousands: '.', decimal: ',', allowZero: true});
            $('.moneyPlus').maskMoney({thousands: '.', decimal: ',', precision: 5, allowZero: true});
        }

        function loadingItems() {
            $('#table').dataTable().fnDestroy();
            let table = $("#table").DataTable({
                processing: true,
                responsive: true,
                ajax: {
                    url: '{{route('inventory.request.list')}}'
                },
                columns: [
                    {name: 'ItemCode', data: 'ItemCode'},
                    {name: 'ItemName', data: 'ItemName'},
                    {name: 'edit', data: 'ItemCode', render: renderEditButton, orderable: false}
                ],
                lengthMenu: [5, 10, 30],
                language: dataTablesPtBr
            });
        }

        function valideTotalCC(cc_total) {
            var total = document.getElementById('totalNota').value;
            var adiantamento = cc_total.value;
            if (total.substring(0, 1) == 'R') {
                total = total.substring(2, total.length);
            }
            if (adiantamento.substring(0, 1) == 'R') {
                adiantamento = adiantamento.substring(2, adiantamento.length);
            }

            if (parseFloat(total) < parseFloat(adiantamento)) {
                swal('Opss!', 'O valor do adiantamento não pode ser maior que o valor da nota!', 'error');
                cc_total.value = 0;
            }
        }

        function keyShowModel(number) {
            if (number == 118) {
                $('#itensModal').modal('show');
            }
        }

        function checkKeyNFE() {
            var key = $('#key_NFE').val();
            if (key.length > 44) {
                alert("Chave da NFE invalida, Possui mais de 44 caracteres");
            }
            if (key.length < 44) {
                alert("Chave da NFE invalida, Possui menos de 44 caracteres");
            }
        }

        var aux = 0;

        function loadTablePN() {
            var campo = document.getElementById('seachPN').value;
            if (campo != '') {
                var table = $('#tableResult');
                var tr;
                var teste;
                $('#tableResult tbody > tr').remove();
                $.get('/getPNJ/' + campo, function (items) {
                    for (var i = 0; i < items.length; i++) {
                        tr = $("<tr id='rowTablePN-" + aux + "'  onclick='setInTable(\"" + items[i].CardCode + "\")'>");
                        tr.append($("<td>" + items[i].CardCode + "</td>"));
                        tr.append($("<td>" + items[i].CardName + " - "+ items[i].CardFName +"</td>"));
                        tr.append($("<td>" + items[i].TaxId4 + "</td>"));
                        if (items[i].TaxId0 != '') {
                            tr.append($("<td>" + items[i].TaxId0 + "</td>"));
                        } else {
                            tr.append($("<td>" + items[i].TaxId4 + "</td>"));
                        }
                        table.find('tbody').append(tr);
                        aux++;
                    }
                });

                if (document.getElementById('resulSearch').style.display == 'block') {
                    document.getElementById('resulSearch').style.display = 'none'
                } else {
                    document.getElementById('resulSearch').style.display = 'block'
                }
            } else {
                alert('campo busca está em branco!');
            }
        }

        function setInTable(code) {
            $('#needs-validation').append($('<input type="hidden" value="' + code + '" data-name="line" name="codPN">'));
            $.get('/getNamePN/' + code, function (items) {
                for (var i = 0; i < items.length; i++) {
                    document.getElementById('parceiroNegocio').value = items[i].CardName;
                    $('#needs-validation').append($('<input type="hidden" value="' + items[i].CardName + '" data-name="line" name="cardName">'));
                    if (items[i].TaxId0 != '') {
                        $('#needs-validation').append($('<input type="hidden" value="' + items[i].TaxId0 + '" data-name="line" name="identification">'));
                    } else {
                        $('#needs-validation').append($('<input type="hidden" value="' + items[i].TaxId4 + '" data-name="line" name="identification">'));
                    }
                }

            });
            $('#pnModal').modal('hide');
        }

        <?php $aux = true;?>
        function renderEditButton(code) {
            return "<center><img src='{{asset('images/add.png')}}' id='addItem-" + code + "' onclick='loadTable(\"" + code + "\");'/></center>";

        }

        var used = new Array();
        var index = @if(isset($body)) {{$cont}} @else 1 @endif;

        function loadTable(code) {
            var table = $('#requiredTable');
            if (index == 1) {
                $('#requiredTable tbody > tr').remove();
            }
            var tr = $("<tr id='rowTable-" + index + "'>");
            tr.append($('<td>' + index + '</td>'));
            tr.find('td').first().append('<input type="hidden" value="' + code + '" data-name="line" name="requiredProducts[' + index + '][codSAP]">');

            $.get('/getProductsSAP/' + code, function (items) {
                for (var i = 0; i < items.length; i++) {
                    tr.append($("<td style='width: 5%'>" + items[i].ItemCode + "</td>"));
                    tr.append($("<td style='width: 15%'>" + items[i].ItemName + "<input type='hidden' value='" + items[i].ItemName + "' name='requiredProducts[" + index + "][itemName]' > </td>"));
                    tr.append($("<td style='width: 10%'><input style='width: 100%' value='0,00' onclick='setMaskMoney()' onblur='gerarTotal(" + index + ");' id='qtd-" + index + "' type='text' class='form-control money' name='requiredProducts[" + index + "][qtd]'></td>"));
                    tr.append($("<td style='width: 10%'><input style='width: 100%' required  onclick='setMaskMoney()' onblur='gerarTotal(" + index + ")' id='price-" + index + "' value='" + parseFloat(items[i].AvgPrice) + "'type='text' class='form-control moneyPlus' name='requiredProducts[" + index + "][preco]' min='1'></td>"));
                    tr.append($("<td style='width: 10%'><input style='width: 100%' value='0' onclick='setMaskMoney()' class='form-control money' id='totalLinha-" + index + "' onblur='gerarPrecoUnitario(" + index + ")' type='text'></td>"));
                    tr.append($("<td style='width: 10%'>"
                        + "<select class='form-control' style='width: 100%' data-live-search='true' id='use-" + index + "' name='requiredProducts[" + index + "][use]' required > <option value=''>Selecione</option> @foreach($use as $keys => $values) <option value='{{$values['code']}}'>{{$values['code']}} - {{$values['value']}}</option> @endForeach</select>"
                        + "</td>"));
                    tr.append($("<td style='width: 10%'>"
                        + "<select class='form-control' style='width: 100%' data-live-search='true' id='project-" + index + "' name='requiredProducts[" + index + "][projeto]' required > <option value=''>Selecione</option> @foreach($projeto as $keys => $values) <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option> @endForeach</select>"
                        + "</td>"));
                    tr.append($("<td style='width: 10%'>"
                        + "<select class='form-control'style='width: 100%' id='role-" + index + "' name='requiredProducts[" + index + "][role]' required > <option value=''>Selecione</option> @foreach($centroCusto as $keys => $values) <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option> @endForeach</select>"
                        + "</td>"));
                    tr.append($("<td style='width: 10%'>"
                        + "<select class='form-control'style='width: 100%' id='cfop-" + index + "' name='requiredProducts[" + index + "][cfop]' required > <option value=''>Selecione</option> @foreach($cfop as $keys => $values) <option value='{{$values['value']}}'>{{$values['value']}} - {{compressText($values['name'],15)}}</option> @endForeach</select>"
                        + "</td>"));
                    tr.append($("<td style='width: 10%'>"
                        + "<select class='form-control'style='width: 100%' id='taxCode-" + index + "' name='requiredProducts[" + index + "][taxCode]' > <option value=''>Selecione</option> @foreach($tax as $keys => $values) <option value='{{$values->value}}'>{{$values->value}} - {{$values->name}}</option> @endForeach</select>"
                        + "</td>"));
                    tr.append($("<td id='itemTable-" + index + "' style='width: 5%'><img src='{{asset('images/remover.png')}}' onclick='removeInArray(\"" + items[i].ItemCode + "\");removeLinha(this);' style='font-size: 3%;color: #ec0707;padding-left: 16px;'/></td>"));
                }
                table.find('tbody').append(tr);
                index++;
            });

        }

        function setTaxFull() {
            var val = document.getElementById('taxCodeGlobal').value;
            var i;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('taxCode-' + i)) {
                    document.getElementById('taxCode-' + i).value = val;
                }
            }
        }

        function gerarTotal(code) {
            clearNumber(code);
            var qtd = document.getElementById('qtd-' + code).value;
            var preco = document.getElementById('price-' + code).value;
            var total = parseFloat(qtd.replace('.', '').replace(',', '.')) * parseFloat(preco.replace('.', '').replace(',', '.'));
            if (!isNaN(total)) {
                document.getElementById('totalLinha-' + code).value = total.format(2, ",", ".");
                sumAllValues();
            }
        }

        function clearNumber(code) {
            var qtd = document.getElementById('qtd-' + code).value;
            var preco = document.getElementById('price-' + code).value;
            var total = document.getElementById('totalLinha-' + code).value;
            document.getElementById('qtd-' + code).value = qtd.replace(/[-]/g, '');
            document.getElementById('price-' + code).value = preco.replace(/[-]/g, '');
            document.getElementById('totalLinha-' + code).value = total.replace(/[-]/g, '');
        }

        function gerarPrecoUnitario(code) {
            clearNumber(code);
            var totalLinha = document.getElementById('totalLinha-' + code).value;
            var qtd = document.getElementById('qtd-' + code).value;
            var prince = parseFloat(totalLinha.replace('.', '').replace(',', '.')) / parseFloat(qtd);
            //console.log(prince);
            if (!isNaN(prince)) {
                document.getElementById('price-' + code).value = prince.format(2, ",", ".");
                sumAllValues();
            }
        }

        function sumAllValues() {
            var total = 0;
            var di_total = document.getElementById('di_total').value;
            var freight_total = document.getElementById('freight_total').value;
            var outhers_total = document.getElementById('outhers_total').value;
            var safe_total = document.getElementById('safe_total').value;
            di_total = parseFloat(di_total.replace('.', '').replace(',', '.'));
            freight_total = parseFloat(freight_total.replace('.', '').replace(',', '.'));
            outhers_total = parseFloat(outhers_total.replace('.', '').replace(',', '.'));
            safe_total = parseFloat(safe_total.replace('.', '').replace(',', '.'));

            var i;
            var x = 0;
            for (i = 1; i < index; i++) {
                if (document.getElementById('totalLinha-' + i)) {
                    x = document.getElementById('totalLinha-' + i).value;
                    total += parseFloat(x.replace('.', '').replace(',', '.'));
                }
            }

            if (isNaN(total)) {
                total = 0;
            }
            if (isNaN(di_total)) {
                di_total = 0;
            }
            if (isNaN(freight_total)) {
                freight_total = 0;
            }
            if (isNaN(outhers_total)) {
                outhers_total = 0;
            }
            if (isNaN(safe_total)) {
                safe_total = 0;
            }
            total += (di_total + freight_total + outhers_total + safe_total);

            var desconto = 0;
            var x;
            var i;

            document.getElementById('totalHeader').innerHTML = total.format(2, ",", ".");
            document.getElementById('totalNota').value = total.format(2, ",", ".");
            document.getElementById('total_pagameto_modal').value = total.format(2, ",", ".");
            addExpensesFromValues();
        }

        function gastosExtras(code, indice) {
            var form = $('#expensesForm');
            $('#gastosExtras').modal('show').find("button[type=submit]").html('Adicionar');
            form.attr('onsubmit', "setValuesExpenses(\"" + code + "\", " + indice + "); return false;");

        }

        function setValuesExpenses(code, indice) {
            $('#gastosExtras').modal('hide');
            var vFrete = document.getElementById('vFrete').value;
            var cPagamentos = document.getElementById('cPagamentos').value;
            var form = $('#form');
            form.append('<input type="hidden" id="dividas[' + code + '][vFrete]" value="' + vFrete + '"  name="dividas[' + code + '][vFrete]">');
            form.append('<input type="hidden" id="dividas[' + code + '][cPagamentos]"  value="' + cPagamentos + '"  name="dividas[' + code + '][cPagamentos]">');
            personalizeExpenses(code, indice);
            addExpensesFromValues()
        }

        function addExpensesFromValues() {
            //sumAllValues();
            var vFrete = 0;
            var x = 0;
            for (var i = 0; i < used.length; i++) {
                if (document.getElementById('dividas[' + used[i] + '][vFrete]')) {
                    
                    x = document.getElementById('dividas[' + used[i] + '][vFrete]').value;
                    
                    if (x.substring(0, 1) == 'R') {
                        x = x.substring(2, x.length);
                    }
                    vFrete = parseFloat(vFrete) + parseFloat(x.replace('.', '').replace(',', '.'));
                }

            }
            var total = document.getElementById('totalNota').value;

            if (total.substring(0, 1) == 'R') {
                total = total.substring(2, total.length);
            }
            total = total.replace('.', '').replace(',', '.');
            document.getElementById('totalNota').value = (parseFloat(vFrete) + parseFloat(total)).format(2, ",", ".");
            document.getElementById('totalHeader').innerHTML = (parseFloat(vFrete) + parseFloat(total)).format(2, ",", ".");
        }

        function personalizeExpenses(code, indice) {
            $('#rowTable-' + indice).css({"background-color": "rgba(232, 219, 180, 0.41)"});
            document.getElementById('itemTable-' + indice).innerHTML = "<img src='{{asset('images/expenses.png')}}' onclick='searchExpenses(\"" + code + "\");' style=' width:37%;font-size: 3%;color:  blue; padding-left: 5px;'/><img src='{{asset('images/remover.png')}}' onclick='removeInArray(\"" + code + "\");removeLinha(this);' style='font-size: 3%;color: #ec0707;padding-left: 16px;'/>";
        }

        function searchExpenses(code) {
            var vFrete = document.getElementById('dividas[' + code + '][vFrete]').value;
            var cPagamentos = document.getElementById('dividas[' + code + '][cPagamentos]').value;

            document.getElementById('vFrete').value = vFrete;
            document.getElementById('cPagamentos').value = cPagamentos;
            var form = $('#expensesForm');
            $('#gastosExtras').modal('show').find("button[type=submit]").html('Atualizar');
            form.attr('onsubmit', "UpdateValuesExpenses(\"" + code + "\"); return false;");
        }

        function UpdateValuesExpenses(code) {
            $('#gastosExtras').modal('hide');
            var vf = document.getElementById('vFrete').value;
            var cP = document.getElementById('cPagamentos').value;
            document.getElementById('dividas[' + code + '][vFrete]').value = vf;
            document.getElementById('dividas[' + code + '][cPagamentos]').value = cP;
            sumAllValues()
        }

        function removeLinha(elemento) {
            var tr = $(elemento).closest('tr');
            tr.fadeOut(400, function () {
                tr.remove();
            });
            $("#composicao-" + elemento).remove();
            sumAllValues();
        }

        function removeInArray(code) {
            var aux = used.indexOf(code);
            if (aux != -1) {
                used.splice(aux, 1);
            }
        }

        function setUseFull() {
            var val = document.getElementById('useGlobal').value;
            var i;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('use-' + i)) {
                    document.getElementById('use-' + i).value = val;
                }
            }
        }

        function setCFOPFull() {
            var val = document.getElementById('cfopGlobal').value;
            var i;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('cfop-' + i)) {
                    document.getElementById('cfop-' + i).value = val;
                }
            }
        }

        function setProjecFull() {
            var val = document.getElementById('projectGlobal').value;
            var i;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('project-' + i)) {
                    document.getElementById('project-' + i).value = val;
                }
            }
        }

        function setRoleFull() {
            var val = document.getElementById('roleGlobal').value;
            var i;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('role-' + i)) {
                    document.getElementById('role-' + i).value = val;
                }
            }
        }

        /*use in modal*/
        function addPayment() {
            var modal = $('#paymentModal');
            var form = $('#form');
            var totalMoney = document.getElementById('total_dinheiro').value;
            var conta_dinheiro = $("#conta_dinheiro option:selected").val();
            var dt_transferencia = document.getElementById('dt_transferencia').value;
            var totalTransfer = document.getElementById('total_transfrencia').value;
            var referencia_transferencia = document.getElementById('referencia_transferencia').value;
            var conta_transferencia = $("#conta_transferencia option:selected").val();
                    @if(false)
            var name_cartao = $("#name_cartao option:selected").val();
            var totalCard = document.getElementById('total_credito').value;
            var parcelas_cartao = document.getElementById('parcelas_cartao').value;
            var totalOther = document.getElementById('total_outros').value;
            var conta_outros = $("#conta_outros option:selected").val();
            var conta_cartao = $("#conta_cartao option:selected").val();

            var conta_cheque = $("#conta_cheque option:selected").val();
            var dt_vencimento_cheque = document.getElementById('dt_vencimento_cheque').value;
            var valor_cheque = document.getElementById('valor_cheque').value;
            var nome_banco_cheque = $("#nome_banco_cheque option:selected").val();
            var filial_cheque = document.getElementById('filial_cheque').value;
            var numero_conta_cheque = document.getElementById('numero_conta_cheque').value;
            var numero_cheque = document.getElementById('numero_cheque').value;
            var endosso_cheque = $("#endosso_cheque option:selected").val();
            @endif

            form.append($('<td><input type="hidden" name="payment[total_dinheiro]" value="' + totalMoney.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[conta_dinheiro]" value="' + conta_dinheiro.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[total_transfrencia]" value="' + totalTransfer.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() + '">'));
            @if(false)
            form.append($('<td><input type="hidden" name="payment[name_cartao]" value="' + name_cartao.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[total_credito]" value="' + totalCard.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[parcelas_cartao]" value="' + parcelas_cartao.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[total_outros]" value="' + totalOther.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[conta_outros]" value="' + conta_outros.trim() + '">'));
            form.append($('<td><input type="hidden" name="payment[conta_cartao]" value="' + conta_cartao.trim() + '">'));

            form.append($('<td><input type="hidden" name="payment[conta_cheque]" value="' + conta_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[dt_vencimento_cheque]" value="' + dt_vencimento_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[valor_cheque]" value="' + valor_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[nome_banco_cheque]" value="' + nome_banco_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[filial_cheque]" value="' + filial_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[numero_conta_cheque]" value="' + numero_conta_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[numero_cheque]" value="' + numero_cheque + '">'));
            form.append($('<td><input type="hidden" name="payment[endosso_cheque]" value="' + endosso_cheque + '">'));
            @endif
            modal.modal('hide');
            return false;
        }

        function setTotal() {
            var totalMoney = 0;
            var totalTransfer = 0;
            var totalCard = 0;
            var totalOther = 0;
            totalMoney = document.getElementById('total_dinheiro').value;
            totalTransfer = document.getElementById('total_transfrencia').value;
            @if(false)
                totalCard = document.getElementById('total_credito').value;
            totalOther = document.getElementById('total_outros').value;
            totalCheque = document.getElementById('valor_cheque').value;
            @endif
                totalMoney = parseFloat(totalMoney.replace('.', '').replace(',', '.'));
            totalTransfer = parseFloat(totalTransfer.replace('.', '').replace(',', '.'));
            if (isNaN(totalMoney)) {
                totalMoney = 0;
            }
            if (isNaN(totalTransfer)) {
                totalTransfer = 0;
            }

            /*var total = parseFloat(totalMoney.replace('.','').replace(',','.')) + parseFloat(totalTransfer.replace('.','').replace(',','.')) + parseFloat(totalCard.replace('.','').replace(',','.')) + parseFloat(totalOther.replace('.','').replace(',','.')) + parseFloat(totalCheque.replace('.','').replace(',','.'));*/
            var total = totalMoney + totalTransfer;
            if (!isNaN(total)) {
                document.getElementById('total_pagameto').value = total.format(2, ",", ".");
            } else {
                document.getElementById('total_pagameto').value = total.format(2, ",", ".");
            }
        }

        function validateTabs() {
            var i;
            var check = false;
            for (i = 1; i <= index; i++) {
                if (document.getElementById('cfop-' + i)) {

                    if (document.getElementById('cfop-' + i).value == "") {
                        check = true;
                    }
                }
            }
            if (document.getElementById('number_nf').value == "") {
                check = true;
            }
            if (document.getElementById('serie').value == "") {
                check = true;
            }
            if (document.getElementById('model').value == "") {
                check = true;
            }
            if (document.getElementById('key_NFE').value == "") {
                check = true;
            }
            if (check) {
                alert("Esses campos são obrigatório: CFOP, Número NF, Serie, Subserie, Modelo, Chave da NFE. ");
            }

        }

        @if(isset($head))
        function cancel(item) {
            swal({
                title: "Tem certeza que deseja cancelar?",
                text: "Esta operação não pode ser desfeita!",
                icon: "warning",
                //buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        waitingDialog.show('Cancelando...')
                        window.location.href = "{{route('purchase.receipts.goods.canceled', $head['id'])}}";
                    }
                });
        }

        function closed(item) {
            swal({
                title: "Tem certeza que deseja Encerrar?",
                text: "O encerramento de um documento é irrevessível!",
                icon: "warning",
                //buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        waitingDialog.show('Cancelando...')
                        window.location.href = "{{route('purchase.receipts.goods.closed', $head['id'])}}";
                    }
                });
        }
        @endif
    </script>
@endsection
