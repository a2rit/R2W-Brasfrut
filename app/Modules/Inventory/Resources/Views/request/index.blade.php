@extends('layouts.main')

@section('title', 'Requisições')

@section('content')


  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de requisições internas</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          @if (auth()->user()->tipo == 'S')
            <li><a class="dropdown-item" href="{{ route('inventory.request.create') }}">Requisição</a></li>
          @endif
          <li><a class="dropdown-item" href="{{ route('inventory.request.report') }}">Relatório</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <div class="row mt-4">
    <form action="<?php echo route('inventory.request.filter'); ?>" method="GET" id="needs-validation"
      enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
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
                  <label>Cod Web</Label>
                  <input type="text" class="form-control" placeholder="REC001" name="codWeb" autocomplete="off"
                  value="{{ old('codWeb') }}">
                </div>
                <div class="col-md-4">
                  <label>Atendente</Label>
                  <select class="form-control selectpicker with-ajax-users" name="name">
                    @if(!empty(old("name"))) <option value="{{ old("name") }}" selected>{{ getUserName(old("name")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-2">
                  <label>Data Inicial</Label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off"
                  value="{{ old('Inicial') }}">
                </div>
                <div class="col-md-2">
                  <label>Data Final</Label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off"
                  value="{{ old('Final') }}">
                </div>
                <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=' '> Selecione</option>
                    <option value="{{ $requests::STATUS_WAIT_CLERK }}"
                      {{ old('status') == $requests::STATUS_WAIT_CLERK && !empty(old('status')) ? 'selected' : '' }}>Pendente</option>
                    <option value="{{ $requests::STATUS_CLERK_LINK }}"
                      {{ old('status') == $requests::STATUS_CLERK_LINK ? 'selected' : '' }}>Andamento</option>
                    <option value="{{ $requests::STATUS_PARTIAL_ATTENDED }}"
                      {{ old('status') == $requests::STATUS_PARTIAL_ATTENDED ? 'selected' : '' }}>Parcial</option>
                    <option value="{{ $requests::STATUS_REFUSED }}"
                      {{ old('status') == $requests::STATUS_REFUSED ? 'selected' : '' }}>Cancelado</option>
                    <option value="{{ $requests::STATUS_RECEIVED }}"
                      {{ old('status') == $requests::STATUS_RECEIVED ? 'selected' : '' }}>Recebido</option>
                    <option value="{{ $requests::STATUS_WAIT_REQUESTER }}"
                      {{ old('status') == $requests::STATUS_WAIT_REQUESTER ? 'selected' : '' }}>Liberado</option>
                    <option value="{{ $requests::STATUS_NFS_SAP }}"
                      {{ old('status') == $requests::STATUS_NFS_SAP ? 'selected' : '' }}>Comprado</option>
                    <option value="{{ $requests::STATUS_CANCELED }}"
                      {{ old('status') == $requests::STATUS_CANCELED ? 'selected' : '' }}>Cancelado</option>
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

      <div class="table-responsive mt-5">
        {{ $busca->links('pagination::bootstrap-4') }}
        <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
          <thead>
            <tr>
              <th style="width: 5%;">#</th>
              <th style="width: 10%;">Cod Web</th>
              <th style="width: 10%;">Cod SAP</th>
              <th>Atendente</th>
              <th style="width: 10%;">Data Requisição</th>
              <th style="width: 10%;">Data Necessária</th>
              <th style="width: 10%;">Status</th>
            </tr>
          </thead>
          <tbody class="zoom-cursor">
            @if (isset($busca))
              <?php $cont = 1; ?>
              @foreach ($busca as $key => $value)
                <tr value="{{ $value->idRequest }}">
                  <td>{{ $cont }}</td>
                  <td>{{ $value->code }}</td>
                  <th>{{ $value->codSAP }}</th>
                  <td>
                    @if (is_null($value->name))
                      Nenhum
                    @else
                      {{ $value->name }}
                    @endif
                  </td>
                  <td>{{ formatDate($value->documentDate) }}</td>
                  <td>{{ formatDate($value->requiredDate) }}</td>
                  <td class="text-center exclude">
                    @if ($value->codStatus == $requests::STATUS_WAIT_CLERK)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-secondary btn-sm w-100">Pendente</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_CLERK_LINK)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-primary btn-sm w-100">Andamento</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_PARTIAL_ATTENDED)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-secondary btn-sm w-100">Parcial</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_REFUSED)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-danger btn-sm w-100">Cancelado</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_RECEIVED)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-success btn-sm w-100">Recebido</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_WAIT_REQUESTER)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-info btn-sm w-100">Liberado</a>
                    @endif
                    @if ($value->codStatus == $requests::STATUS_NFS_SAP)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-success btn-sm w-100">Comprado</a>
                    @endif

                    @if ($value->codStatus == $requests::STATUS_LINK)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-warning btn-sm w-100">Vincular</a>
                    @endif

                    @if ($value->codStatus == $requests::STATUS_CANCELED)
                      <a href="{{ route('inventory.request.searching', ['id' => $value->idRequest]) }}"
                        class="btn btn-danger btn-sm w-100">Cancelado</a>
                    @endif
                  </td>
                </tr>
                <?php $cont++; ?>
              @endForeach
            @endif
          </tbody>
        </table>
        {{ $busca->links('pagination::bootstrap-4') }}
      </div>
  </div>
  <div class="modal inmodal" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Pré-visualização</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="table-responsive">
              <table id="table" class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Cod SAP</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script>

    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));
    
    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    $('#requiredTable tbody tr > *:not(.exclude)').on('click', function(event) {
      let idRequest = $(event.currentTarget).parent().attr('value');

      $('#table').dataTable().fnDestroy();

      $("#table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: `{{ route('inventory.anyData') }}/${idRequest}`
        },
        columns: [{
            name: 'codSAP',
            data: 'codSAP'
          },
          {
            name: 'itemName',
            data: 'itemName'
          },
          {
            name: 'quantityRequest',
            data: 'quantityRequest',
            render: renderFormatedQuantity
          }
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

    function renderFormatedQuantity(valor) {
      return new Intl.NumberFormat('pt-BR').format(valor);
    }
  </script>
@endsection
