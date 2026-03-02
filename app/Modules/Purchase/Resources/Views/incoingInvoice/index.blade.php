@extends('layouts.main')

@section('title', 'Nota Fiscal de Entrada')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de notas fiscais de entrada</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('purchase.ap.invoice.create') }}">Nota fiscal de entrada</a>
          </li>
          <li><a class="dropdown-item" href="{{ route('purchase.ap.invoice.report') }}">Relatório</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>


  <div class="row">
    <div class="col-4">
      <div class="card border-top-primary border-top-3 mb-3 text-center">
        <div class="card-header">Abertos</div>
        <div class="card-body">
          <h5 class="card-title">{{$buscaGraph->where('status', 1)->count()}} <span class="small">Notas fiscais</span></h5>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card border-top-danger border-top-3 mb-3 text-center">
        <div class="card-header">Cancelados</div>
        <div class="card-body">
          <h5 class="card-title">{{$buscaGraph->where('status', 2)->count()}} <span class="small">Notas fiscais</span></h5>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card border-top-success border-top-3 mb-3 text-center">
        <div class="card-header">Fechados</div>
        <div class="card-body">
          <h5 class="card-title">{{$buscaGraph->where('status', 0)->count()}} <span class="small">Notas fiscais</span></h5>
        </div>
      </div>
    </div>
    <span class="fst-italic text-small">Periodo: últimos 12 meses</span>
  </div>
  <hr>

  <div class="row pt-2">
    <form action="{{ route('purchase.ap.invoice.filter') }}" method="get" id="needs-validation"
      enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
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
                <div class="col-md-3">
                  <label>Código SAP</Label>
                  <input type="text" class="form-control" placeholder="10" name="codSAP" autocomplete="off" value="{{ old('codSAP') }}">
                </div>
                <div class="col-md-4">
                  <label>Código WEB</Label>
                  <input type="text" class="form-control" placeholder="II0001" name="code" autocomplete="off" value="{{ old('code') }}">
                </div>
                <div class="col-md-5">
                  <label>Parceiro</Label>
                    <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body" name="cardName">
                      @if(!empty(old("cardName"))) <option value="{{ old("cardName") }}" selected>{{ getPartnerName(old("cardName")) }}</option>@endif
                    </select>
                </div>
              </div>
              <div class="row pt-2">
                <div class="col-md-4">
                  <label>CNPJ/CPF</Label>
                  <input type="text" class="form-control" id='cpfcnpj' placeholder="337.214.827-43"
                    onblur="maskBrasilInput();" name="cpf_cnpj" autocomplete="off" value="{{ old("cpf_cnpj") }}">
                </div>
                <div class="col-md-3">
                  <label>Número NF</Label>
                  <input type="text" class="form-control" placeholder="N° NF" name="sequenceSerial" autocomplete="off" value="{{ old('sequenceSerial') }}">
                </div>
                <div class="col-md-5">
                  <label>Usuário</Label>
                  <select class="form-control selectpicker with-ajax-users" name="user">
                    @if(!empty(old("user"))) <option value="{{ old("user") }}" selected>{{ getUserName(old("user")) }}</option>@endif
                  </select>
                </div>
              </div>
              <div class="row pt-2">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Inicial</label>
                    <input type="date" name="data_fist" placeholder="{{ DATE('d/m/Y') }}" class="form-control"
                      autocomplete="off" value="{{ old('data_fist') }}">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Final</label>
                    <input type="date" name="data_last" placeholder="{{ DATE('d/m/Y') }}" class="form-control"
                      autocomplete="off" value="{{ old('data_last') }}">
                  </div>
                </div>
                <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=''>Selecione</option>
                    <option value='1' @if((Integer)old('status') === $OPCH::STATUS_OPEN) selected @endif>Aberto</option>
                    <option value='0' @if((Integer)old('status') === $OPCH::STATUS_OPEN) selected @endif>Fechado</option>
                    <option value='2' @if((Integer)old('status') === $OPCH::STATUS_OPEN) selected @endif>Cancelado</option>
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
      {{ $items->links('pagination::bootstrap-4') }}
      <div class="table-responsive ">
        <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
          <thead>
            <tr>
              <th style="width: 5%;" class="text-center">#</th>
              <th style="width: 10%">Cod. SAP</th>
              <th style="width: 10%">Cod. WEB</th>
              <th style="width: 20%">Usuario</th>
              <th style="width: 25%">Fornecedor</th>
              <th style="width: 10%">Data e Hora</th>
              <th style="width: 10%">Total</th>
              <th style="width: 10%">Status</th>
            </tr>
          </thead>
          <tbody class="zoom-cursor">
            @if (isset($items))
              <?php $cont = 1; ?>
              @foreach ($items as $key => $value)
                <tr value="{{$value->id}}">
                  <td class="text-center">{{ $cont }}</td>
                  <td class="text-center">{{ $value->codSAP }}</td>
                  <td class="text-center">{{ $value->code }}</td>
                  <td>{{ $value->user }}</td>
                  <td style="max-width: 16em;">
                    <div class="d-flex flex-row" style="max-width: 100%;">
                      <a class="text-warning" href="{{route('partners.edit', $value->cardCode)}}" target="_blank">
                        <svg class="icon icon-lg">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                        </svg>
                      </a>
                      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="{{ $value->cardCode }} - {{ $value->cardName }}">{{ $value->cardCode }} - {{ $value->cardName }}</span>
                    </div>
                  </td>
                  <td>
                    {{ formatDate($value->created_at) }} - {{ date('H:i', strtotime($value->created_at)) }}</td>
                  <td>{{ number_format($value->docTotal, 2, ',', '.') }}</td>
                  <td class="exclude">
                    @if ($value->status == 0)
                        <a href="{{ route('purchase.ap.invoice.read', $value->id) }}" class="btn btn-success btn-sm"
                          style=" width: 100%">FECHADO</a>
                    @endif
                    @if ($value->status == 1)
                        <a href="{{ route('purchase.ap.invoice.read', $value->id) }}" class="btn btn-primary btn-sm"
                          style=" width: 100%">ABERTO</a>
                    @endif
                    @if ($value->status == 2)
                        <a href="{{ route('purchase.ap.invoice.read', $value->id) }}" class="btn btn-danger btn-sm"
                          style=" width:100%">CANCELADO</a>
                    @endif
                    @if ($value->status == 3)
                        <a href="{{ route('purchase.ap.invoice.read', $value->id) }}" class="btn btn-warning btn-sm"
                          style=" width:100%">PENDENTE</a>
                    @endif
                  </td>
                </tr>
                <?php $cont++; ?>
              @endForeach
            @endif
          </tbody>
        </table>
      </div>
    </div>
    {{ $items->links('pagination::bootstrap-4') }}
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
                <th>Preço unit.</th>
                <th>Total</th>
                <th>Utilização</th>
                <th>Projeto</th>
                <th>C. Custo</th>
                <th>C. Custo 2</th>
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
  <script type="text/javascript">

    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));
    
    $("table").css("width", "100%");

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    $('#requiredTable tbody tr > *:not(.exclude)').on('dblclick', function(event) {
      let idPurchaseOrder = $(event.currentTarget).parent().attr('value');

      $('#tablePreview').dataTable().fnDestroy();
      $("#tablePreview").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: `{{ route('purchase.ap.invoice.anyData') }}` + '/' + idPurchaseOrder
        },
        columns: [{
          name: 'itemName',
          data: 'itemName',
          render: renderItemName
        },
          {
            name: 'quantity',
            data: 'quantity',
            render: format_quantitiesDataTable
          },
          {
            name: 'price',
            data: 'price',
            render: format_totalsDataTable
          },
          {
            name: 'lineSum',
            data: 'lineSum',
            render: format_totalsDataTable
          },
          {
            name: 'Descr',
            data: 'Descr'
          },
          {
            name: 'codProject',
            data: 'codProject'
          },
          {
            name: 'distrRule',
            data: 'distrRule'
          },
          {
            name: 'distriRule2',
            data: 'distriRule2'
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

    function format_totalsDataTable(data, type, row){
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
  </script>
@endsection
