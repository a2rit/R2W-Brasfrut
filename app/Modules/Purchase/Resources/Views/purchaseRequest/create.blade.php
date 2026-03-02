@extends('layouts.main')
@section('title', 'Solicitação de compra')
@section('content')
  @if (!empty($head->message))
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif
  <form action="{{ route('purchase.request.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    @if (isset($head))<input type="hidden" name="id"
        value="{{ $head->id }}">@endif

    <div class="card">
      <h5 class="card-header fw-bolder">
        @if (isset($head))
          Solicitação de compras - detalhes
        @else
          Solicitação de compras - cadastro
        @endif
      </h5>
      <div class="card-body">
        <div class="row">
          @if (isset($head))
            <input type="hidden" name="id" value="{{ $head->id }}">
            <div class="col-md-2">
              <label class="fw-bold">Cod. SAP</label>
              <input type="text" readonly value="{{ $head->codSAP }}" class="form-control locked">
            </div>
            <div class="col-md-2">
              <label>Cod. WEB</label>
              <input type="text" readonly value="{{ $head->code }}" class="form-control locked">
            </div>
            <div class="col-md-4">
              <label>Usuário</label>
              <input type="text" value="{{ $head->name }}" readonly class="form-control locked">
            </div>
          @endif
          <div class="form-group col-md-4">
            <label>Solicitante</label>
            <select id="requester" class="form-control selectpicker" @if (isset($head) && isset($head->codSAP)) disabled @endif
              name="requester" required>
              <option></option>
              @foreach ($requesters as $requester)
                <option @if (isset($head) && $head->idSolicitante == $requester->id) selected @endif value='{{ $requester->id }}'>
                  {{ $requester->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row mt-1">
          <div class="col-md-2">
            <label>Data Solicitação:</label>
            <input type="date" class="form-control locked"
              @if (isset($head)) value="{{ date_format($head->created_at, 'Y-m-d') }}" @else value="{{ date('Y-m-d') }}" @endif
              required readonly>
          </div>
          <div class="col-md-2">
            <label>Data Necessária:</label>
            <input type="date" name="data" class="form-control"
              @if (isset($head)) value="{{ $head->requriedDate }}" @else value="{{ date('Y-m-d') }}" @endif
              @if (isset($head) && ($head->codStatus != '1' && $head->codStatus != '3')) readonly @endif required>
          </div>
        </div>

        @if (isset($head))
          <div class="row mt-2">
            <div class="col-md-3">
              <div class="form-group">
                <label for="status">Status</label>
                <input id="status" type="text" class="form-control locked"
                  value="{{ $head::TEXT_STATUS[$head->codStatus] }}" readonly>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="updated">Atualizado em</label>
                <input id="updated" type="text" class="form-control locked"
                  value="{{ date('d/m/Y H:i:s', strtotime($head->updated_at)) }}" readonly>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="sync_at">Sincronizado em</label>
                <input id="sync_at" type="text" readonly class="form-control locked"
                  @if (!empty($head->codSAP) && !empty($head->sync_at)) value="{{ date('d/m/Y H:i:s', strtotime($head->sync_at)) }}"
                    @elseif(!empty($head->codSAP) && empty($head->sync_at)) value="{{ date('d/m/Y H:i:s', strtotime($head->updated_at)) }}" 
                    @elseif((empty($head->codSAP) || !empty($head->codSAP)) && $head->is_locked == '1')
                      value="PROCESSANDO"
                    @else
                      value="AGUARDANDO USUARIO" @endif>
              </div>
            </div>
          </div>
      </div>
    </div>
    <div class="card mt-3">
      <h6 class="card-header fw-bolder">Documentos associados</h6>
      <div class="card-body">
        @if (!empty($head->internal_request))
          <div class="row mb-2">
            <div class="form-group">
              <h6 class="fw-bolder">Requisição interna</h6>
              <a href="{{ route('inventory.request.searching', $head->idInternalRequest) }}"
                class="btn btn-primary mt-1">{{ $head->internal_request->code }}</a>
            </div>
          </div>
          <hr>
        @endif
        @if ($head->isQuotation)
          <div class="row mb-2">
            <div class="form-group">
              <h6 class="fw-bolder">Cotação</h6>
              <div>
                @foreach (getAllQuotationsCode($head->id) as $value)
                  <a href="{{ route('purchase.quotation.read', $value['id']) }}"
                    class="btn btn-primary mt-1">{{ $value['code'] }}</a>
                @endforeach
              </div>
            </div>
          </div>
          <hr>
        @endif
        <?php $associatedOrders = getAllOrdersCode($head->id); ?>
        @if (!empty($associatedOrders))
        <hr>
          <div class="row mt-2">
            <div class="form-group">
              <h6 class="fw-bolder">P. Compras</h6>
              <div>
                @foreach ($associatedOrders as $value)
                  <a href="{{ route('purchase.order.read.code', $value) }}"
                    class="btn btn-primary mt-1">{{ $value }}</a>
                @endforeach
              </div>
            </div>
          </div>
        @endif
        @endif
      </div>
    </div>
    <div class="row mt-4">
      <div class="tabs-container container-fluid">
        <div class="tab-content">
          <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
              <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
              <a class="nav-link" data-toggle="tab" href="#tab-2">Anexos</a>
            </li>
          </ul>
          <div class="tab-pane active" id="tab-1">
            <div class="panel-body mt-3">
              <a href='#' class='text-dark text-tooltip' data-coreui-placement="right" title="Adicionar linha"
                onclick="loadingItems()" @if (isset($head->codSAP)) style="display: none;" @endif>
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
                      <th>Descrições</th>
                      <th>Quantidade</th>
                      @if (Route::currentRouteName() === 'purchase.request.read')
                        <th>Qtd. Pendente</th>
                      @endif
                      <th style="width: 8%;">Ult. Fornecedor</th>
                      <th>Projeto</th>
                      <th>C. de Custo</th>
                      <th>C. de Custo 2</th>
                      <th>Depósito</th>
                      <th>Conta</th>
                      <th style="width: 8%;">Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if (isset($body))
                      <?php $cont = 1; ?>
                      @foreach ($body as $key => $value)
                        <tr id="rowTable-{{ $cont }}">
                          <td>{{ $cont }}</td>
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
                              name="requiredProducts[{{ $value['id'] }}][codSAP]">
                            <input type='hidden' value="{{ $value['itemName'] }}"
                              name="requiredProducts[{{ $value['id'] }}][itemName]">
                          </td>
                          <td>
                            <div class="input-group min-200 mt-1">
                              <div class="input-group-text">{{ $value['itemUnd'] ?? ' ' }}</div>
                              <input onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)"
                                value="{{ number_format($value['quantity'], 3, '.', '') }}"
                                id="qtd-{{ $value['id'] }}" type="text" class="form-control qtd min-100"
                                name="requiredProducts[{{ $value['id'] }}][qtd]" required
                                @if (isset($head) && !($head->codStatus == $POR::STATUS_OPEN || $head->codStatus == $POR::STATUS_LINK)) readonly @endif>
                              <input type='hidden' value='{{ $value['itemUnd'] }}'
                                name='requiredProducts[{{ $value['id'] }}][itemUnd]'>
                            </div>
                          </td>
                          @if (Route::currentRouteName() == 'purchase.request.read')
                            <td>
                              <input type='hidden' value="{{ number_format($value['quantityPendente'], 3, '.', '') }}"
                                name="requiredProducts[{{ $value['id'] }}][quantityPendente]">
                              <input type="text" class="form-control qtd min-100 locked"
                                value="{{ number_format($value['quantityPendente'], 3, '.', '') }}" readonly>
                            </td>
                          @endif
                          <td>
                            @php
                              $lastProvider = getLastPurchaseItem($value['itemCode']);
                            @endphp
                            @if (!empty($lastProvider))
                              <a href="{{ route('partners.edit', $lastProvider['CardCode']) }}" target="_blank"
                                class='btn btn-primary w-100 text-tooltip' title="{{ $lastProvider['CardName'] }}"
                                value="{{ $lastProvider['CardCode'] }}"
                                name='requiredProducts[{{ $cont }}][lastProvider]'
                                id="lastProvider-{{ $cont }}">{{ $lastProvider['CardCode'] }}
                              </a>
                            @endif
                          </td>
                          <td style="max-width: 12em;">
                            <select class='form-control selectpicker text-tooltip' id='project-{{ $value['id'] }}'
                              name='requiredProducts[{{ $value['id'] }}][projeto]' required data-container="body"
                              @if (isset($head) && isset($head->codSAP)) readonly @endif>
                              <option value=''>Selecione</option>
                              @foreach ($projeto as $keys => $values)
                                <option value="{{ $values['value'] }}" class="text-tooltip"
                                  @if ($values['value'] == $value['project']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role-{{ $value['id'] }}'
                              name='requiredProducts[{{ $value['id'] }}][centroCusto]' required
                              onchange="validLine({{ $value['id'] }})" data-container="body"
                              @if (isset($head) && isset($head->codSAP)) readonly @endif>
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['distrRule']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker' id='role2-{{ $value['id'] }}'
                              name='requiredProducts[{{ $value['id'] }}][centroCusto2]' data-container="body"
                              @if (isset($head) && isset($head->codSAP)) readonly @endif>
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto2 as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value['distriRule2']) selected @endif>{{ $values['value'] }} -
                                  {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select style='width: 2em;' class='form-control selectpicker' id='whs-{{ $value['id'] }}'
                              name='requiredProducts[{{ $value['id'] }}][wareHouseCode]' data-container="body"
                              @if (isset($head) && isset($head->codSAP)) readonly @endif>
                              <option value=''>Selecione</option>
                              @foreach ($wareHouseCode as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if (isset($value['wareHouseCode'])) @if ($values['value'] == $value['wareHouseCode']) selected @endif
                                  @endif>{{ $values['value'] }} - {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select class='form-control selectpicker @if (isset($head) && !empty($head->codSAP)) locked @endif' name='requiredProducts[{{ $value['id'] }}][accounting_account]' data-container='body' required>
                              <option value=''>Selecione</option> 
                              @foreach ($budgetAccountingAccounts as $keys => $budgetAccountingAccount) 
                                <option value='{{ $budgetAccountingAccount['value'] }}' @if($budgetAccountingAccount['value'] == $value["accounting_account"]) selected @endif>{{ $budgetAccountingAccount['value'] }} - {{ $budgetAccountingAccount['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          @if (isset($head) && $head->codStatus == $POR::STATUS_OPEN)
                            <td id="itemTable-{{ $value['id'] }}" class="text-center">
                              <a class="text-danger text-tooltip" title="Remover linha"
                                onclick="removeInArray('{{ $value['itemCode'] }}');removeLinha(this);" type="button">
                                <svg class="icon icon-xl">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                </svg>
                              </a>
                            </td>
                          @else
                            <td></td>
                          @endif
                          <?php $cont++; ?>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Atalhos</h6>
                <div class="card-body row">
                  <div class="col-md-3">
                    <label>Projeto Principal</label>
                    <select class='form-control selectpicker' data-live-search='true' name='project'
                      id='projectGlobal' data-container="body">
                      <option value=''>Selecione</option>
                      @foreach ($projeto as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Centro de Custo Principal</label>
                    <select class='form-control selectpicker' data-live-search='true' name='role' id='roleGlobal'
                      data-container="body">
                      <option value=''>Selecione</option>
                      @foreach ($centroCusto as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Centro de Custo Principal 2</label>
                    <select class='form-control selectpicker' data-live-search='true' name='role2' id='roleGlobal2'
                      data-container="body">
                      <option value=''>Selecione</option>
                      @foreach ($centroCusto2 as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label>Depósito Principal</label>
                    <select class='form-control selectpicker' data-live-search='true' name='whs' id='whsGlobal'
                      data-container="body">
                      <option value=''>Selecione</option>
                      @foreach ($wareHouseCode as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="row mt-3">
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
          <div class="tab-pane" id="tab-2">
            <div class="panel-body mt-3">
              <div class="card">
                <div class="card-body">
                  <div class="col-md-12">
                    <label>Observação</label>
                    <textarea @if (isset($head) && $head->codStatus != $head::STATUS_OPEN) readonly @endif class="form-control" rows="3" name="observation"
                      maxlength="200">
@if (isset($head)){{ $head->observation }}@endif
</textarea>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
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
                                <a class="btn btn-danger text-tooltip" title="Remover linha" type="button"
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
        <div class="col-md-12 mt-5">
          @if (isset($head) && ($head->codStatus == $POR::STATUS_OPEN || $head->codStatus == $POR::STATUS_PENDING))
            @if (auth()->user()->tipoCompra == 'A' && !is_null($head->codSAP))
              <div class="col-md-9">
                <div class="dropdown">
                  <button class="btn btn-dark dropdown-toggle float-start" type="button" data-coreui-toggle="dropdown"
                    aria-expanded="false">
                    <svg class="icon">
                      <use xlink:href="{{ asset('icons_assets/custom.svg#copy-to') }}"></use>
                    </svg> Copiar para
                  </button>
                  <ul class="dropdown-menu">
                    @if (checkAccess('purchase_quotation'))
                      <li><a class="dropdown-item" type="button" id="btn-from" onclick="fromQuotation()">Cotação de
                          compra</a></li>
                    @endif
                    @if (checkAccess('purchase_order'))
                      <li><a class="dropdown-item" type="button" id="btn-from" data-coreui-toggle="modal"
                          data-coreui-target="#purchaseModal">Pedido de compra</a></li>
                    @endif
                  </ul>
                </div>
              </div>
            @endif
            @if ($head->codStatus == $head::STATUS_OPEN)
              @if ($head->is_locked == false)
                <button class="btn btn-primary float-end" onclick="save(event)">Atualizar</button>
              @endif
              <button class="btn btn-danger float-end me-1" onclick="cancel(event)">Cancelar</button>
            @endif
          @endif
          @if (isset($head))
            <a onclick="duplicate()" class="btn btn-success float-end me-1" type="button">Duplicar</a>
          @endif
          @if (!isset($head))
            <button class="btn btn-primary float-end" type="button" id="btn-save" onclick="valide()">Salvar</button>
          @endif
        </div>
      </div>
    </div>
  </form>

  <div class="modal" id="purchaseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Informações adicionais</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label for="cardCode">Parceiro de negócios</label>
              <select id="cardCode" class="form-control selectpicker with-ajax-suppliers" data-width="100%"
                data-size="7" name="cardCode"></select>
            </div>
            <div class="col-md-6">
              <label>Condição de Pagamento</label>
              <select class="form-control selectpicker" @if (isset($head) && $head->codStatus != $head::STATUS_OPEN) readonly @endif
                data-live-search="true" data-size="10" name="condPagamentos" id="paymentTerms">
                @foreach ($paymentConditions as $key => $value)
                  <option value="{{ $value['GroupNum'] }}" @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                    {{ $value['PymntGroup'] }}</option>
                @endForeach
              </select>
            </div>
          </div>
        </div><!-- body -->
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-sm btn-primary" onclick="fromPurchaseGo()">Copiar para pedido de
            compras</button>
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
                  <th style="width: 45%">Descrições</th>
                  <th style="width: 15%">Estoque</th>
                  <th style="width: 15%">Quantidade</th>
                  <th style="width: 10%">Opções</th>
                </tr>
              </thead>
              <tbody>
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

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-suppliers')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));


    $(document).ready(function() {
      $.each($(`input[name*='quantityPendente']`), function(index, element) {
        if ($(element).val() <= 0) {
          $(element).closest('tr').find('select').attr('disabled', true);
          $(element).closest('tr').find('input').addClass('locked');
        }
      });
      fillTopNav(
        @if (isset($head))
          @json($head->getTopNavData())
        @else
          @json($POR->getTopNavData())
        @endif 
      );
      setMaskMoney();
    });

    $('.dataTables-example').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });

    function loadingItems() {
      $('#table').DataTable().clear()
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
            name: 'quantity',
            data: 'ItemCode',
            render: renderQuantityInputLoadingItems,
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

    function renderQuantityInputLoadingItems(code) {
      return `<input id="quantityInputLoadingItems" type="text" class="form-control qtd" data-itemCode="${code}" onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'>`;
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

    function renderWHSButton(valor) {
      return `<center>
                      <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS" @if (isset($head->codSAP)) style="display: none;" @endif>
                        <svg class="icon icon-xl">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                        </svg>
                      </a>
                    </center>`
    }

    function renderFormatedQuantity(valor) {
      return new Intl.NumberFormat('pt-BR').format(valor);
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

    var index =
      @if (isset($body))
        {{ $cont + 1 }}
      @else
        1
      @endif ;

    let block = false;


    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("Processando...");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'purchase_requests')
      formData.append('id', $('input[name="id"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('purchase.request.updateUploads') }}",
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
            window.location.href = "{{ route('purchase.request.remove.upload') }}/" + id + "/" + idRef;
          }
        });
    }


    function loadTable(code, quantity = 0) {
      var table = $('#requiredTable');

      if (quantity === 0) {
        quantity = (parseFloat($(`#itensModal input[data-itemCode="${code}"]`).val().replace(/[.]/gi, '').replace(/[,]/gi,
            '.')) || 0);
      }

      if (index == 1) {
        $('#requiredTable tbody > tr').remove();
      }

      if (block) {
        return false; // previne que o index fique com contagem incorreta
      }

      block = true;
      var tr = $("<tr id='rowTable-" + index + "'>");
      tr.append($('<td >' + index + '</td>'));
      tr.find('td').first().append('<input type="hidden" value="' + code + '" data-name="line" name="requiredProducts[' +
        index + '][codSAP]">');
      $.get('/getProductsSAP/' + code, function(items) {

        for (var i = 0; i < items.length; i++) {
          var whs = items[i].DfltWH;
          let price = parseFloat(items[i].LastPurPrc).toFixed(4);

          tr.append($(`<td style="max-width: 16em;">
                                        <div class="d-flex flex-row" style="max-width: 100%;">
                                            <a class="text-warning" href="{{ route('inventory.items.edit') }}/${items[i].ItemCode}" target="_blank">
                                                <svg class="icon icon-lg">
                                                    <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                                </svg>
                                                </a>
                                            <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${items[i].ItemCode} - ${items[i].ItemName}">${items[i].ItemCode} - ${items[i].ItemName}</span>
                                            <input type='hidden' value='${items[i].ItemName}' name='requiredProducts[${index}][itemName]'>
                                            <input type='hidden' id="itemCode-${index}" value='${items[i].ItemCode}' name='requiredProducts[${index}][itemCode]'>
                                        </div>
                                    </td>`));
          
          tr.append($(
            `<td >
              <div class="input-group min-200 mt-1">
                <div class="input-group-text">${items[i].BuyUnitMsr || ""}: ${renderFormatedQuantity(items[i].NumInBuy)}</div>
                <input id="qtd-${index}" required type='text' class='form-control qtd min-100' value='${quantity}' onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)' name='requiredProducts[${index}][qtd]' required>
                <input type='hidden' value='${items[i].BuyUnitMsr || ""}' name='requiredProducts[${index}][itemUnd]'> 
              </div>
            </td>`
          ));

          @if (Route::currentRouteName() == 'purchase.request.read')
            tr.append($("<td ></td>"));
          @endif

          tr.append($("<td id='lastProvider-" + index + "'></td>"));
          tr.append($(`<td style="max-width: 12em;">
                          <select class='form-control selectpicker' id='project-${index}' name='requiredProducts[${index}][projeto]' data-container='body' required>
                                  <option value=''>Selecione</option>
                                  @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}' data-coreui-toggle="tooltip" title="{{ $values['value'] }} - {{ $values['name'] }}">{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach
                          </select>
                      </td>`));

          tr.append($("<td >" +
            "<select  class='form-control selectpicker' id='role-" + index + "' name='requiredProducts[" + index +
            "][centroCusto]' required onchange='validLine(" + index +
            ");' data-container='body'> <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));

          tr.append($("<td >" +
            "<select class='form-control selectpicker disabled' id='role2-" + index +
            "' name='requiredProducts[" + index +
            "][centroCusto2]' data-container='body'> <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));

          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='whs-" + index + "' name='requiredProducts[" + index +
            "][wareHouseCode]' data-container='body'> <option value=''>Selecione</option> @foreach ($wareHouseCode as $keys => $values) <option  value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));

            tr.append($(`<td>
                <select id="accounting_account-${index}" class='form-control selectpicker' name='requiredProducts[${index}][accounting_account]' data-container='body' data-linenum='${index}'>
                  <option value=''>Selecione</option> 
                  @foreach ($budgetAccountingAccounts as $keys => $budgetAccountingAccount) 
                      <option value='{{ $budgetAccountingAccount['value'] }}'>{{ $budgetAccountingAccount['value'] }} - {{ $budgetAccountingAccount['name'] }}</option>
                    @endForeach
                </select>
              </td>`));

          tr.append($(`<td class='text-center'>
                          <a class="text-danger text-tooltip" title="Remover linha" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
                            <svg class="icon icon-xl">
                              <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                            </svg>
                          </a>
                        </td>`));


        }
        table.find('tbody').append(tr);

        $('#whs-' + index).val(whs);
        $('.selectpicker').selectpicker(selectpickerConfig);
        $('.text-tooltip').tooltip();
        addLastProvider(code, index);
        setMaskMoney();
        index++;

        block = false;
      });
    }

    function addLastProvider(code, index) {
      $.get('/getProductsProvider/' + code, function(items) {
        if (typeof items[0] != 'undefined') {
          $('#lastProvider-' + index).append($(
            `<a href="{{ route('partners.edit') }}/${items[0].CardCode}" target="_blank" class="btn btn-primary w-100 text-tooltip" title="${items[0].CardName}">${(items[0].CardCode)}</a>`
          ));
          $('.text-tooltip').tooltip();
        }
      });
    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {
        tr.remove();
      });
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    var used = new Array();

    function removeInArray(code) {
      var aux = used.indexOf(code);
      if (aux != -1) {
        used.splice(aux, 1);
      }
    }

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

    $('#projectGlobal').on('change', function(event) {

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

    });

    $('#roleGlobal').on('change', function(event) {
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

    });

    $('#roleGlobal2').on('change', function(event) {
      var val = $('#roleGlobal2').val();
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

    });


    $('#whsGlobal').on('change', function(event) {

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

    });

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

    function valide() {

      var erros = new Array();

      if ($('#requester').val() == '') {
        erros.push('Informe o solicitante! \n');
      }

      if (index == '1') {
        erros.push('Adicione um item! \n');
      }

      $.each($('#requiredTable tbody').find('input[id*="qtd-"]'), function(index, value) {
        if (parseFloat($(value).val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) <= 0 || $(value).val()
          .length === 0) {
          erros.push(`Adicione uma quantidade válida no item ${index+1}\n`)
        }
      });

      $.each($('#requiredTable tbody').find('select[id*="accounting_account-"]'), function(index, value) {
        
        let lineNum = $(value).attr("data-linenum");

        let itemCode = $(`#itemCode-${lineNum}`).val();

        if($(value).val().length <= 0 && itemCode.includes("AF") == false){
          erros.push(`Adicione uma conta contábil no item ${index+1}\n`);
        }
      });

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
        return
      } else {
        $('input, select').attr('disabled', false);
        $('#needs-validation').submit()
      }

    }


    function save(event) {
      $('input, select').attr('disabled', false);
      document.getElementById("needs-validation").submit();
    }

    function fromPurchaseGo() {
      var erros = new Array();

      if ($('#cardCode').val() == '') {
        erros.push('Adicione um parceiro! \n');

      }
      if ($('#paymentTerms').val() == '') {
        erros.push('Adicione uma condição de pagamento! \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        $('#needs-validation').attr('action', '');
        $('#needs-validation').append($(`<input type="hidden" name="cardCode" value="${$('#cardCode').val()}">`))
        $('#needs-validation').append($(
          `<input type="hidden" name="condPagamentos" value="${$('#paymentTerms').val()}">`))
        $('#needs-validation').attr('action', '{{ route('purchase.request.from.purchase') }}');
        $('#purchaseModal').hide();
        $('#needs-validation').submit();
        // $('#btn-save').attr('type', 'submit');
      }
    }

    function fromQuotation() {
      swal({
          title: "Gerar cotação de compra",
          text: "Tem certeza ?",
          icon: "warning",
          //buttons: true,
          buttons: ["Não", "Sim"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            waitingDialog.show('Aguarde...')
            $('#needs-validation').attr('action', '');
            $('#needs-validation').attr('action', '{{ route('purchase.request.from.quotation') }}');
            $('#purchaseModal').hide();
            $('#needs-validation').submit();
          }
        });
    }

    $(document).on("keypress", "#quantityInputLoadingItems", function(event) {
      if (event.keyCode === 13) {
        loadTable($(this).attr('data-itemCode'), $(this).val());
      }
    })

    @if (isset($head))

      function duplicate() {

        swal({
            title: "Tem certeza que deseja Duplicar?",
            text: "Após confirmado não é possivel desfazer a operação!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Duplicando...')
              window.location.href = "{{ route('purchase.request.duplicate', $head->id) }}";
            }
          });
      }

      function cancel(event) {
        event.preventDefault()
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
              window.location.href = "{{ route('purchase.request.canceled', $head->id) }}";
            }
          });
      }
    @endif
  </script>
@endsection
