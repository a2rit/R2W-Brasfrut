@extends('layouts.main')
@section('title', 'Emprestimo')
@section('content')

  <div class="row mb-3">
    <h3 class="header-page">Empréstimo de ferramentas - cadastro</h3>
  </div>
  <hr>

  <form action="{{ route('inventory.stockloan.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="row">
      <div class="col-md-3">
        <label>Data atual</label>
        <input type="date" value="{{ DATE('Y-m-d') }}" readonly class="form-control">
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Data de lançamento:</label>
          <input class="form-control" type="date" @if (isset($dt)) value="{{ $dt }}" @endif
            required name="data">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Depósito atual:</label>
          <select class='form-control locked' name='fromWarehouse' readonly>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if ($values['code'] == 06) selected @endif>
                {{ $values['code'] }} - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Depósito destino:</label>
          <select class='form-control locked' name='toWarehouse'readonly>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if ($values['code'] == 26) selected @endif>
                {{ $values['code'] }} - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <div class="form-group ">
          <label>Solicitante</label>
          <select id="requester" class="form-control selectpicker" data-live-search="true" name="requester" required>
            <option></option>
            @foreach ($requesters as $requester)
              <option value='{{ $requester->id }}'>{{ $requester->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <div class="col-12 mt-4">
      <div class="tabs-container">
        <ul class="nav nav-tabs" id="myTabs">
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
            <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
            <a class="nav-link" data-toggle="tab" href="#tab-2">Anexos</a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="tab-1">
            <div class="panel-body mt-4">
              <a href='#' class='text-dark' onclick="loadingItems()" data-coreui-toggle="modal"
                data-coreui-target="#itensModal">
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <div class="table-responsive">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%">
                  <thead>
                    <tr>
                      <th style="width: 5%;">#</th>
                      <th>Descrições</th>
                      <th style="width: 10%">U.M.</th>
                      <th style="width: 15%">Quantidade</th>
                      <th style="width: 10%">Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-2">
            <div class="panel-body mt-4">
              <div class="col-md-12">
                <label>Observação</label>
                <textarea class="form-control" rows="5" name="comments" maxlength="200"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 mt-4">
      <button class="btn btn-primary float-end" type="button" id="btn-salvar" onclick="validate()">Salvar</button>
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
            <table id="table" class="table table-default table-bordered table-hover" style="width: 100%;">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  <th style="width: 55%">U.M.</th>
                  <th style="width: 15%">Em estoque</th>
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

@endsection


@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    $(document).ready(function() {
      setMaskMoney();
    });

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
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
      var fromWhs = $('select[name="fromWarehouse"]').val();
      var toWhs = $('select[name="toWarehouse"]').val();

      if (fromWhs == '') {
        swal("Os seguintes erros foram encontrados: ", "Por favor escolha um depósito", "error");
      } else {
        let table = $("#table").DataTable({
          processing: true,
          serverSide: true,
          responsive: true,
          destroy: true,
          ajax: {
            url: "{{ route('inventory.request.list.whs') }}",
            data: function(d) {
              d.isLoan = '1';
              d.whsCode = fromWhs;
            }
          },
          // render: renderWHS, orderable: false}
          columns: [{
              name: 'ItemCode',
              data: 'ItemCode'
            },
            {
              name: 'ItemName',
              data: 'ItemName'
            },
            {
              name: 'BuyUnitMsr',
              data: 'BuyUnitMsr'
            },
            {
              name: 'OnHand',
              data: 'OnHand',
              render: renderFormatedQuantity,
              orderable: false
            },
            {
              name: 'edit',
              data: 'ItemCode',
              render: renderEditButton,
              orderable: false
            }
          ],
          lengthMenu: [5, 10, 20],
          language: dataTablesPtBr
        });
      }
    }

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

    var index = 1;

    function loadTable(code) {
      var table = $('#requiredTable');

      if (index == 1) {
        $('#requiredTable tbody > tr').remove();
      }

      var tr = $("<tr id='rowTable-" + index + "'>");
      tr.append($('<td>' + index + '</td>'));
      tr.find('td').first().append('<input type="hidden" value="' + code + '" data-name="line" name="requiredProducts[' +
        index + '][codSAP]">');

      $.get('/getProductsSAP/' + code, function(items) {
        for (var i = 0; i < items.length; i++) {
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
                                    <input type='hidden' value='${items[i].ItemCode}' name='requiredProducts[${index}][codSAP]' >
                                </td>`));
          tr.append($("<td>" + (items[i].BuyUnitMsr || " ") + "<input type='hidden' value='" + items[i].BuyUnitMsr +
            "' name='requiredProducts[" + index + "][itemUnd]' > </td>"));
          tr.append($("<td><input type='text' id='qtd-" + index +
            "'  class='form-control qtd' onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)' name='requiredProducts[" +
            index + "][qtd]'></td>"));
          tr.append($(`<td class='text-center'>
                                    <a class="text-danger" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
                                        <svg class="icon icon-xl">
                                        <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                        </svg>
                                    </a>
                                </td>`));
        }
        table.find('tbody').append(tr);
        setMaskMoney();
        $('.text-truncate').tooltip();
        index++;
      });
    }

    function validLine(code) {
      var select = document.getElementById('role-' + code).value;

      if (select == '1.0') {
        $('#role2-' + code).removeClass('disabled');
        $('#role2-' + code).attr('required', true);
      } else {
        $('#role2-' + code).addClass('disabled');
        $('#role2-' + code).attr('required', false);
      }
      $('#role2-' + code).selectpicker('destroy')
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render')
    }


    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {
        tr.remove();
      });
      $("#composicao-" + elemento).remove();
    }

    var used = new Array();

    function removeInArray(code) {
      var aux = used.indexOf(code);
      if (aux != -1) {
        used.splice(aux, 1);
      }
    }

    function sumAllValues() {
      var total = document.getElementById('totalNota').value;
      if (isNaN(total)) {
        total = 0;
      }
      var desconto = 0;
      var x;
      var i;

      for (i = 1; i < index; i++) {
        if (document.getElementById('totalLinha-' + i)) {
          x = document.getElementById('totalLinha-' + i).value;
          total = parseFloat(total) + parseFloat(x.replace('.', '').replace(',', '.'));
        }

      }
      document.getElementById('totalNota').value = total.format(2, ",", ".");

    }

    function validate() {

      var erros = new Array();


      if (index == 1) {
        erros.push('Adicione ao menos 1 item!');
      }

      if (erros.length > 0) {
        swal("Atenção: ", erros.toString(), "warning")
      } else {
        $('#btn-salvar').attr('type', 'submit');
      }
    }

    function setProjecFull() {
      var val = document.getElementById('projectGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('project-' + i)) {
          document.getElementById('project-' + i).value = val;
        }
      }
    }

    function setWHSFull() {
      var val = document.getElementById('whsGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('whs-' + i)) {
          document.getElementById('whs-' + i).value = val;
          setContaLine(i);
        }
      }
    }

    function setRoleFull() {
      var val = document.getElementById('roleGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('role-' + i)) {
          document.getElementById('role-' + i).value = val;
        }
      }
    }

    function setRoleFull2() {
      var val = document.getElementById('roleGlobal2').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('role2-' + i)) {
          document.getElementById('role2-' + i).value = val;
        }
      }
    }

    function setContaFull() {
      var val = document.getElementById('contaGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('conta-' + i)) {
          document.getElementById('conta-' + i).value = val;
        }
      }
    }
  </script>
@endsection
