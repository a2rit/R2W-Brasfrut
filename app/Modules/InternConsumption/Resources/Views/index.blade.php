@extends('layouts.main')
@section('title', 'Consumo Interno')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Consumo Interno</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="{{ route('intern-consumption.create', '0') }}">Consumo interno</a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('intern-consumption.create', '1') }}">Consumo interno - Perdas</a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('intern-consumption.create', '2') }}">Consumo interno - Eventos</a>
          </li>
          <li><a class="dropdown-item" href="{{ route('intern-consumption.report.index') }}">Relatório</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>
  
  <div class="table-responsive">
    <table class="table table-default table-striped" id="table">
      <thead>
        <tr>
          <th>Id</th>
          <th>
            Tipo
            <select class="form-control" id="document_type" onchange="table.draw()">
              <option value="">Tipo</option>
              <option value="0">Padrão</option>
              <option value="1">Perdas</option>
              <option value="2">Eventos</option>
            </select>
          </th>
          <th>
            Solicitante
            <select class="form-control selectpicker with-ajax-users" id="requester" name="requester" onchange="table.draw()"></select>
          </th>
          <th>
            <select class="form-control" id="status" onchange="table.draw()">
              <option value="">Status</option>
              <option value="new">Pendente de autorização</option>
              <option value="authorized">Liberado</option>
              <option value="unauthorized">Não liberado</option>
              <option value="finalized">Finalizado</option>
              <option value="journal-pending">Pendente de lançamento contábil</option>
              {{-- <option value="waiting_production_order">Aguardando ordem de produção</option> --}}
            </select>
          </th>
          <th>
            Data da solicitação
            <input type="text" id="request_date" name="request_date" class="form-control dtrangepicker" onchange="table.draw()" autocomplete="off"/>
          </th>
          <th>Regra de distribuição</th>
          <th>Projeto</th>
          <th>Observação</th>
          <th>Detalhes</th>
        </tr>
      </thead>
    </table>
  </div>
@endsection

@section('scripts')
  <script>
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));

    let table = $('#table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('intern-consumption.indexData') }}",
        data: function(data) {
          data.status = $('#status').val();
          data.request_date = $('#request_date').val();
          data.requester = $('#requester').val();
          data.document_type = $('#document_type').val();
        }
      },
      columns: [
        {
          name: 'id',
          data: 'id'
        },
        {
          name: 'document_type',
          data: 'document_type',
          orderable: false,
          render: renderDocumentType,
        },
        {
          name: 'requester',
          data: 'requester_name',
          orderable: false
        },
        {
          name: 'status',
          data: 'status_label',
          orderable: false
        },
        {
          name: 'date',
          data: 'date',
          render: renderDate,
          orderable: false
        },
        {
          name: 'distribution_rule',
          data: 'distribution_rule_name'
        },
        {
          name: 'project_name',
          data: 'project_name'
        },
        {
          name: 'observation',
          data: 'observation',
          orderable: false
        },
        {
          name: 'show_url',
          data: 'show_url',
          render: renderViewButton,
          orderable: false
        }
      ],
      order: [
        [0, "desc"]
      ],
      lengthMenu: [10, 25, 50, 100],
      language: dataTablesPtBr,
      searching: true,
      stateSave: true,
    });

    $('#request_date').daterangepicker(daterangepickerConfig);

    $('.dtrangepicker').daterangepicker(daterangepickerConfig);
    $('.dtrangepicker').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      $(this).trigger('change');
    });


    function renderDate(value) {
      return dateFormat(value, 'UTC:dd-mm-yyyy');
    }

    function renderViewButton(url) {
      return `<center>
        <a class='text-primary' href="${url}">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('icons_assets/custom.svg#edit') }}"></use>
          </svg>
        </a>
      </center>`;
    }

    function renderDocumentType(data){
      const document_type_text = {
        0: 'Padrão',
        1: 'Perdas',
        2: 'Eventos',
      }
      return document_type_text[data];
    }

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });
  </script>
@endsection
