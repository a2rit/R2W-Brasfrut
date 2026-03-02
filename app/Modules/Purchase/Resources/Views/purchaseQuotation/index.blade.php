@extends('layouts.main')

@section('title', 'Cotação de compra')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de cotações de compras</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-coreui-toggle="modal" data-coreui-target="#requestModal">Copiar
              de solicitação</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <div class="row">
    <div class="col-3">
      <div class="card border-top-primary border-top-3 mb-3 text-center">
        <div class="card-header">Abertos</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 1)->count() }} <span class="small">Cotações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-danger border-top-3 mb-3 text-center">
        <div class="card-header">Cancelados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 2)->count() }} <span class="small">Cotações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-success border-top-3 mb-3 text-center">
        <div class="card-header">Fechados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 4)->count() }} <span class="small">Cotações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-dark border-top-3 mb-3 text-center">
        <div class="card-header">Pendentes</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 3)->count() }} <span class="small">Cotações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card border-top-warning border-top-3 mb-3 text-center">
        <div class="card-header">PC. Gerados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 5)->count() }} <span class="small">Cotações</span></h5>
        </div>
      </div>
    </div>
    <span class="fst-italic text-small">Periodo: últimos 12 meses</span>
  </div>
  <hr>

  <div class="col-lg-12">
    <form action="{{route('purchase.quotation.filter')}}" method="GET" id="needs-validation" enctype="multipart/form-data"
      onsubmit="waitingDialog.show('Carregando...')">
      {{-- {!! csrf_field() !!} --}}
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
                  <label>Cod Web</Label>
                  <input type="text" class="form-control" placeholder="PQ00001" name="code" autocomplete="off" value="{{ old("code") }}">
                </div>
                <div class="col-md-2">
                  <label>Cod SAP</Label>
                  <input type="text" class="form-control" placeholder="00001" name="codSAP" autocomplete="off" value="{{ old("codSAP") }}">
                </div>
                <div class="col-md-4">
                  <label>Usuário</Label>
                  <select class="form-control selectpicker with-ajax-users" name="solicitante">
                    @if(!empty(old("solicitante"))) <option value="{{ old("solicitante") }}" selected>{{ getUserName(old("solicitante")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-4">
                  <label>Fornecedor</Label>
                  <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body" name="provider">
                    @if(!empty(old("provider"))) <option value="{{ old("provider") }}" selected>{{ getPartnerName(old("provider")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-3">
                  <label>Data Inicial</Label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial"
                    autocomplete="off" value="{{ old("data_fist") }}">
                </div>
                <div class="col-md-3">
                  <label>Data Final</Label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off" value="{{ old("data_last") }}">
                </div>
                <div class="col-md-3">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=''> Selecione</option>
                    <option value='1' @if((Integer)old('status') === $POR::STATUS_OPEN) selected @endif>Aberto</option>
                    <option value='3' @if((Integer)old('status') === $POR::STATUS_PENDING) selected @endif>Pendente</option>
                    <option value='4' @if((Integer)old('status') === $POR::STATUS_CLOSE) selected @endif>Fechado</option>
                    <option value='2' @if((Integer)old('status') === $POR::STATUS_CANCEL) selected @endif>Cancelado</option>
                    <option value="5" @if((Integer)old('status') === $POR::STATUS_PC_G) selected @endif>PC. Gerado</option>
                  </select>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                  <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar formulário</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
    <div class="col-md-12 mt-5">
      {{ $busca->links('pagination::bootstrap-4') }}
      <div class="table-responsive">
        <table id="requiredTable"
          class="table table-default table-striped table-bordered table-hover dataTables-example text-center">
          <thead>
            <tr>
              <th style="width: 5%;" class="text-center">#</th>
              <th width="8%">Cod SAP</th>
              <th width="10%">Cod Web</th>
              <th width="15%">Usuário</th>
              <th width="20%">Solicitante</th>
              <th width="20%">Fornecedor</th>
              <th width="10%">Data e Hora</th>
              <th width="12%">Status</th>
            </tr>
          </thead>
          <tbody class="zoom-cursor">
            @foreach ($busca as $key => $value)
              <tr value="{{ $value->id }}">
                <td>{{ $key+1 }}</td>
                <td>{{ $value->codSAP }}</td>
                <td>{{ $value->code }}</td>
                <td>{{ $value->name_solicitante }}</td>
                <td>{{ getSolicitanteRequest($value->idRequest) }}</td>
                <td style="max-width: 9em;">
                  @if (!empty($value->provider1))
                    <div class="d-flex flex-row" style="max-width: 100%;">
                      <a class="text-warning" href="{{ route('partners.edit', $value->provider1) }}" target="_blank">
                        <svg class="icon icon-lg">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                        </svg>
                      </a>
                      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                        title="{{ $value->provider1 }} - {{ $value->provider1Name }}">{{ $value->provider1 }} -
                        {{ $value->provider1Name }}</span>
                    </div>
                  @endif
                </td>
                <td> {{ formatDate($value->created_at) }} - {{ date('H:i', strtotime($value->created_at)) }}</td>
                <td style="width: 5%" class="exclude">
                  @if ($value->status == $POR::STATUS_CLOSE)
                    <a href="{{ route('purchase.quotation.read', ['id' => $value->id]) }}"
                      class="btn btn-success btn-sm" style=" width: 100%">FECHADO</a>
                  @endif
                  @if ($value->status == $POR::STATUS_OPEN)
                    <a href="{{ route('purchase.quotation.read', ['id' => $value->id]) }}"
                      class="btn btn-primary btn-sm" style=" width: 100%">ABERTO</a>
                  @endif
                  @if ($value->status == $POR::STATUS_PENDING)
                    <a href="{{ route('purchase.quotation.read', ['id' => $value->id]) }}"
                      class="btn btn-secondary btn-sm"style="width:100%;">PENDENTE</a>
                  @endif
                  @if ($value->status == $POR::STATUS_CANCEL)
                    <a href="{{ route('purchase.quotation.read', ['id' => $value->id]) }}"
                      class="btn btn-danger btn-sm" style=" width: 100%">CANCELADO</a>
                  @endif
                  @if ($value->status == $POR::STATUS_PC_G)
                    <a href="{{ route('purchase.quotation.read', ['id' => $value->id]) }}"
                      class="btn btn-warning btn-sm"style="width:100%;">PC GERADO</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    {{ $busca->links('pagination::bootstrap-4') }}
  </div>

  <div class="modal inmodal" id="requestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="requestForm" action="{{ route('puchase.quotation.copy.from.request') }}">
      <div class="modal-dialog modal-lg container">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Copiar de solicitação</h5>
            <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </button>
          </div>
          <div class="modal-body">
            <div class="tab-content">
              <div class="tab-pane active" id="tab-1">
                <div class="panel-body">
                  <div class="table-responsive">
                    <table id="table" class="table table-striped table-bordered table-hover data-table"
                      style="width: 100%;" data-search="true">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Cod. SAP</th>
                          <th>Cod. WEB</th>
                          <th>Data de lançamento</th>
                          <th>Solicitante</th>
                          <th>Observação</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if (!empty($p_requests))
                          @foreach ($p_requests as $key => $value)
                            <tr>
                              <td class="text-center">
                                <input type="checkbox" name="id_doc[]" value="{{ $value->idPurchaseRequest }}"
                                  class="form-check-input p-2">
                              </td>
                              <td>{{ $value->codSAP }}</td>
                              <td>{{ $value->code }}</td>
                              <td>{{ formatDate($value->requriedDate) }}</td>
                              <td>{{ $value->solicitante }}</td>
                              <td>{{ $value->observation }}</td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
            <button type="submit" class="btn btn-primary">Continuar</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="modal" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Pré-visualização</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table id="tablePreview"
              class="table table-default table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>Descrição</th>
                  <th>Quantidade</th>
                  <th>Qtd. Pendente</th>
                  <th>Preço</th>
                  <th>Total</th>
                </tr>
              </thead>
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
  <script>
    
    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    $("table").css("width", "100%");

    $(".data-table").DataTable({
      responsive: true,
      buttons: [],
      lengthMenu: [7, 15, 30],
      "searching": true,
      "bInfo": false,
      "order": [
        [1, 'desc']
      ],
      language: dataTablesPtBr
    });

    $('tbody tr > *:not(.exclude)').on('dblclick', function(event) {
      let idPurchaseQuotation = $(event.currentTarget).parent().attr('value');

      $('#tablePreview').dataTable().fnDestroy();

      $("#tablePreview").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: `{{ route('purchase.quotation.anyData') }}` + '/' + idPurchaseQuotation
        },
        columns: [{
            name: 'itemName',
            data: 'itemName',
            render: renderItemName
          },
          {
            name: 'qtd',
            data: 'qtd',
            render: format_quantitiesDataTable
          },
          {
            name: 'quantityPendente',
            data: 'quantityPendente',
            render: format_quantitiesDataTable
          },
          {
            name: 'priceP1',
            data: 'priceP1',
            render: format_totalsDataTable
          },
          {
            name: 'totalP1',
            data: 'totalP1',
            render: format_totalsDataTable
          },
        ],
        order: [
          [1, "desc"]
        ],
        language: dataTablesPtBr,
        paging: false,
        "lengthChange": false,
        "ordering": false,
        "bFilter": false,
        "bInfo": false,
        "searching": false
      });

      $('#previewModal').modal('show');
    });

    function renderItemName(value, type, row) {
      return `<a class="text-warning" href="{{ route('inventory.items.edit') }}/${row.itemCode}" target="_blank">
        <svg class="icon icon-lg">
          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
        </svg>
      </a>
      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${row.itemCode} - ${row.itemName}">${row.itemCode} - ${row.itemName}</span>`;
    }

    function format_totalsDataTable(data, type, row) {
      const formatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      });
      return formatter.format(data);
    }

    function format_quantitiesDataTable(data, type, row) {
      //const formatter = new Number(data).toLocaleString("pt-BR")
      const formatter = new Intl.NumberFormat('pt-BR').format(data)
      return formatter
    }
  </script>

@endsection
