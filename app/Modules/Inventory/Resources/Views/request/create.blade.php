@extends('layouts.main')
@section('title', 'Requisições')
@section('content')

  <div class="col-12">
    <h3 class="header-page">Requisição interna - cadastro</h3>
  </div>
  <hr>
  <form action="{{ route('inventory.request.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="row">
      <div class="ibox-content">
        <div class="row">
          <div class="col-md-3">
            <label>Usuário</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled class="form-control">
          </div>
          <div class="col-md-3">
            <label>Data atual</label>
            <div class="input-group">
              <input required data-mask="99/99/9999" type="text" class="form-control datepicker" disabled
                onchange="changePostingDate(this)" value="{{ date('d/m/Y') }}">
              <div class="input-group-addon">
                <span class="glyphicon glyphicon-th"></span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Data Necessaria:</label>
              <div class="input-group">
                <input required type="date" name="data" class="form-control"
                  value="{{ date('Y-m-d') }}">
                <div class="input-group-addon">
                  <span class="glyphicon glyphicon-th"></span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Depósito:</label>
              <select name="whs" id="whs" class="form-control selectpicker" required>
                <option value=''>Selecione</option>
                <option value='1'>Uso e Consumo</option>
                <option value='2'>Manutenção</option>
                <option value='3'>Eventos</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <div class="tabs-container mt-4">
      <div class="tab-content">
        <div class="tab-pane active" id="tab-1">
          <div class="panel-body">
            <div class="table-responsive">
              <a href='#' class='text-dark' onclick="openModal()">
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <table id="requiredTable"
                class="table table-default table-striped table-bordered table-hover dataTables-example">
                <thead>
                  <tr>
                    <th style="width: 2%">#</th>
                    <th style="width: 25%">Descrições</th>
                    <th style="width: 15%">Quantidade</th>
                    <th>Projeto</th>
                    <th style="width: 20%">Centro de custo</th>
                    <th style="width: 20%">Centro de custo2</th>
                    <th style="width: 10%">Opções</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="row">
              <div class="col-md-8">
                <div class="row">

                  <div class="col-md-4">
                    <label>Projeto Principal</label>
                    <select class='form-control selectpicker' name='project' id='projectGlobal'
                      onchange="setProjecFull()">
                      <option value=''>Selecione</option>
                      @foreach ($projeto as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}'
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label>Centro de Custo Principal</label>
                    <select class='form-control selectpicker' name='role' id='roleGlobal'
                      onchange="setRoleFull()">
                      <option value=''>Selecione</option>
                      @foreach ($centroCusto as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label>Centro de Custo Principal 2</label>
                    <select class='form-control selectpicker' name='role2' id='roleGlobal2'
                      onchange="setRoleFull2()">
                      <option value=''>Selecione</option>
                      @foreach ($centroCusto2 as $keys => $values)
                        <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                          - {{ $values['name'] }}</option>
                      @endForeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <label>Observação</label>
                <textarea class="form-control" rows="3" name="obsSolicitante" maxlength="254"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-primary mt-5 float-end" type="submit">Salvar</button>
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
            <table id="table" class="table table-bordered table-hover" style="width: 100%;">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  <th style="width: 55%">U.M.</th>
                  <th style="width: 15%">Em estoque</th>
                  <th style="width: 15%">Quantidade</th>
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
@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">

    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    $(document).ready(function() {
      setMaskMoney();
    });

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

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    let whsCode = ['', '01', '06', '17'];

    function openModal() {
      var whs = $('#whs').val();
      if (whs == '') {
        alert('Por favor escolha um depósito');
      } else {
        
        $("#itensModal").modal('show');
        let table = $("#table").DataTable({
          processing: true,
          serverSide: true,
          responsive: true,
          destroy: true,
          ajax: {
            url: "{{ route('inventory.request.list.whs') }}",
            data: function(d) {
              d.whsCode = whsCode[whs];
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
          lengthMenu: [5, 30, 50],
          language: dataTablesPtBr
        });
      }
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

    <?php $aux = true; ?>

   
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
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render');

    }
    var index = 1;

    function loadTable(code, quantity = 0) {

      if (valideCode(code, true)) {
        var table = $('#requiredTable');

        if (quantity === 0) {
          quantity = (parseFloat($(`#itensModal input[data-itemCode="${code}"]`).val().replace(/[.]/gi, '').replace(/[,]/gi,
            '.')) || 0);
        }

        if (index == 1) {
          $('#requiredTable tbody > tr').remove();
        }

        var tr = $("<tr id='rowTable-" + index + "'>");
        tr.append($('<td style="width: 5%">' + index + '</td>'));
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
            tr.append($(
              "<td><input required type='text' class='form-control min-100 qtd' name='requiredProducts[" +
              index +
              "][qtd]' value='"+quantity+"' min='1' onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'></td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' data-container='body' id='project-" + index +
              "' name='requiredProducts[" + index +
              "][projeto]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' data-container='body' id='role-" + index +
              "' name='requiredProducts[" + index + "][centroCusto]' required onchange='validLine(" + index +
              ");'> <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' data-container='body' id='role2-" + index +
              "' name='requiredProducts[" + index +
              "][centroCusto2]' > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
              "</td>"));
              tr.append($(`<td class='text-center'>
                          <a class="text-danger" onclick='removeInArray("${items[i].ItemCode}");removeLinha(this);' type="button">
                            <svg class="icon icon-xl">
                              <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                            </svg>
                          </a>
                        </td>`));
          }
          table.find('tbody').append(tr);
          $('.selectpicker').selectpicker(selectpickerConfig);
          $('.text-truncate').tooltip();
          index++;
        });
      } else {
        alert('Opss!!! o item já foi selecionado anteriormente');
      }
    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {
        tr.remove();
      });
    }

    var used = new Array();

    function removeInArray(code) {
      var aux = used.indexOf(code);
      if (aux != -1) {
        used.splice(aux, 1);
      }
    }

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

    function setProjecFull() {
      var val = document.getElementById('projectGlobal').value;

      setTimeout(() => {
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('project-' + i)) {
            document.getElementById('project-' + i).value = val;
          }
        }

        $(`select[id*='project-']`).selectpicker('destroy');
        $(`select[id*='project-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
    }

    function setRoleFull() {
      var val = document.getElementById('roleGlobal').value;

      setTimeout(() => {
        if (val == '1.0') {
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

      }, 600);
    }

    function setRoleFull2() {
      var val = document.getElementById('roleGlobal2').value;
      setTimeout(() => {
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

      }, 600);
    }

    function changePostingDate(item) {
      $('[name=doc_date]').val(item.value);
    }

    $(document).on("keypress", "#quantityInputLoadingItems", function(event) {
      if (event.keyCode === 13) {
        loadTable($(this).attr('data-itemCode'), $(this).val());
      }
    })
  </script>
@endsection
