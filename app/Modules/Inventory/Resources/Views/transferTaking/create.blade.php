@extends('layouts.main')
@section('title', 'Pedido de transferência ')
@section('content')

  <div class="row mb-1">
    <div class="col-12">
      <h3 class="header-page">Pedido de transferência - cadastro</h3>
    </div>
  </div>
  <hr>

  <form action="{{ route('inventory.transferTaking.save') }}" method="post" id="needs-validation" class="container-fluid"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Salvando...')">
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
          <label>Depósito origem:</label>
          <select id='fromWhs' class='form-control selectpicker' name='warehouse' required>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if (isset($wh) && $values['code'] == $wh) selected @endif>
                {{ $values['code'] }}
                - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Depósito destino:</label>
          <select id='toWhs' class='form-control selectpicker' name='toWarehouse' required>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if (isset($whs) && $values['code'] == $whs) selected @endif>
                {{ $values['code'] }}
                - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
    </div>
    <div class="tabs-container mt-5">
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
                    <th>#</th>
                    <th>Descrições</th>
                    <th>U.M.</th>
                    <th>Qnt. Solicitada</th>
                    <th>Projeto</th>
                    <th>Centro de Custo</th>
                    <th>Centro de Custo2</th>
                    <th>Opções</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
            <div class="row mt-5">
              <div class="col-md-2">
                <label>Projeto Principal</label>
                <select class='form-control selectpicker' name='project' id='projectGlobal' onchange="setProjecFull()">
                  <option value=''>Selecione</option>
                  @foreach ($projeto as $keys => $values)
                    <option value="{{ $values['code'] }}">{{ $values['code'] }}
                      - {{ $values['value'] }}</option>
                  @endForeach
                </select>
              </div>
              <div class="col-md-3">
                <label>Centro de Custo Principal</label>
                <select class='form-control selectpicker' name='role' id='roleGlobal' onchange="setRoleFull()">
                  <option value=''>Selecione</option>
                  @foreach ($centroCusto as $keys => $values)
                    <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                      - {{ $values['name'] }}</option>
                  @endForeach
                </select>
              </div>
              <div class="col-md-3">
                <label>Centro de Custo 2 Principal</label>
                <select class='form-control selectpicker' name='role2' id='roleGlobal2' onchange="setRoleFull2()">
                  <option value=''>Selecione</option>
                  @foreach ($centroCusto2 as $keys => $values)
                    <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                      - {{ $values['name'] }}</option>
                  @endForeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="tab-2">
          <div class="panel-body">
            <div class="col-md-12 mt-3 form-group">
              <label>Observação</label>
              <textarea class="form-control" rows="5" name="comments"></textarea>
            </div>
            <div class="row mt-4">
              <div class="col-md-6">
                <div class="table-responsive">
                  <table class="table table-default table-striped table-bordered table-hover dataTables-example"
                    style="width: 75%;">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Anexo</th>
                        <th>Visualizar</th>
                        <th>Remover</th>
                      </tr>
                    </thead>
                    <tbody>
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
      <div class="col-12 mt-5">
        <button class="btn btn-primary float-end" type="button" id="btn-save" onclick="valide()">Salvar</button>
      </div>
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
            <table id="table" class="table table-default table-striped table-bordered table-hover"
              style="width: 100%;">
              <thead>
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  <th style="width: 55%">U.M.</th>
                  <th style="width: 15%">Qtd. Estoque</th>
                  <th style="width: 10%">Opções</th>
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

    $('.dataTables-example').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });


    function openModal() {
      var fromWhs = $('#fromWhs').val();
      var toWhs = $('#toWhs').val();
      if (toWhs == '' || fromWhs == '') {
        alert('Por favor escolha um depósito');
        return;
      } else {
        $('#table').DataTable().clear();
        $("#itensModal").modal('show');
        let table = $("#table").DataTable({
          processing: true,
          serverSide: true,
          responsive: true,
          destroy: true,
          ajax: {
            url: "{{ route('inventory.request.list.whs') }}",
            data: function(d) {
              d.whsCode = fromWhs;
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
              name: 'edit',
              data: 'ItemCode',
              render: renderEditButton,
              orderable: false
            }
            // {name: 'ItemCode', data: 'ItemCode'},
            // {name: 'ItemName', data: 'ItemName'},
            // {name: 'InvntryUom', data: 'InvntryUom'},
            // // {name: 'OnHand', data: 'OnHand'},
            // {name: 'edit', data: 'ItemCode', render: renderEditButton, orderable: false}
          ],
          lengthMenu: [5, 15, 30],
          language: dataTablesPtBr
        });
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


    var index =
      @if (isset($body))
        {{ $cont + 1 }}
      @else
        1
      @endif ;


    function valide() {
      var erros = new Array();
      var itens = $("#requiredTable >tbody >tr").length;
      if (index == '1') {
        erros.push('Adicione um item! \n');
      }

      var fromWHS = $('#fromWhs').val();
      var toWHS = $('#toWhs').val();

      if (fromWHS == toWHS) {
        erros.push('Selecione depositos diferentes!');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        $('#btn-save').attr('type', 'submit');
      }

    }



    function loadTable(code) {
      var table = $('#requiredTable');
      var depositoAtual = $("#fromWhs :selected").val();

      if (index == 1) {
        $('#requiredTable tbody > tr').remove();
      }

      var tr = $("<tr id='rowTable-" + index + "'>");
      tr.append($('<td>' + index + '</td>'));

      $.get('/getProductsFromWhs/' + code + '|' + depositoAtual, function(whs) {
        /* 
        if(whs[0].OnHand == 0){
            alert("Não existe estoque do Item selecionado no Deposito de Origem ");
            return false;
        }
        else{
        */
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
                            <input type='hidden' value='${items[i].ItemName}' name='items[${index}][itemName]' >
                            <input type='hidden' value='${items[i].ItemCode}' name='items[${index}][itemCode]' >
                        </td>`));
            tr.append($("<td><p>" + items[i]['BuyUnitMsr'] + "</p> </td>"));
            tr.append($(
              "<td><input style='width: 100%;min-width: 100px;' required type='text' class='form-control qtd' id='qtd-" +
              index +
              "' onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)' name='items[" +
              index + "][qtd]' required></td>"));

            tr.append($("<td>" +
              "<select class='form-control selectpicker' data-container='body' id='project-" + index +
              "'    style='width:200px' name='items[" + index +
              "][projectCode]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['code'] }} - {{ $values['value'] }}</option> @endForeach</select>" +
              "</td>"));
            // tr.append($("<td>"
            //     + "<select class='form-control' id='role-" + index + "' name='items[" + index + "][role]' required > <option value=''>Selecione</option> @foreach ($role as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['code'] }} - {{ $values['value'] }}</option> @endForeach</select>"
            //     + "</td>")); 
            tr.append($("<td >" +
              "<select class='form-control selectpicker' data-container='body' id='role-" + index +
              "' name='items[" + index + "][centroCusto]' required onchange='validLine(" + index +
              ");'> <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td >" +
              "<select class='form-control selectpicker' data-container='body' id='role2-" + index +
              "' name='items[" + index +
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
          setMaskMoney();
          $('.selectpicker').selectpicker(selectpickerConfig);
          $('.text-truncate').tooltip();
          index++;
        });
        //}

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
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render');
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

    function setProjecFull() {
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
    }

    function setRoleFull() {
      var val = $('#roleGlobal').val();
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
        $(`select[id*='role-'], select[id*='role2-'], #roleGlobal2`).selectpicker(selectpickerConfig).selectpicker(
          'render');

      }, 600);
    }

    function setRoleFull2() {
      var val = $('#roleGlobal2').val();

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
