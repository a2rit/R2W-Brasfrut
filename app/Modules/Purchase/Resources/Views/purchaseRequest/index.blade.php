@extends('layouts.main')

@section('title', 'Solicitação de compras')

@section('content')
  <div class="row">
    <div class="col-md-6">
      <h3 class="header-page">Lista de solicitações de compras</h3>
    </div>
    <div class="col-md-6">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('purchase.request.create') }}">Solicitação de compra</a>
          </li>
          <li><a class="dropdown-item" href="{{ route('purchase.request.report') }}">Relatório</a></li>
        </ul>
      </div>
      <div class="dropdown float-end me-1">
        <button class="btn btn-primary dropdown-toggle" type="button" data-coreui-toggle="dropdown"
          aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#tools') }}"></use>
          </svg> Ferramentas
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="#" data-coreui-toggle="modal" data-coreui-target="#massEditingModal"
              onclick="loadPurchaserRequest()">Edição em massa</a>
          </li>
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
          <h5 class="card-title">{{ $buscaGraph->where('codStatus', 1)->count() }} <span
              class="small">Solicitações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-danger border-top-3 mb-3 text-center">
        <div class="card-header">Cancelados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('codStatus', 2)->count() }} <span
              class="small">Solicitações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-success border-top-3 mb-3 text-center">
        <div class="card-header">Fechados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('codStatus', 5)->count() }} <span
              class="small">Solicitações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-dark border-top-3 mb-3 text-center">
        <div class="card-header">PC. Parcial</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('codStatus', 3)->count() }} <span
              class="small">Solicitações</span></h5>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card border-top-warning border-top-3 mb-3 text-center">
        <div class="card-header">Cotação Gerada</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('codStatus', 6)->count() }} <span
              class="small">Solicitações</span></h5>
        </div>
      </div>
    </div>
    <span class="fst-italic text-small">Periodo: últimos 12 meses</span>
  </div>
  <hr>

  <div class="row pt-2">
    <form action="<?php echo route('purchase.request.filter'); ?>" method="get" id="needs-validation" class="mb-5" enctype="multipart/form-data"
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
                  <input type="text" class="form-control" placeholder="SLC00001 ou REC00001" name="code"
                    autocomplete="off" value="{{ old('code') }}">
                </div>
                <div class="col-md-2">
                  <label>Cod SAP</Label>
                  <input type="text" class="form-control" placeholder="00001" name="codSAP" autocomplete="off"
                    value="{{ old('codSAP') }}">
                </div>
                <div class="col-md-4">
                  <label>Usuário</Label>
                  <select class="form-control selectpicker with-ajax-users" name="usuario">
                    @if (!empty(old('usuario')))
                      <option value="{{ old('usuario') }}" selected>{{ getUserName(old('usuario')) }}</option>
                    @endif
                  </select>
                </div>
                <div class="col-md-4">
                  <label>Solicitante</Label>
                  <select class="form-control selectpicker with-ajax-users" name="solicitante">
                    @if (!empty(old('solicitante')))
                      <option value="{{ old('solicitante') }}" selected>{{ getUserName(old('solicitante')) }}</option>
                    @endif
                  </select>
                </div>
              </div>
              <div class="row pt-2">
                <div class="col-md-2">
                  <label>Data Inicial</Label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial"
                    autocomplete="off" value="{{ old('data_fist') }}">
                </div>
                <div class="col-md-2">
                  <label>Data Final</Label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off"
                    value="{{ old('data_last') }}">
                </div>
                <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=''> Selecione</option>
                    @foreach ($POR::TEXT_STATUS as $key => $status)
                      <option value="{{ $POR::STATUS_OPEN }}" @if ((int) old('status') === $key) selected @endif>{{ $status }}</option>
                    @endforeach
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

    <div class="col-md-12">
      {{ $busca->links('pagination::bootstrap-4') }}
      <div class="table-responsive text-center">
        <table id="requiredTable"
          class="table table-default table-striped table-bordered table-hover dataTables-example">
          <thead>
            <tr>
              <th style="width: 5%;" class="text-center">#</th>
              <th>Cod. SAP</th>
              <th>Cod Web</th>
              <th>Usuário</th>
              <th>Solicitante</th>
              <th>Data Criação</th>
              <th>Data Necessária</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody class="zoom-cursor">
            @if (isset($busca))
              @foreach ($busca as $key => $value)
                <tr value="{{ $value->id }}">
                  <td>{{ $key + 1 }}</td>
                  <td style="width:10%">{{ $value->codSAP }}</td>
                  <td style="width:10%">{{ $value->code }}</td>
                  <td style="width:20%">{{ $value->name }}</td>
                  <td style="width:20%">{{ $value->solicitante }}</td>
                  <td style="width:15%">{{ formatDate($value->created_at) }} -
                    {{ date('H:i', strtotime($value->created_at)) }}</td>
                  <td style="width:10%">{{ formatDate($value->requriedDate) }}</td>
                  <td class="exclude" style="width:15%">
                    <a href="{{ route('purchase.request.read', ['id' => $value->id]) }}"
                      class="btn {{ $value::STATUS_COLOR[$value->codStatus] }} btn-sm" style="width: 100%">{{ $value::TEXT_STATUS[$value->codStatus] }}</a>
                  </td>
                </tr>
              @endForeach
            @endif
          </tbody>
        </table>
      </div>
      {{ $busca->links('pagination::bootstrap-4') }}
    </div>
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
            <table id="table"
              class="table table-default table-striped table-bordered table-hover dataTables-example">
              <thead>
                <tr>
                  <th>Descrição</th>
                  <th>Quantidade</th>
                  <th>Qtd. Pendente</th>
                  <th>Projeto</th>
                  <th>C. Custo</th>
                  <th>C. Custo 2</th>
                  <th>Depósito</th>
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

  <div class="modal inmodal" id="massEditingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="editMassForm">
      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edição em massa</h5>
            <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="massEditingAction">
            <div class="row">
              <div class="col-md-4">
                <label for="">Solicitante</label>
                <select class="form-control selectpicker with-ajax-users" name="requester"
                  onchange='$("#massEditingTable").DataTable().draw()'></select>
              </div>
              <div class="col-md-3">
                <label for="">Data</label>
                <input type="text" id="date" name="date" class="form-control dtrangepicker"
                  autocomplete="off" onchange='$("#massEditingTable").DataTable().draw()' />
              </div>
              <div class="col-12 mt-2">
                <button class="btn btn-warning btn-sm" onclick="clearForm(event)" type="button">Limpar
                  formulário</button>
              </div>
            </div>
            <div class="table-responsive mt-4">
              <table id="massEditingTable" class="table table-default table-striped table-bordered table-hover"
                style="width: 100%;">
                <thead>
                  <tr>
                    <th style="width: 3%" data-orderable="false"><input type="checkbox" class="form-check"
                        style="width: 20px; height: 20px;"
                        onclick="turnCheckedAllCheckboxes(event, 'editMassForm', 'idPurchaseRequest-')"></th>
                    <th style="width: 10%">Cod. SAP</th>
                    <th style="width: 10%">Cod. WEB</th>
                    <th>Solicitante</th>
                    <th style="width: 10%">Data Solicitada</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-12 mt-4">
              <button type="button" class="btn btn-success float-end"
                onclick="editingMass(event, 'close')">Fechar</button>
              <button type="button" class="btn btn-danger float-end me-1"
                onclick="editingMass(event, 'cancel')">Cancelar</button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-white float-start" data-coreui-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script>
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));

    $('.dtrangepicker').daterangepicker(daterangepickerConfig);
    $('.dtrangepicker').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      $(this).trigger('change');
    });

    $('#requiredTable tbody tr > *:not(.exclude)').on('dblclick', function(event) {
      let idPurchaseRequest = $(event.currentTarget).parent().attr('value');

      $('#table').dataTable().fnDestroy();

      $("#table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: `{{ route('purchase.request.anyData') }}` + '/' + idPurchaseRequest
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
            name: 'quantityPendente',
            data: 'quantityPendente',
            render: format_quantitiesDataTable
          },
          {
            name: 'project',
            data: 'project'
          },
          {
            name: 'distrRule',
            data: 'distrRule'
          },
          {
            name: 'distriRule2',
            data: 'distriRule2'
          },
          {
            name: 'wareHouseCode',
            data: 'wareHouseCode'
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

    function loadPurchaserRequest() {
      let massEditingTable = $("#massEditingTable").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        searching: true,
        ajax: {
          url: "{{ route('purchase.request.getPurchaseRequests') }}",
          data: function(d) {
            d.date = $('#massEditingModal').find('input[name="date"]').val();
            d.requester = $('#massEditingModal').find('select[name="requester"]').val();
          }
        },
        columns: [{
            name: 'id',
            data: 'id',
            render: renderCheckBox,
            orderable: false
          },
          {
            name: 'codSAP',
            data: 'codSAP'
          },
          {
            name: 'code',
            data: 'code'
          },
          {
            name: 'name',
            data: 'name',
          },
          {
            name: 'requriedDate',
            data: 'requriedDate',
            render: format_date
          },

        ],
        lengthMenu: [8, 15, 30],
        language: dataTablesPtBr
      });
    }

    function renderItemName(value, type, row) {
      return `<a class="text-warning" href="{{ route('inventory.items.edit') }}/${row.itemCode}" target="_blank">
        <svg class="icon icon-lg">
          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
        </svg>
      </a>
      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${row.itemCode} - ${row.ItemName}">${row.itemCode} - ${row.ItemName}</span>`;
    }

    function renderCheckBox(data, type, row) {
      return `<center>
          <input id="idPurchaseRequest-${data}" name="idPurchaseRequest[${data}]" data-code="${row.code}" type="checkbox" class="form-check" style="width: 20px;">
      </center>`
    }

    function format_date(data, type, row) {
      return new Date(data + " 21:00:00").toLocaleDateString("pt-BR");
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

    function editingMass(event, action) {

      $('input[name="massEditingAction"]').val(action);
      let codes = $('#editMassForm input[name*="idPurchaseRequest"]:checked').serializeArray().map(function(item) {
        return $(`input[name="${item.name}"]`).attr('data-code');
      });

      swal({
          title: `Tem certeza que deseja ${(action === 'cancel' ? 'cancelar' : 'fechar')}?`,
          text: `Os pedidos ${codes.toString()} serão enviados para processamento.`,
          icon: "warning",
          //buttons: true,
          buttons: ["Não", "Sim"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            waitingDialog.show('Processando...');
            $.ajax({
              url: '{{ route('purchase.request.editingMass') }}',
              type: 'POST',
              headers: {
                'X-CSRF-TOKEN': $('body input[name="_token"]').val()
              },
              dataType: "json",
              data: $('#editMassForm').serialize(),
              success: function(response) {

                $('#editMassForm input[name*="idPurchaseRequest"]:checked').serializeArray().map(function(item) {
                  $(`input[name="${item.name}"]`).closest("tr").remove();
                });

                waitingDialog.hide();
                swal({
                  title: "Sucesso!",
                  text: response.message,
                  icon: "success"
                });
              },
              error: function(error) {
                swal({
                  title: "Erro",
                  text: error,
                  icon: "error"
                });
                waitingDialog.hide();
              }
            });
          }
        });
    }

    let checkedCheckbox = false;
    function turnCheckedAllCheckboxes(event, id, elementId = null) {
      if (checkedCheckbox === false) {
        $(`#${id}`).find(`input[id*="${elementId}"]`).attr('checked', true);
        checkedCheckbox = true;
      } else if (checkedCheckbox === true) {
        $(`#${id}`).find(`input[id*="${elementId}"]`).attr('checked', false);
        checkedCheckbox = false;
      }
    }
  </script>

@endsection
