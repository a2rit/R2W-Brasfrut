@extends('layouts.main')
@section('title', 'Nota Fiscal de Entrada')
@section('content')

  <form id="needs-validation" onkeydown='keyShowModel(event.keyCode)' enctype="multipart/form-data">
    {!! csrf_field() !!}

    <div class="card">
      <h5 class="card-header fw-bolder">Nota fiscal de entrada - cadastro</h5>
      <div class="card-body">
        <input type="hidden" name="idPurchaseOrder" value="{{ $head->id }}">
          <div class="row">
            <div class="col-md-3">
              <label>Usuario</label>
              <input type="text" value="{{ Auth::user()->name }}" class="form-control locked" readonly>
            </div>
          </div>
        <div class="row mt-2">
          <div class="col-md-6">
            <label for="cardCode">Parceiro de negócios</label>
            <select id="cardCode" class="form-control selectpicker with-ajax-suppliers" data-size="10" data-width="100%"
              name="cardCode" onchange="getPartnerContracts($(this).val()); getPartnersComplements($(this).val());" disabled>
                <option value="{{ $head->cardCode }}" selected>{{ getProviderData($head->cardCode)['CardName'] }}</option>
            </select>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-3">
            <div class="form-group">
              <label>Data do Documento</label>
              <input type="date" name="dataDocumento" id="dataDocumento" class="form-control"
                value="{{ $head->taxDate }}" required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Lançamento</label>
              <input type="date" name="dataLancamento" id="dataLancamento" class="form-control"
                value="{{ $head->docDate }}" required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Vencimento</label>
              <input type="date" name="dataVencimento" id="dataVencimento" class="form-control"
                value="{{ $head->docDueDate }}" id="dataVencimento" required>
            </div>
          </div>
        </div>
        <div class="row mt-1">
          <div class="col-md-4">
            <label>Condição de Pagamento</label>
            <select class="form-control selectpicker" id="conditionPagamentos"
              onchange="app.changePaymentCondition(this.value)" required name="condPagamentos" onChange="getDueDate();">
              <option value=''>Selecione</option>
              @foreach ($paymentConditions as $key => $value)
                <option value="{{ $value['GroupNum'] }}" @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                  {{ $value['PymntGroup'] }}</option>
              @endForeach
            </select>
          </div>
          <div class="col-md-3">
            <label>Contrato</label>
            <select class='form-control selectpicker' id='contractGlobal' name="contract"
              onchange="setContractFull()">
              <option value=''>Selecione</option>
              @if(isset($contracts))
                @foreach ($contracts as $keys => $contract)
                  <option value='{{ $contract->code }}' @if($head->contract == $contract->code) selected @endif>
                    {{ $contract->code }} - {{ $contract->contractNumber }}
                  </option>
                @endForeach
              @endif
            </select>
          </div>
        </div>
        <div class="row mt-3">
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
              <a href='#' class='text-dark' onclick="loadingItems()">
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
                    @if (!empty($body))
                      <?php
                      $cont = 1;
                      ?>
                      @foreach ($body as $key => $value)
                        <tr id='rowTable-{{ $cont }}'>
                          <td>
                            {{ $cont }}
                            <input type="hidden" name="requiredProducts[{{ $cont }}][idItemPurchaseOrder]" value="{{ $value['id'] }}">
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
                            <a class='btn btn-warning' href='#' value="{{ $value['id'] }}" data-linenum="{{ $cont }}"
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
                              onchange="validLine({{ $cont }})" required>
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
                          <td id="itemTable-{{ $cont }}">
                            <a class="text-danger"
                              onclick="removeInArray('{{ $value['itemCode'] }}');removeLinha(this);" type="button">
                              <svg class="icon icon-xl">
                                <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                              </svg>
                            </a>
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
                      <select class='form-control selectpicker' name='role' id='roleGlobal' onchange="setRoleFull()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>C. de Custo Principal 2</label>
                      <select class='form-control selectpicker' name='role2' id='roleGlobal2' onchange="setRoleFull2()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto2 as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>CFOP Principal</label>
                      <select class="form-control selectpicker" name='cfop' id='cfopGlobal' onchange="setCFOPFull()">
                        <option value=''>Selecione</option>
                        @foreach ($cfop as $keys => $values)
                          <option value='{{ $values['value'] }}'> {{ $values['value'] }}
                            - {{ compressText($values['name'], 20) }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Código de Imposto</label>
                      <select class="form-control selectpicker" name='tax' id='taxCodeGlobal' onchange="setTaxFull()">
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
                        <select class="form-control selectpicker" required name="type_tax">
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
                        <input type="text" maxlength="9" class="form-control" required name="number_nf">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Serie</label>
                        <input type="text" maxlength="3" class="form-control" name="serie">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Modelo</label>
                        <select class="form-control selectpicker" required name="model" id="modelTax"
                          onChange="validModelTax()">
                          <option value=''>Selecione</option>
                          @foreach ($model as $key => $value)
                            <option value="{{ $value['value'] }}">{{ $value['name'] }}</option>
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
                    <label class="fw-bolder">Observações</label>
                    <textarea class="form-control" rows="5" name="obsevacoes" maxlength="200">
                      @if (isset($head->comments)){{ $head->comments }}@endif
                    </textarea>
                  </div>
                  <div class="col-md-12 form-group mt-3">
                    <label class="fw-bolder">Observações do diário</label>
                    <textarea class="form-control" rows="3" name="jrnlmemo" maxlength="50"></textarea>
                  </div>
                </div>
              </div>
              <div class="row mt-4 mt-2">
                <div class="col-md-6">
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
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-md-6">
                  <span class="input-group-btn w-100">
                    <!-- image-preview-input -->
                    <div class="btn btn-default image-preview-input w-100">
                      <span class="image-preview-input-title">Abrir</span>
                      <input class="form-control" type="file" multiple name="input-file-preview[]" />
                      <!-- rename it -->
                    </div>
                  </span>
                  <br>
                  <div class="col-md-12 mt-2 d-md-flex justify-content-md-end">
                    <a class="btn btn-danger image-preview-clear me-1" type="button" style="display:none;">
                      Limpar anexos
                    </a>
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
                value={{ number_format($head->total_a_pagar, 2, '.', '') }}
                readonly>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="total_a_pagar">Valor adiantado</label>
              <input type="text" id="valor_adiantado" name="valor_adiantado" readonly class="form-control locked">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="total_imposto_retido">IRF</label>
              <input type="text" id="total_imposto_retido" name="total_imposto_retido" readonly class="form-control locked">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="impostos_r">Imposto</label>
              <input type="text" id="impostos_r" class="form-control moneyPlus" name="impostos_r"
                onblur="sumAllValues()"
                value={{ number_format($head->impostos_r, 2, ',', '.') }}>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Total sem descontos</label>
              <input type="text" class="form-control locked" name="docTotal"
                value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                readonly id="totalNota">
              <input type="hidden" class="form-control"
                value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                id="docTotal">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12 mt-5">
      <button class="btn btn-primary float-end" type="button" id="btn-save"
        onclick="validateTabs()">Salvar</button>
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
            <button type="button" class="btn btn-sm btn-primary" onclick="saveAdvancePayments(event)"
              data-coreui-dismiss="modal">Salvar</button>
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

  <div class="modal inmodal" id="gastosExtras" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
              class="sr-only">Close</span></button>
          <h4 class="modal-title">Adicionar Despesas</h4>
        </div>
        <div class="modal-body">
          <form onsubmit="exPensesAdditional(); return false;" id="expensesForm">
            <input type="hidden" name="line">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tipo</label>
                  <select class="form-control selectpicker" required id="cPagamentos" name="condPagamentos">
                    @foreach ($typeOut as $key => $value)
                      <option value="{{ $value['code'] }}">{{ $value['value'] }}</option>
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
          <button type="button" form="expensesForm" class="btn btn-white"
            data-coreui-dismiss="modal">Cancelar</button>
          <button type="submit" form="expensesForm" class="btn btn-primary">Adicionar</button>
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
  <div class="modal inmodal" id="paymentModal" tabindex="-1" role="dialog" data-backdrop="static"
    aria-hidden="true">
    <div class="modal-dialog" style="width: 70%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span
              class="sr-only">Close</span>
          </button>
          <h4 class="modal-title">Adicionar Pagamento</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="line">
          <div class="row">
            <div class="col-lg-12">
              <div class="tabs-container">
                <ul class="nav nav-tabs" id="modalPayment">
                  <li><a data-toggle="tab" href="#Mtab-2">Transferência</a></li>
                  <li class="active"><a data-toggle="tab" href="#Mtab-4">Dinheiro</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane" id="Mtab-2">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label>Conta Contábil</label>
                              <select class="form-control selectpicker" id="conta_transferencia">
                                @if (Route::currentRouteName() == 'purchase.ap.invoice.read' && (isset($head) && !is_null($head->codSAP)))
                                  readonly
                                @endif name="conta_transferencia"
                                <option selected readonly value=''>Selecione</option>
                                @foreach ($accounts as $item)
                                  <option value="{{ $item['value'] }}"
                                    @if (isset($payments[0]->transfer) &&
                                            trim($payments[0]->transfer) == 'Y' &&
                                            $payments[0]->transferAccount == $item['value']
                                    ) selected @endif>{{ $item['name'] }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label>Data</label>
                              <input type="date"
                                @if (isset($payments[0]->transfer) && trim($payments[0]->transfer) == 'Y') value="{{ $payments[0]->transferDate }}" @endif
                                autocomplete="off" class="form-control" name="dt_transferencia" id="dt_transferencia">
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label>Valor</label>
                              <input type="text"
                                @if (isset($payments[0]->transfer) && trim($payments[0]->transfer) == 'Y') value="{{ number_format((float) $payments[0]->transferSum, 2, ',', '.') }}" @endif
                                class="form-control money" id="total_transfrencia" name="total_transfrencia"
                                onblur="setTotal();">
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label>Referência</label>
                              <input type="text"
                                @if (isset($payments[0]->transfer) && trim($payments[0]->transfer) == 'Y') value="{{ $payments[0]->transferReference }}" @endif
                                class="form-control" name="referencia_transferencia" id="referencia_transferencia">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane active" id="Mtab-4">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Conta Contábil</label>
                              <select class="form-control selectpicker" name="conta_dinheiro" id="conta_dinheiro">
                                <option value="NULL">Selecione</option>
                                @foreach ($accounts as $item)
                                  <option value="{{ $item['value'] }}"
                                    @if (isset($payments[0]->money) && trim($payments[0]->money) == 'Y' && $payments[0]->cashAccount == $item['value']) selected @endif>{{ $item['name'] }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Valor</label>
                              <input type="text" class="form-control money"
                                @if (isset($payments[0]->money) && trim($payments[0]->money) == 'Y') value='{{ number_format($payments[0]->cashSum, 2, ',', '.') }}' @endif
                                @if (Route::currentRouteName() == 'purchase.ap.invoice.read') readonly
                                                                   @else value="0" @endif
                                id='total_dinheiro' onblur="setTotal();" name="total_dinheiro">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="col-md-9">
            <div class="form-group">
              <div class="col-md-3">
                <center><label>Total</label></center>
                <input type="text" class="form-control money" id='total_pagameto_modal' readonly
                  value="{{ number_format($head->docTotal, 2, ',', '.') }}">
              </div>
              <div class="col-md-3">
                <input type="hidden" class="form-control money" id='total_pagameto' readonly>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <button type="button" form="contactForm" class="btn btn-white" data-coreui-dismiss="modal">Cancelar
            </button>
            <button type="submit" form="contactForm" onclick="addPayment();" class="btn btn-primary">
              Adicionar
            </button>
          </div>
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
                  <input class="form-control" type="date" v-model="installment.due_date">
                </td>
                <td>
                  <input form="needs-validation" type="hidden" :name="'installments[' + index + '][value]'"
                    :value="installment.value" />
                  <vue-numeric class="form-control" separator="." :min="0" :precision="4" v-model="installment.value"></vue-numeric>
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
            <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
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
                value={{ number_format($head->docTotal, 2, '.', '') }}
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
                        name="requiredProducts[{{$index}}][withheldTaxes][{{ $index }}][WTCode]"
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
          <button type="button" id="saveWithheldTax" class="btn btn-primary" onclick="saveWithheldTax(event)">Salvar</button>
        </div>
      </div>
    </div>
  </div>
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
      setMaskMoney();
      sumAllValues();
      fillTopNav(@json($incoing_invoice_model->getTopNavData()))
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

    function validModelTax() {
      var e = document.getElementById("modelTax");
      var itemSelecionado = e.options[e.selectedIndex].value;

      if ($('#modelTax').val() === '46') {
        // NFS-e
        $('#key_NFE').prop('required', true);
      } else {
        $('#key_NFE').prop('required', false);
      }
    }

    function getDueDate() {
      var codition = $('#conditionPagamentos').val();
      var date = $('#dataDocumento').val();
      $.get("{{ route('home/get/dueDate') }}" + '/' + date + '/' + codition, function(items) {
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

    $('#withheldtax-modal').on('show.coreui.modal', event => {
      let totalIRF = 0;
      let itemId = $(event.relatedTarget).attr('value');
      let lineNum = $(event.relatedTarget).attr('data-linenum');
      let qtd = $('#qtd-' + lineNum).val();
      let preco = $('#price-' + lineNum).val();
      let valorBase = parseFloat(qtd.replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco.replace('.', '').replace(
        ',', '.'));
        
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
      $('#withheldValorBase').val(valorBase.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
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

      if($('#cardCode').val().length <= 0){
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
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");'>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    function renderWHS(valor) {
      return `<center>
                      <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS">
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
      var e = document.getElementById("modelTax");
      var itemSelecionado = e.options[e.selectedIndex].value;
      if ($('#modelTax').val() === '28') {
        //FAT
        return;
      }
      if (itemSelecionado != 46) {
        var key = $('#key_NFE').val();
        if (key.length > 44) {
          alert("Chave da NFE invalida, Possui mais de 44 caracteres");
        }
        if (key.length < 44) {
          alert("Chave da NFE invalida, Possui menos de 44 caracteres");
        }
      }
    }

    var aux = 0;


    <?php $aux = true; ?>

      //   if (document.getElementById('resulSearch').style.display == 'block') {
      //       document.getElementById('resulSearch').style.display = 'none'
      //   } else {
      //       document.getElementById('resulSearch').style.display = 'block'
      //   }
      //     } else {
      //         alert('campo busca está em branco!');
      //     }
      // }

        function openModalPartner(){
            $('#pnModal').modal('show');
        }
        function setInTable(code) {
            //$('#needs-validation').append($('<input type="hidden" value="' + code + '" data-name="line" name="cardCode">'));
            $.get('/getNamePN/' + code, function (items) {
                for (var i = 0; i < items.length; i++) {
                    document.getElementById('parceiroNegocio').value = items[i].CardName;
                    $('#needs-validation').append($('<input type="hidden" value="' + items[i].CardName + '" data-name="line" name="cardName">'));
                    $('#cardCode').val(items[i].CardCode);
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
      return `<center>
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");'>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    var used = [];
    var index = {{ $cont ?? 1 }};

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
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='role-" + index + "' name='requiredProducts[" + index +
            "][costCenter]' data-container='body' required > <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='role2-" + index + "' name='requiredProducts[" + index +
            "][costCenter2]' data-container='body' > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
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
            if($('#contractGlobal').val().length > 0 && $('#contractGlobal').val() == value.code){
              selectContract.append($(`<option value='${value.code}' selected>${value.code} - ${value.contractNumber}</option>`));
            }else{
              selectContract.append($(`<option value='${value.code}'>${value.code} - ${value.contractNumber}</option>`));
            }
          });

          tr.append($(`<td></td>`).append(selectContract));
          tr.append($(`<td id='itemTable-${index}' class='text-center'>
                          <a class="text-danger" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
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

    let contracts = @json($contracts);

    function getPartnerContracts(cardCode){

      if(cardCode){
        $.get(`{{route("partners.get.contracts")}}/${$('#cardCode').val()}`, function(items) {
  
          $('#contractGlobal, select[id*="contract-"]').selectpicker('deselectAll');
          $('#contractGlobal, select[id*="contract-"]').find('option').not(':first').remove();

          contracts = [];
          $.each(items, function(ind, value){
            $('#contractGlobal').append(`<option value='${value.Code}'>${value.Code} - ${value.U_A2R_PNCONTRATO}</option>`);
            contracts[ind] = {Code: value.Code, U_A2R_PNCONTRATO: value.U_A2R_PNCONTRATO};
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

    function gerarTotal(code) {

      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = parseFloat(qtd.replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco.replace('.', '').replace(',',
        '.'));
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

      document.getElementById('total_pagameto_modal').value = total.format(2, ",", ".");
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
      }, 500)
      //$("#composicao-" + elemento).remove();

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
      $(`select[id*='use-']`).selectpicker('destroy');
      $(`select[id*='use-']`).selectpicker(selectpickerConfig).selectpicker('render');
    }

    function setTaxFull() {
      var val = document.getElementById('taxCodeGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('taxCode-' + i)) {
          document.getElementById('taxCode-' + i).value = val;
        }
      }
      $(`select[id*='taxCode-']`).selectpicker('destroy');
      $(`select[id*='taxCode-']`).selectpicker(selectpickerConfig).selectpicker('render');
    }

    function setCFOPFull() {
      var val = document.getElementById('cfopGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('cfop-' + i)) {
          document.getElementById('cfop-' + i).value = val;
        }
      }
      $(`select[id*='cfop-']`).selectpicker('destroy');
      $(`select[id*='cfop-']`).selectpicker(selectpickerConfig).selectpicker('render');
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
        $(`select[id*='role2-']`).attr('required', true)
        $(`#roleGlobal2`).removeClass('disabled');
      } else {
        $(`select[id*='role2-']`).addClass('disabled');
        $(`select[id*='role2-']`).attr('required', false)
        $(`#roleGlobal2`).addClass('disabled');
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

    function getPartnersComplements(cardCode){
      if(cardCode){
        $.get(`{{route("partners.get.partner")}}/${cardCode}`, function(items) {
          if(Object.keys(items).length !== 0){
            $('#cnpj').val((items.TaxId0 || items.TaxId4));
          }
        });
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

      form.append($('<td><input type="hidden" name="payment[total_dinheiro]" value="' + totalMoney.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[conta_dinheiro]" value="' + conta_dinheiro.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() +
        '">'));
      form.append($('<td><input type="hidden" name="payment[total_transfrencia]" value="' + totalTransfer.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia
        .trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() +
        '">'));
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
      totalMoney = parseFloat(totalMoney.replace(/[.]/gi, '').replace(/[,]/gi, '.'));
      totalTransfer = parseFloat(totalTransfer.replace(/[.]/gi, '').replace(/[,]/gi, '.'));
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
      var input = $("#needs-validation").find("*:invalid").first();
      var tabPane = input.closest('.tab-pane').first();
      if (tabPane.length === 1) {
        $('#myTabs').find('a[href="#' + tabPane.attr('id') + '"]').tab('show');
      }

      var codPN = $('#cardCode').val();
      if (parseFloat($('#total_a_pagar').val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) < 0) {
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

    function saveAdvancePayments(event) {
      advancePaymentsCache.splice(0, advancePaymentsCache.length)
      let total_advance_payments = 0;
      $.each($('input[name*="advancePayments"]:checked'), function(index, value) {
        if (!advancePaymentsCache.includes($(value).val())) {
          advancePaymentsCache.push(`${$(value).val()}`)
          let doc_total = parseFloat($(value).parent().parent().find('td').eq(4).text().replace('.', '').replace(',',
            '.'))
          let dpm_appl = parseFloat($(value).parent().parent().find('td').eq(5).text().replace('.', '').replace(',',
            '.'))
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

      $.each($('select[id*="contract-"]'), function(index, value){
        if($(value).val().length > 0){
          countContracts++;
          if(contractCode === null){contractCode = $(value).val()}
        }

        lines++;
        if(contractCode !== null && contractCode != $(value).val()){
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

      if(countContracts > 0){

        if(lines != countContracts){
          erros.push('Ao selecionar um contrato, todas as demais linhas do documento devem conter contrato selecionado! \n');
        }

        if(totalDoc > parseFloat(contractData.residualAmount)){
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
            text: response.message,
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
        $('#role2-' + code).attr('required', true);
      } else {
        $('#role2-' + code).addClass('disabled');
        $('#role2-' + code).attr('required', false);
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

        if (valorBase <= 0) {
          swal('Opss!', 'O valor base não pode ser menor que 0!', 'error');
          $(`#withheldValue-${lineNum}`).val(0);
          return
        }

        let valorRetido = $(`#withheldValue-${lineNum}`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.');
        let rate = roundNumber((valorRetido / parseFloat(valorBase)) * 100);

        $(`#withheldRate-${lineNum}`).val(rate);
        totalIRF += parseFloat(roundNumber(valorRetido));
      });

      $('#withheldTotalIRF').val(totalIRF.toFixed(2));
      setMaskMoney();
    }
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
