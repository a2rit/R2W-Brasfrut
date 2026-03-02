@extends('layouts.main')
@section('title', 'Nota Fiscal de Entrada')
@section('content')

  @if (isset($head) && !empty($head->message))
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif

  <form id="needs-validation" onkeydown='keyShowModel(event.keyCode)' enctype="multipart/form-data">
    {!! csrf_field() !!}
    @if (isset($head))
      <input type="hidden" name="id" value="{{ $head->id }}">
    @endif

    <div class="card">
      <h5 class="card-header fw-bolder">
        @if (isset($head))
          Nota fiscal de entrada - detalhes
        @else
          Nota fiscal de entrada - cadastro
        @endif
      </h5>
      <div class="card-body">
        @if (isset($head))
          <div class="row">
            <div class="col-md-2">
              <label>Cod. SAP</label>
              <input type="text" readonly value="{{ $head->codSAP }}" class="form-control locked">
            </div>
            <div class="col-md-2">
              <label>Cod. WEB</label>
              <input type="text" readonly value="{{ $head->code }}" class="form-control locked">
            </div>
            <div class="col-md-2">
              <label for="">Status</label>
              <input type="text" class="form-control locked" value="{{ $head::STATUS_TEXT[$head->status] }}" disabled>
            </div>
            <div class="col-md-4">
              <label>Usuario</label>
              <input type="text" readonly value="{{ $head->name }}" class="form-control locked">
            </div>
          </div>
        @endif
        <div class="row mt-2">
          <div class="col-md-6">
            <label for="cardCode">Parceiro de negócios</label>
            @php
              if (isset($head)) {
                  $partner = getProviderData($head->cardCode);
              }
            @endphp
            <select id="cardCode" class="form-control selectpicker with-ajax-suppliers" data-size="10" data-width="100%"
              name="cardCode" @if (isset($head)) disabled @endif
              onchange="getPartnerContracts($(this).val()); getPartnersComplements($(this).val());" required>
              @if (isset($head))
                <option value="{{ $head['cardCode'] }}" selected>{{ $partner['CardCode'] }} - {{ $partner['CardName'] }}
                </option>
              @endif
            </select>
          </div>
          <div class="col-md-3">
            <label>CNPJ</label>
            <input id="cnpj" type="text"
              @if (!empty($partner)) value="{{ $partner['TaxId0'] ?? $partner['Taxid4'] }}" @endif
              class="form-control locked" readonly>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-3">
            <div class="form-group">
              <label>Data do Documento</label>
              <input type="date" name="dataDocumento" id="dataDocumento"
                @if (!empty($head->codSAP)) class="form-control locked" readonly @else class="form-control" @endif
                @if (isset($head->taxDate)) value="{{ $head->taxDate }}"
                                                @else value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Lançamento</label>
              <input type="date" name="dataLancamento" id="dataLancamento"
                @if (!empty($head->codSAP)) class="form-control locked" readonly @else class="form-control" @endif
                @if (isset($head->docDate)) value="{{ $head->docDate }}"
                                                @else value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Vencimento</label>
              <input type="date" name="dataVencimento" id="dataVencimento"
                @if (!empty($head->codSAP)) class="form-control" @else class="form-control" @endif
                @if (isset($head->docDueDate)) value="{{ $head->docDueDate }}"
                                                @else value="{{ DATE('Y-m-d') }}" @endif
                required id="dataVencimento">
            </div>
          </div>
          @if (isset($head))
            <div class="col-md-3">
              <div class="form-group">
                <label for="sync_at">Sincronizado em</label>
                <input id="sync_at" type="text" readonly class="form-control locked"
                  @if (!empty($head->codSAP) && !empty($head->sync_at) && $head->is_locked == '0') value="{{ date('d/m/Y H:i:s', strtotime($head->sync_at)) }}"
                                                    @elseif(!empty($head->codSAP) && empty($head->sync_at)) value="{{ date('d/m/Y H:i:s', strtotime($head->updated_at)) }}" 
                                                    @elseif((empty($head->codSAP) || !empty($head->codSAP)) && $head->is_locked == '1')
                                                        value="PROCESSANDO"
                                                    @else
                                                        value="AGUARDANDO USUARIO" @endif>
              </div>
            </div>
          @endif
        </div>
        <div class="row mt-2">
          <div class="col-md-4">
            <label>Condição de Pagamento</label>
            <select class="form-control selectpicker" id="conditionPagamentos"
              onchange="app.changePaymentCondition(this.value)" required name="condPagamentos" onChange="getDueDate();"
              @if (isset($head->codSAP)) disabled @endif>
              <option value=''>Selecione</option>
              @foreach ($paymentConditions as $key => $value)
                <option data-num-installments="{{ $value['InstNum'] }}" value="{{ $value['GroupNum'] }}"
                  @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                  {{ $value['PymntGroup'] }}</option>
              @endForeach
            </select>
          </div>
          <div class="col-md-3">
            <label>Contrato</label>
            <select class='form-control selectpicker' id='contractGlobal' name="contract" onchange="setContractFull()"
              @if (isset($head)) disabled @endif>
              <option value=''>Selecione</option>
              @if (isset($contracts))
                @foreach ($contracts as $keys => $contract)
                  <option value='{{ $contract->code }}' @if ($head->contract == $contract->code) selected @endif>
                    {{ $contract->code }} - {{ $contract->contractNumber }}
                  </option>
                @endForeach
              @endif
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-4">
      <h6 class="card-header fw-bolder">Parcelas e Adiantamentos</h6>
      <div class="card-body">
        <div class="row">
          <div class="col-md-1">
            <div class="form-group">
              <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                data-coreui-target="#installments">Parcelas</button>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                data-coreui-target="#advancePaymentsModal">Adiantamentos vinculados</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if (isset($head))
      <div class="card mt-4">
        <h6 class="card-header fw-bolder">Documentos associados</h6>
        <div class="card-body">
          <div class="col-12">
            @if (!empty($head->idPurchaseOrder))
              <div class="col-3 mt-3">
                <p class="m-0">Pedido de compras</p>
                <a href="{{ route('purchase.order.read', $head->idPurchaseOrder) }}"
                  class="btn btn-primary mt-1">{{ $head->purchase_order_code }}</a>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endif
    <div class="row mt-5">
      <div class="tabs-container">
        <ul class="nav nav-tabs" id="myTabs">
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
            <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
            <a class="nav-link" data-toggle="tab" href="#tab-3">Impostos</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-4">
            <a class="nav-link" data-toggle="tab" href="#tab-4">Anexos</a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active mt-3" id="tab-1">
            <div class="panel-body">
              <a href='#' class='text-dark text-tooltip' data-coreui-placement="right" title="Adicionar linha"
                onclick="loadingItems()" @if (isset($head->codSAP)) style="display: none;" @endif>
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <div class="table-responsive mt-1">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Descrições</th>
                      <th>Quantidade</th>
                      <th>Preço Unit.</th>
                      <th>Total</th>
                      <th>IRF</th>
                      <th>Utilização</th>
                      <th>Projeto</th>
                      <th>C. de Custo</th>
                      <th>C. de Custo2</th>
                      <th>CFOP</th>
                      <th>Cod. de Imposto</th>
                      <th>Conta</th>
                      <th>Contrato</th>
                      <th>Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if (isset($body))
                      <?php
                      $cont = 1;
                      ?>
                      @foreach ($body as $key => $value)
                        <tr id='rowTable-{{ $cont }}'>
                          <td>
                            {{ $cont }}
                            <input type="hidden" name="requiredProducts[{{ $cont }}][idItemPurchaseOrder]"
                              value="{{ $value['idItemPurchaseOrder'] }}">
                          </td>
                          <td>
                            <div class="d-flex flex-row" style="max-width: 100%;">
                              <a class="text-warning" href="{{ route('inventory.items.edit', $value['itemCode']) }}"
                                target="_blank">
                                <svg class="icon icon-lg">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                </svg>
                              </a>
                              <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                title="{{ $value['itemCode'] }} - {{ $value['itemName'] }}">{{ $value['itemCode'] }} -
                                {{ $value['itemName'] }}</span>
                            </div>
                            <input type='hidden'value="{{ $value['itemCode'] }}"
                              name="requiredProducts[{{ $cont }}][codSAP]">
                            <input type='hidden' value="{{ $value['itemName'] }}"
                              name="requiredProducts[{{ $cont }}][itemName]">
                          </td>
                          <td>
                            <div class="input-group min-200 mt-1">
                              <div class="input-group-text">{{ $value['itemUnd'] ?? ' ' }}</div>
                              <input value="{{ number_format($value['quantity'], 3, ',', '.') }}"
                                onclick='destroyMask(event)'
                                onblur='setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)'
                                id="qtd-{{ $cont }}" type="text" class="form-control qtd"
                                name="requiredProducts[{{ $cont }}][qtd]" style="width: 100px;">
                              <input type='hidden' value='{{ $value->itemUnd }}'
                                name='requiredProducts[{{ $cont }}][itemUnd]'>
                            </div>
                          </td>
                          <td>
                            <input required onclick='destroyMask(event)'
                              onblur='setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)'
                              id="price-{{ $cont }}" value="{{ number_format($value['price'], 4, ',', '.') }}"
                              type="text" class="form-control moneyPlus"
                              name="requiredProducts[{{ $cont }}][preco]" min='1'
                              style="width: 100px;">
                          </td>
                          <td>
                            <input readonly type='text' id="totalLinha-{{ $cont }}"
                              value="{{ number_format($value['quantity'] * $value['price'] - (isset($withheld_taxes_items[$value['id']]) ? $withheld_taxes_items[$value['id']]->sum('Value') : 0), 2, ',', '.') }}"
                              onclick='destroyMask(event)'
                              onblur='setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)'
                              class="form-control money" style="width: 100px;">
                          </td>
                          <td class="text-center">
                            <a class='btn btn-warning' href='#'
                              @if (Route::currentRouteName() == 'purchase.ap.invoice.read') value="{{ $value['id'] }}" data-linenum="{{ $cont }}" @endif
                              data-coreui-toggle="modal" data-coreui-target="#withheldtax-modal">
                              <svg class="icon">
                                <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                              </svg>
                            </a>
                            <span
                              id="withheld-tax-{{ $cont }}">{{ number_format(isset($withheld_taxes_items[$value['id']]) ? $withheld_taxes_items[$value['id']]->sum('Value') : 0, 2, ',', '.') }}</span>
                          </td>
                          <td>
                            <select class="form-control selectpicker" id="use-{{ $cont }}"
                              name="requiredProducts[{{ $cont }}][use]" data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($use as $keys => $values)
                                <option value="{{ $values['code'] }}"
                                  @if ($values['code'] == $value['codUse']) selected @endif>{{ $values['code'] }}
                                  - {{ $values['utilizacao'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker" id="project-{{ $cont }}"
                              name="requiredProducts[{{ $cont }}][projeto]" data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($projeto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['codProject']) selected @endif>{{ $values['value'] }}
                                  - {{ substr($values['name'], 0, 10) }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][costCenter]' data-container="body"
                              onchange="validLine({{ $cont }});" required>
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['costCenter']) selected @endif>{{ $values['value'] }}
                                  - {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role2-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][costCenter2]' data-container="body"
                              onchange="validLine({{ $cont }})">
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto2 as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['costCenter2']) selected @endif>{{ $values['value'] }}
                                  - {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker" id="cfop-{{ $cont }}"
                              name="requiredProducts[{{ $cont }}][cfop]" data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($cfop as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['codCFOP']) selected @endif>{{ $values['value'] }}
                                  - {{ compressText($values['name'], 15) }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='taxCode-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][taxCode]' data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($tax as $keys => $values)
                                <option value="{{ $values->value }}"
                                  @if ($values->value == $value['taxCode']) selected @endif>{{ $values->value }}
                                  - {{ $values->name }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker @if (isset($head) && !empty($head->codSAP)) locked @endif' name='requiredProducts[{{ $cont }}][accounting_account]' data-container='body' required>
                              <option value=''>Selecione</option> 
                              @foreach ($budgetAccountingAccounts as $keys => $budgetAccountingAccount) 
                                <option value='{{ $budgetAccountingAccount['value'] }}' @if($budgetAccountingAccount['value'] == $value["accounting_account"]) selected @endif>{{ $budgetAccountingAccount['value'] }} - {{ $budgetAccountingAccount['name'] }}</option>
                              @endForeach
                            </select>
                        </td>
                          <td>
                            <select class='form-control selectpicker' id='contract-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][contract]' data-container="body" disabled>
                              <option value=''>Selecione</option>
                              @foreach ($contracts as $keys => $contract)
                                <option value='{{ $contract->code }}'
                                  @if ($contract->code == $value['contract']) selected @endif>
                                  {{ $contract->code }} - {{ $contract->contractNumber }}
                                </option>
                              @endForeach
                            </select>
                          </td>
                          <td id="itemTable-{{ $cont }}" class="text-center">
                            @if (empty($head->codSAP))
                              <a class="text-danger text-tooltip" title="Remover linha"
                                onclick="removeInArray('{{ $value['itemCode'] }}');removeLinha(this);" type="button">
                                <svg class="icon icon-xl">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                </svg>
                              </a>
                            @endif
                          </td>
                          <?php $cont++; ?>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>

              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Atalhos</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <label>Utilização Principal</label>
                      <select class='form-control selectpicker' name='use' id='useGlobal' onchange="setUseFull()">
                        <option value=''>Selecione</option>
                        @foreach ($use as $keys => $values)
                          <option value='{{ $values['code'] }}'>{{ $values['code'] }}
                            - {{ $values['value'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Projeto Principal</label>
                      <select class='form-control selectpicker' name='project' id='projectGlobal'
                        onchange="setProjecFull()">
                        <option value=''>Selecione</option>
                        @foreach ($projeto as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>C. de Custo Principal</label>
                      <select class='form-control selectpicker' name='role' id='roleGlobal'
                        onchange="setRoleFull()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}
                          </option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>C. de Custo Principal 2</label>
                      <select class='form-control selectpicker' name='role2' id='roleGlobal2'
                        onchange="setRoleFull2()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto2 as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>CFOP Principal</label>
                      <select class="form-control selectpicker" name='cfop' id='cfopGlobal'
                        onchange="setCFOPFull()">
                        <option value=''>Selecione</option>
                        @foreach ($cfop as $keys => $values)
                          <option value='{{ $values['value'] }}'> {{ $values['value'] }}
                            - {{ compressText($values['name'], 20) }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Código de Imposto</label>
                      <select class="form-control selectpicker" name='tax' id='taxCodeGlobal'
                        onchange="setTaxFull()">
                        <option value=''>Selecione</option>
                        @foreach ($tax as $keys => $values)
                          <option value="{{ $values->value }}">{{ $values->value }}
                            - {{ compressText($values->name, 20) }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Conta Principal</label>
                      <select class='form-control selectpicker' data-live-search='true' name='accounting_account' id='accounting_account_global'
                        data-container="body">
                        <option value=''>Selecione</option>
                        @foreach ($budgetAccountingAccounts as $keys => $budgetAccountingAccount) 
                          <option value='{{ $budgetAccountingAccount['value'] }}'>{{ $budgetAccountingAccount['value'] }} - {{ $budgetAccountingAccount['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane" id="tab-3">
            <div class="panel-body mt-4">
              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Impostos</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control selectpicker" required name="type_tax"
                          @if (!empty($head->codSAP)) disabled @endif>
                          <option value="-2" @if (isset($taxes[0]->seqCode) && $taxes[0]->seqCode == '-2') selected @endif>
                            Externo
                          </option>
                          <option value="-1" @if (isset($taxes[0]->seqCode) && $taxes[0]->seqCode == '-1') selected @endif>
                            Manual
                          </option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Número NF</label>
                        <input type="text" maxlength="9" class="form-control"
                          @if (isset($taxes[0]->sequenceSerial)) value="{{ $taxes[0]->sequenceSerial }}" @endif
                          @if (!empty($head->codSAP)) readonly @endif required name="number_nf">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Serie</label>
                        <input type="text" maxlength="3" class="form-control"
                          @if (isset($taxes[0]->seriesStr)) value="{{ $taxes[0]->seriesStr }}" @endif
                          @if (Route::currentRouteName() == 'purchase.ap.invoice.read' && (isset($head) && !is_null($head->codSAP))) readonly @endif name="serie">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Modelo</label>
                        <select class="form-control selectpicker" required name="model" id="modelTax">
                          <option value=''>Selecione</option>
                          @foreach ($model as $key => $value)
                            <option value="{{ $value['value'] }}" @if (isset($taxes[0]->sequenceModel) && $taxes[0]->sequenceModel == $value['value']) selected @endif>
                              {{ $value['name'] }}
                            </option>
                          @endForeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-4">
            <div class="panel-body">
              <div class="card mt-4">
                <div class="card-body">
                  <div class="col-md-12 form-group">
                    <label>Observações</label>
                    <textarea class="form-control" rows="5" name="obsevacoes" @if (isset($head) && !$head::STATUS_OPEN) readonly @endif
                      maxlength="200">
@if (isset($head->comments))
{{ $head->comments }}
@endif
</textarea>
                  </div>
                  <div class="col-md-12 form-group mt-2">
                    <label>Observações do diário</label>
                    <textarea class="form-control" rows="3" name="jrnlmemo" @if (isset($head) && !$head::STATUS_OPEN) readonly @endif
                      maxlength="50">
@if (isset($head->JrnlMemo))
{{ $head->JrnlMemo }}
@endif
</textarea>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-6 mt-2">
                  <div class="table-responsive">
                    <table class="table table-default table-striped table-bordered table-hover dataTables-example">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Anexo</th>
                          <th>Visualizar</th>
                          <th>Remover</th>
                        </tr>
                      </thead>
                      <tbody>

                        @if (isset($upload))
                          <?php $upCont = 1; ?>
                          @foreach ($upload as $key => $item)
                            <tr id="lineUp-{{ $upCont }}">
                              <td style="width: 10%;">{{ $upCont }}</td>
                              <td style="width: 70%;">
                                {{ preg_split('/[;=]/', $item->diretory)[1] }}
                              </td>
                              <td style="width: 10%;" class="text-center">
                                <a href="{{ $item->diretory }}" target="_blank" class="btn btn-primary">
                                  <svg class="icon">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                                  </svg>
                                </a>
                              </td>
                              <td style="width: 10%;" class="text-center">
                                <a class="btn btn-danger" type="button"
                                  onclick='removeUpload({{ $item->id }},{{ $item->idReference }})'>
                                  <svg class="icon">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                  </svg>
                                </a>
                              </td>
                            </tr>
                            <?php $upCont++; ?>
                          @endforeach
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-md-6 mt-2">
                  <!-- image-preview-filename input [CUT FROM HERE]-->
                  {{-- <input type="text" class="form-control image-preview-filename"
                                                      readonly="readonly"> --}}
                  <!-- don't give a name === doesn't send on POST/GET -->
                  <span class="input-group-btn w-100">
                    <!-- image-preview-input -->
                    <div class="btn btn-default image-preview-input w-100">
                      <span class="image-preview-input-title">Abrir</span>
                      <input class="form-control" type="file" multiple name="input-file-preview[]" />
                      <!-- rename it -->
                    </div>
                  </span>
                  <!-- /input-group image-preview [TO HERE]-->
                  <br>
                  <div class="col-md-12 mt-2 d-md-flex justify-content-md-end">
                    <a class="btn btn-danger image-preview-clear me-1" type="button" style="display:none;">
                      Limpar anexos
                    </a>
                    @if (isset($head))
                      <a class="btn btn-primary w-25 me-2" onclick="updateUploads(event)">Atualizar anexos</a>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4">
      <h6 class="card-header fw-bolder">Totais</h6>
      <div class="card-body">
        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label for="total_a_pagar">Total a pagar</label>
              <input type="text" id="total_a_pagar" name="total_a_pagar" class="form-control money locked"
                @if (isset($head)) value={{ number_format($head->total_a_pagar, 2, '.', '') }} @endif
                readonly>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="total_a_pagar">Valor adiantado</label>
              <input type="text" id="valor_adiantado" name="valor_adiantado" readonly class="form-control locked"
                @if (Route::currentRouteName() == 'purchase.ap.invoice.read') value="{{ number_format($advancePayments->sum('DocTotal') - $advancePayments->sum('DpmAppl'), 2, ',', '.') }}" @endif>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="total_imposto_retido">IRF</label>
              <input type="text" id="total_imposto_retido" name="total_imposto_retido" readonly
                class="form-control locked"
                @if (Route::currentRouteName() == 'purchase.ap.invoice.read') value="{{ $withheld_taxes_items->sum('Value') }}" @endif>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="impostos_r">Imposto</label>
              <input type="text" id="impostos_r" class="form-control moneyPlus" name="impostos_r"
                onblur="sumAllValues()"
                @if (isset($head)) value={{ number_format($head->impostos_r, 2, ',', '.') }} @endif>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Total sem descontos</label>
              <input type="text" class="form-control money locked" name="docTotal"
                @if (isset($head)) value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                              @else value="0" @endif
                readonly id="totalNota">
              <input type="hidden" class="form-control"
                @if (isset($head)) value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                              @else value="0" @endif
                id="docTotal">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-5">
      <div class="col-md-4">
        {{-- {{dd($OPPR->validUserApproved($head->id))}} --}}
        @if (isset($head) && $head->status == $head::STATUS_PENDING && $OPPR->validUserApproved($head->id))
          <button type="button" class="btn btn-danger float-start" data-coreui-toggle="modal"
            data-coreui-target="#reproveModal">Reprovar</button>
          <button type="button" class="btn btn-primary float-start ms-1"
            onclick="aprovar({{ $head->id }})">Aprovar</button>
        @endif
      </div>
      <div class="col-md-8">
        @if ((isset($head) && $head->status == 1 && $head->is_locked != "1") || !isset($head))
          <button class="btn btn-primary float-end" type="button" id="btn-save"
            onclick="validateTabs()">Salvar</button>
        @endif
        @if (isset($head))
          <a onclick="duplicate()" class="btn btn-success float-end me-1" type="button">Duplicar</a>

          @if ($head->status == $head::STATUS_OPEN)
            <button class="btn btn-danger float-end me-1" onclick="cancel(event)">Cancelar</button>
          @endif
        @endif
      </div>
    </div>
  </form>
  <div class="modal fade" id="advancePaymentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Vincular adiantamento de pagamento para fornecedor</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table id="advancePaymentsTable" class="table table-default table-striped table-bordered table-hover"
            style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 1%">#</th>
                <th style="width: 10%">Cod. SAP</th>
                <th style="width: 30%">Descrições</th>
                <th style="width: 10%">Data</th>
                <th style="width: 10%">Valor</th>
                <th style="width: 10%">Total utilizado</th>
              </tr>
            </thead>
            <tbody>
              @if (isset($advancePayments))
                @foreach ($advancePayments as $value)
                  @if (isset($head) && $head->codSAP)
                    <?php $payment = getAdvanceProviderSAP($value->codSAP); ?>
                    <tr>
                      <td><input type="checkbox" checked disabled></td>
                      <td>{{ $payment['DocNum'] }}</td>
                      <td>{{ $payment['Comments'] }}</td>
                      <td>{{ date_format(date_create($payment['DocDate']), 'd-m-Y') }}</td>
                      <td>{{ number_format($payment['DocTotal'], 2, ',', '.') }}</td>
                      <td>{{ number_format($payment['DpmAppl'], 2, ',', '.') }}</td>
                    </tr>
                  @endif
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
        <div class="modal-footer">

          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
          @if (!isset($head) || $head->status == 1)
            <button type="button" class="btn btn-sm btn-primary" onclick="saveAdvancePayments(event)"
              data-coreui-dismiss="modal">Salvar</button>
          @endif
        </div>
      </div>
    </div>
  </div>


  <div class="modal inmodal" id="itensModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lista de itens</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive mt-3 ">
            <table id="table" class="table table-bordered table-hover" style="width: 100%;">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  {{-- <th style="width: 15%">Qtd. Estoque</th> --}}
                  <th style="width: 10%">Opções</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  {{-- <td></td> --}}
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal inmodal" id="modalWHS" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title">Informações de depósitos</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <div class="table-responsive mt-1 table-default">
            <table id="tableWHS" class="table table-hover table-bordered">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Codigo</th>
                  <th style="width: 75%">Depósito</th>
                  <th style="width: 20%">quantidade</th>
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
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <div id="app">
    <div class="modal fade" tabindex="-1" role="dialog" id="installments">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Parcelas</h5>
            <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Nº</th>
                  <th>Data de vencimento</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tr v-for="(installment, index) in installments">
                <td>@{{ index + 1 }}</td>
                <td>
                  <input form="needs-validation" type="hidden" :name="'installments[' + index + '][due_date]'"
                    :value="installment.due_date" />
                  <input :id="'installments-' + index + ''" class="form-control" type="date"
                    v-model="installment.due_date">
                </td>
                <td>
                  <input form="needs-validation" type="hidden" :name="'installments[' + index + '][value]'"
                    :value="installment.value" />
                  <vue-numeric class="form-control" separator="." :min="0" :precision="4"
                    @if (isset($head->codSAP)) read-only @endif v-model="installment.value"></vue-numeric>
                </td>
              </tr>
            </table>
            <div><strong>Total: </strong>
              <vue-numeric currency="R$" separator="." read-only :precision="4" :value="installmentsTotal">
              </vue-numeric>
            </div>
            {{-- <div v-if="!totalEqual()"><strong>O total do documento difere do total de parcelas!</strong></div> --}}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-primary" data-coreui-dismiss="modal">Fechar</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </div>

  <div class="modal fade" id="withheldtax-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lista de impostos retidos na fonte</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {!! csrf_field() !!}
          <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-3">
              <label for="">Valor base:</label>
              <input type="text" class="form-control money locked" id="withheldValorBase"
                @if (isset($head)) value={{ number_format($head->docTotal, 2, '.', '') }} @endif
                readonly>
            </div>
            <div class="col-md-3">
              <label for="">Valor IRF:</label>
              <input type="text" class="form-control money locked" id="withheldTotalIRF" readonly>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-default table-striped table-bordered table-hover dataTables-example"
              style="width: 100%;">
              <thead>
                <th>#</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 50%;">Nome</th>
                <th style="width: 20%;">Aliquota (%)</th>
                <th style="width: 20%;">Valor IRF</th>
              </thead>
              <tbody>
                @foreach ($withheldTaxesSap as $index => $withheld)
                  <tr>
                    <td class="text-center"><input type="checkbox" id="withheldCheckbox-{{ $index }}"
                        data-linenum="{{ $index }}"
                        name="requiredProducts[{{ $index }}][withheldTaxes][{{ $index }}][WTCode]"
                        class="form-check-input p-2" value="{{ $withheld['WTCode'] }}"
                        onchange="updateValueWithheldTaxModal({{ $index }})"></td>
                    <td><span>{{ $withheld['WTCode'] }}</span></td>
                    <td><span>{{ $withheld['WTName'] }}</span></td>
                    <td><input id="withheldRate-{{ $index }}" class="form-control aliquota locked"
                        value="{{ number_format($withheld['Rate'], 5, '.', '') }}"
                        data-default-aliquota="{{ number_format($withheld['Rate'], 5, '.', '') }}" readonly></td>
                    <td><input id="withheldValue-{{ $index }}" class="form-control money"
                        onclick='destroyMask(event)'
                        onblur='updateValueWithheldTaxModal({{ $index }});focusBlur(event)'></td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                {{-- dados adicionados por javascript --}}
              </tfoot>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
          @if (!isset($head) || (isset($head) && empty($head->codSAP)))
            <button type="button" id="saveWithheldTax" class="btn btn-primary"
              onclick="saveWithheldTax(event)">Salvar</button>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if (isset($head))
    <div class="modal" id="reproveModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
            <h4 class="modal-title">Reprovar Pedido</h4>
          </div>

          <form id="reproveForm" action="{{ route('purchase.ap.invoice.reprove'), $head->id }}">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="col-md-12">
                    <label for="justify">Justificativa</label>
                    <textarea required="required" class="form-control" rows="5" name="justify"></textarea>
                    <input type="hidden" name="id_P" value="{{ $head->id }}">
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <div class="col-md-12">
                <div class="row">
                  <div class="col-md-6"></div>
                  <div class="col-md-3">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-danger" type="submit">Reprovar</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endsection
@section('scripts')
  {{-- <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script> --}}
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-bank')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('banks.get.all') }}"));
    selectpicker.filter('.with-ajax-suppliers')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    let withheld_taxes_items_cache = [];
    let advancePaymentsCache = [];


    @if (isset($advancePayments))
      @foreach ($advancePayments as $payment)
        advancePaymentsCache.push("{{ $payment['codSAP'] }}")
      @endforeach
    @endif

    @if (isset($withheld_taxes_items))
      let indice = 1;
      @foreach ($withheld_taxes_items as $index => $irfArray)
        @foreach ($irfArray as $irf)
          if (withheld_taxes_items_cache[indice]) {
            withheld_taxes_items_cache[indice].push({
              'WTCode': '{{ $irf->WTCode }}',
              'Rate': '{{ $irf->Rate }}',
              'Value': '{{ $irf->Value }}'
            });
          } else {
            withheld_taxes_items_cache[indice] = [{
              'WTCode': '{{ $irf->WTCode }}',
              'Rate': '{{ $irf->Rate }}',
              'Value': '{{ $irf->Value }}'
            }];

          }
        @endforeach
        indice++;
      @endforeach
    @endif

    $(document).ready(function() {
      @if (isset($head) && !empty($head->codSAP))

        //bloqueia campos da table das linhas
        $.each($(`#requiredTable tbody tr`), function(index, element) {
          $(element).addClass('locked');
          $(element).find('select').prop('disabled', true);
          $(element).find('input, select').addClass('locked');
        });

        //atualiza os selectpickers
        let selectspickersToDisable = $(`#requiredTable tbody`).find('.selectpicker');
        selectspickersToDisable.selectpicker('destroy');
        selectspickersToDisable.selectpicker(selectpickerConfig).selectpicker('render');
      @endif

      @if (!empty($head))
        setMaskMoney();
        sumAllValues();
        fillTopNav(@json($head->getTopNavData()));
      @else
        fillTopNav(@json($incoing_invoice_model->getTopNavData()))
      @endif

      @if (isset($head) && (!is_null($head->codSAP) || $head->status != 1))
        let inputs = $('body').find('input, select');
        @if ($head->status == $head::STATUS_OPEN)
          inputs = inputs.not("input[id*='installments-']");
          if ($('#conditionPagamentos').find(":selected").attr('data-num-installments') <= 1) {
            inputs = inputs.not('#dataVencimento');
          }
        @endif
        inputs.attr('readonly', true);
      @endif
    });


    function removeUpload(id, idRef) {
      swal({
          title: "Tem certeza que deseja excluir o arquivo?",
          text: "Esta operação não pode ser desfeita!",
          icon: "warning",
          //buttons: true,
          buttons: ["Não", "Sim"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            waitingDialog.show('Excluindo...')
            window.location.href = "{{ route('purchase.ap.invoice.remove.upload') }}/" + id + "/" + idRef;
          }
        });
    }

    function getDueDate() {
      var codition = $('#conditionPagamentos').val();
      var date = $('#dataDocumento').val();
      $.get("{{ route('home/get/dueDate') }}" + '/' + date + '/' + codition, function(items) {
        document.getElementById('dataVencimento').value = items;
      });
    }

    $('#withheldtax-modal').on('show.coreui.modal', event => {
      let totalIRF = 0;
      let itemId = $(event.relatedTarget).attr('value');
      let lineNum = $(event.relatedTarget).attr('data-linenum');
      let qtd = $('#qtd-' + lineNum).val();
      let preco = $('#price-' + lineNum).val();
      let valorBase = parseFloat(qtd.replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco.replace(/[.]/gi,
        '').replace(/[,]/gi, '.'));

      $('#withheldtax-modal').find('input[type="checkbox"]').prop('checked', false);
      $('#withheldtax-modal').find('input[id*="withheldValue-"]').val(0.00);

      $.each($('#withheldtax-modal').find('input[id*="withheldRate-"]'), function(index, value) {
        let withheldRateInput = $(value)
        withheldRateInput.val(withheldRateInput.attr('data-default-aliquota'))
      });

      if (withheld_taxes_items_cache[lineNum]) {
        $.each(withheld_taxes_items_cache[lineNum], function(index, value) {
          let checkbox = $(`#withheldtax-modal input[value='${value.WTCode}']`);

          if (checkbox) {
            let checkboxLineNum = checkbox.attr("data-linenum");

            $(`#withheldRate-${checkboxLineNum}`).val(value.Rate);
            $(`#withheldValue-${checkboxLineNum}`).val(value.Value);

            checkbox.prop('checked', true)
          }

          totalIRF += (parseFloat(value.Rate) / 100) * parseFloat(valorBase)
        });


        $('#withheldTotalIRF').val(totalIRF.toFixed(2));

        $('#withheldtax-modal').find('span[data-valor-retido]').text(totalIRF.toLocaleString('pt-BR', {
          minimumFractionDigits: 2
        }));

      }
      $('#withheldValorBase').val(valorBase);
      $('#withheldtax-modal').find('span[data-valor-base]').text(valorBase.toLocaleString('pt-BR', {
        minimumFractionDigits: 2
      }));
      $('#saveWithheldTax').attr('value', lineNum);
      setMaskMoney();
    })

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
      $('.qtd').maskMoney({
        precision: 3,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $('.money').maskMoney({
        precision: 2,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $('.moneyPlus').maskMoney({
        precision: 4,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $('.aliquota').maskMoney({
        precision: 5,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"], input[class *="aliquota"]'),
        function(index, value) {
          $(value).trigger('mask.maskMoney')
        })
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    function loadingItems() {

      if ($('#cardCode').val().length <= 0) {
        swal('Opss!', 'Informe o Parceiro de Negócios!', 'error');
        return;
      }

      $('#table').dataTable().fnDestroy();
      $("#itensModal").modal('show');
      let table = $("#table").DataTable({
        processing: true,
        responsive: true,
        ajax: {
          url: "{{ route('purchase.ap.invoice.getproductssap') }}",
          data: function(d) {
            // d.myKey = '01';
            // d.isNFS = '1';
            // d.purchase = true;

          }
        },
        columns: [{
            name: 'ItemCode',
            data: 'ItemCode'
          },
          {
            name: 'ItemName',
            data: 'ItemName'
          },
          // {name: 'OnHand', data: 'ItemCode', render: renderWHS, orderable: false},
          {
            name: 'edit',
            data: 'ItemCode',
            render: renderEditButton,
            orderable: false
          }
        ],
        lengthMenu: [5, 30, 50],
        language: dataTablesPtBr
      });

    }

    function renderEditButton(code) {
      return `<center>
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");' @if (isset($head->codSAP)) style="display: none;" @endif>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    function renderWHS(valor) {
      return `<center>
                      <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS" @if (isset($head->codSAP)) style="display: none;" @endif>
                        <svg class="icon icon-xl">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                        </svg>
                      </a>
                    </center>`
    }

    function openModalWHS(itemCode) {
      $('#tableWHS').dataTable().fnDestroy();

      $("#tableWHS").DataTable({
        searching: false,
        ajax: {
          url: "{{ route('inventory.get.whs') }}" + '/' + itemCode
        },
        columns: [{
            name: 'WhsCode',
            data: 'WhsCode'
          },
          {
            name: 'WhsName',
            data: 'WhsName'
          },
          {
            name: 'edit',
            data: 'OnHand',
            render: renderWHS,
            orderable: false
          }
        ],
        lengthMenu: [5, 15, 30],
        language: dataTablesPtBr
      });
    }

    // function valideTotalCC(cc_total) {
    //   var total = document.getElementById('totalNota').value;
    //   var adiantamento = cc_total.value;
    //   if (total.substring(0, 1) == 'R') {
    //     total = total.substring(2, total.length);
    //   }
    //   if (adiantamento.substring(0, 1) == 'R') {
    //     adiantamento = adiantamento.substring(2, adiantamento.length);
    //   }

    //   if (parseFloat(total) < parseFloat(adiantamento)) {
    //     swal('Opss!', 'O valor do adiantamento não pode ser maior que o valor da nota!', 'error');
    //     cc_total.value = 0;
    //   }
    // }

    function keyShowModel(number) {
      if (number == 118) {
        $('#itensModal').modal('show');
      }
    }

    var aux = 0;

    function valide(button) {
      var codPN = $('#cardCode').val();

      if (codPN == '') {
        alert('Adicione um parceiro');
      } else {
        $('input, select').attr('disabled', false);
        $(button).attr('type', 'submit');
      }

    }

    <?php $aux = true; ?>

    function renderEditButton(code) {
      return `<center>
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");' @if (isset($head->codSAP)) style="display: none;" @endif>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    var used = [];
    var index =
      @if (isset($body))
        {{ $cont }}
      @else
        1
      @endif ;

    function loadTable(code) {
      var table = $('#requiredTable');
      if (index == 1) {
        $('#requiredTable tbody > tr').remove();
      }
      var tr = $("<tr id='rowTable-" + index + "'>");
      tr.append($('<td>' + index + '</td>'));

      $.get('/getProductsSAP/' + code, function(items) {

        for (var i = 0; i < items.length; i++) {
          let price = parseFloat(items[i].AvgPrice).toFixed(4);
          var whs = items[i].DfltWH;
          tr.append($(`<td style="max-width: 16em;">
                                    <div class="d-flex flex-row" style="max-width: 100%;">
                                        <a class="text-warning" href="{{ route('inventory.items.edit') }}/${items[i].ItemCode}" target="_blank">
                                            <svg class="icon icon-lg">
                                                <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                            </svg>
                                            </a>
                                        <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${items[i].ItemCode} - ${items[i].ItemName}">${items[i].ItemCode} - ${items[i].ItemName}</span>
                                    </div>
                                    <input type='hidden' value='${items[i].ItemName}' name='requiredProducts[${index}][itemName]' >
                                    <input type='hidden' id="itemCode-${index}" value='${items[i].ItemCode}' name='requiredProducts[${index}][codSAP]' >
                                </td>`));

          tr.append($(
            `<td >
              <div class="input-group min-200 mt-1">
                <div class="input-group-text">${items[i].BuyUnitMsr || " "}: ${renderFormatedQuantity(items[i].NumInBuy)}</div>
                <input onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(${index});focusBlur(event)' id='qtd-${index}' type='text' class='form-control qtd min-100' name='requiredProducts[${index}][qtd]'>
                <input type='hidden' value='${items[i].BuyUnitMsr}' name='requiredProducts[${index}][itemUnd]'> 
              </div>
            </td>`
          ));
          tr.append($("<td ><input onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" + index +
            ");focusBlur(event)' id='price-" + index + "' value='" + price.replace('.', ',') +
            "'type='text' class='form-control moneyPlus min-100' name='requiredProducts[" + index +
            "][preco]' min='1'></td>"));
          tr.append($("<td ><input onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" + index +
            ");focusBlur(event)' class='form-control money min-100 locked' id='totalLinha-" + index +
            "' type='text' readonly></td>"));
          tr.append($(`<td>
                        <a class="btn btn-warning" data-coreui-toggle="modal" data-coreui-target="#withheldtax-modal" data-linenum="${index}">
                            <svg class="icon">
                                <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                            </svg>
                        </a>
                        <span id="withheld-tax-${index}">0,00</span>
                    </td>`));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='use-" + index + "' name='requiredProducts[" + index +
            "][use]' data-container='body' required > <option value=''>Selecione</option> @foreach ($use as $keys => $values) <option value='{{ $values['code'] }}' @if ($values['code'] == '33') selected @endif>{{ $values['code'] }} - {{ $values['utilizacao'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='project-" + index + "' name='requiredProducts[" +
            index +
            "][projeto]' data-container='body' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}' @if ($values['value'] == 'SEM PROJETOS') selected @endif>{{ $values['value'] }} - {{ substr($values['name'], 0, 10) }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($(`<td>
                        <select class='form-control selectpicker' id='role-${index}' name='requiredProducts[${index}][costCenter]' data-container='body' onchange='validLine(${index});' required> 
                          <option value=''>Selecione</option> 
                          @foreach ($centroCusto as $keys => $values) 
                            <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> 
                          @endForeach
                        </select>
                      </td>`));

          tr.append($(`<td>
                <select class='form-control selectpicker' id='role2-${index}' name='requiredProducts[${index}][costCenter2]' data-container='body'> 
                  <option value=''>Selecione</option> 
                  @foreach ($centroCusto2 as $keys => $values) 
                    <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option>
                  @endForeach
                </select>
              </td>`));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='cfop-" + index + "' name='requiredProducts[" + index +
            "][cfop]' data-container='body' required > <option value=''>Selecione</option> @foreach ($cfop as $keys => $values) <option value='{{ $values['value'] }}' @if ($values['value'] == '1933') selected @endif>{{ $values['value'] }} - {{ compressText($values['name'], 15) }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='taxCode-" + index + "' name='requiredProducts[" +
            index +
            "][taxCode]' data-container='body' > <option value=''>Selecione</option> @foreach ($tax as $keys => $values) <option value='{{ $values->value }}' @if ($values->value == 'IATE004') selected @endif>{{ $values->value }} - {{ $values->name }}</option> @endForeach</select>" +
            "</td>"));

          tr.append($(`<td>
              <select id="accounting_account-${index}" class='form-control selectpicker' name='requiredProducts[${index}][accounting_account]' data-container='body' required>
                <option value=''>Selecione</option> 
                @foreach ($budgetAccountingAccounts as $keys => $budgetAccountingAccount) 
                    <option value='{{ $budgetAccountingAccount['value'] }}'>{{ $budgetAccountingAccount['value'] }} - {{ $budgetAccountingAccount['name'] }}</option>
                  @endForeach
              </select>
            </td>`));

          let selectContract = $(`<select class='form-control selectpicker' id='contract-${index}' name='requiredProducts[${index}][contract]' data-container='body' disabled>
                                  <option value=''>Selecione</option>
                              </select>`);

          $.each(contracts, (index, value) => {
            if ($('#contractGlobal').val().length > 0 && $('#contractGlobal').val() == value.code) {
              selectContract.append($(
                `<option value='${value.code}' selected>${value.code} - ${value.contractNumber}</option>`));
            } else {
              selectContract.append($(
                `<option value='${value.code}'>${value.code} - ${value.contractNumber}</option>`));
            }
          });

          tr.append($(`<td></td>`).append(selectContract));

          tr.append($(`<td id='itemTable-${index}' class='text-center'>
                          <a class="text-danger text-tooltip" title="Remover linha" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
                            <svg class="icon icon-xl">
                              <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                            </svg>
                          </a>
                        </td>`));
        }
        setTimeout(function() {
          setMaskMoney();

        }, 0500);

        table.find('tbody').append(tr);
        //gerarTotal(index);
        $('.selectpicker').selectpicker(selectpickerConfig);
        $('.text-truncate').tooltip();

        setTimeout(function() {
          index++;
        }, 0500);

      });
    }

    let contracts =
      @if (isset($head))
        @json($contracts)
      @else
        []
      @endif ;

    function getPartnerContracts(cardCode) {

      if (cardCode) {
        $.get(`{{ route('partners.get.contracts') }}/${$('#cardCode').val()}`, function(items) {

          $('#contractGlobal, select[id*="contract-"]').selectpicker('deselectAll');
          $('#contractGlobal, select[id*="contract-"]').find('option').not(':first').remove();

          contracts = [];
          $.each(items, function(ind, value) {
            $('#contractGlobal').append(
              `<option value='${value.code}'>${value.code} - ${value.contractNumber}</option>`);
            contracts.push({
              code: value.code,
              contractNumber: value.contractNumber,
              residualAmount: value.residualAmount
            })
          });

          $(`select[id*="contract-"], #contractGlobal`).selectpicker('destroy');
          $(`select[id*="contract-"], #contractGlobal`).selectpicker(selectpickerConfig).selectpicker('render');
        });
      }
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
      $(event.target).select()
    }

    function replaceValueToMask(event) {
      $(event.target).val($(event.target).val() + '.')
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

      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = parseFloat(qtd.replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco.replace(/[.]/gi, '')
        .replace(/[,]/gi, '.'));
      let totalIRF = 0.00;

      if (code in withheld_taxes_items_cache) {
        $.each(withheld_taxes_items_cache[code], function(index, value) {
          totalIRF += parseFloat((value['Value'] / parseFloat(total)) * 100) / 100 * parseFloat(total)
        })
      }

      if (!isNaN(total)) {
        $('#withheld-tax-' + code).text(roundNumber(totalIRF).format(2, ",", "."))
        document.getElementById('totalLinha-' + code).value = (roundNumber(total - totalIRF)).format(2, ",", ".");

        setTimeout(function() {
          sumAllValues();
        }, 0500);
      }
      setMaskMoney();
    }


    function sumAllValues() {
      var total = 0;
      var total_withheld_taxes = 0;
      var advance_payments = document.getElementById('valor_adiantado').value;

      advance_payments = parseFloat(advance_payments.replace(/[.]/gi, '').replace(/[,]/gi, '.'));

      var i;
      var x = 0;
      for (i = 1; i < index; i++) {
        if ($('#totalLinha-' + i).length) {
          x = $('#totalLinha-' + i).val();
          total += parseFloat(x.replace(/[.]/gi, '').replace(/[,]/gi, '.'));
        }
      }

      for (i = 1; i < index; i++) {
        if ($("#withheld-tax-" + i).length) {
          x = $('#withheld-tax-' + i).text();
          total_withheld_taxes += parseFloat(x.replace(/[.]/gi, '').replace(/[,]/gi, '.'));
        }
      }
      if (isNaN(advance_payments)) {
        advance_payments = 0;
      }
      if (isNaN(total)) {
        total = 0;
      }


      totalImposto = $('#impostos_r').val();
      total_a_pagar = roundNumber(total - (totalImposto.replace(/[.]/gi, '').replace(/[,]/gi, '.')));
      var desconto = 0;
      var x;
      var i;
      $('#total_a_pagar').val(total_a_pagar.format(2, ",", "."));
      $('#total_imposto_retido').val(total_withheld_taxes.format(2, ",", "."));
      $('#total_a_pagar2').val(total_a_pagar.format(2, ",", "."));

      $('#totalHeader').text(($("#total_a_pagar").value));
      document.getElementById('totalNota').value = (parseFloat(Math.round((total + total_withheld_taxes) * 100) / 100))
        .format(2, ",", ".");
      app.total = total;

      $('#total_a_pagar').val((total_a_pagar - advance_payments).format(2, ",", "."));

      if (isNaN(total_a_pagar)) {
        $('#total_a_pagar').val('0,00');
        $('#total_a_pagar2').val('0,00');
      }
    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.remove();

      setTimeout(function() {
        sumAllValues();
      }, 500);
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

    $('#accounting_account_global').on('change', function(event) {

      setTimeout(() => {

        let val = $('#accounting_account_global').val();
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('accounting_account-' + i)) {
            document.getElementById('accounting_account-' + i).value = val;
          }
        }

        $(`select[id*='accounting_account-']`).selectpicker('destroy');
        $(`select[id*='accounting_account-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
    });

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
      setTimeout(() => {

        let val = $('#projectGlobal').val();
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('project-' + i)) {
            document.getElementById('project-' + i).value = val;
          }
        }

        $(`select[id*='project-']`).selectpicker('destroy');
        $(`select[id*='project-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);

    }

    function setRoleFull() {
      var val = $('#roleGlobal').val();

      let constCentersThatAllowCenterCost2 = [
        '1.0',
        '2.1',
        '2.2',
        '2.3',
        '2.4'
      ];

      if (constCentersThatAllowCenterCost2.includes(val)) {
        $(`select[id*='role2-']`).removeClass('disabled');
        $(`#roleGlobal2`).removeClass('disabled');
      } else {
        $(`select[id*='role2-']`).addClass('disabled');
        $(`#roleGlobal2`).addClass('disabled');
      }

      if (val == '1.0') {
        $(`select[id*='role2-']`).attr('required', true)
      } else {
        $(`select[id*='role2-']`).attr('required', false)
      }

      $.each($(`select[id*='role-']`), function(index, element) {
        if ($(element)) {
          $(element).val(val);
        }
      });

      $(`select[id*='role-'], select[id*='role2-'], #roleGlobal2`).selectpicker('destroy');
      $(`select[id*='role-'], select[id*='role2-'], #roleGlobal2`).selectpicker(selectpickerConfig)
        .selectpicker('render');

    }

    function setRoleFull2() {
      var val = $('#roleGlobal2').val();

      setTimeout(() => {
        $(`select[id*='role2-']`).removeClass('disabled');
        $(`select[id*='role2-']`).attr('required', true)
        $(`#roleGlobal2`).removeClass('disabled');

        $.each($(`select[id*='role2-']`), function(index, element) {
          if ($(element)) {
            $(element).val(val);
          }
        });

        $(`select[id*='role2-'], #roleGlobal2`).selectpicker('destroy');
        $(`select[id*='role2-'], #roleGlobal2`).selectpicker(selectpickerConfig).selectpicker('render');

      }, 600);
    }

    function setContractFull() {
      setTimeout(() => {

        let val = $('#contractGlobal').val();
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('contract-' + i)) {
            document.getElementById('contract-' + i).value = val;
          }
        }

        $(`select[id*='contract-']`).selectpicker('destroy');
        $(`select[id*='contract-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
    }

    function getPartnersComplements(cardCode) {
      if (cardCode) {
        $.get(`{{ route('partners.get.partner') }}/${cardCode}`, function(items) {
          if (Object.keys(items).length !== 0) {
            $('#cnpj').val((items.TaxId0 || items.TaxId4));
          }
        });
      }
    }

    /*use in modal*/
    // function addPayment() {
    //   var modal = $('#paymentModal');
    //   var form = $('#form');
    //   var totalMoney = document.getElementById('total_dinheiro').value;
    //   var conta_dinheiro = $("#conta_dinheiro option:selected").val();
    //   var dt_transferencia = document.getElementById('dt_transferencia').value;
    //   var totalTransfer = document.getElementById('total_transfrencia').value;
    //   var referencia_transferencia = document.getElementById('referencia_transferencia').value;
    //   var conta_transferencia = $("#conta_transferencia option:selected").val();
    //   @if (false)
    //     var name_cartao = $("#name_cartao option:selected").val();
    //     var totalCard = document.getElementById('total_credito').value;
    //     var parcelas_cartao = document.getElementById('parcelas_cartao').value;
    //     var totalOther = document.getElementById('total_outros').value;
    //     var conta_outros = $("#conta_outros option:selected").val();
    //     var conta_cartao = $("#conta_cartao option:selected").val();

    //     var conta_cheque = $("#conta_cheque option:selected").val();
    //     var dt_vencimento_cheque = document.getElementById('dt_vencimento_cheque').value;
    //     var valor_cheque = document.getElementById('valor_cheque').value;
    //     var nome_banco_cheque = $("#nome_banco_cheque option:selected").val();
    //     var filial_cheque = document.getElementById('filial_cheque').value;
    //     var numero_conta_cheque = document.getElementById('numero_conta_cheque').value;
    //     var numero_cheque = document.getElementById('numero_cheque').value;
    //     var endosso_cheque = $("#endosso_cheque option:selected").val();
    //   @endif

    //   form.append($('<td><input type="hidden" name="payment[total_dinheiro]" value="' + totalMoney.trim() + '">'));
    //   form.append($('<td><input type="hidden" name="payment[conta_dinheiro]" value="' + conta_dinheiro.trim() + '">'));
    //   form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() +
    //     '">'));
    //   form.append($('<td><input type="hidden" name="payment[total_transfrencia]" value="' + totalTransfer.trim() + '">'));
    //   form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia
    //     .trim() + '">'));
    //   form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() +
    //     '">'));
    //   @if (false)
    //     form.append($('<td><input type="hidden" name="payment[name_cartao]" value="' + name_cartao.trim() + '">'));
    //     form.append($('<td><input type="hidden" name="payment[total_credito]" value="' + totalCard.trim() + '">'));
    //     form.append($('<td><input type="hidden" name="payment[parcelas_cartao]" value="' + parcelas_cartao.trim() +
    //       '">'));
    //     form.append($('<td><input type="hidden" name="payment[total_outros]" value="' + totalOther.trim() + '">'));
    //     form.append($('<td><input type="hidden" name="payment[conta_outros]" value="' + conta_outros.trim() + '">'));
    //     form.append($('<td><input type="hidden" name="payment[conta_cartao]" value="' + conta_cartao.trim() + '">'));

    //     form.append($('<td><input type="hidden" name="payment[conta_cheque]" value="' + conta_cheque + '">'));
    //     form.append($('<td><input type="hidden" name="payment[dt_vencimento_cheque]" value="' + dt_vencimento_cheque +
    //       '">'));
    //     form.append($('<td><input type="hidden" name="payment[valor_cheque]" value="' + valor_cheque + '">'));
    //     form.append($('<td><input type="hidden" name="payment[nome_banco_cheque]" value="' + nome_banco_cheque + '">'));
    //     form.append($('<td><input type="hidden" name="payment[filial_cheque]" value="' + filial_cheque + '">'));
    //     form.append($('<td><input type="hidden" name="payment[numero_conta_cheque]" value="' + numero_conta_cheque +
    //       '">'));
    //     form.append($('<td><input type="hidden" name="payment[numero_cheque]" value="' + numero_cheque + '">'));
    //     form.append($('<td><input type="hidden" name="payment[endosso_cheque]" value="' + endosso_cheque + '">'));
    //   @endif
    //   modal.modal('hide');
    //   return false;
    // }

    @if (isset($head))
      function cancel(event) {
        event.preventDefault();
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
              waitingDialog.show('Cancelando...');
              window.location.href = "{{ route('purchase.ap.invoice.canceled', $head->id) }}";
            }
          });
      }

      function duplicate() {
        swal({
            title: "Tem certeza que deseja duplicar?",
            text: "Esta operação não pode ser desfeita!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willDuplicate) => {
            if (willDuplicate) {
              waitingDialog.show('Duplicando...');
              window.location.href = "{{ route('purchase.ap.invoice.duplicate', $head->id) }}";
            }
          });
      }
    @endif


    function validateTabs() {
      var input = $("#needs-validation").find("*:invalid").first();
      var tabPane = input.closest('.tab-pane').first();
      if (tabPane.length === 1) {
        $('#myTabs').find('a[href="#' + tabPane.attr('id') + '"]').tab('show');
      }

      var codPN = $('#cardCode').val();
      if (parseFloat($('#total_a_pagar').val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) < 0 && $('#valor_adiantado').val() == 0) {
        swal({
          title: "Opss...",
          text: "O valor do campo 'Total a pagar' não pode ser negativo!",
          icon: "error",
          buttons: ["Fechar"],
        })
        return;
      }

      if (codPN == '') {
        alert('Adicione um parceiro');
      } else {
        $('#btn-save').attr('type', 'submit');
      }

    }

    @if ((isset($head) && is_null($head->codSAP)) || !isset($head))
      $('#advancePaymentsModal').on('show.coreui.modal', event => {
        let cardCode = $('#cardCode');
        if (cardCode.val()) {
          $.ajax({
            type: 'get',
            url: "{{ route('purchase.ap.invoice.getadvancepayments') }}",
            data: {
              'cardCode': cardCode.val()
            },
            dataType: 'json',
            success: function(response) {
              let tbody = $('#advancePaymentsModal #advancePaymentsTable tbody');
              tbody.empty()
              let exists = false;
              if (response.status === 'success') {
                $.each(response.adPayments, function(index, value) {
                  let tr = $('<tr></tr>')
                  tr.append(
                    `<td><input type='checkbox' value="${value.DocNum}" name="advancePayments[]" ${Object.values(advancePaymentsCache).includes(value.DocNum) ? 'checked' : null}></td>`
                  );
                  tr.append(`<td>${value.DocNum}</td>`)
                  tr.append(`<td>${value.Comments}</td>`)
                  tr.append(`<td>${new Date(value.DocDate).toLocaleDateString('pt-BR')}</td>`)
                  tr.append(`<td>${Number(value.DocTotal).format(2, ',', '.')}</td>`)
                  tr.append(`<td>${Number(value.DpmAppl).format(2, ',', '.')}</td>`)
                  tbody.append(tr)

                });
              } else if (response.status === 'error') {

                event.preventDefault();
                swal({
                  title: "Opss...",
                  text: response.message,
                  icon: "info",
                  buttons: ["Fechar"],
                })
              }
              return
            },
            error: function(response) {
              event.preventDefault();
              swal({
                title: "Opss...",
                text: "Ocorreu um erro, tente novamente. " + response.message,
                icon: "error",
                buttons: ["Fechar"],
              })
            }
          });
        } else {

          event.preventDefault();
          swal({
            title: "INFO",
            text: "É necessário selecionar um fornecedor antes de prosseguir!",
            icon: "error",
            buttons: ["Fechar"],
          })

        }
        waitingDialog.hide();
      })
    @endif

    function saveAdvancePayments(event) {
      advancePaymentsCache.splice(0, advancePaymentsCache.length)
      let total_advance_payments = 0;
      $.each($('input[name*="advancePayments"]:checked'), function(index, value) {
        if (!advancePaymentsCache.includes($(value).val())) {
          advancePaymentsCache.push(`${$(value).val()}`)
          let doc_total = parseFloat($(value).parent().parent().find('td').eq(4).text().replace(/[.]/gi, '').replace(
            /[,]/gi, '.'))
          let dpm_appl = parseFloat($(value).parent().parent().find('td').eq(5).text().replace(/[.]/gi, '').replace(
            /[,]/gi, '.'))
          total_advance_payments += doc_total - dpm_appl
        }
      });
      $('#valor_adiantado').val(total_advance_payments.format(2, ",", "."));
      sumAllValues();
    }

    $('#needs-validation').submit(function(event) {
      event.preventDefault();
      let erros = new Array();

      let countContracts = 0;
      let lines = 0;
      let contractCode = null;
      let totalDoc = parseFloat($('input[name="docTotal"]').val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
      $('[disabled]').removeAttr('disabled');

      $.each($('select[id*="contract-"]'), function(index, value) {
        if ($(value).val().length > 0) {
          countContracts++;
          if (contractCode === null) {
            contractCode = $(value).val()
          }
        }

        lines++;
        if (contractCode !== null && contractCode != $(value).val()) {
          erros.push('Os contratos não devem ser diferentes! \n');
          return false;
        }
      });

      $.each($('#requiredTable tbody').find('select[id*="accounting_account-"]'), function(index, value) {
        let lineNum = $(value).attr("data-linenum");

        let itemCode = $(`#itemCode-${lineNum}`).val();

        if($(value).val().length <= 0 && itemCode.includes("AF") == false){
          erros.push(`Adicione uma conta contábil no item ${index+1}\n`);
        }
      });

      let contractData = contracts[contracts.findIndex(x => x.code === contractCode)];

      if (countContracts > 0) {

        if (lines != countContracts) {
          erros.push(
            'Ao selecionar um contrato, todas as demais linhas do documento devem conter contrato selecionado! \n');
        }

        if (totalDoc > parseFloat(contractData.residualAmount)) {
          erros.push('O valor total do documento não deve exceder o valor residual atual do contrato! \n');
        }
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
        return false;
      }

      waitingDialog.show('Processando...');

      $.each(withheld_taxes_items_cache, function(idLineItem, item) {
        let element = $('#requiredTable tbody #rowTable-' + idLineItem).find('td').eq(6);
        element.find('input').remove();
        if (idLineItem != 0) {
          $.each(item, function(index, value) {
            element.append($('<input type="hidden" value="' + value.WTCode + '" name="requiredProducts[' +
              idLineItem + '][withheldTaxes][' + index + '][WTCode]">'));
            element.append($('<input type="hidden" value="' + value.Rate + '" name="requiredProducts[' +
              idLineItem + '][withheldTaxes][' + index + '][Rate]">'));
            element.append($('<input type="hidden" value="' + value.Value + '" name="requiredProducts[' +
              idLineItem + '][withheldTaxes][' + index + '][Value]">'));
          });
        }
      });

      let form = $('#needs-validation').serialize();
      var form_data = new FormData($('#needs-validation')[0]);

      $.each(advancePaymentsCache, function(index, value) {
        form_data.append('advancePayments[][codSAP]', value)
      });


      $.ajax({
        type: 'post',
        url: "{{ route('purchase.ap.invoice.save') }}",
        headers: {
          'X-CSRF-TOKEN': $('body input[name="_token"]').val()
        },
        data: form_data,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(response) {
          waitingDialog.hide();
          if (response.status == 'success') {
            window.location.href = `{{ route('purchase.ap.invoice.read') }}/${response.invoiceId}`;
          } else {

            waitingDialog.hide();
            swal({
              title: "Opss...",
              text: response.message,
              icon: "error",
              buttons: ["Fechar"],
            })
          }
        },
        error: function(response) {

          waitingDialog.hide();
          swal({
            title: "Opss...",
            text: "Ocorreu um erro, tente novamente." + response.message,
            icon: "error",
            buttons: ["Fechar"],
          })
        }
      });
    });


    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("Processando...");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'incoing_invoices')
      formData.append('id', $('input[name="id"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('purchase.ap.invoice.updateUploads') }}",
          data: formData,
          dataType: 'json',
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('body input[name="_token"]').val()
          }
        })
        .always(function(response) {
          waitingDialog.hide();
          swal({
              title: "Processando",
              text: "Documento sendo salvo. Deseja atualizar a página?",
              icon: "info",
              //buttons: true,
              buttons: ["Não", "Sim"],
              dangerMode: true,
            })
            .then((refresh) => {
              if (refresh) {
                document.location.reload(true);
              }
            });
        });
    }

    function validLine(code) {
      var select = document.getElementById('role-' + code).value;
      let constCentersThatAllowCenterCost2 = [
        '1.0',
        '2.1',
        '2.2',
        '2.3',
        '2.4'
      ];

      if (constCentersThatAllowCenterCost2.includes(select)) {
        $('#role2-' + code).removeClass('disabled');
      } else {
        $('#role2-' + code).addClass('disabled');
      }

      if (select == '1.0') {
        $(`select[id*='role2-']`).attr('required', true)
      } else {
        $(`select[id*='role2-']`).attr('required', false)
      }

      $('#role2-' + code).selectpicker('destroy')
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render');
    }

    function saveWithheldTax(event) {
      const lineItemNum = $(event.target).attr('value');
      let qtd = document.getElementById('qtd-' + lineItemNum).value;
      let preco = document.getElementById('price-' + lineItemNum).value;
      let valorBase = parseFloat($('#withheldValorBase').val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
      let totalIRF = 0;

      if (withheld_taxes_items_cache[lineItemNum] && Object.keys(withheld_taxes_items_cache[lineItemNum]).length > 0) {
        delete withheld_taxes_items_cache[lineItemNum]
      }

      $.each($('#withheldtax-modal input[id*="withheldCheckbox-"]:checked'), function(index, value) {

        let lineWithheldNum = $(value).attr('data-linenum');

        if (valorBase <= 0) {
          $(`#withheldValue-${lineWithheldNum}`).val(0);
          return
        }

        let valorRetido = $(`#withheldValue-${lineWithheldNum}`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.');
        let rate = roundNumber((valorRetido / parseFloat(valorBase)) * 100);

        if (withheld_taxes_items_cache[lineItemNum]) {
          withheld_taxes_items_cache[lineItemNum].push({
            'WTCode': $(value).attr('value'),
            'Rate': rate,
            'Value': valorRetido
          });
        } else {
          withheld_taxes_items_cache[lineItemNum] = [{
            'WTCode': $(value).attr('value'),
            'Rate': rate,
            'Value': valorRetido
          }];
        }
        totalIRF += parseFloat(roundNumber(valorRetido));
      });


      $('#withheld-tax-' + lineItemNum).text(roundNumber(totalIRF).format(2, ",", "."))
      gerarTotal(lineItemNum);
      $('#withheldtax-modal').modal('hide');
    }

    function updateValueWithheldTaxModal(id) {
      let valorBase = parseFloat($('#withheldValorBase').val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
      let totalIRF = 0;

      $.each($('#withheldtax-modal input[id*="withheldCheckbox-"]:checked'), function(index, value) {

        let lineNum = $(value).attr('data-linenum');
        let withheldRateElement = $(`#withheldRate-${lineNum}`);

        if (valorBase <= 0) {
          swal('Opss!', 'O valor base não pode ser menor que 0!', 'error');
          $(`#withheldValue-${lineNum}`).val(0);
          return
        }

        let valorRetido = $(`#withheldValue-${lineNum}`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.');
        let rate = roundNumber((valorRetido / parseFloat(valorBase)) * 100);

        if (valorRetido <= 0) {
          rate = withheldRateElement.attr("data-default-aliquota");
        }

        withheldRateElement.val(rate);
        totalIRF += parseFloat(roundNumber(valorRetido));
      });

      $('#withheldTotalIRF').val(totalIRF.toFixed(2));
      setMaskMoney();
    }

    @if (isset($head))
      function aprovar(idP) {
        swal({
            title: "Deseja aprovar a NFE?",
            text: "Essa ação não pode ser desfeita!",
            type: "warning",
            buttons: ['Não', 'Sim'],
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Aprovando...')
              window.location.href = "{{ route('purchase.ap.invoice.approve', $head->id) }}";
            }
          });
      }
    @endif

    // function getBudgetInfo(itemCode, whsCode, event) {
    //   let cost_center_code = $(event.target).val();

    //   if(cost_center_code == '1.0'){
    //     return false;
    //   }

    //   $.ajax({
    //       type: 'GET',
    //       data: {
    //           itemCode: itemCode,
    //           whsCode: whsCode,
    //           cost_center_code: cost_center_code
    //       },
    //       success: function(response) {
    //         console.log(response);
    //         budget_data = response;
    //         if (budget_data && (parseFloat(budget_data.PORCENTAGE) >= 70 && parseFloat(budget_data.PORCENTAGE) <= 100)) {
    //           let porcentage = parseFloat(budget_data.PORCENTAGE).format(2, ",", ".")
    //           swal({
    //             title: "Orçamento",
    //             text: `O nível do orçamento para o centro de custo selecionado está com ${porcentage}%.\n
    //                     Caso o nível chegue a 0%, será gerada uma solicitação de aprovação para o documento`,
    //             icon: "info",
    //             dangerMode: true,
    //           })
    //         }
    //       },
    //   });
    // }
  </script>
  <script>
    let app = new Vue({
      el: '#app',
      data: {
        paymentConditions: @json($paymentConditions),
        installments: @json($head->installments ?? []),
        total: 0
      },
      computed: {
        installmentsTotal() {
          return _.sumBy(this.installments, 'value');
        },

      },
      methods: {
        changePaymentCondition(value) {
          let paymentCondition = _.find(this.paymentConditions, {
            GroupNum: value
          });
          let qty = Math.max(parseInt(paymentCondition.InstNum), 1);
          this.installments = this.installments.slice(0, qty);
          while (this.installments.length < qty) {
            this.installments.push({});
          }
        },
        totalEqual() {
          return this.installmentsTotal.toFixed(4) === parseFloat($('#totalNota').val()).toFixed(4)
        }
      },
      mounted() {
        let paymentCondition = $('#conditionPagamentos').val();
        if (!this.installments.length && paymentCondition) {
          this.changePaymentCondition(paymentCondition);
        }
      }
    })
  </script>
@endsection
