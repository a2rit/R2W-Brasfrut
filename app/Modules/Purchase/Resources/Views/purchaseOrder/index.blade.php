@extends('layouts.main')
@section('title', 'Pedido de compra')
@section('content')
  <div class="row">
    <div class="col-6">
      <h3 class="header-page">Lista de pedidos de compras</h3>
    </div>
    <div class="col-6">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('purchase.order.create') }}">Pedido de compras</a></li>
          <li><a class="dropdown-item" href="#" data-coreui-toggle="modal" data-coreui-target="#requestModal">Copiar
              de solicitação</a></li>
          <li><a class="dropdown-item" href="{{ route('purchase.order.report') }}">Relatório</a></li>
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
          <li><a class="dropdown-item" href="#" data-coreui-toggle="modal" data-coreui-target="#massEditingModal"
              onclick="loadPurchaseOrder()">Edição em massa</a></li>
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
          <h5 class="card-title">{{ $buscaGraph->where('status', 1)->count() }} <span class="small">Pedidos</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-danger border-top-3 mb-3 text-center">
        <div class="card-header">Cancelados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 2)->count() }} <span class="small">Pedidos</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-success border-top-3 mb-3 text-center">
        <div class="card-header">Fechados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 0)->count() }} <span class="small">Pedidos</span></h5>
        </div>
      </div>
    </div>
    <div class="col-2">
      <div class="card border-top-dark border-top-3 mb-3 text-center">
        <div class="card-header">Pendentes</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 3)->count() }} <span class="small">Pedidos</span></h5>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card border-top-danger border-top-3 mb-3 text-center">
        <div class="card-header">Reprovados</div>
        <div class="card-body">
          <h5 class="card-title">{{ $buscaGraph->where('status', 4)->count() }} <span class="small">Pedidos</span></h5>
        </div>
      </div>
    </div>
    <span class="fst-italic text-small">Periodo: últimos 12 meses</span>
  </div>
  <hr>

  <div class="col-lg-12">
    <form action="{{ route('purchase.order.filter') }}" method="GET" id="needs-validation"
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
                  <label>Código SAP</Label>
                  <input type="text" class="form-control" value="{{ old('codSAP') }}" placeholder="001" name="codSAP" autocomplete="off">
                </div>
                <div class="col-md-2">
                  <label>Código WEB</Label>
                  <input type="text" class="form-control" value="{{ old('code') }}" placeholder="PO0001" name="code" autocomplete="off">
                </div>
                <div class="col-md-4">
                  <label>Parceiro</Label>
                  <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body" name="cardName">
                    @if(!empty(old("cardName"))) <option value="{{ old("cardName") }}" selected>{{ getPartnerName(old("cardName")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-3">
                  <label>CNPJ/CPF</Label>
                  <input type="text" class="form-control" id='cpfcnpj' value="{{ old('cpf_cnpj') }}"
                    placeholder="337.214.827-43" onblur="maskBrasilInput();" name="cpf_cnpj" autocomplete="off">
                </div>
                <div class="col-md-4">
                  <label>Usuario</Label>
                  <select class="form-control selectpicker with-ajax-users" name="usuario">
                    @if(!empty(old("usuario"))) <option value="{{ old("usuario") }}" selected>{{ getUserName(old("usuario")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Inicial</label>
                    <input type="date" name="data_fist" value="{{ old('data_fist') }}"
                      placeholder="{{ DATE('d/m/Y') }}" class="form-control datepicker" placeholder="Inicial" autocomplete="off">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Final</label>
                    <input type="date" name="data_last" value="{{ old('data_last') }}"
                      placeholder="{{ DATE('d/m/Y') }}" class="form-control datepicker" placeholder="Final" autocomplete="off">
                  </div>
                </div>
                <div class="col-md-4">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=''>Selecione</option>
                    <option value='1' @if((Integer)old('status') === $OPOR::STATUS_OPEN) selected @endif>Aberto</option>
                    <option value='0' @if((Integer)old('status') === $OPOR::STATUS_CLOSE && !empty(old('status'))) selected @endif>Fechado</option>
                    <option value='2' @if((Integer)old('status') === $OPOR::STATUS_CANCEL) selected @endif>Cancelado</option>
                    <option value='3' @if((Integer)old('status') === $OPOR::STATUS_PENDING) selected @endif>Pendente</option>
                    <option value='4' @if((Integer)old('status') === $OPOR::STATUS_REPROVE) selected @endif>Reprovado</option>
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
      @if (isset($items))
        {{ $items->links('pagination::bootstrap-4') }}
        <div class="table-responsive">
          <table id="requiredTable"
            class="table table-default table-striped table-bordered table-hover dataTables-example">
            <thead>
              <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 10%;">Cod. SAP</th>
                <th style="width: 10%;">Cod. WEB</th>
                <th style="width: 20%;">Usuário</th>
                <th style="width: 25%;">Fornecedor</th>
                <th style="width: 10%;">Data e Hora</th>
                <th style="width: 10%;">Total</th>
                <th style="width: 10%;">Status</th>
              </tr>
            </thead>
            <tbody class="zoom-cursor">
              @foreach ($items as $key => $value)
                <tr value="{{ $value->id }}">
                  <td class="text-center">{{ $key + 1 }}</td>
                  <td class="text-center">{{ $value->codSAP }}</td>
                  <td class="text-center">{{ $value->code }}</td>
                  <td>{{ $value->user->name }}</td>
                  <td style="max-width: 16em;">
                    <div class="d-flex flex-row" style="max-width: 100%;">
                      <a class="text-warning" href="{{ route('partners.edit', $value->cardCode) }}" target="_blank">
                        <svg class="icon icon-lg">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                        </svg>
                      </a>
                      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                        title="{{ $value->cardCode }} - {{ $value->cardName }}">{{ $value->cardCode }} -
                        {{ $value->cardName }}</span>
                    </div>
                  </td>
                  <td>{{ formatDate($value->created_at) }} - {{ date('H:i', strtotime($value->created_at)) }}</td>
                  <td>{{ number_format($value->docTotal, 2, ',', '.') }}</td>
                  <td class="exclude">
                    @if ($value->status == $value::STATUS_CLOSE)
                      <a href="{{ route('purchase.order.read', $value->id) }}" class="btn btn-success btn-sm"
                        style=" width: 100%">FECHADO</a>
                    @endif
                    @if ($value->status == $value::STATUS_OPEN)
                      <a href="{{ route('purchase.order.read', $value->id) }}" class="btn btn-primary btn-sm"
                        style=" width: 100%">ABERTO</a>
                    @endif
                    @if ($value->status == $value::STATUS_CANCEL)
                      <a href="{{ route('purchase.order.read', $value->id) }}" class="btn btn-danger btn-sm"
                        style=" width: 100%">CANCELADO</a>
                    @endif
                    @if ($value->status == $value::STATUS_PENDING)
                      <a href="{{ route('purchase.order.read', $value->id) }}" class="btn btn-secondary btn-sm"
                        style="width:100%">PENDENTE</a>
                    @endif
                    @if ($value->status == $value::STATUS_REPROVE)
                      <a href="{{ route('purchase.order.read', $value->id) }}"
                        class="btn btn-danger btn-sm">REPROVADO</a>
                    @endif
                  </td>
                </tr>
              @endForeach
            </tbody>
          </table>
        </div>
    </div>
    {{ $items->links('pagination::bootstrap-4') }}
    @endif
  </div>

  <div class="modal inmodal" id="requestModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="requestForm" action="{{ route('puchase.order.copy.from.request') }}">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Copiar de solicitação</h5>
            <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Fornecedor</label>
                  <select class="form-control selectpicker with-ajax-suppliers" name="cardCode" required>
                    @if (isset($preferenceSuplier) && !is_null($preferenceSuplier))
                      <option value="">{{ $preferenceSuplier }}</option>
                    @endif
                  </select>
                </div>
              </div>
            </div>
            <div class="table-responsive mt-4">
              <table id="copy-request" class="table table-default table-striped table-bordered table-hover"
                style="width: 100%;">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Cod. SAP</th>
                    <th>Cod. WEB</th>
                    <th>Usuario</th>
                    <th>Solicitante</th>
                    <th>Data de lançamento</th>
                    <th>Observação</th>
                  </tr>
                </thead>
                <tbody>
                  @if (!empty($p_requests))
                    @foreach ($p_requests as $key => $value)
                      <tr>
                        <td>
                          <input type="checkbox" name="id_doc[]" value="{{ $value->id }}"
                            class="form-check-input p-2">
                        </td>
                        <td>{{ $value->codSAP }}</td>
                        <td>{{ $value->code }}</td>
                        <td>{{ $value->name }}</td>
                        <td>{{ $value->solicitante }}</td>
                        <td>{{ formatDate($value->requriedDate) }}</td>
                        <td>{{ $value->observation }}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
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
                  <th>Preço unit.</th>
                  <th>Total</th>
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
                <label for="">Fornecedor</label>
                <select class="form-control selectpicker with-ajax-partner" name="partner" onchange='$("#massEditingTable").DataTable().draw()'></select>
              </div>
              <div class="col-md-4">
                <label for="">Usuário</label>
                <select class="form-control selectpicker with-ajax-users" name="user" onchange='$("#massEditingTable").DataTable().draw()'></select>
              </div>
              <div class="col-md-3">
                <label for="">Data</label>
                <input type="text" id="date" name="date" class="form-control dtrangepicker" autocomplete="off" onchange='$("#massEditingTable").DataTable().draw()'/>
              </div>
              <div class="col-12 mt-2">
                <button class="btn btn-warning btn-sm" onclick="clearForm(event)" type="button">Limpar formulário</button>
              </div>
            </div>
            <div class="table-responsive mt-4">
              <table id="massEditingTable" class="table table-default table-striped table-bordered table-hover"
                style="width: 100%;">
                <thead>
                  <tr>
                    <th style="width: 3%" data-orderable="false"><input type="checkbox" class="form-check" style="width: 20px; height: 20px;" onclick="turnCheckedAllCheckboxes(event, 'editMassForm', 'idPurchaseOrder-')"></th>
                    <th style="width: 10%">Cod. SAP</th>
                    <th style="width: 10%">Cod. WEB</th>
                    <th>Usuario</th>
                    <th>Fornecedor</th>
                    <th style="width: 10%">Data</th>
                    <th style="width: 10%">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td></td>
                    <td></td>
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
  <script type="text/javascript">
    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));

    $('.dtrangepicker').daterangepicker(daterangepickerConfig);
    $('.dtrangepicker').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      $(this).trigger('change');
    });

    $(document).ready(() => {
      $("table").css("width", "100%");

      $("#copy-request").DataTable({
        autoWidth: false,
        responsive: true,
        buttons: [],
        lengthMenu: [5, 20, 30],
        "searching": true,
        "bInfo": false,
        language: dataTablesPtBr,
        columns: [{
            "width": "5%"
          },
          {
            "width": "10%"
          },
          {
            "width": "10%"
          },
          {
            "width": "25%"
          },
          {
            "width": "25%"
          },
          {
            "width": "10%"
          },
          {
            "width": "15%"
          }
        ]
      });

    });

    $('#requiredTable tbody tr > *:not(.exclude)').on('dblclick', function(event) {
      let idPurchaseOrder = $(event.currentTarget).parent().attr('value');

      $('#tablePreview').dataTable().fnDestroy();
      $("#tablePreview").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: `{{ route('purchase.order.preview') }}` + '/' + idPurchaseOrder
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
      const formatter = new Intl.NumberFormat('pt-BR').format(data)
      return formatter
    }

    function format_date(data, type, row) {
      return new Date(data+" 21:00:00").toLocaleDateString("pt-BR");
    }

    function loadPurchaseOrder() {
      let massEditingTable = $("#massEditingTable").DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        searching: true,
        ajax: {
          url: "{{ route('purchase.order.getPurchaseOrders') }}",
          data: function(d) {
            d.date = $('#massEditingModal').find('input[name="date"]').val();
            d.user = $('#massEditingModal').find('select[name="user"]').val();
            d.partner = $('#massEditingModal').find('select[name="partner"]').val();
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
            name: 'cardName',
            data: 'cardName',
          },
          {
            name: 'taxDate',
            data: 'taxDate',
            render: format_date
          },
          {
            name: 'docTotal',
            data: 'docTotal',
            render: format_totalsDataTable
          },

        ],
        lengthMenu: [8, 15, 30],
        language: dataTablesPtBr
      });
    }

    function renderCheckBox(data, type, row) {
      return `<center>
          <input id="idPurchaseOrder-${data}" name="idPurchaseOrder[${data}]" data-code="${row.code}" type="checkbox" class="form-check" style="width: 20px;">
      </center>`
    }

    function editingMass(event, action) {

      $('input[name="massEditingAction"]').val(action);
      let codes = $('#editMassForm input[name*="idPurchaseOrder"]:checked').serializeArray().map(function(item) {
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
            url: '{{ route('purchase.order.editingMass') }}',
            type: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('body input[name="_token"]').val()
            },
            dataType: "json",
            data: $('#editMassForm').serialize(),
            success: function(response) {

              $('#editMassForm input[name*="idPurchaseOrder"]:checked').serializeArray().map(function(item) {
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
    function turnCheckedAllCheckboxes(event, id, elementId = null){
      if(checkedCheckbox === false){
        $(`#${id}`).find(`input[id*="${elementId}"]`).attr('checked', true);
        checkedCheckbox = true;
      }else if(checkedCheckbox === true){
        $(`#${id}`).find(`input[id*="${elementId}"]`).attr('checked', false);
        checkedCheckbox = false;
      }
    }

  </script>
@endsection
