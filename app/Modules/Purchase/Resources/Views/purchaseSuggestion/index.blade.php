@extends('layouts.main')
@section('title', 'Lista de sugestões de compras')
@section('content')
  <div class="row">
    <div class="col-6">
      <h3 class="header-page">Lista de sugestões de compras</h3>
    </div>
  </div>
  <hr>

  <div class="col-lg-12">
    <form action="{{ route('purchase.suggestion.filter') }}" method="GET" id="needs-validation"
      enctype="multipart/form-data">
      <div class="accordion" id="filterAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-coreui-toggle="collapse"
              data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Filtros
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
            data-coreui-parent="#filterAccordion">
            <div class="accordion-body">
              <div class="row">
                <div class="col-md-2">
                  <label>Item</Label>
                  <select class="form-control selectpicker with-ajax-item" name="itemCode[]" multiple></select>
                </div>
                <div class="col-md-3">
                  <label>Grupo</Label>
                  <select class="form-control selectpicker" name="group">
                    <option value=""></option>
                    @foreach ($itemGroups as $key => $value)
                      <option value="{{ $value->value }}" {{ old('group') == $value->value ? 'selected' : '' }}>
                        {{ $value->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Subgrupo</label>
                    <select class="form-control selectpicker" name="subGroup">
                      <option value=""></option>
                      @foreach ($subGroups as $subGroup)
                        <option value="{{ $subGroup->value }}">{{ $subGroup->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <label>Depósito</Label>
                  <select class="form-control selectpicker" name="warehouse">
                    <option value=""></option>
                    @foreach ($warehouses as $key => $value)
                      <option value="{{ $value->value }}" {{ old('warehouse') == $value->value ? 'selected' : '' }}>
                        {{ $value->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label>Parceiro</Label>
                  <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body"
                    name="cardCode">
                    @if (!empty(old('cardCode')))
                      <option value="{{ old('cardCode') }}" selected>{{ getPartnerName(old('cardCode')) }}</option>
                    @endif
                  </select>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                  <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar
                    formulário</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
    <div class="col-md-12 mt-5">
      @if (isset($items))
        {{ $items->links('pagination::bootstrap-4') }}
        <div class="table-responsive">
          <table id="requiredTable"
            class="table table-default table-striped table-bordered table-hover dataTables-example">
            <thead>
              <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 5%;" class="text-center">
                  <input type="checkbox" class="form-check-input p-2" onclick="checkAllCheckboxes(event)">
                </th>
                <th style="width: 15%;">Descrição</th>
                <th style="width: 10%;">U.M.C</th>
                <th style="width: 12%;">Fornecedor Preferêncial</th>
                <th style="width: 10%;">Histórico Vendas</th>
                <th style="width: 15%;">Depósito</th>
                <th style="width: 10%;">Estoque Mínimo</th>
                <th style="width: 10%;">Estoque Máximo</th>
                <th style="width: 10%;">Sugestão U.M.C</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($items as $key => $value)
                @php $quantitySugestao = 0; @endphp
                <tr>
                  <td class="text-center">{{ $key + 1 }}</td>
                  <td class="text-center">
                    <input type="checkbox" name="requiredProducts[{{ $key }}][itemCode]"
                      id="checkboxSelector-{{ $key }}" class="form-check-input"
                      style="width: 20px; height: 20px;" value="{{ $value->ItemCode }}"
                      data-lineNum="{{ $key }}">
                    <input type="hidden" name="requiredProducts[{{ $key }}][itemName]"
                      id="itemName-{{ $key }}" value="{{ $value->ItemName }}">
                    <input type="hidden" name="requiredProducts[{{ $key }}][lastPrice]"
                      id="lastPrice-{{ $key }}" value="{{ $value->LastPurPrc }}">
                  </td>
                  <td style="max-width: 16em;">
                    <div class="d-flex flex-row" style="max-width: 100%;">
                      <a class="text-warning" href="{{ route('inventory.items.edit', $value->ItemCode) }}"
                        target="_blank">
                        <svg class="icon icon-lg">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                        </svg>
                      </a>
                      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                        title="{{ $value->ItemCode }} - {{ $value->ItemName }}">{{ $value->ItemCode }} -
                        {{ $value->ItemName }}</span>
                    </div>
                  </td>
                  <td class="text-center">
                    {{ $value->BuyUnitMsr }} - {{ number_format($value->NumInBuy, 3)}}
                    <input type="hidden" id="buyUnitMsr-{{ $key }}" value="{{ $value->BuyUnitMsr }}">
                    <input type="hidden" id="numInBuy-{{ $key }}" value="{{ $value->NumInBuy }}">
                  </td>
                  <td style="max-width: 16em;">
                    @if (!empty($value->CardCode))
                      <div class="d-flex flex-row" style="max-width: 100%;">
                        <a class="text-warning" href="{{ route('partners.edit', $value->CardCode) }}" target="_blank">
                          <svg class="icon icon-lg">
                            <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                          </svg>
                        </a>
                        <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                          title="{{ $value->CardCode }} - {{ $value->CardName }}">{{ $value->CardCode }} -
                          {{ $value->CardName }}</span>
                      </div>
                      <input type="hidden" id="cardCode-{{ $key }}" value="{{ $value->CardCode }}">
                      <input type="hidden" id="cardName-{{ $key }}" value="{{ $value->CardName }}">
                    @endif
                  </td>
                  <td class="text-center">
                    <a href="#" class="btn btn-primary" onclick="loadSalesHistory('{{ $value->ItemCode }}')">
                      <svg class="icon">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                      </svg>
                    </a>
                  </td>
                  <td>
                    @foreach (auth()->user()->warehouses as $user_warehouse)
                      @php
                        $itemWhsData = $itemModel->getDataWarehouse($value->ItemCode, $user_warehouse->whsCode);
                      @endphp

                      @if(!empty($itemWhsData))
                        @php $quantitySugestao += $itemWhsData->disponivel @endphp
                        <div class="input-group min-150 mt-1">
                          <div class="input-group-text">{{ $value->InvntryUom }}</div>
                          <input class="form-control min-150 text-tooltip" value="{{ $user_warehouse->whsCode }} - {{ number_format($itemWhsData->disponivel, 3, ',', '.') }}" data-coreui-toggle="tooltip"
                            title="{{ $user_warehouse->whsCode }} - {{ $itemWhsData->WhsName }}" readonly>
                        </div>
                      @endif
                    @endforeach
                    <input type="hidden" id="invntryUom-{{ $key }}" value="{{ $value->InvntryUom }}">
                    <input type="hidden" id="warehouseCode-{{ $key }}" value="{{ $value->WhsCode }}">
                  </td>
                  <td>
                    {{ number_format($value->MinLevel, 3, ',', '.') }}
                  </td>
                  <td>
                    {{ number_format($value->MaxLevel, 3, ',', '.') }}
                  </td>
                  <td>
                    <div class="input-group min-150">
                      <div class="input-group-text">{{ $value->BuyUnitMsr }}</div>
                      <input type="text" id="quantity-{{ $key }}" class="form-control qtd min-100"
                        name="requiredProducts[{{ $key }}][quantity]"
                        @if($quantitySugestao > $value->MaxLevel || $value->MaxLevel == 0) value="0" @else value="{{ number_format(ceil(abs($value->MaxLevel - $quantitySugestao) / $value->NumInBuy), 3, ',', '.') }}" @endif
                        onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </td>
                </tr>
              @endForeach
            </tbody>
          </table>
        </div>
    </div>
    {{ $items->links('pagination::bootstrap-4') }}
    <div class="col-12 mt-4">
      @if (checkAccess('purchase_suggestion_order'))
        <button class="btn btn-success float-end" onclick="abrirModalPedido(event)">Gerar pedido de compras</button>
      @endif
      @if (checkAccess('purchase_suggestion_request'))
        <button class="btn btn-warning float-end me-1" onclick="abrirModalSolicitacao(event)">Gerar solicitação de
          compras</button>
      @endif
    </div>
    @endif
  </div>

  <div class="modal inmodal" id="salesHistoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Histórico de vendas</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive mt-3 ">
            <table id="salesHistoryTable" class="table table-default table-bordered table-hover" style="width: 100%;">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 25%">Mês e Ano</th>
                  <th style="width: 25%">Total vendas</th>
                  <th style="width: 25%">Preço unit. médio</th>
                  <th style="width: 25%">Quantidade total</th>
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

  <div class="modal fade" id="gerarSolicitacaoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Informações Adicionais</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="gerarSolicitacaoForm">
          <div class="modal-body">
            <div class="row mt-2 mb-3">
              <div class="col-md-2">
                <label>Data necessária</label>
                <input type="date" class="form-control" name="data" required>
                <input type="hidden" name="requester" value="{{ auth()->user()->userClerk }}">
              </div>
            </div>
            <input type="hidden" name="documentType" value="1">
            <div class="table-responsive">
              <table id="gerarSolicitacaoTable"
                class="table table-default table-striped table-bordered table-hover dataTables-example">
                <thead>
                  <tr>
                    <th style="width: 5%;" class="text-center">#</th>
                    <th style="width: 15%;">Descrição</th>
                    <th style="width: 10%;">U.M.C</th>
                    <th style="width: 10%;">U.M.E</th>
                    <th>Histórico vendas</th>
                    <th style="width: 15%;">Projeto</th>
                    <th style="width: 15%;">C. Custo</th>
                    <th style="width: 15%;">C. Custo2</th>
                    <th style="width: 15%;">Conta</th>
                    <th style="width: 15%;">Sugestão</th>
                    <th style="width: 10%;">Opção</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-dark" data-coreui-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="gerarPedidoModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Informações Adicionais</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="gerarPedidoForm">
          <div class="modal-body">
            <div class="row mt-2 mb-3">
              <input type="hidden" name="requester" value="{{ auth()->user()->userClerk }}">
              <div class="col-md-2">
                <label>Data de lançamento</label>
                <input type="date" class="form-control locked" name="dataLancamento" value="{{ date('Y-m-d') }}"
                  required readonly>
              </div>
              {{-- <div class="col-md-2">
                <label>Data do documento</label>
                <input type="date" class="form-control" name="dataDoc" value="{{ date("Y-m-d") }}" required readonly>
              </div> --}}
            </div>
            <input type="hidden" name="documentType" value="2">
            <div class="table-responsive">
              <table id="gerarPedidoTable"
                class="table table-default table-striped table-bordered table-hover dataTables-example">
                <thead>
                  <tr>
                    <th class="text-center">#</th>
                    <th style="width: 20%;">Descrição</th>
                    <th>U.M.C</th>
                    <th>U.M.E</th>
                    <th>Fornecedor</th>
                    <th>Projeto</th>
                    <th>C. Custo</th>
                    <th>C. Custo2</th>
                    <th>Sugestão</th>
                    <th>Ult. Preço</th>
                    <th>Preço Unit.</th>
                    <th style="width: 10%;">Opção</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-dark" data-coreui-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('inventory.items.suppliersSearch') }}"));
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));
    selectpicker.filter('.with-ajax-item').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('inventory.items.itemsSearch') }}"));

    $(document).ready(()=>{
      setMaskMoney();
    });

    $(document).on('show.coreui.modal', '.modal', function() {
      const zIndex = 1040 + 10 * $('.modal:visible').length;
      $(this).css('z-index', zIndex);
      setTimeout(() => $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'));
    });
    
    $(document).on('hide.coreui.modal', '.modal', function() {
      const zIndex = 1040 - 10 * $('.modal:visible').length;
      $(this).css('z-index', zIndex);
      $(document).find('.modal-backdrop').hide();
      setTimeout(() => $('.modal-backdrop .show').find('.modal-stack').css('z-index', zIndex - 1).removeClass('modal-stack'));
    });

    $('#requiredTable').DataTable({
      columnDefs: [{targets: 0 }],
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

    function checkAllCheckboxes(event) {
      $('#requiredTable tbody').find('input[type="checkbox"]').click();
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
      });
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
      $(event.target).select()
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    function abrirModalSolicitacao(event) {

      if ($('input[id*="checkboxSelector"]:checked').length > 0) {
        let table = $(`#gerarSolicitacaoTable`);
        table.find("tbody tr").remove();
        $.each($('input[id*="checkboxSelector"]:checked'), function(index, value) {
          let lineNum = $(value).attr('data-lineNum');
          let itemCode = $(value).val();
          let itemName = $('#itemName-' + lineNum).val();
          let itemUnd = $('#buyUnitMsr-' + lineNum).val();
          let warehouseCode = $('#warehouseCode-' + lineNum).val();
          table.append($(`<tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center" style="max-width: 16em;">
                              <input type="hidden" name="requiredProducts[${lineNum}][codSAP]" value="${itemCode}">
                              <input type="hidden" name="requiredProducts[${lineNum}][itemName]" value="${itemName}">
                              <input type="hidden" name="requiredProducts[${lineNum}][itemUnd]" value="${itemUnd}">
                              <input type="hidden" name="requiredProducts[${lineNum}][wareHouseCode]" value="${warehouseCode}">
                              <div class="d-flex flex-row" style="max-width: 100%;">
                                <a class="text-warning" href="{{ route('inventory.items.edit') }}/${itemCode}"
                                  target="_blank">
                                  <svg class="icon icon-lg">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                  </svg>
                                </a>
                                <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                  title="${$(value).val()} - ${$('#itemName-'+lineNum).val()}">${$(value).val()} - ${$('#itemName-'+lineNum).val()}</span>
                              </div>
                            </td>
                            <td>
                              ${itemUnd} - ${parseFloat($('#numInBuy-'+lineNum).val()).toFixed(3)}
                            </td>
                            <td>
                              ${$("#invntryUom-"+lineNum).val()}
                            </td>
                            <td>
                              <a href="#" class="btn btn-primary" onclick="loadSalesHistory('${itemCode}')">
                                <svg class="icon">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                                </svg>
                              </a>
                            </td>
                            <td class="text-center">
                              ${createSelectProjectElement(lineNum, 'gerarSolicitacaoModal')}
                            </td>
                            <td class="text-center">
                              ${createSelectCostCenterElement(lineNum, 'gerarSolicitacaoModal')}
                            </td>
                            <td class="text-center">
                              ${createSelectCostCenter2Element(lineNum, 'gerarSolicitacaoModal')}
                            </td>
                            <td class="text-center">
                              ${createSelectAccountingAccountElement(lineNum, 'gerarSolicitacaoModal')}
                            </td>
                            <td>
                              <div class="input-group min-150">
                                <div class="input-group-text">${itemUnd}</div>
                                <input type="text" class="form-control qtd min-100" name="requiredProducts[${lineNum}][qtd]" value="${$('#quantity-'+lineNum).val()}" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
                              </div>
                            </td>
                            <td id='itemTable-${lineNum}' class='text-center'>
                                <a class="text-danger" onclick='removeLinha(this);' type="button">
                                  <svg class="icon icon-xl">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                  </svg>
                                </a>
                              </td>
                          </tr>`));
        });

        $('.text-truncate').tooltip();
        $(`.selectpicker`).selectpicker(selectpickerConfig).selectpicker('render');
        $('#gerarSolicitacaoModal').modal('show');

      } else {
        swal('Opss!', 'É necessário selecionar pelo menos um item!', 'error');
      }
    }

    function abrirModalPedido(event) {

      if ($('input[id*="checkboxSelector"]:checked').length > 0) {
        let table = $(`#gerarPedidoTable`);
        table.find("tbody tr").remove();
        let contador = 0;
        $.each($('input[id*="checkboxSelector"]:checked'), function(index, value) {
          let lineNum = $(value).attr('data-lineNum');
          let cardCode = $('#cardCode-' + lineNum).val();

          if (cardCode !== undefined) {
            let itemCode = $(value).val();
            let itemName = $('#itemName-' + lineNum).val();
            let itemUnd = $('#buyUnitMsr-' + lineNum).val();

            table.append($(`<tr id="order-line-${index}">
                              <td class="text-center">${index + 1}</td>
                              <td class="text-center" style="max-width: 16em;">
                                <input type="hidden" name="requiredProducts[${lineNum}][codSAP]" value="${itemCode}">
                                <input type="hidden" name="requiredProducts[${lineNum}][itemName]" value="${itemName}">
                                <input type="hidden" name="requiredProducts[${lineNum}][itemUnd]" value="${itemUnd}">
                                <input type="hidden" name="requiredProducts[${lineNum}][wareHouseCode]" value="${$('#warehouseCode-'+lineNum).val()}">
                                <div class="d-flex flex-row" style="max-width: 100%;">
                                  <a class="text-warning" href="{{ route('inventory.items.edit') }}/${itemCode}"
                                    target="_blank">
                                    <svg class="icon icon-lg">
                                      <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                    </svg>
                                  </a>
                                  <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                    title="${itemCode} - ${itemName}">${itemCode} - ${itemName}</span>
                                </div>
                              </td>
                              <td>
                                ${itemUnd} - ${parseFloat($('#numInBuy-'+lineNum).val()).toFixed(3)}
                              </td>
                              <td>
                                ${$("#invntryUom-"+lineNum).val()}
                              </td>
                              <td>
                                <select name="requiredProducts[${lineNum}][cardCode]" class="form-control selectpicker with-ajax-partner locked" readonly>
                                  <option value="${cardCode}">${cardCode} - ${$('#cardName-'+lineNum).val()}</option>
                                </select>
                              </td>
                              <td class="text-center">
                                ${createSelectProjectElement(lineNum, 'gerarPedidoModal')}
                              </td>
                              <td class="text-center">
                                ${createSelectCostCenterElement(lineNum, 'gerarPedidoModal')}
                              </td>
                              <td class="text-center">
                                ${createSelectCostCenter2Element(lineNum, 'gerarPedidoModal')}
                              </td>
                              <td>
                                <div class="input-group min-150">
                                  <div class="input-group-text">${itemUnd}</div>
                                  <input type="text" class="form-control qtd min-100" name="requiredProducts[${lineNum}][qtd]" value="${$('#quantity-'+lineNum).val()}" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
                                </div>
                              </td>
                              <td>
                                <input type="text" class="form-control moneyPlus min-100 locked" onclick='destroyMask(event)' value="${parseFloat($('#lastPrice-'+lineNum).val()).toFixed(4)}" onblur="setMaskMoney();focusBlur(event)" readonly>
                              </td>
                              <td>
                                <input type="text" class="form-control moneyPlus min-100" name="requiredProducts[${lineNum}][price]" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)" required>
                              </td>
                              <td id='itemTable-${lineNum}' class='text-center'>
                                <a class="text-danger" onclick=';removeLinha(this);' type="button">
                                  <svg class="icon icon-xl">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                  </svg>
                                </a>
                              </td>
                            </tr>`));

            contador++;
          }
        });

        if (contador === 0) {
          swal('Opss!', 'Apenas são listados os itens que contém Fornecedores Preferênciais!', 'error');
        }

        $('.text-truncate').tooltip();
        $(`.selectpicker`).selectpicker(selectpickerConfig).selectpicker('render');
        $('#gerarPedidoModal').modal('show');

      } else {
        swal('Opss!', 'É necessário selecionar pelo menos um item!', 'error');
      }
    }

    function loadSalesHistory(itemCode){
      $('#salesHistoryTable').dataTable().fnDestroy();

      $('#salesHistoryModal').modal('show');

      $("#salesHistoryTable").DataTable({
        searching: true,
        ajax: {
          url: "{{ route('purchase.suggestion.sales-history') }}" + '/' + itemCode,dataSrc:""
        },
        columns: [{
            name: 'date',
            data: 'date',
            orderable: false
          },
          {
            name: 'totalSales',
            data: 'totalSales',
            orderable: false
          },
          {
            name: 'averagePrice',
            data: 'averagePrice',
            orderable: false,
            render: format_totalsDataTable
          },
          {
            name: 'quantityTotal',
            data: 'quantityTotal',
            orderable: false,
            render: format_quantitiesDataTable
          }
        ],
        language: dataTablesPtBr
      });
    }

    function format_totalsDataTable(data, type, row) {
      const formatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      });
      return formatter.format(data);
    }

    function format_quantitiesDataTable(data, type, row){
      const formatter = new Intl.NumberFormat('pt-BR').format(data)
      return formatter
    }

    function createSelectProjectElement(index, idModal) {
      let select = $(`<select class="form-control selectpicker" name="requiredProducts[${index}][projeto]" data-container="#${idModal}" required>
          <option value=""></option>
        </select>`);

      $.each(@json($projeto), function(index, value) {
        select.append($(`<option value="${value.value}">${value.value} - ${value.name}</option>`));
      });
      return select[0].outerHTML;
    }

    function createSelectCostCenterElement(index, idModal) {
      let select = $(`<select class="form-control selectpicker" name="requiredProducts[${index}][centroCusto]" data-container="#${idModal}" required>
          <option value=""></option>
        </select>`);

      $.each(@json($centroCusto), function(index, value) {
        select.append($(`<option value="${value.value}">${value.value} - ${value.name}</option>`));
      });
      return select[0].outerHTML;
    }

    function createSelectCostCenter2Element(index, idModal) {
      let select = $(`<select class="form-control selectpicker" name="requiredProducts[${index}][centroCusto2]" data-container="#${idModal}">
          <option value=""></option>
        </select>`);

      $.each(@json($centroCusto2), function(index, value) {
        select.append($(`<option value="${value.value}">${value.value} - ${value.name}</option>`));
      });

      return select[0].outerHTML;
    }

    function createSelectAccountingAccountElement(index, idModal) {
      let select = $(`<select class="form-control selectpicker" name="requiredProducts[${index}][accounting_account]" data-container="#${idModal}">
          <option value=""></option>
        </select>`);

      $.each(@json($budgetAccountingAccounts), function(index, value) {
        select.append($(`<option value="${value.value}">${value.value} - ${value.name}</option>`));
      });

      return select[0].outerHTML;
    }

    $('#gerarSolicitacaoForm, #gerarPedidoForm').submit(function(event) {
      event.preventDefault();
      waitingDialog.show("Processando...");
      $.ajax({
          type: 'POST',
          url: "{{ route('purchase.suggestion.save') }}",
          data: $(`#${$(event.target).attr('id')}`).serialize(),
          headers: {
            'X-CSRF-TOKEN': $('body input[name="_token"]').val()
          }
        })
        .always(function(response) {
          waitingDialog.hide();
          swal({
            title: (response.status === "success" ? "Sucesso" : "Opss..."),
            text: response.message,
            icon: (response.status === "success" ? "success" : "error"),
          });
        });
    });

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.remove();
    }
  </script>
@endsection
