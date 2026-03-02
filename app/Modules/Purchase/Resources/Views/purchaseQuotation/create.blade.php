@extends('layouts.main')
@section('title', 'Cotação de compras')
@section('content')

  @if (isset($head) && !empty($head->message))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      {{ $head->message }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form action="{{ route('purchase.quotation.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}

    <div class="card">
      <h5 class="card-header fw-bolder">
        @if (isset($head))
          Cotação de compras - detalhes
        @else
          Cotação de compras - cadastro
        @endif
      </h5>
      <div class="card-body">
        <div class="row" id='form'>
          @if (isset($head))
            <div class="row">
              <input type="hidden" name="id" id="id_quotation" value="{{ $head->id }}">
              <div class="col-md-2">
                <label>Cod. SAP</label>
                <input type="text" readonly value="{{$head->codSAP}}" class="form-control locked">
              </div>
              <div class="col-md-2">
                <label>Cod. WEB</label>
                <input type="text" readonly value="{{ $head->code }}" class="form-control locked">
              </div>
              <div class="col-md-2">
                <label>Status</label>
                <input type="text" readonly value="{{ $head::TEXT_STATUS[$head->status] }}" class="form-control locked">
              </div>
              <div class="col-md-4">
                <label>Usuário</label>
                <input type="text" readonly value="{{ $head->name_solicitante }}" class="form-control locked">
              </div>
            </div>
          @endif

          <div class="row mt-2">
            <div class="col-md-6">
              <label>Parceiro de Negócio</label>
              <select id="cardCode"
                class="form-control selectpicker with-ajax-partner @if (isset($head) && !empty($head->provider1)) locked @endif"
                name="cardCode" required>
                @if (isset($head) && !empty($head->provider1))
                  <option value="{{ $head['provider1'] }}" selected>{{ $head->provider1Name }}</option>
                @endif
              </select>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-3">
              <div class="form-group">
                <label>Data do Documento</label>
                <input type="date" name="dataDocumento" id="dataDocumento" class="form-control locked"
                  @if (isset($head->created_at)) value="{{ date_format($head->created_at, 'Y-m-d') }}"
                        @else value="{{ DATE('Y-m-d') }}" @endif
                  required readonly>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Data de Lançamento</label>
                <input type="date" name="dataLancamento" class="form-control locked"
                  @if (isset($head->data_i)) value="{{ $head->data_i }}" @else value="{{ DATE('Y-m-d') }}" @endif
                  required readonly>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Data de Entrega</label>
                <input type="date" name="dataVencimento" id="dataVencimento" class="form-control"
                  @if (isset($head->data_f)) value="{{ $head->data_f }}" @else value="{{ DATE('Y-m-d') }}" @endif>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-md-3">
              <div class="form-group">
                <label>Condição de Pagamento</label>
                <select class="form-control selectpicker" data-live-search="true" data-size="10" name="paymentTerms"
                  id="conditionPagamentos" onChange="getDueDate()" @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif>
                  <option value=''>Selecione</option>
                  @foreach ($paymentConditions as $key => $value)
                    <option value="{{ $value['GroupNum'] }}" @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                      {{ $value['PymntGroup'] }}</option>
                  @endForeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-3">
      <h6 class="card-header fw-bolder">Documentos associados</h6>
      <div class="card-body">
        @if ($head->parent)
          <div class="row">
            <div>
              <p class="fw-bolder">Cotação de compra</p>
              <div class="col-12">
                <a href="{{ route('purchase.quotation.read', $head->parent) }}" class="btn btn-primary"
                  style="padding: 5px 20px;">{{ getQuotationCode($head->parent) }}</a>
              </div>
            </div>
          </div>
          <hr>
        @endif
        @if (!$purchase_orders->isEmpty())
          <div class="row">
            <div class="form-group">
              <p class="fw-bolder">Pedidos de compras</p>
              @foreach ($purchase_orders as $purchase_order)
                <a href="{{ route('purchase.order.read', $purchase_order->id) }}" class="btn btn-primary ms-1"
                  style="padding: 5px 20px;">{{ $purchase_order->code }}</a>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
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
            <a class="nav-link" data-toggle="tab" href="#tab-3">Comparativo</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-4">
            <a class="nav-link" data-toggle="tab" href="#tab-4">Anexos</a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active mt-5" id="tab-1">
            <div class="panel-body">
              {{-- <img src="{{asset('images/add.png')}}" data-toggle="modal" onclick="loadingItems();" data-target="#itensModal" @if (isset($head) && ($head->status == '2' || $head->status == '0')) style="display: none;" @endif/> --}}
              <div class="table-responsive">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Solic. Compras</th>
                      <th>Descrição</th>
                      <th>Ult. Fornecedor</th>
                      <th>Ult. Preço</th>
                      <th>Qtd. Necessária</th>
                      <th>Qtd. Pendente</th>
                      <th>Quantidade</th>
                      <th>Preço Unitário</th>
                      <th>Total</th>
                      <th>Opção</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $cont = 1;
                    @endphp
                    @foreach ($body as $key => $value)
                      <tr id="rowTable-{{ $cont }}" data-row="{{ $cont }}" value="{{ $value['id'] }}"
                        @if ($value['status'] == 2) style="background-color: rgb(220, 223, 228);" @endif>
                        <td>{{ $cont }}
                          <input type='hidden' value="{{ $value['idPurchaseQuotation'] }}"
                            name="requiredProducts[{{ $cont }}][idPurchaseQuotation]">
                          <input type='hidden' name='requiredProducts[{{ $cont }}][idItem]'
                            id='item-{{ $cont }}' value='{{ $value['id'] }}'>
                        </td>
                        <td>
                          @if(!empty($value->idPurchaseRequest))
                            <a href="{{ route('purchase.request.read', $value->idPurchaseRequest) }}" class="btn btn-primary ms-1"
                              style="padding: 5px 20px;">{{ $value->purchase_request->code }}</a>
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
                          <input type='hidden' value="{{ $value['itemCode'] }}"
                            name="requiredProducts[{{ $cont }}][codSAP]">
                          <input type='hidden' value="{{ $value['itemName'] }}"
                            name="requiredProducts[{{ $cont }}][itemName]">
                        </td>
                        <td>
                          @if (!empty($value['lastProvider']))
                            <a href="{{ route('partners.edit', $value['lastProvider']) }}" target="_blank"
                              class='btn btn-primary' value="{{ $value['lastProvider'] }}"
                              name='requiredProducts[{{ $cont }}][lastProvider]'
                              id="lastProvider-{{ $cont }}">{{ $value['lastProvider'] }}</a>
                          @endif
                        </td>
                        <td>
                          <input type='text' class='form-control money min-100 locked'
                            value="{{ number_format($value['lastPrice'], 2, ',', '.') }}"
                            name='requiredProducts[{{ $cont }}][lastPrice]' id="lastPrice-{{ $cont }}"
                            readonly>
                        </td>
                        <td>
                          <div class="input-group min-200 mt-1">
                            <div class="input-group-text">{{ $value['itemUnd'] ?? ' ' }}</div>
                            <input value="{{ $value['qtd'] }}"
                              onblur="setMaskMoney();gerarTotal({{ $cont }})" type="text"
                              class="form-control qtd locked min-100" name="requiredProducts[{{ $cont }}][qtd]"
                              min="1" readonly></td>
                            <input type='hidden'
                              value="{{ $value['itemUnd'] }}" name="requiredProducts[{{ $cont }}][itemUnd]">
                          </div>
                        <td><input value="{{ (float) $value['quantityPendente'] }}"
                            type="text" class="form-control qtd locked min-100" id="qtdpendente-{{ $cont }}"
                            readonly></td>
                        <td><input value="{{ (float) $value['qtdP1'] }}"
                            onclick='destroyMask(event)'
                            onblur="setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)"
                            id="qtd-{{ $cont }}" type="text" class="form-control qtd min-100"
                            name="requiredProducts[{{ $cont }}][qtdP1]" min="1"></td>
                        <td><input value="{{ (float) $value['priceP1'] }}"
                            onclick='destroyMask(event)'
                            onblur="setMaskMoney();gerarTotal({{ $cont }}); focusBlur(event)"
                            id="price-{{ $cont }}" type="text" class="form-control moneyPlus min-100"
                            name="requiredProducts[{{ $cont }}][priceP1]">
                        <td><input onblur="gerarTotal({{ $cont }})"
                            id="totalLinha-{{ $cont }}" type="text" class="form-control money locked min-100"
                            name="requiredProducts[{{ $cont }}][totalP1]" value="{{ $value['totalP1'] }}"
                            readonly>
                        </td>
                        <td id="itemTable-{{$cont}}" class="text-center">
                            @if ($value['status'] == 1 && $head->status == $head::STATUS_OPEN)
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
                    <input type="hidden" value="{{ $cont - 1 }}" id="cont">
                  </tbody>
                </table>
              </div>
              <div class="card mt-3">
                <h6 class="card-header fw-bolder">Totais</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-2">
                      <label>Total de despesas</label>
                      <input type="text" class="form-control money locked" name="totalSemDesconto" readonly
                        @if (isset($head)) value="{{ $head_expenses->sum('lineTotal') }}" @endif
                        id="totalDespesas">
                    </div>
                    <div class="col-md-2">
                      <label>Total</label>
                      <input type="text" class="form-control locked" name="docTotal"
                        @if (isset($head)) value="{{ $head->docTotal }}" @endif readonly id="totalNota">
                      <input type="hidden" class="form-control"
                        @if (isset($head)) value="{{ number_format($head->docTotal, 2, ',', '.') }}" @endif
                        id="docTotal">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-3">
            <div class="col-md-12 mb-1 mt-4">
              <div class="card mt-3">
                <h6 class="card-header fw-bolder">Cotações associadas</h6>
                <div class="card-body">
                  <?php $quotations_associated = !empty($comparative_head); ?>
                  <div class="btn-group">
                    @foreach ($comparative_head as $head_comparative)
                      <a type="button" href="{{ route('purchase.quotation.read', $head_comparative->id) }}"
                        class="btn btn-primary me-1">{{ $head_comparative->code }}</a>
                    @endforeach
                  </div>
                </div>
              </div>
              <div class="table-responsive mt-3">
                <h5>Itens recomendados
                  <svg class="icon icon-sm text-tooltip" data-coreui-toggle="tooltip" title="Os melhores itens são escolhidos usando dois critérios: quantidade próxima da solicitada e pelo menor preço.">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#question-circle') }}"></use>
                  </svg>
                </h5>
                <div class="accordion mt-3 mb-3" id="filterBestItemsAccordion">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button" type="button" data-coreui-toggle="collapse"
                        data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Filtros
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                      data-coreui-parent="#filterBestItemsAccordion">
                      <div class="accordion-body">
                        <div class="row">
                          <div class="col-md-4">
                            <label>Fornecedor</Label>
                            <select id="cardCodeFilterBestItems" class="form-control selectpicker with-ajax-partner" data-container="body"></select>
                          </div>
                           {{-- <div class="col-md-3">
                            <label>Ordenar por</Label>
                              <select class="form-control selectpicker" id="deliveryDateFilterBestItems">
                                <option value=''>Selecione</option>
                                <option value=''>Menor prazo de entrega</option>
                                <option value=''>Maior prazo de entrega</option>
                                <option value=''>Menor quantidade de parcelas</option>
                                <option value=''>Maior quantidade de parcelas</option>
                              </select>
                          </div> --}}
                          {{-- <div class="col-md-3">
                            <label>Condição de Pagamento</label>
                            <select class="form-control selectpicker" id="conditionPagamentosFilterBestItems" required>
                              <option value=''>Selecione</option>
                              <option value=''>Menor quantidade de parcelas</option>
                              <option value=''>Maior quantidade de parcelas</option>
                            </select>
                          </div> --}}
                        </div>
                        <div class="row mt-2">
                          <div class="col-12">
                            <button class="btn btn-primary btn-sm float-end ms-1" type="button" onclick="tableFilterBestItems.draw()">Filtrar</button>
                            <button class="btn btn-warning btn-sm float-end" onclick="clearComparativeForm(event); tableFilterBestItems.draw()" type="button">Limpar formulário</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableFilterBestItems" class="table table-default table-striped table-bordered table-hover" style="width: 100%;">
                          <thead>
                            <tr>
                              <th style="width: 5%;">#</th>
                              <th style="width: 10%;">Cotação</th>
                              <th style="width: 15%;">Fornecedor</th>
                              <th style="width: 10%;">Data Entrega</th>
                              <th style="width: 15%;">Cond. pagamento</th>
                              <th style="width: 15%;">Descrição Item</th>
                              <th style="width: 10%;">Quantidade</th>
                              <th style="width: 10%;">Preço Unit.</th>
                              <th style="width: 10%;">Total</th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>
                        <button type="button" class="btn btn-primary float-end" onclick="gerarPedidoComparativoItems()">Gerar Pedido de Compras</button>
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
                      @foreach($expenses as $key => $value)
                        @php $expense = $head_expenses->where("expenseCode", "=", $value["code"])->first() ?? [];
                        @endphp
                        <tr>
                          <td>
                            {{ $value["value"] }}
                            <input type="hidden" name="expenses[{{ $key }}][expenseCode]" value="{{ $value["code"] }}">
                          </td>
                          <td>
                            <input
                              @if (!empty($expense)) value='{{ number_format($expense->lineTotal) }}' @endif
                              id='di_total' onclick='destroyMask(event)'
                              onblur="setMaskMoney();sumAllValues();focusBlur(event)" type="text"
                              class="form-control money min-100" name="expenses[{{ $key }}][lineTotal]">
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][tax]" data-container="body">
                              <option value="" readonly>Selecione</option>
                              @foreach ($tax as $item)
                                <option value="{{ $item->value }}"
                                  @if (!empty($expense) && $expense->tax == $item->value) selected @endif>{{ $item->value }} - {{ $item->name }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <input @if (!empty($expense)) && value='{{ $expense->comments }}' @endif
                              type="text" class="form-control min-100" name="expenses[{{ $key }}][comments]">
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][project]" data-container="body">
                              <option value="">Selecione</option>
                              @foreach ($projeto as $item)
                                <option value="{{ $item['value'] }}"
                                  @if (!empty($expense) && $expense->project == $item['value']) selected @endif>{{ $item['value'] }} - {{ $item['name'] }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][costCenter]" data-container="body">
                              <option value="" readonly>Selecione</option>
                              @foreach ($centroCusto as $item)
                                <option value="{{ $item['value'] }}"
                                  @if (!empty($expense) && $expense->costCenter == $item['value']) selected @endif>
                                    {{ $item['value'] }} - {{ $item['name'] }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>
                            <select class="form-control selectpicker" name="expenses[{{ $key }}][costCenter2]" data-container="body">
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
          <div class="tab-pane" id="tab-4">
            <div class="panel-body mt-3">
              <div class="card">
                <div class="card-body">
                  <label>Observação</label>
                  <textarea class="form-control" rows="5" name="obsevacoes" maxlength="200"
                    @if (isset($head->status) && $head->status != $head::STATUS_OPEN) readonly @endif>
                    @if (isset($head->comments))
                      {{ $head->comments }}
                    @endif
                  </textarea>
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
                    {{-- @if (isset($head))
                      <a class="btn btn-primary w-25 me-2" onclick="updateUploads(event)">Atualizar anexos</a>
                    @endif --}}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12 mt-5">
      @if (isset($head->status) && ($head->status == $head::STATUS_OPEN || $head->status == $head::STATUS_PENDING))

        <div class="dropdown">
          <button class="btn btn-dark dropdown-toggle float-start" type="button" data-coreui-toggle="dropdown"
            aria-expanded="false">
            <svg class="icon">
              <use xlink:href="{{ asset('icons_assets/custom.svg#copy-to') }}"></use>
            </svg> Copiar para
          </button>
          <ul class="dropdown-menu">
            <li><a href="{{ route('purchase.quotation.duplicate', $head->parent ?? $head->id) }}"
                class="dropdown-item">Cotação associada</a></li>
            <li><a type="button" class="dropdown-item" @if (!empty($head->codSAP)) onclick="criarPedidoItem()" @else onclick="alert('É necessário aguardar até que a cotação seja sincronizada com o SAP!')"  @endif>Pedido de compra</a></li>
          </ul>
        </div>

        @if ((int) $head->status == $head::STATUS_OPEN && $head->is_locked != true)
          <button class="btn btn-primary float-end me-1" onclick="valide(this)" type="button">Atualizar</button>
        @endif
        <button class="btn btn-danger float-end me-1" onclick="cancel()">Cancelar</button>
        {{-- <button class="btn btn-warning float-end me-1" onclick="closed()">Fechar</button> --}}

      @endif
    </div>
  </form>
  @if (isset($head))
    <div class="modal inmodal" id="modalPedido" tabindex="-1" role="dialog" aria-hidden="true">
      <form action="{{ route('purchase.quotation.from.purchase') }}" method="post" id="formPedido"
        enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" @if (isset($head)) value="{{ $head->id }}" @endif name="id">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
              <h5 class="modal-title">Gerar pedido de compras</h5>
              <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
              <div class="table-responsive">
                <table id="tablePedido" class="table table-default table-striped table-bordered table-hover"
                  style="width: 100%">
                  <thead>
                    <tr>
                      <th style="width: 5%" class="text-center">
                        <input type="checkbox" class="form-check-input p-2" onclick="checkAllPedidos(event)">
                      </th>
                      <th style="width: 15%">Cotação</th>
                      <th style="width: 30%">Item</th>
                      <th style="width: 15%">Valor</th>
                      <th style="width: 35%">Fornecedor</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $contP = 0;
                    @endphp
                    @foreach ($body as $key => $value)
                    @if ($value['quantityPendente'] > 0)
                    <?php $best_price_item = getBestPriceQuotationItem($value['idItemPurchaseRequest']); ?>
                    {{-- {{dd($comparative_head, $best_price_item, $value)}} --}}
                        <tr id="itemPedido-{{ $contP }}" data-row="{{ $contP }}"
                          value="{{ $contP }}">
                          <td class="text-center">
                            <input type="checkbox" class="form-check-input p-2"
                              name="itemPedido[{{ $contP }}][itemCode]" value="{{ $value['itemCode'] }}"
                              id="checkP-{{ $contP }}" disabled>
                            <input type="hidden" name="itemPedido[{{ $contP }}][itemID]"
                              @if (!empty($best_price_item)) value="{{ $best_price_item['id'] }}" @endif>
                            <input type="hidden" name="itemPedido[{{ $contP }}][idQuotation]"
                              value="{{ $head->id }}">
                          </td>
                          <td>
                            <select name="providerPurchase" id="" class="form-control selectpicker" data-container="body"
                              onchange="getQuotationItem(event)" onfocus="getQuotationItem(event)">
                              @foreach ($comparative_head as $c_head)
                                <option value="{{ $c_head->id }}" data-item="{{ $value['idItemPurchaseRequest'] }}"
                                  data-row="{{ $contP }}" @if (!empty($best_price_item) && $best_price_item->idPurchaseQuotation == $c_head->id) selected @endif>
                                  {{ $c_head->code }}</option>
                              @endforeach
                            </select>
                          </td>
                          <td>{{ $value['itemCode'] . ' - ' . $value['itemName'] }}</td>
                          <td><input type="text" class="form-control money locked" readonly
                              id="valor-{{ $contP }}"></td>
                          <td>
                            <input id="pedidoProvider-{{ $contP }}" type="hidden"
                              name="itemPedido[{{ $contP }}][provider]" class="form-control" readonly>
                            <input id="pedidoProvider-{{ $contP }}-text" class="form-control" readonly>
                          </td>
                        </tr>
                        @php
                          $contP++;
                        @endphp
                      @endif
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary" onclick="gerarPedido(event)">Gerar Pedido</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    @php $partner = $head->partner(); @endphp

    @if(!empty($partner))

      <!-- Send To Partner Modal -->
      <form id="sendToPartnerForm">
        <div class="modal fade" id="sendToPartnerModal" tabindex="-1" aria-labelledby="sendToPartnerLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="sendToPartnerLabel">Enviar Cotação Para o Parceiro</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" value="{{ $head->id }}">
                <div class="col-12 mt-2">
                  <label>Forma de envio</label>
                  <select name="type" class="form-control selectpicker" onchange="changeSendToPartnerContactMask(event)">
                    <option value="1" selected>E-mail</option>
                    {{-- <option value="2">Whatsapp</option> --}}
                  </select>
                </div>
                <div class="col-12 mt-2">
                  <label>Contato</label>
                  <input type="email" class="form-control" name="contact" value="{{ $partner->E_Mail }}">
                </div>
                <div class="col-12 mt-2">
                  <label>Mensagem</label>
                  <textarea type="text" class="form-control" name="message" placeholder="Mensagem" cols="40" rows="8"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-white" data-coreui-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Enviar</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    @endif
  @endif

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    $('.dataTables-example').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });

    $(document).ready(function() {
      $.each($(`input[id*='qtdpendente']`), function(index, element) {
        if ($(element).val() <= 0) {
          $(element).closest('tr').find('select').attr('disabled', true);
          $(element).closest('tr').find('input').addClass('locked');
        }
      });

      fillTopNav(
        @if (isset($head))
          @json($head->getTopNavData())
        @endif 
      );

      setMaskMoney();

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
      @endif

      setTimeout(function() {
        sumAllValues();
      }, 1000);

    });

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    @if (isset($body))
      var index = $('#cont').val();
    @else
      var index = 0;
    @endif

    var expensesIndex = {{ count($expenses) }}

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
      $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"]'), function(index, value) {
        $(value).trigger('mask.maskMoney')
      })
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


    function getDueDate() {
      var codition = $('#conditionPagamentos').val();
      var date = $('#dataDocumento').val();
      $.get("{{ route('home/get/dueDate') }}" + '/' + date + '/' + codition, function(items) {
        document.getElementById('dataVencimento').value = items;
      });
    }

    var aux = 0;

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

    var used = new Array();

    function gerarTotal(code) {
      var qtd = $('#qtd-' + code)
      var qtd_pendente = parseFloat($('#qtdpendente-' + code).val().replace(/[.]/gi, '').replace(/[,]/gi, '.'))

      if (parseFloat(qtd.val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) > qtd_pendente) {
        qtd.val(qtd_pendente);
        alert('A quantidade informada não deve ser maior que a quantidade pendente do item!');
        setMaskMoney()
        return
      }
      var preco = document.getElementById('price-' + code).value.replace(/[.]/gi, '').replace(/[,]/gi, '.');
      var total = parseFloat(qtd.val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco);
      if (!isNaN(total)) {
        document.getElementById('totalLinha-' + code).value = (total.toFixed(2));
      }

      setTimeout(function() {
        sumAllValues();
      }, 0500);
      setMaskMoney()
    }

    function sumAllValues() {
      var total = 0;
      var totalDespesas = 0;
      var i = 0;
      var x = 0;

      for (let index = 0; index < expensesIndex; index++) {
        const value = parseFloat($(`input[name="expenses[${index}][lineTotal]"]`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) || 0;
        totalDespesas += value;
      }

      for (i = 1; i <= index; i++) {
        if (document.getElementById('totalLinha-' + i)) {
          x = document.getElementById('totalLinha-' + i).value || 0;
          total += parseFloat(x);
        }
      }

      if (isNaN(total)) {
        total = 0;
      }

      total += totalDespesas;

      var x;
      var i;
      
      totalDoc = total.format(2, ",", ".");
      total = (Math.round((parseFloat(total)) * 100) / 100);
      document.getElementById('totalNota').value = total.format(2, ",", ".");
      //document.getElementById('total_pagameto_modal').value = total.format(2, ",", ".");
      // addExpensesFromValues();
    }


    var totalDoc = 0.00;


    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      var row = tr.attr('data-row');
      var deleted = false;
      if ($(`input[name="requiredProducts[${row}][id]"]`).val() == '') {
        deleted = true;
      }

      if ($(tr.attr('value'))) {
        $(tr).append($(`<input type="hidden" name="requiredProducts[${row}][deleted]" value="${tr.attr('value')}">`))
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

    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    function checkAllPedidos(event) {
      $('#modalPedido tbody').find('input[type="checkbox"]').click()
    }

    function valide(button) {
      var erros = new Array();

      if ($('#qtd-1').val() == "") {
        erros.push('Informe a quantidade! \n');
      }
      if ($('#cardCode').val() == "") {
        erros.push('Adicione um fornecedor! \n');
      }

      if (index == '0') {
        erros.push('Adicione um item! \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        var table = $('#requiredTable');
        $('input, select').attr('disabled', false);
        $(button).attr('type', 'submit');
      }

    }

    @if (isset($head))
      function cancel() {
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
              window.location.href = "{{ route('purchase.quotation.canceled', $head->id) }}";
            }
          });
      }

      function closed() {
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
              waitingDialog.show('Cancelando...')
              window.location.href = "{{ route('purchase.order.closed', $head->id) }}";
            }
          });
      }

      let contP = {{ $contP }};

      function gerarPedido(event) {
        var validate = false;
        for (var i = 0; i < contP; i++) {
          if ($('#checkP-' + i).prop('checked') === true) {
            validate = true;
          }
        }
        
        if (!validate) {
          event.preventDefault();
          swal('Atenção', 'Selecione ao menos 1 item para prosseguir!', 'warning');
          return false;
        }
      }

      function gerarPedidoComparativoItems(event){
        var validate = false;
        $.each($('input[id*="idQuotationComparation-"]'), function(index, element){
          if ($(element).prop('checked') === true) {
            validate = true;
          }
        });
        
        if (!validate) {
          event.preventDefault();
          swal('Atenção', 'Selecione ao menos 1 item para prosseguir!', 'warning');
          return false;
        }else{
          let form = $(`<form style="display: none;" action="{{ route('purchase.quotation.from.purchase') }}" method="post"></form>`);
          $.each($('#tableFilterBestItems tbody input'), function(index, value){
            form.append(value)
          });
          form.append($(`<input name="_token" value="{{ csrf_token() }}" type="hidden">`));
          form.append($(`<input name="id" value="{{ $head->id }}" type="hidden">`));
          $('body').append(form);
          form.submit();
        }
      }

      function criarPedidoItem() {
        $('#modalPedido').modal('show');
      }

      $('#modalPedido').on('show.coreui.modal', event => {
        setMaskMoney();
        $.each($('#modalPedido select'), function(index, value) {
          $(value).focus();
          $(value).blur();
        })
      })

      function getQuotationItem(event) {
        let row = $(event.target).find(':selected').data('row');
        $(event.target).parent().parent().find('input').val('');
        $.ajax({
          type: 'get',
          url: "{{ route('purchase.quotation.getitem') }}",
          dataType: 'json',
          data: {
            'item': $(event.target).find(':selected').data('item'),
            'head': $(event.target).val()
          },
          success: function(response) {
            if (response.item !== null && response.item.codSAP !== null) {
              if (response.item.totalP1) {
                $(`#checkP-${row}`).attr('disabled', false);
              }

              $(`#itemPedido-${row}`).find(`#valor-${row}`).val(response.item.totalP1 || 0)
              $(`#itemPedido-${row}`).find(`#pedidoProvider-${row}-text`).val(`${response.item.providerCode || ' '} - ${response.item.providerName || ' '}`)
              $(`#itemPedido-${row}`).find(`#pedidoProvider-${row}`).val(`${response.item.providerCode}`)
              $(`input[name="itemPedido[${row}][itemID]"]`).val(response.item.id);
              $(`input[name="itemPedido[${row}][itemCode]"]`).val(response.item.itemCode);
              $(`input[name="itemPedido[${row}][idQuotation]"]`).val(response.item.idPurchaseQuotation);
            }else{
              $(`#checkP-${row}`).attr('disabled', true);
              $(`#itemPedido-${row}`).find(`#valor-${row}`).val('')
              $(`#itemPedido-${row}`).find(`#pedidoProvider-${row}-text`).val('')
              $(`#itemPedido-${row}`).find(`#pedidoProvider-${row}`).val('')
              $(`input[name="itemPedido[${row}][itemID]"]`).val('');
              $(`input[name="itemPedido[${row}][itemCode]"]`).val('');
              $(`input[name="itemPedido[${row}][idQuotation]"]`).val('');
            }
          }
        });
      }

      function updateUploads(event) {
        event.preventDefault();
        waitingDialog.show("Processando...");
        let formData = new FormData();
        $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
          formData.append('input-file-preview[]', value)
        })
        formData.append('table', 'purchase_quotation')
        formData.append('id', $('input[name="id"]').val())

        $.ajax({
            type: 'POST',
            url: "{{ route('purchase.quotation.updateUploads') }}",
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

      $('#sendToPartnerForm').on('submit', function(event){
        event.preventDefault();
        waitingDialog.show('Enviando cotação para o parceiro...');
        $.ajax(
          {
            type: 'POST',
            url: "{{route('purchase.quotation.sendToPartner')}}",
            data: $(this).serialize(),
            dataType: 'json',
            headers: {
              'X-CSRF-TOKEN': $('body input[name="_token"]').val()
            },
            success: function(data, textStatus, xhr){
                waitingDialog.hide();
                if(xhr.status === 200){
                    swal({
                        title: `Tudo certo!`,
                        text: "A cotação foi enviada com sucesso!",
                        icon: "success"
                    })
                }else{
                    waitingDialog.hide();
                    swal({
                        title: "Opss...",
                        text: data.message,
                        icon: "error"
                    });
                }
            },
            error: function(data, textStatus, xhr){
              // console.log(data.status);
                waitingDialog.hide();
                swal({
                    title: "Opss...",
                    text: data.message,
                    icon: "error",
                    buttons: ["Fechar"],
                })
            }
        })
      });

      let tableFilterBestItems = $('#tableFilterBestItems').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        searching: false,
        ordering:  false,
        ajax: {
          url: "{{ route('purchase.quotation.filterBestItems') }}",
          data: function(data) {
            data.cardCode = $('#cardCodeFilterBestItems').val();
            data.deliveryDate = $('#deliveryDateFilterBestItems').val();
            data.conditionPagamentos = $('#conditionPagamentosFilterBestItems').val();
            data.idItemsPurchaseRequest = @json($body->pluck('idItemPurchaseRequest'));
          }
        },
        initComplete: function(settings, json) {
          $('.text-truncate').tooltip();
        },
        columns: [
          {
            name: 'checkbox',
            data: 'id',
            render: renderCheckBox,
          },
          {
            name: 'redirectQuotationButton',
            data: 'id',
            render: renderRedirectQuotationButton
          },
          {
            name: 'provider1',
            data: 'CardCode',
            render: renderProvider
          },
          {
            name: 'data_f',
            data: 'data_f',
            render: renderFormatedDate
          },
          {
            name: 'PymntGroup',
            data: 'PymntGroup',
          },
          {
            name: 'itemCode',
            data: 'itemCode',
            render: renderItemName
          },
          {
            name: 'qtdP1',
            data: 'qtdP1',
            render: renderFormatedQuantity,
          },
          {
            name: 'priceP1',
            data: 'priceP1',
            render: renderFormatedPrice
          },
          {
            name: 'totalP1',
            data: 'totalP1',
            render: renderFormatedPrice
          },
        ],
        language: dataTablesPtBr,
      });

      function renderRedirectQuotationButton(value, display, data){
        return `
          <a href="{{ route('purchase.quotation.read') }}/${value}" class="btn btn-primary" _target="blank">${data.code}</a>
        `;
      }

      function renderProvider(value, display, data){
        return `
          <div class="d-flex flex-row" style="max-width: 16em;">
            <a class="text-warning" href="{{ route('partners.edit') }}/${value}" target="_blank">
              <svg class="icon icon-lg">
                <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
              </svg>
            </a>
            <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
              title="${value} - ${data.CardName}">${value} - ${data.CardName}</span>
          </div>
        `;
      }

      function renderItemName(value, display, data) {
        return `
        <div class="d-flex flex-row" style="max-width: 16em;">
          <a class="text-warning" href="{{ route('inventory.items.edit') }}/${data.itemCode}" target="_blank">
            <svg class="icon icon-lg">
              <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
            </svg>
          </a>
          <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${data.itemCode} - ${data.itemName}">${data.itemCode} - ${data.itemName}</span>
          </div>`;
      }

      function renderFormatedQuantity(valor) {
        return new Intl.NumberFormat('pt-BR').format(valor);
      }

      function renderFormatedPrice(data, type, row){
        const formatter = new Intl.NumberFormat('pt-BR', {
          style: 'currency',
          currency: 'BRL'
        });
        return formatter.format(data);
      }

      let contRenderCheckBox = 0;
      function renderCheckBox(value, type, data) {
        if(parseFloat(data.quantityPendente) > 0){
          return `
            <center>
                <input id="idQuotationComparation-${contRenderCheckBox}" name="itemPedido[${contRenderCheckBox}][itemCode]" value="${data.itemCode}" type="checkbox" class="form-check" style="width: 20px;">
                <input type="hidden" name="itemPedido[${contRenderCheckBox}][itemID]" value="${data.id}">
                <input type="hidden" name="itemPedido[${contRenderCheckBox}][idQuotation]" value="${data.idPurchaseQuotation}">
                <input type="hidden" name="itemPedido[${contRenderCheckBox}][provider]" value="${data.CardCode}">
            </center>`
        }else{
          return '';
        }
      }

      function clearComparativeForm(event){
        $.each($('#filterBestItemsAccordion .accordion-body').find('select, input'), function(index, element){
          element.value = '';
          if($(element).is("select")){
            $(element).selectpicker('destroy');
            $(element).selectpicker(selectpickerConfig).selectpicker('render');
          }
        });
      }

      function changeSendToPartnerContactMask(event){
        let element = $(element);
        if(element.val() == '1'){
          element.attr('type', 'email')
        }
      }
    @endif
  </script>
@endsection
