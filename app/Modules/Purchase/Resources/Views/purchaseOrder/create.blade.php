@extends('layouts.main')
@section('title', 'Pedido de compra')
@section('content')
  @if (isset($head) && $head->message)
    <div class="alert alert-info">
      {{ $head->message }}
    </div>
  @endif
  <form action="{{ route('purchase.order.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <input type="hidden" name="id" @if (isset($head)) value="{{ $head->id }}" @endif>
    <div class="card">
      <h5 class="card-header fw-bolder">
        @if (isset($head))
          Pedido de compras - detalhes
        @else
          Pedido de compras - cadastro
        @endif
      </h5>
      <div class="card-body">
        <div class="row form-group">
          @if (isset($head))
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
              <input type="text" class="form-control locked" value='{{ $head::STATUS_TEXT[$head->status] }}'>
            </div>
            <div class="col-md-1">
              <label>Origem</label>
              <input type="text" readonly value="{{ $head->origem }}" class="form-control locked">
            </div>
            <div class="col-md-4">
              <label>Comprador</label>
              <input type="text" readonly value="{{ $head->user->name }}" class="form-control locked">
            </div>
          @endif
        </div>

        <div class="row mt-2">
          <div class="col-md-5">
            <label>Parceiro de Negócio</label>
            @php
              if (isset($head)) {
                  $partner = getProviderData($head['cardCode']);
              }
            @endphp
            <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-width="100%"
              data-container="body" name="cardCode" @if (isset($head->codSAP)) disabled @endif
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
                @if (isset($head) && !empty($head->codSAP)) class="form-control locked" readonly @else class="form-control" @endif
                @if (isset($head->taxDate)) value="{{ $head->taxDate }}"
                                              @else value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Lançamento</label>
              <input type="date" name="dataLancamento" class="form-control locked"
                @if (isset($head) && !empty($head->codSAP)) readonly @endif
                @if (isset($head->docDate)) value="{{ $head->docDate }}"
                                                @else value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data de Entrega</label>
              <input type="date" name="dataVencimento" id="dataVencimento" class="form-control"
                @if (isset($head->docDueDate)) value="{{ $head->docDueDate }}"
                                                   @else value="{{ DATE('Y-m-d') }}" @endif
                @if (isset($head) && $head->status != 1) readonly @endif required>
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
          <div class="col-md-3">
            <div class="form-group">
              <label>Transportadora</label>
              <input type="text" class="form-control locked" name="transporter"
                @if (isset($head->transporter) && !$head->isRequest) value="{{ $head->transporter }}"
                                                   readonly @endif>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Condição de Pagamento</label>
              <select class="form-control selectpicker" required name="condPagamentos" id="conditionPagamentos"
                onChange="getDueDate()" @if (isset($head) && ($head->status == $OPOR::STATUS_CANCEL || $head->status == $OPOR::STATUS_CLOSE)) readonly @endif>
                <option value=''>Selecione</option>
                @foreach ($paymentConditions as $key => $value)
                  <option value="{{ $value['GroupNum'] }}" @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                    {{ $value['PymntGroup'] }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <label>Frete</label>
            <select class="form-control selectpicker" required name="incoTerm" id="incoTerm"
              @if (isset($head) && ($head->status == $OPOR::STATUS_CANCEL || $head->status == $OPOR::STATUS_CLOSE)) readonly @endif>
              <option value=''>Selecione</option>
              @foreach ($incoterm as $key => $value)
                <option value="{{ $key }}"
                  @if (isset($head->incoTerm) && $head->incoTerm == $value) selected @elseif($value == 'CIF') selected  @else @endif>
                  {{ $value }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label>Contrato</label>
            <select class='form-control selectpicker' name='contract' id='contractGlobal' onchange="setContractFull()"
              @if (isset($head) && !empty($head->codSAP)) disabled @endif>
              <option value=''>Selecione</option>
              @if (isset($contracts))
                @foreach ($contracts as $keys => $contract)
                  <option value='{{ $contract->code }}' @if (isset($head) && $head->contract == $contract->code) selected @endif>
                    {{ $contract->code }} - {{ $contract->contractNumber }}
                  </option>
                @endForeach
              @endif
            </select>
          </div>
          @if (!empty($approval_methods))
            <div class="col-md-3">
              <label>Forma de aprovação</label>
              <select class='form-control selectpicker' name='approval_method' id='approval_method'
                @if (!empty($head->codSAP)) disabled @endif required>
                @if ((int) $approval_methods['R2W'])
                  <option value="1">R2W</option>
                @endif
                @if ((int) $approval_methods['SAP'])
                  <option value="2">SAP</option>
                @endif
              </select>
            </div>
          @endif
        </div>
      </div>
    </div>
    <div class="card mt-4">
      <h6 class="card-header fw-bolder">Documentos associados</h6>
      <div class="card-body">
        @if (!empty($head->idQuotation))
          <div class="col-12">
            <p for="" class="fw-bolder">Cotações:</p>
            <div class="btn-group">
              <a href="{{ route('purchase.quotation.read', $head->idQuotation) }}" class="btn btn-primary"
                target="_blank">{{ getQuotationCode($head->idQuotation) }}</a>
            </div>
          </div>
        @endif
        @if (isset($head) && !empty($advancePayment) && count($advancePayment) > 0)
          <div class="col-12">
            <p for="" class="fw-bolder mt-2">Adiantamentos:</p>
            @foreach ($advancePayment as $payment)
              <div class="btn-group">
                <a type="button" href="{{ route('purchase.advance.provider.read', $payment->id) }}"
                  class="btn btn-primary" target="_blank">{{ $payment->code }}</a>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    @if(!empty($head) && !empty($head->justification))
      <div class="alert alert-danger mt-4">
        <b>Justificativa de cancelamento:</b> {{ $head->justification }}
      </div>
    @endif

    <div class="row mt-5">
      <div class="tabs-container">
        <ul class="nav nav-tabs" id="myTabs">
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
            <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
            <a class="nav-link" data-toggle="tab" href="#tab-2">Despesas</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
            <a class="nav-link" data-toggle="tab" href="#tab-3">Anexos</a>
          </li>
          @if (isset($head) && $head->status != $OPOR::STATUS_REPROVE)
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-4">
              <a class="nav-link" data-toggle="tab" href="#tab-4">Aprovadores</a>
            </li>
          @elseif(isset($head) && $head->status == $OPOR::STATUS_REPROVE)
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-5">
              <a class="nav-link" data-toggle="tab" href="#tab-5">Reprovado</a>
            </li>
          @endif
        </ul>
        <div class="tab-content">
          <div class="tab-pane active mt-4" id="tab-1">
            <div class="panel-body">
              <a href='#' class='text-dark text-tooltip' data-coreui-placement="right" title="Adicionar linha"
                onclick="loadingItems()" @if (isset($head) && ($head->status == '2' || $head->status == '0')) style="display: none;" @endif>
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <div class="table-responsive">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Cod. Solicitação</th>
                      <th>Descrições</th>
                      <th>Quantidade</th>
                      <th>Preço Unit.</th>
                      <th>Ult. Fornecedor</th>
                      <th>Ult. Preço</th>
                      <th>Total</th>
                      <th>Projeto</th>
                      <th>C. de Custo</th>
                      <th>C. de Custo2</th>
                      <th>Depósito</th>
                      <th>Contrato</th>
                      <th>Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if (isset($body))
                      <?php $cont = 1;
                      $docTotal = 0; ?>
                      @foreach ($body as $key => $value)
                        <?php $docTotal += (float) $value['lineSum']; ?>
                        <tr id="rowTable-{{ $cont }}" value="{{ $value['id'] }}"
                          data-row="{{ $cont }}"
                          @if ($value['status'] == 'C') data-locked="locked" @endif>
                          <td class="expandable">
                            {{ $cont }}
                            <input type='hidden' value="{{ $value['id'] }}"
                              name="requiredProducts[{{ $cont }}][id]">
                            <input type='hidden' value="{{ $value['idPurchaseOrders'] }}"
                              name="requiredProducts[{{ $cont }}][idPurchaseOrders]">
                            @if (isset($value['idItemPurchaseRequest']))
                              <input type='hidden' value="{{ $value['idItemPurchaseRequest'] }}"
                                name="requiredProducts[{{ $cont }}][idItemPurchaseRequest]">
                            @endif
                          </td>
                          <td class="expandable">
                            @if ($value['idItemPurchaseRequest'])
                              <a class="btn btn-primary"
                                href="{{ route('purchase.request.read', $value['idPurchaseRequest']) }}">{{ $value['requestCode'] }}</a>
                              <input type='hidden' value="{{ $value['requestCode'] }}"
                                name="requiredProducts[{{ $cont }}][requestCode]" readonly>
                            @endif
                          </td>
                          <td style="max-width: 16em;">
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
                              <input value="{{ $value['quantity'] }}" onclick='destroyMask(event)'
                                onblur="setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)"
                                id="qtd-{{ $cont }}" type="text" class="form-control qtd min-100"
                                name="requiredProducts[{{ $cont }}][qtd]" min="1"
                                @if (!empty($value['quantityRequest'])) data-quantity-request="{{ $value['quantityRequest'] }}" @endif>
                            </div>
                            <input type='hidden' value="{{ $value['itemUnd'] }}"
                              name="requiredProducts[{{ $cont }}][itemUnd]">
                          </td>
                          <td>
                            <input
                              @if ($head->isQuotation && $value['requestCode']) value="{{ number_format($value['price'], 4, ',', '.') }}"  @else value="{{ number_format($value['price'], 4) }}" @endif
                              onclick='destroyMask(event)'
                              onblur="setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)"
                              id="price-{{ $cont }}" type="text" class="form-control moneyPlus min-100"
                              name="requiredProducts[{{ $cont }}][preco]">
                          </td>
                          <td>
                            @if (!empty($value['lastProvider']))
                              <a href="{{ route('partners.edit', $value['lastProvider']) }}" target="_blank"
                                class='btn btn-primary' value="{{ $value['lastProvider'] }}"
                                name='requiredProducts[{{ $cont }}][lastProvider]'
                                id="lastProvider-{{ $cont }}">{{ $value['lastProvider'] }}
                              </a>
                            @endif
                          </td>
                          <td>
                            <input type='text' class='form-control min-100 locked'
                              value="{{ number_format($value['lastPrice'], 2, ',', '.') }}"
                              name='requiredProducts[{{ $cont }}][lasPrice]'
                              id="lastPrice-{{ $cont }}" readonly>
                          </td>
                          <td>
                            <input readonly value="{{ number_format($value['lineSum'], 2, ',', '.') }}"
                              id="totalLinha-{{ $cont }}" type="text" class="form-control min-100 locked"
                              name="requiredProducts[{{ $cont }}][totalLine]">
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='project-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][projeto]' data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($projeto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['codProject']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][costCenter]' required
                              onchange='validLine({{ $cont }})' data-container="body">
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['costCenter']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role2-{{ $cont }}'
                              name='requiredProducts[{{ $cont }}][costCenter2]' data-container="body">
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto2 as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['costCenter2']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker'
                              name='requiredProducts[{{ $cont }}][whsCode]' id='whsGlobal'
                              data-container="body">
                              <option value=''>Selecione</option>
                              @foreach ($warehouses as $keys => $values)
                                <option value='{{ $values['value'] }}'
                                  @if ($values['value'] == $value['whsCode']) selected @endif>
                                  {{ $values['value'] }} - {{ $values['name'] }}
                                </option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker'
                              name='requiredProducts[{{ $cont }}][contract]'
                              id='contract-{{ $cont }}' data-container="body"
                              @if (isset($head) && !empty($head->codSAP)) disabled @endif>
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
                            <a class="text-danger text-tooltip" title="Remover linha"
                              onclick="removeInArray('{{ $value['itemCode'] }}');removeLinha(this);" type="button">
                              <svg class="icon icon-xl">
                                <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                              </svg>
                            </a>
                          </td>
                          <input type="hidden" value="{{ $value['idPurchaseRequest'] }}" id="idPurchaseRequest"
                            name="requiredProducts[{{ $cont }}][idPurchaseRequest]">
                          <?php $cont++; ?>
                        </tr>
                      @endforeach
                      <input type="hidden" value="{{ $cont - 1 }}" id="cont">
                    @endif
                  </tbody>
                </table>
              </div>
              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Atalhos</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <label>Projeto Principal</label>
                      <select class='form-control selectpicker' name='project' id='projectGlobal'
                        onchange="setProjecFull()">
                        <option value=''>Selecione</option>
                        @foreach ($projeto as $keys => $values)
                          <option value="{{ $values['value'] }}">{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Centro de Custo Principal</label>
                      <select class='form-control selectpicker' name='role' id='roleGlobal'
                        onchange="setRoleFull()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label>Centro de Custo 2 Principal</label>
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
                      <label>Comprador</label>
                      <select class='form-control selectpicker' name='buyer'>
                        <option value=''>Selecione</option>
                        @foreach ($sellers as $keys => $values)
                          <option value='{{ $values['value'] }}' @if (isset($head) && $head->buyer == $values['value']) selected @endif>
                            {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <hr>
              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Totais</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Desconto</label>
                        <input type="text" class="form-control money" name="discountPercent"
                          @if (isset($head)) value="{{ number_format($head->discountPercent, 2, ',', '.') }}" @endif
                          id="discountPercent" onclick='destroyMask(event)'
                          onblur="setMaskMoney();sumAllValues();focusBlur(event)">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Total sem descontos</label>
                        <input type="text" class="form-control money locked" name="totalSemDesconto" readonly
                          @if (isset($head)) value="{{ number_format($head->discountPercent + $head->docTotal - $head_expenses->sum('lineTotal'), 2, ',', '.') }}" @endif
                          id="totalSemDesconto">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Total de despesas</label>
                        <input type="text" class="form-control money locked" name="totalSemDesconto" readonly
                          @if (isset($head)) value="{{ $head_expenses->sum('lineTotal') }}" @endif
                          id="totalDespesas">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Total</label>
                        <input type="text" class="form-control locked" name="docTotal"
                          @if (isset($head)) value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                                                            @elseif(isset($docTotal)) value="{{ $docTotal }}" @endif
                          readonly id="totalNota">
                        <input type="hidden" class="form-control"
                          @if (isset($head)) value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                                                            @elseif(isset($docTotal)) value="{{ $docTotal }}" @endif
                          id="docTotal">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-2">
            <div class="panel-body">
              <div class="row mt-3">
                <div class="table-responsive">
                  <table id="tableExpense"
                    class="table table-default table-striped table-bordered table-hover dataTables-example">
                    <thead>
                      <tr>
                        <th style="width: 10%;">Despesa</th>
                        <th style="width: 10%;">Valor</th>
                        <th style="width: 20%;">Código Imposto</th>
                        <th style="width: 20%;">Observação</th>
                        <th style="width: 15%;">Projeto</th>
                        <th style="width: 15%;">Centro de custo</th>
                        <th style="width: 10%;">Centro de custo2</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($expenses as $key => $value)
                        @php$expense = isset($head)
                              ? $head_expenses->where('expenseCode', '=', $value['code'])->first()
                              : null;
                        @endphp
                        <tr>
                          <td>
                            {{ $value['value'] }}
                            <input type="hidden" name="expenses[{{ $key }}][expenseCode]"
                              value="{{ $value['code'] }}">
                          </td>
                          <td>
                            <input
                              @if (!empty($expense)) value='{{ number_format($expense->lineTotal) }}' @endif
                              id='di_total' onclick='destroyMask(event)'
                              onblur="setMaskMoney();sumAllValues();focusBlur(event)" type="text"
                              class="form-control money min-100" name="expenses[{{ $key }}][lineTotal]">
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][tax]"
                              data-container="body">
                              <option value="" readonly>Selecione</option>
                              @foreach ($tax as $item)
                                <option value="{{ $item->value }}"
                                  @if (!empty($expense) && $expense->tax == $item->value) selected @endif>{{ $item->value }} -
                                  {{ $item->name }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <input @if (!empty($expense)) && value='{{ $expense->comments }}' @endif
                              type="text" class="form-control min-100"
                              name="expenses[{{ $key }}][comments]">
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][project]"
                              data-container="body">
                              <option value="">Selecione</option>
                              @foreach ($projeto as $item)
                                <option value="{{ $item['value'] }}"
                                  @if (!empty($expense) && $expense->project == $item['value']) selected @endif>{{ $item['value'] }} -
                                  {{ $item['name'] }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][costCenter]"
                              data-container="body">
                              <option value="" readonly>Selecione</option>
                              @foreach ($centroCusto as $item)
                                <option value="{{ $item['value'] }}"
                                  @if (!empty($expense) && $expense->costCenter == $item['value']) selected @endif>
                                  {{ $item['value'] }} - {{ $item['name'] }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker"
                              name="expenses[{{ $key }}][costCenter2]" data-container="body">
                              <option value="" readonly>Selecione</option>
                              @foreach ($centroCusto2 as $item)
                                <option value="{{ $item['value'] }}"
                                  @if (!empty($expense) && $expense->costCenter2 == $item['value']) selected @endif>
                                  {{ $item['value'] }} - {{ $item['name'] }}</option>
                              @endforeach
                            </select>
                          </td>
                        </tr>
                      @endForeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-3">
            <div class="panel-body">
              <div class="card mt-4">
                <div class="card-body">
                  <div class="col-md-12 form-group">
                    <label>Observação</label>
                    <textarea class="form-control" rows="5" name="obsevacoes" @if (isset($head) && !$head::STATUS_OPEN) readonly @endif
                      maxlength="200">
@if (isset($head->comments))
{{ $head->comments }}
@endif
</textarea>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-6 mt-2">
                  <div class="table-responsive">
                    <table class="table table-default table-striped table-bordered table-hover dataTables-example w-100">
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
          <div class="tab-pane" id="tab-4">
            <div class="panel-body">
              <div class="row mt-3">
                <div class="table-responsive">
                  <table id="tableLefted"
                    class="table table-default table-striped table-bordered table-hover dataTables-example w-100">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Data</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if (isset($approvers))
                        <?php $cont = 1; ?>
                        @foreach ($approvers as $key => $value)
                          <tr>
                            <td>{{ $cont }}</td>
                            <td>{{ $value->name }}</td>
                            @if ($value->status == 0)
                              <td>Pendente</td>
                            @elseif($value->status == 1)
                              <td>Aprovado</td>
                            @endif
                            <td>{{ formatDate($value->created) }}</td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-5">
            <div class="panel-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="table-responsive">
                    <table id="tableLefted"
                      class="table table-default table-striped table-bordered table-hover dataTables-example">
                      <thead>
                        <tr>

                          <th>Nome</th>
                          <th>Justificativa</th>
                          <th>Data</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if (isset($head) && $head->status == $OPOR::STATUS_REPROVE)
                          <tr>

                            <td>
                              @if (isset($head->reprove_user))
                                {{ getUserName($head->reprove_user) }}
                              @endif
                            </td>
                            <td>{{ $head->reprove_justify }}</td>
                            <td>
                              @if (isset($head->reprove_date))
                                {{ formatDate($head->reprove_date) }}
                              @endif
                            </td>
                          </tr>
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12 mt-5">
      @if (!empty($head->codSAP) && $head->status == $head::STATUS_OPEN && checkAccess('purchase_advance_provider'))
        <div class="dropdown">
          <button class="btn btn-dark dropdown-toggle float-start" type="button" data-coreui-toggle="dropdown"
            aria-expanded="false">
            <svg class="icon">
              <use xlink:href="{{ asset('icons_assets/custom.svg#copy-to') }}"></use>
            </svg> Copiar para
          </button>
          <ul class="dropdown-menu">
            <li>
              <a href="{{ route('purchase.advance.provider.copy.from.purchase.order', $head->id) }}"
                class="dropdown-item" id="btn-from">Adiantamento para fornecedores</a>
            </li>
            <li>
              <a href="{{ route('purchase.ap.invoice.copy', $head->id) }}" class="dropdown-item" id="btn-from">Nota
                Fiscal de Entrada (Serviços)</a>
            </li>
          </ul>
        </div>
      @endif

      @if (!isset($head))
        <button type="button" class="btn btn-primary float-end me-1" onclick="valide(this)">Salvar</button>
      @elseif (isset($head))
        @if ($head->status == $head::STATUS_OPEN && $head->is_locked == false)
          <button type="button" class="btn btn-primary float-end" onclick="valide(this)">Atualizar</button>
        @endif
        @if (isset($head) && $head->status == $OPOR::STATUS_PENDING && $OPPR->validUserPurcharseApproved($head->id))
          <button type="button" class="btn btn-primary float-end"
            onclick="aprovar({{ $head->id }})">Aprovar</button>
          <button type="button" class="btn btn-danger float-end" data-coreui-toggle="modal"
            data-coreui-target="#reproveModal">Reprovar</button>
        @endif
        @if ($head->status == $OPOR::STATUS_OPEN || $head->status == $OPOR::STATUS_PENDING)
          <button type="button" class="btn btn-danger float-end me-1" data-coreui-toggle="modal" data-coreui-target="#cancelationJustificationModal">Cancelar</button>
          <button type="button" class="btn btn-warning float-end me-1" onclick="closed(event)">Fechar</button>
        @endif
        @if (isset($head) && Route::current()->getName() !== 'purchase.order.read.from.request')
          <button type="button" class="btn btn-success float-end me-1" onclick="duplicate(event)">Duplicar</button>
        @endif
      @endif
    </div>
  </form>


  <div class="modal inmodal" id="itensModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lista de itens</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive mt-3 ">
            <table id="table" class="table table-default table-striped table-bordered table-hover"
              style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  <th style="width: 15%">Qtd. Estoque</th>
                  <th style="width: 10%">Opções</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
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

  @if (isset($head))
    <div class="modal" id="reproveModal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
            <h4 class="modal-title">Reprovar Pedido</h4>
          </div>

          <form id="reproveForm" action="{{ route('purchase.order.reprove'), $head->id }}">
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
            <table id="tableWHS" class="table table-default table-striped table-bordered table-hover">
              <thead>
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

  <div class="modal inmodal" id="cancelationJustificationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title">Justificativa de cancelamento</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <div class="col-12">
            <label>Justificativa:</label>
            <input id="justification" type="text" maxlength="200" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-sm btn-danger" onclick="cancel()">Cancelar</button>
        </div>
      </div>
    </div>
  </div>


  {{-- <div class="modal inmodal" id="paymentModal" tabindex="-1" role="dialog" data-backdrop="static"
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
                  @if (false)
                    <li><a data-toggle="tab" href="#Mtab-1">Cheque</a></li>
                  @endif
                  <li><a data-toggle="tab" href="#Mtab-2">Transferência</a></li>
                  @if (false)
                    <li><a data-toggle="tab" href="#Mtab-3">Cartão de Crédito</a></li>
                  @endif
                  <li class="active"><a data-toggle="tab" href="#Mtab-4">Dinheiro</a></li>
                  @if (false)
                    <li><a data-toggle="tab" href="#Mtab-5">Outros</a></li>
                  @endif
                </ul>
                <div class="tab-content">
                  @if (false)
                    <div class="tab-pane" id="Mtab-1">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Conta Contábil</label>
                                <select class="form-control selectpicker" name="conta_cheque" id="conta_cheque">
                                  <option selected readonly>Selecione</option>
                                  @foreach ($accounts as $item)
                                    <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Data de Vencimento</label>
                                <input type="text" class="form-control datepicker" name="dt_vencimento_cheque"
                                  id="dt_vencimento_cheque">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Valor</label>
                                <input type="text" class="form-control money" name="valor_cheque" value="0"
                                  id="valor_cheque" onblur="setTotal();">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Nome do Banco</label>
                                <select class="form-control selectpicker with-ajax-bank" data-width="100%"
                                  data-size="7" name="nome_banco_cheque" id="nome_banco_cheque">
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Filial</label>
                                <input type="text" class="form-control" name="filial_cheque" id="filial_cheque">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Conta</label>
                                <input type="text" class="form-control" name="numero_conta_cheque"
                                  id="numero_conta_cheque">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Nº Cheque</label>
                                <input type="text" class="form-control" name="numero_cheque" id="numero_cheque">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>
                                  Endosso</label>
                                <select class="form-control seleckpicker" data-live-source='true' name="endosso_cheque"
                                  id="endosso_cheque">
                                  <option value="N" selected>Não</option>
                                  <option value="Y">Sim</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif
                  <div class="tab-pane" id="Mtab-2">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label>Conta Contábil</label>
                              <select class="form-control selectpicker" name="conta_transferencia"
                                id="conta_transferencia">
                                <option selected readonly>Selecione</option>
                                @foreach ($accounts as $item)
                                  <option value="{{ $item['value'] }}"
                                    @if (isset($payments[0]->transfer) && trim($payments[0]->transfer) == 'Y' && $payments[0]->transferAccount == $item['value']) selected @endif>{{ $item['name'] }}</option>
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
                                @if (isset($payments[0]->transfer) && trim($payments[0]->transfer) == 'Y') value="{{ number_format($payments[0]->transferSum, 2, ',', '.') }}" @endif
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
                  @if (false)
                    <div class="tab-pane" id="Mtab-3">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Conta Contábil</label>
                                <select class="form-control selectpicker" name="conta_cartao" id="conta_cartao">
                                  <option value="NULL">Selecione</option>
                                  @foreach ($accounts as $item)
                                    <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Nome do Cartão</label>
                                <select class="form-control selectpicker" name="name_cartao" id="name_cartao">
                                  <option value="NULL">Selecione</option>
                                  @foreach ($cartao as $key => $value)
                                    <option value="{{ $value->value }}">{{ $value->value }}
                                      - {{ $value['value'] }}</option>
                                  @endForeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Valor</label>
                                <input type="text" class="form-control money" value="0" id='total_credito'
                                  onblur="setTotal();" name="total_credito">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Nº Parcelas</label>
                                <input type="number" class="form-control" id='parcelas_cartao'
                                  name="parcelas_cartao">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif
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
                                @if (isset($head->status) && $head->status != $OPOR::STATUS_OPEN) readonly
                                                                   @else value="0" @endif
                                id='total_dinheiro' onblur="setTotal();" name="total_dinheiro">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @if (false)
                    <div class="tab-pane" id="Mtab-5">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="col-md-4">
                              <div class="form-group">
                                <label>Conta Contábil</label>
                                <select class="form-control selectpicker" name="conta_outros" id="conta_outros">
                                  <option value="NULL">Selecione</option>
                                  @foreach ($accounts as $item)
                                    <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group">
                                <label>Valor</label>
                                <input type="text" class="form-control money"
                                  @if (isset($head->status) && $head->status != $OPOR::STATUS_OPEN) readonly
                                                                       @else value="0" @endif
                                  id='total_outros' onblur="setTotal();" name="total_outros">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif
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
                  @if (isset($head->docTotal)) value="{{ number_format($head->docTotal, 2, ',', '.') }}"
                                       @else value="0" @endif>
              </div>
              <div class="col-md-3">
                @if (false)
                  <center><label>Saldo</label></center>
                  <input type="text" class="form-control money" id='total_pagameto' readonly>
                @endif
                <input type="hidden" class="form-control money" id='total_pagameto' readonly>

              </div>
            </div>
          </div>
          <div class="col-md-3">
            <button type="button" form="contactForm" class="btn btn-white" data-dismiss="modal">Cancelar
            </button>
            <button type="submit" form="contactForm" onclick="addPayment();" class="btn btn-primary">
              Adicionar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div> --}}

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    $(document).ready(function() {

      $.each($(`tr[data-locked='locked']`), function(index, element) {
        $(element).find('select').attr('disabled', 'disabled');
        $(element).find('input').addClass('locked');
      });

      @if (isset($head))
        @if ($head->status == '0' || $head->status == '2')
          setTimeout(function() {
            var table = $('#requiredTable');
            table.find('input').each(function(i, item) {
              $(item).prop('readonly', true);
            });
            table.find('select').each(function(i, item) {
              $(item).prop('disabled', true).selectpicker('refresh');
            });

            var form = $('#needs-validation');
            form.find('input').each(function(i, item) {
              $(item).prop('readonly', true);
            });

          }, 0050);
        @endif

        @if (empty($head->codSAP) && $head->status == 1)
          $('#requiredTable').find('input').not('#parceiroNegocio')
            .not('input[id*="lastProvider-"]').not('input[id*="lastPrice-"]')
            .not('input[id*="totalLinha-"]').each(function(i, item) {

              $(item).prop('readonly', false);
            });
          $('#requiredTable').find('select').each(function(i, item) {
            $(item).removeAttr('readonly');
          });
        @endif
      @endif

      fillTopNav(
        @if (isset($head))
          @json($head->getTopNavData())
        @else
          @json($purchase_order_model->getTopNavData())
        @endif
      );

      setTimeout(function() {
        sumAllValues();
        setMaskMoney();
      }, 1000);

    });

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    @if (isset($body))
      var index = $('#cont').val();
    @else
      var index = 0;
    @endif ;

    var expensesIndex = {{ count($expenses) }}

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

    $(document).on('click', '#close-preview', function() {
      $('.image-preview').popover('hide');
      // Hover befor close the preview
      $('.image-preview').hover(
        function() {
          $('.image-preview').popover('show');
        },
        function() {
          $('.image-preview').popover('hide');
        }
      );
    });
    $(function() {
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
      $('.image-preview-clear').click(function() {
        $('.image-preview').attr("data-content", "").popover('hide');
        $('.image-preview-filename').val("");
        $('.image-preview-clear').hide();
        $('.image-preview-input input:file').val("");
        $(".image-preview-input-title").text("Browse");
      });
      // Create the preview image
      $(".image-preview-input input:file").change(function() {
        var img = $('<img/>', {
          id: 'dynamic',
          width: 250,
          height: 200
        });
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function(e) {
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
      columnDefs: [{
        width: '20%',
        targets: 0
      }],
      fixedColumns: true,
      responsive: {
        details: {
          type: "column",
          target: ".expandable"
        }
      },
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });

    function setMaskMoney(target = null) {
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

      if (target) {
        $(target).trigger('mask.maskMoney');
      } else {
        $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"]'), function(index, value) {
          $(value).trigger('mask.maskMoney')
        });
      }
    }

    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("O processo pode demorar um pouco, aguarde até que seja concluído!");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'purchase_orders')
      formData.append('id', $('input[name="id"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('purchase.order.updateUploads') }}",
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
            window.location.href = "{{ route('purchase.order.remove.upload') }}/" + id + "/" + idRef;
          }
        });
    }

    function loadingItems() {

      if ($('#cardCode').val().length <= 0) {
        swal('Opss!', 'Informe o Parceiro de Negócios!', 'error');
        return;
      }

      $('#table').DataTable().clear();
      $("#itensModal").modal('show');
      let table = $("#table").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        ajax: {
          url: "{{ route('inventory.request.list.whs') }}",
          data: function(d) {
            d.purchase = true;
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
          {
            name: 'OnHand',
            data: 'ItemCode',
            render: renderWHSButton,
            orderable: false
          },
          {
            name: 'edit',
            data: 'ItemCode',
            render: renderEditButton,
            orderable: false
          }
        ],
        lengthMenu: [5, 15, 30],
        language: dataTablesPtBr
      });
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
      $(event.target).select()
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }


    function renderWHSButton(valor) {
      return `<center>
                      <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS" @if (isset($head->codSAP)) style="display: none;" @endif>
                        <svg class="icon icon-xl">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                        </svg>
                      </a>
                    </center>`
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
            render: renderFormatedQuantity,
            orderable: false
          }
        ],
        lengthMenu: [5, 15, 30],
        language: dataTablesPtBr
      });
    }


    @if (isset($head))
      function aprovar(idP) {
        swal({
            title: "Deseja aprovar o pedido?",
            text: "Essa ação não pode ser desfeita!",
            type: "warning",
            buttons: ['Não', 'Sim'],
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Aprovando...')
              window.location.href = "{{ route('purchase.order.approve', $head->id) }}";
            }
          });
      }
    @endif

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

    function getDueDate() {
      var codition = $('#conditionPagamentos').val();
      var date = $('#dataDocumento').val();
      $.get("{{ route('home/get/dueDate') }}" + '/' + date + '/' + codition, function(items) {
        document.getElementById('dataVencimento').value = items;
      });
    }

    var aux = 0;

    $(document).on('changed.bs.select', '#cardCode', function(event) {
      $.get('/getNamePN/' + $(event.target).val(), function(items) {
        $('#conditionPagamentos').val(items[0].GroupNum).selectpicker('refresh');
      });
    });

    <?php $aux = true; ?>

    function valideCode(code, mark = true) {
      if (used.indexOf(code) == '-1') {
        if (mark) {
          used.push(code);
        }
        return true;
      } else {
        return false;
      }
    }

    function chengeIcon(element) {
      document.getElementById('addItem-' + element).src = "{{ asset('images/addCinza.png') }}";
    }

    var used = new Array();

    function addLastProvider(code, index) {
      $.get('/getProductsProvider/' + code, function(items) {
        if (typeof items[0] != 'undefined') {
          $('#lastProvider-' + index).append($(
            `<a href="{{ route('partners.edit') }}/${items[0].CardCode}" target="_blank" class="btn btn-primary">${(items[0].CardCode)}</a>`
          ));
        }
      });
    }

    function loadTable(code) {
      var table = $('#requiredTable');
      $('.dataTables_empty').remove(); //Remove todas as linhas vazias da tables

      if (valideCode(code, true)) {
        $.get('/getProductsSAP/' + code, function(items) {

          index++;
          if (!valideCode(index, true)) {
            index++;
          }

          var tr = $("<tr id='rowTable-" + index + "'>");
          tr.append($('<td>' + index + '</td>'));
          tr.find('td').first().append('<input type="hidden" value="' + code +
            '" data-name="line" name="requiredProducts[' + index + '][codSAP]">');


          for (var i = 0; i < items.length; i++) {

            if (items[i].ItemCode) {

              //4 casas decimais é definida para a classe moneyPlus
              let price = parseFloat(items[i].LastPurPrc).toFixed(4);
              var whs = items[i].DfltWH;
              tr.append($("<td></td>"));
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
                                            <input type='hidden' value='${items[i].ItemCode}' name='requiredProducts[${index}][itemCode]' >
                                        </td>`));

              tr.append($(
                `<td >
                  <div class="input-group min-200 mt-1">
                    <div class="input-group-text">${items[i].BuyUnitMsr || " "}: ${renderFormatedQuantity(items[i].NumInBuy)}</div>
                    <input required onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(${index});focusBlur(event)' id='qtd-${index}' min='0.001' type='text' class='form-control qtd min-100' name='requiredProducts[${index}][qtd]'>
                    <input type='hidden' value='${items[i].BuyUnitMsr || ""}' name='requiredProducts[${index}][itemUnd]'> 
                  </div>
                </td>`
              ));
              if (price > 0) {
                tr.append($(
                  "<td ><input required onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" +
                  index + ");focusBlur(event)' id='price-" + index + "' value='" + price.replace('.', ',') +
                  "' type='text' class='form-control moneyPlus min-100' name='requiredProducts[" + index +
                  "][preco]' min='1'></td>"));
              } else {
                tr.append($(
                  "<td ><input required onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" +
                  index + ");focusBlur(event)' id='price-" + index +
                  "' value='' type='text' class='form-control moneyPlus min-100' name='requiredProducts[" +
                  index +
                  "][preco]' min='1'></td>"));
              }
              tr.append($("<td id='lastProvider-" + index + "'></td>"));
              tr.append($("<td><input name='requiredProducts[" + index +
                "][lastPrice]' value='" + price.replace('.', ',') + "' id='lastPrice-" + index +
                "' class='form-control moneyPlus min-100 locked' readonly></td>"))

              tr.append($(
                "<td ><input value='0'  class='form-control min-100 locked' readonly id='totalLinha-" +
                index + "' type='text'></td>"));

              tr.append($("<td >" +
                "<select class='form-control selectpicker'  id='project-" + index + "' name='requiredProducts[" +
                index +
                "][projeto]' required data-container='body'> <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
                "</td>"));
              tr.append($("<td >" +
                "<select class='form-control selectpicker' id='role-" + index + "' name='requiredProducts[" +
                index + "][costCenter]' required onchange='validLine(" + index +
                ");' data-container='body'> <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
                "</td>"));
              tr.append($("<td >" +
                "<select class='form-control selectpicker' id='role2-" + index + "' name='requiredProducts[" +
                index +
                "][costCenter2]' data-container='body'> <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
                "</td>"));
              tr.append($("<td >" +
                "<select class='form-control selectpicker' id='whs-" + index + "' name='requiredProducts[" +
                index + "][whsCode]' required onchange='setContaLine(" + index +
                ")' data-container='body'> <option value=''>Selecione</option> @foreach ($warehouses as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
                "</td>"));

              let selectContract = $(`<select class='form-control selectpicker' id='contract-${index}' name='requiredProducts[${index}][contract]' data-container='body' disabled>
                                    <option value=''>Selecione</option>
                                </select>`);

              $.each(contracts, (index, value) => {
                if ($('#contractGlobal').val().length > 0 && $('#contractGlobal').val() == value.code) {
                  selectContract.append($(
                      `<option value='${value.code}' selected>${value.code} - ${value.contractNumber}</option>`
                      ));
                } else {
                  selectContract.append($(
                    `<option value='${value.code}'>${value.code} - ${value.contractNumber}</option>`));
                }
              });

              tr.append($(`<td></td>`).append(selectContract));

              tr.append($(`<td class='text-center'>
                              <a class="text-danger text-tooltip" title="Remover linha" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
                                <svg class="icon icon-xl">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                </svg>
                              </a>
                            </td>`));
              addLastProvider(code, index);
            }
          }

          table.find('tbody').append(tr);

          $('#whs-' + index).val(whs);
          $(`#rowTable-${index} .selectpicker`).selectpicker(selectpickerConfig);
          $('.tooltip').tooltip();
          gerarTotal(index);
          setTimeout(function() {
            setMaskMoney();
          }, 0500);
        });
      }
    }

    let contracts =
      @if (isset($head))
        @json($contracts)
      @else
        []
      @endif ;

    function getPartnerContracts(cardCode) {

      if (cardCode) {
        $.get(`{{ route('partners.get.contracts') }}/${cardCode}`, function(items) {

          $('#contractGlobal, select[id*="contract-"]').selectpicker('deselectAll');
          $('#contractGlobal, select[id*="contract-"]').find('option').not(':first').remove();
          $('#contractGlobal, select[id*="contract-"]').find('option').not(':first').remove();
          $(`select[id*="contract-"]`).append(`<option value=''>Selecione</option>`);

          contracts = [];
          $.each(items, function(ind, value) {
            for (let indic = 0; indic < index; indic++) {
              $(`#contract-${indic+1}`).append(
                `<option value='${value.code}'>${value.code} - ${value.contractNumber}</option>`);
            }
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

    function getPartnersComplements(cardCode) {
      if (cardCode) {
        $.get(`{{ route('partners.get.partner') }}/${cardCode}`, function(items) {
          if (Object.keys(items).length !== 0) {
            $('#cnpj').val((items.TaxId0 || items.TaxId4));
          }
        });
      }
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

    function gerarTotal(code) {
      clearNumber(code);

      var qtd = $('#qtd-' + code).val().replace(/[.]/gi, '').replace(/[,]/gi, '.');
      var qtd_request = parseFloat($('#qtd-' + code).attr('data-quantity-request'))

      if (parseFloat(qtd) > parseFloat(qtd_request)) {
        $('#qtd-' + code).val($('#qtd-' + code).attr('data-quantity-request').replace('.', ','));
        alert('A quantidade não deve ser maior que a quantidade do item pendente associado a solicitação de compras!');
        return
      }
      var preco = parseFloat((document.getElementById('price-' + code).value).replace(/[.]/gi, '').replace(/[,]/gi, '.'));

      var total = roundNumber(parseFloat(qtd) * parseFloat(preco));

      if (!isNaN(total)) {
        document.getElementById('totalLinha-' + code).value = total.format(2, ",", ".");
      }

      setTimeout(function() {
        sumAllValues();
      }, 0500);
      setMaskMoney();
    }

    function sumAllValues() {
      var total = 0;

      var discountPercent = document.getElementById('discountPercent').value
      var totalDespesas = 0;

      for (let index = 0; index < expensesIndex; index++) {
        const value = parseFloat($(`input[name="expenses[${index}][lineTotal]"]`).val().replace(/[.]/gi, '').replace(
          /[,]/gi, '.')) || 0;
        totalDespesas += value;
      }

      discountPercent = parseFloat(discountPercent.replace(/[.]/gi, '').replace(/[,]/gi, '.'));

      var i = 0;
      var x = 0;
      for (i = 1; i <= index; i++) {

        if (document.getElementById('totalLinha-' + i)) {

          if ($(`input[name="requiredProducts[${i}][deleted]"]`).val()) {
            $(`#totalLinha-${i}`).val('0,00')
          }
          x = document.getElementById('totalLinha-' + i).value;
          total += Math.round((parseFloat(x.replace(/[.]/gi, '').replace(/[,]/gi, '.'))) * 100) / 100

        }
      }

      if (isNaN(total)) {
        total = 0;
      }

      //if (isNaN(safe_total)) {
      //    safe_total = 0;
      //}
      //total += (di_total + freight_total + outhers_total + safe_total);
      total += totalDespesas;
      total -= discountPercent
      var desconto = discountPercent;
      var x;
      var i;

      totalDoc = Math.round((total) * 100) / 100 || 0.00;
      document.getElementById('totalNota').value = totalDoc.format(2, ",", ".");
      // document.getElementById('docTotalHeader').value = totalDoc.format(2, ",", ".");
      document.getElementById('totalDespesas').value = totalDespesas.format(2, ",", ".");
      // document.getElementById('total_pagameto_modal').value = totalDoc.format(2, ",", ".");
      document.getElementById('totalSemDesconto').value = (totalDoc + discountPercent - totalDespesas).format(2, ",",
        ".");
      // addExpensesFromValues();
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
      var prince = parseFloat(totalLinha.replace(/[.]/gi, '').replace(/[,]/gi, '.')) / parseFloat(qtd);

      if (!isNaN(prince)) {
        document.getElementById('price-' + code).value = prince.format(5, ",", ".");

        setTimeout(function() {
          sumAllValues();
        }, 0500);
      }
    }

    var totalDoc = 0.00;


    // function gastosExtras(code, indice) {
    //   var form = $('#expensesForm');
    //   $('#gastosExtras').modal('show').find("button[type=submit]").html('Adicionar');
    //   form.attr('onsubmit', "setValuesExpenses(\"" + code + "\", " + indice + "); return false;");

    // }

    // function setValuesExpenses(code, indice) {
    //   $('#gastosExtras').modal('hide');
    //   var vFrete = document.getElementById('vFrete').value;
    //   var cPagamentos = document.getElementById('cPagamentos').value;
    //   var form = $('#form');
    //   form.append('<input type="hidden" id="dividas[' + code + '][vFrete]" value="' + vFrete + '"  name="dividas[' +
    //     code + '][vFrete]">');
    //   form.append('<input type="hidden" id="dividas[' + code + '][cPagamentos]"  value="' + cPagamentos +
    //     '"  name="dividas[' + code + '][cPagamentos]">');
    //   personalizeExpenses(code, indice);
    //   addExpensesFromValues()
    // }

    // function addExpensesFromValues() {
    //   //sumAllValues();
    //   var vFrete = 0;
    //   var x = 0;
    //   for (var i = 0; i < used.length; i++) {
    //     if (document.getElementById('dividas[' + used[i] + '][vFrete]')) {
    //       x = document.getElementById('dividas[' + used[i] + '][vFrete]').value;
    //       if (x.substring(0, 1) == 'R') {
    //         x = x.substring(2, x.length);
    //       }
    //       vFrete = parseFloat(vFrete) + parseFloat(x.replace(/[.]/gi, '').replace(/[,]/gi, '.'));
    //     }

    //   }
    //   var total = document.getElementById('totalNota').value;

    //   if (total.substring(0, 1) == 'R') {
    //     total = total.substring(2, total.length);
    //   }
    //   total = total.replace(/[.]/gi, '').replace(/[,]/gi, '.');
    //   document.getElementById('totalNota').value = (parseFloat(vFrete) + parseFloat(total)).format(2, ",", ".");
    //   document.getElementById('docTotalHeader').value = (parseFloat(vFrete) + parseFloat(total)).format(2, ",", ".");
    //   totalDoc = parseFloat(vFrete) + parseFloat(total);
    // }

    // function personalizeExpenses(code, indice) {
    //   $('#rowTable-' + indice).css({
    //     "background-color": "rgba(232, 219, 180, 0.41)"
    //   });
    //   document.getElementById('itemTable-' + indice).innerHTML =
    //     "<img src='{{ asset('images/expenses.png') }}' onclick='searchExpenses(\"" + code +
    //     "\");' style=' width:37%;font-size: 3%;color:  blue; padding-left: 5px;'/><img src='{{ asset('images/remover.png') }}' onclick='removeInArray(\"" +
    //     code + "\");removeLinha(this);' style='font-size: 3%;color: #ec0707;padding-left: 16px;'/>";
    // }

    // function searchExpenses(code) {
    //   var vFrete = document.getElementById('dividas[' + code + '][vFrete]').value;
    //   var cPagamentos = document.getElementById('dividas[' + code + '][cPagamentos]').value;

    //   document.getElementById('vFrete').value = vFrete;
    //   document.getElementById('cPagamentos').value = cPagamentos;
    //   var form = $('#expensesForm');
    //   $('#gastosExtras').modal('show').find("button[type=submit]").html('Atualizar');
    //   form.attr('onsubmit', "UpdateValuesExpenses(\"" + code + "\"); return false;");
    // }

    // function UpdateValuesExpenses(code) {
    //   $('#gastosExtras').modal('hide');
    //   var vf = document.getElementById('vFrete').value;
    //   var cP = document.getElementById('cPagamentos').value;
    //   document.getElementById('dividas[' + code + '][vFrete]').value = vf;
    //   document.getElementById('dividas[' + code + '][cPagamentos]').value = cP;

    //   setTimeout(function() {
    //     sumAllValues();
    //   }, 0500);
    // }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      var row = tr.attr('data-row');
      var deleted = false;

      if ($(`input[name="requiredProducts[${row}][id]"]`).val() == '' || row === undefined) {
        deleted = true;
      }

      if (row) {
        $(tr).append($(`<input name="requiredProducts[${row}][deleted]" value="${tr.attr('value')}">`))
      }
      tr.fadeOut(400, function() {

        if (deleted) {
          tr.remove();
        } else {
          tr.css({
            display: 'none'
          });
        }

        setTimeout(function() {
          sumAllValues();
        }, 0600);

      });
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

    function setTaxFull() {
      var val = document.getElementById('taxCodeGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('taxCode-' + i)) {
          document.getElementById('taxCode-' + i).value = val;
        }
      }
    }
    @if (!empty($head))
      function duplicate(event) {

        event.preventDefault()

        swal({
            title: "Tem certeza que deseja Duplicar?",
            text: "Documentos associados ao pedido de compras não serão carregados para o pedido duplicado, assim como no SAP!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Duplicando...')
              window.location.href = "{{ route('purchase.order.duplicate', $head->id) }}";
            }
          });
      }
    @endif

    function setWhsFull() {
      setTimeout(() => {
        let val = $('#whsGlobal').val();
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('whs-' + i)) {
            document.getElementById('whs-' + i).value = val;
          }
        }

        $(`select[id*='whs-']`).selectpicker('destroy');
        $(`select[id*='whs-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
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
      @if (false)
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
      form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() +
        '">'));
      form.append($('<td><input type="hidden" name="payment[total_transfrencia]" value="' + totalTransfer.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia
        .trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() +
        '">'));
      @if (false)
        form.append($('<td><input type="hidden" name="payment[name_cartao]" value="' + name_cartao.trim() + '">'));
        form.append($('<td><input type="hidden" name="payment[total_credito]" value="' + totalCard.trim() + '">'));
        form.append($('<td><input type="hidden" name="payment[parcelas_cartao]" value="' + parcelas_cartao.trim() +
          '">'));
        form.append($('<td><input type="hidden" name="payment[total_outros]" value="' + totalOther.trim() + '">'));
        form.append($('<td><input type="hidden" name="payment[conta_outros]" value="' + conta_outros.trim() + '">'));
        form.append($('<td><input type="hidden" name="payment[conta_cartao]" value="' + conta_cartao.trim() + '">'));

        form.append($('<td><input type="hidden" name="payment[conta_cheque]" value="' + conta_cheque + '">'));
        form.append($('<td><input type="hidden" name="payment[dt_vencimento_cheque]" value="' + dt_vencimento_cheque +
          '">'));
        form.append($('<td><input type="hidden" name="payment[valor_cheque]" value="' + valor_cheque + '">'));
        form.append($('<td><input type="hidden" name="payment[nome_banco_cheque]" value="' + nome_banco_cheque + '">'));
        form.append($('<td><input type="hidden" name="payment[filial_cheque]" value="' + filial_cheque + '">'));
        form.append($('<td><input type="hidden" name="payment[numero_conta_cheque]" value="' + numero_conta_cheque +
          '">'));
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

    selectpicker.filter('.with-ajax-contact').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('partners.get.contacts') }}"));
    selectpicker.filter('.with-ajax-bank').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('banks.get.all') }}"));
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    function getAjaxSelectPickerOptions(url) {
      return {
        ajax: {
          url: url,
          type: 'get',
          dataType: 'json',
          // Use "\{\{\{q}}}" as a placeholder and Ajax Bootstrap Select will
          // automatically replace it with the value of the search query.
          data: function() {
            var params = {
              q: '\{\{\{q}}}',
              c: $('#cardCode').val()
            };
            return params;
          }
        },
        success: function(data) {
          $('.selectpicker').selectpicker('refresh');
        },
        locale: {
          emptyTitle: 'Selecione'
        },
        log: 0,
        preprocessData: function(data) {
          var i, l = data.length,
            array = [];
          if (l) {
            for (i = 0; i < l; i++) {
              var name = data[i].name;
              var maxLength = 70;
              if (name.length > maxLength) {
                name = name.substr(0, maxLength) + '...';
              }
              array.push($.extend(true, data[i], {
                text: name,
                value: data[i].value,
                data: {
                  subtext: '' //data[i].name
                }
              }));
            }
          }
          // You must always return a valid array when processing data. The
          // data argument passed is a clone and cannot be modified directly.
          return array;
        }
      }
    }

    function valide(button) {
      var erros = new Array();

      var codPN = $('#cardCode').val();
      if ($('#conditionPagamentos').val() == "") {
        erros.push('Informe a condição de pagamento! \n');
      }

      let countContracts = 0;
      let lines = 0;
      let contractCode = $('#contractGlobal').val() || null;
      let totalDoc = parseFloat($('input[name="docTotal"]').val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));

      $.each($('select[id*="contract-"]'), function(index, value) {
        if ($(value).val()) {
          countContracts++;
        }

        lines++;
        if (contractCode !== null && contractCode != $(value).val()) {
          erros.push('Os contratos não devem ser diferentes! \n');
        }
      });

      let contractData = contracts[contracts.findIndex(x => x.code === contractCode)];
      contractData

      if (countContracts > 0) {

        if (lines != countContracts) {
          erros.push(
            'Ao selecionar um contrato, todas as demais linhas do documento devem conter contrato selecionado! \n');
        }

        if (totalDoc > parseFloat(contractData.residualAmount)) {
          erros.push('O valor total do documento não deve exceder o valor residual atual do contrato! \n');
        }
      }

      if ($('#qtd-1').val() == "") {
        erros.push('Informe a quantidade! \n');
      }
      if (codPN == '') {
        erros.push('Adicione um parceiro! \n');
      }

      if (index == '0') {
        erros.push('Adicione um item! \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
        return false;
      } else {
        var table = $('#requiredTable');
        $('input, select').attr('disabled', false);
        $(button).attr('type', 'submit');
      }
    }

    @if (isset($head))
      function cancel() {

        waitingDialog.show('Cancelando...');

        $.ajax({
          type: 'POST',
          url: "{{ route('purchase.order.canceled') }}",
          data: {
            "id": "{{ $head->id }}",
            "justification": $('#justification').val()
          },
          headers: {
            'X-CSRF-TOKEN': $('body input[name="_token"]').val()
          },
          success: function(response) {
            waitingDialog.hide();
            
            document.location.reload(true);
          },
          error: function(response) {
            waitingDialog.hide();
            swal({
              title: "Opss...",
              text: "Ocorreu um erro, tente novamente.",
              icon: "error",
              buttons: ["Fechar"],
            })
          }
        })



        // swal({
        //     title: "Tem certeza que deseja cancelar?",
        //     text: "Esta operação não pode ser desfeita!",
        //     icon: "warning",
        //     //buttons: true,
        //     buttons: ["Não", "Sim"],
        //     dangerMode: true,
        //     input: "text",
        //     inputAttributes: {
        //       autocapitalize: "off"
        //     },
        //     preConfirm: async (text) => {
        //       console.log(text);
        //       try {
        //         $.ajax({
        //           type: 'POST',
        //           url: "{{ route('purchase.order.canceled') }}",
        //           data: {
        //             "id": "{{ $head->id }}",
        //             "justification": text
        //           },
        //           dataType: 'json',
        //           processData: false,
        //           contentType: false,
        //           headers: {
        //             'X-CSRF-TOKEN': $('body input[name="_token"]').val()
        //           },
        //           success: function(response) {
        //             waitingDialog.hide();
        //             console.log(response);
        //           },
        //           error: function(response) {
        //             waitingDialog.hide();
        //             swal({
        //               title: "Opss...",
        //               text: "Ocorreu um erro, tente novamente.",
        //               icon: "error",
        //               buttons: ["Fechar"],
        //             })
        //           }
        //         })

        //         return response.json();
        //       } catch (error) {
        //         Swal.showValidationMessage(`
        //           Não foi possível processar: ${error}
        //         `);
        //       }
        //     },
        //     allowOutsideClick: () => !Swal.isLoading()
        //   })
        //   .then((ok) => {
        //     if (ok) {
        //       // waitingDialog.show('Cancelando...')
        //       // window.location.href = "{{ route('purchase.order.canceled', $head->id) }}";
        //       console.log(123);
        //     }
        //   });
      }

      function closed(event) {
        event.preventDefault();
        swal({
            title: "Tem certeza que deseja Fechar?",
            text: "Esta operação não pode ser desfeita!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Fechando...')
              window.location.href = "{{ route('purchase.order.closed', $head->id) }}";
            }
          });
      }
    @endif
  </script>
@endsection
