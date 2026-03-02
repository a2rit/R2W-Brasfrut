@extends('layouts.main')
@section('title', 'Transfêrencia de estoque')
@section('content')
  @if ($head->is_locked == 1)
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif
  <form action="{{ route('inventory.transfer.store') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="row mb-1">
      <div class="col-10">
        <h3 class="header-page">Transferência - detalhes</h3>
      </div>
      <div class="col-2">
        <div class="dropdown float-end">
          <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
            <svg class="icon">
              <use xlink:href="{{ asset('icons_assets/custom.svg#printer') }}"></use>
            </svg> Imprimir
          </button>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="{{ route('inventory.transfer.print', $head->id) }}" target="_blank">PDF</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <hr><br>
    <div class="row">
      <div class="col-md-2">
        <label>Cod. SAP</label>
        <input type="text" value="{{ $head->codSAP }}" disabled class="form-control">
      </div>
      <div class="col-md-2">
        <label>Cod. WEB</label>
        <input type="text" value="{{ $head->code }}" disabled class="form-control">
      </div>
      <div class="col-md-4">
        <label>Usuário</label>
        <input type="text" value="{{ $head->name }}" disabled class="form-control">
      </div>
      <div class="col-md-2">
        <label>Data do documento</label>
        <input type="date" value="{{ $head->docDate }}" @if (isset($head->codSAP)) disabled @endif
          class="form-control">
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Data de lançamento:</label>
          <input type="date" name="taxDate" class="form-control" value="{{ $head->taxDate }}"
            @if (!empty($head->codSAP)) disabled @endif>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Depósito atual:</label>
          <select class='form-control selectpicker' name='fromWarehouse'
            @if (!empty($head->codSAP)) disabled @endif>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if ($values['code'] == $head->fromWarehouse) selected @endif>
                {{ $values['code'] }} - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <label>Depósito destino:</label>
          <select class='form-control selectpicker' name='toWarehouse' @if (!empty($head->codSAP)) disabled @endif>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if ($values['code'] == $head->toWarehouse) selected @endif>
                {{ $values['code'] }} - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      @if (!empty($request))
        <h5>Documentos associados</h5>
        <div class="col-md-3">
          <div class="form-group">
            <label for="status">Pedido</label>
            <div>
              <a type="text" class="btn btn-primary"
                href="{{ route('inventory.transferTaking.edit', $request->id) }}">{{ $request->code }}</a>
            </div>
          </div>
        </div>
      @endif
    </div>
    <div class="tabs-container mt-4">
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
          <div class="panel-body">
            <div class="table-responsive">
              <table id="requiredTable"
                class="table table-default table-striped table-bordered table-hover dataTables-example"
                style="width: 100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Descrições</th>
                    <th>U.M.</th>
                    <th>Quantidade</th>
                    <th>Projeto</th>
                    <th>Centro de custo</th>
                    <th>Centro de custo2</th>
                  </tr>
                </thead>
                <tbody>
                  @if (isset($body))
                    <?php $cod = 1;
                    $total = 0; ?>
                    @foreach ($body as $key => $value)
                      <tr>
                        <td>{{ $cod }}</td>
                        <td style="max-width: 16em;">
                          <div class="d-flex flex-row" style="max-width: 100%;">
                            <a class="text-warning" href="{{ route('inventory.items.edit', $value['itemCode']) }}"
                              target="_blank">
                              <svg class="icon icon-lg">
                                <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                              </svg>
                            </a>
                            <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                              title="{{ $value['itemCode'] }} - {{ $value['itemName'] }}">{{ $value['itemCode'] }} -
                              {{ $value['itemName'] }}</span>
                              <input type='hidden'value="{{ $value['itemCode'] }}"
                                name="items[{{ $value['id'] }}][itemCode]">
                              <input type='hidden' value="{{ $value['itemName'] }}"
                                name="items[{{ $value['id'] }}][itemName]">
                              <input type='hidden'value="{{ $head->id }}" name='items[{{ $value['id'] }}][id]'>
                          </div>
                        </td>
                        <td>
                          <p>{{ $value['itemUnd'] }}</p>
                        </td>
                        <td><input style='width: 100%;min-width: 100px;' type="text" class="form-control qtd locked"
                            @if (!empty($head->codSAP)) readonly @endif
                            name='items[{{ $value['id'] }}][quantity]' value="{{ $value['quantity'] }}">
                        </td>
                        <td>
                          <select style='width:150px' class='form-control selectpicker'
                            name='items[{{ $value['id'] }}][projectCode]'
                            @if (!empty($head->codSAP)) disabled @endif>
                            <option></option>
                            @foreach ($projeto as $keys => $values)
                              <option value='{{ $values['code'] }}' @if ($values['code'] == $value['projectCode']) selected @endif>
                                {{ $values['code'] . ' - ' . substr($values['value'], 0, 15) }}</option>
                            @endForeach
                          </select>
                        </td>
                        <td>
                          <select style='width:150px' class='form-control selectpicker' id="role-{{ $cod }}"
                            name='items[{{ $value['id'] }}][centroCusto]' onchange="validLine({{ $cod }})"
                            @if (!empty($head->codSAP)) disabled @endif>
                            <option></option>
                            @foreach ($centroCusto as $keys => $values)
                              <option value='{{ $values['value'] }}'
                                @if ($values['value'] == $value['costCenter']) selected @endif>{{ $values['value'] }} -
                                {{ $values['name'] }}</option>
                            @endForeach
                          </select>
                        </td>
                        <td>
                          <select style='width:150px' class='form-control selectpicker' id="role2-{{ $cod }}"
                            name='items[{{ $value['id'] }}][centroCusto2]'
                            @if (!empty($head->codSAP)) disabled @endif>
                            <option></option>
                            @foreach ($centroCusto2 as $keys => $values)
                              <option value='{{ $values['value'] }}'
                                @if ($values['value'] == $value['costCenter2']) selected @endif>{{ $values['value'] }} -
                                {{ $values['name'] }}</option>
                            @endForeach
                          </select>
                        </td>
                      </tr>
                      <?php $cod++; ?>
                    @endForeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-pane" id="tab-2">
          <div class="panel-body mt-3">
            <div class="col-md-12">
              <label>Observação</label>
              <textarea class="form-control" rows="5" @if (!empty($head->codSAP)) disabled @endif name="comments">{{ $head->comments }}</textarea>
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

                      @if (isset($upload))
                        <?php $upCont = 1; ?>
                        @foreach ($upload as $key => $item)
                          <tr id="lineUp-{{ $upCont }}">
                            <td style="width: 10%;">{{ $upCont }}</td>
                            <td style="width: 70%;">
                              {{ preg_split('/[;=]/', $item->diretory)[1] }}
                            </td>
                            <td style="width: 10%;" class="text-center">
                              <a href="{{ $item->diretory }}" target="_blank" class="btn btn-primary">
                                <svg class="icon">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                                </svg>
                              </a>
                            </td>
                            <td style="width: 10%;" class="text-center">
                              <a class="btn btn-danger" type="button"
                                onclick='removeUpload({{ $item->id }},{{ $item->idReference }})'>
                                <svg class="icon">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                </svg>
                              </a>
                            </td>
                          </tr>
                          <?php $upCont++; ?>
                        @endforeach
                      @endif
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
    </div>
    @if ($head->status != 4)
    <div class="col-md-12 mt-5">
        @if (empty($head->codSAP))
          <button class="btn btn-primary float-end" type="submit">Salvar</button>
        @endif
        @if (is_null($head->codSAP))
          <button class="btn btn-danger float-end me-1" value="{{ $head->id }}"
            onclick="cancelTransfer(event)">Cancelar</button>
        @endif
      </div>
    @endif
  </form>
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
      });
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

    <?php $aux = true; ?>

    function renderEditButton(code) {
      return `<center>
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");' @if (isset($head->codSAP)) style="display: none;" @endif>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    function renderFormatedQuantity(valor) {
      return new Intl.NumberFormat('pt-BR').format(valor);
    }

    function gerarTotal(code) {
      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = qtd * parseFloat(preco.replace('.', '').replace(',', '.'));
      if (!isNaN(total)) {
        document.getElementById('totalLinha-' + code).setAttribute('value', total.format(2, ",", "."));
        sumAllValues();
      }
    }

    function gerarPrecoUnitario(code) {
      var totalLinha = document.getElementById('totalLinha-' + code).value;
      var qtd = document.getElementById('qtd-' + code).value;
      var prince = parseFloat(totalLinha.replace('.', '').replace(',', '.')) / parseFloat(qtd);

      if (!isNaN(prince)) {
        document.getElementById('price-' + code).value = prince.format(2, ",", ".");
        sumAllValues();
      }
    }
    var index = 1;

    function loadTable(code) {

      if (valideCode(code, true)) {
        var table = $('#requiredTable');

        if (index == 1) {
          $('#requiredTable tbody > tr').remove();
        }

        var tr = $("<tr id='rowTable-" + index + "'>");
        tr.append($('<td>' + index + '</td>'));

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
            tr.append($("<td><input type='number' value='0' onblur='gerarTotal(" + index + ")' id='qtd-" + index +
              "'  class='form-control' name='items[" + index + "][qtd]'></td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' id='project-" + index +
              "' name='items[" + index +
              "][projectCode]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
              "</td>"));
            // tr.append($("<td>"
            //             +"<select class='form-control' id='role-"+index+"' name='items["+ index + "][role]' required > <option value=''>Selecione</option> @foreach ($role as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['value'] }}</option> @endForeach</select>"
            //             +"</td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' id='role-" + index + "' name='items[" +
              index +
              "][centroCusto]' required > <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td>" +
              "<select class='form-control selectpicker' id='role2-" + index + "' name='items[" +
              index +
              "][centroCusto2]' > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
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

    function cancelTransfer(event) {
      event.preventDefault()
      swal({
          title: "Tem certeza que deseja cancelar?",
          text: "Esta operação não pode ser desfeita!",
          icon: "warning",
          //buttons: true,
          buttons: ["Não", "Sim"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            waitingDialog.show('Cancelando...')
            window.location.href = "{{ route('inventory.transfer.cancel', $head->id) }}";
          }
        });
    }

    function removeUpload(id, idRef) {
      swal({
          title: "Tem certeza que deseja excluir o arquivo?",
          text: "Esta operação não pode ser desfeita!",
          icon: "warning",
          //buttons: true,
          buttons: ["Não", "Sim"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            waitingDialog.show('Excluindo...')
            window.location.href = "{{ route('inventory.transfer.remove.upload') }}/" + id + "/" + idRef;
          }
        });
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

    $('#roleGlobal2').on('change', function(event) {
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

    });
  </script>
@endsection
