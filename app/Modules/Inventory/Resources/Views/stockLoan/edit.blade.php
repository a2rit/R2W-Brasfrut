@extends('layouts.main')
@section('title', 'Empréstimo de estoque')
@section('content')

  @if ($head->is_locked == 1)
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif

  <div class="row">
    <div class="col-10">
      <h3 class="header-page">Empréstimo de ferramentas - detalhes</h3>
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
            <a class="dropdown-item" href="{{ route('inventory.stockloan.print', [$head->id, 'pdf']) }}"
              target="_blank">PDF</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <hr>
  <form action="{{ route('inventory.stockloan.store') }}" method="post" id="needs-validation"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <input type="hidden" name="id" value="{{ $head->id }}">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-2">
          <label>Cod. SAP</label>
          <input type="text" value="{{ $head->codSAP }}" disabled class="form-control">
          <input type="hidden" value="{{ $head->id }}" name="stockLoan_id">
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
          <input type="date" value="{{ $head->taxDate }}" disabled class="form-control">
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Data de lançamento:</label>
            <input type="date" class="form-control" value="{{ $head->docDate }}" disabled>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group ">
            <label>Solicitante</label>
            <select id="requester" class="form-control selectpicker" data-live-search="true" name="returner" disabled>
              <option></option>
              @foreach ($requesters as $requester)
                <option @if (isset($head->requester) && $head->requester == $requester->id) selected @endif value='{{ $requester->id }}'>
                  {{ $requester->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-3">
          <div class="form-group">
            <label>Depósito atual:</label>
            <select class='form-control' name='fromWarehouse' style="pointer-events: none;" readonly>
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
            <select class='form-control' name='toWarehouse' style="pointer-events: none;" readonly>
              <option value=''>Selecione</option>
              @foreach ($warehouses as $keys => $values)
                <option value='{{ $values['code'] }}' @if ($values['code'] == 26) selected @endif>
                  {{ $values['code'] }} - {{ $values['value'] }}</option>
              @endForeach
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="">Status</label>
            <input type="text" class="form-control" value="{{ $stockLoan::TEXT_STATUS[$head->status] }}" readonly>
          </div>
        </div>
      </div>
      <hr>
      @if ($head->status == $stockLoan::STATUS_OPEN || $head->status == $stockLoan::STATUS_PENDING)
        <div class="row mt-4">
          <h4>Informações adicionais da transação</h4>
          <div class="col-md-3">
            <div class="form-group">
              <label>Entregue por:</label>
              <select id="deliveryUser" class="form-control selectpicker" data-live-search="true" name="deliveryUser">
                <option></option>
                @foreach ($requesters as $requester)
                  <option value='{{ $requester->id }}'>{{ $requester->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Recebido por:</label>
              <select id="receiverUser" class="form-control selectpicker" data-live-search="true" name="receiverUser">
                <option></option>
                @foreach ($requesters as $requester)
                  <option value='{{ $requester->id }}'>{{ $requester->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Tipo da transação:</label>
              <select id="transType" class="form-control selectpicker" name="transType" onchange="changeTransType()">
                <option value='1'>Recebimento</option>
                <option value='2'>Devolução</option>
              </select>
            </div>
          </div>
        </div>
      @endif
      <div class="col-12 mt-4">
        <div class="tabs-container">
          <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
              <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
              <a class="nav-link" data-toggle="tab" href="#tab-3">Histórico</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
              <a class="nav-link" data-toggle="tab" href="#tab-2">Anexos</a>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-1">
              <div class="panel-body mt-3">
                <div class="table-responsive">
                  <table id="requiredTable"
                    class="table table-default table-striped table-bordered table-hover dataTables-example"
                    style="width: 100%">
                    <thead>
                      <tr>
                        <th style="width: 5%;">#</th>
                        <th>Descrições</th>
                        <th style="width: 5%;">U.M.</th>
                        <th style="width: 20%;">Quantidade solicitada</th>
                        <th style="width: 20%;">Quantidade Recebida</th>
                        <th style="width: 20%;">Quantidade Devolvida</th>
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
                                <a class="text-warning" href="{{ route('inventory.items.edit', $value->itemCode) }}"
                                  target="_blank">
                                  <svg class="icon icon-lg">
                                    <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                  </svg>
                                </a>
                                <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                  title="{{ $value->itemCode }} - {{ $value->itemName }}">{{ $value->itemCode }} -
                                  {{ $value->itemName }}</span>
                              </div>
                              <input type='hidden'value="{{ $value->itemCode }}"
                                name="items[{{ $value->id }}][itemCode]">
                              <input type='hidden' value="{{ $value->itemName }}"
                                name="items[{{ $value->id }}][itemName]">
                              <input type='hidden' value="{{ $head->id }}"
                                name='items[{{ $value->id }}][id]'></td>
                            </td>
                            <td>{{ $value->itemUnd }}<input type='hidden' value="{{ $value->itemUnd }}"
                              name="items[{{ $value->id }}][itemUnd]"></td>
                            <td><input type="text" class="form-control qtd locked" value="{{ $value->quantity }}"
                                name='items[{{ $value->id }}][quantity]' onclick='destroyMask(event)'
                                onblur='setMaskMoney();focusBlur(event)' readonly></td>
                            <td><input type="text" class="form-control qtd" value="{{ $value->quantityPending }}"
                                name='items[{{ $value->id }}][quantityPending]'
                                data-initialvalue="{{ $value->quantityPending }}" onclick='destroyMask(event)'
                                onblur='setMaskMoney();focusBlur(event);checkValue({{ $value->id }})'
                                @if ($value->quantity == $value->quantityPending) readonly @endif></td>
                            <td><input type="text" class="form-control qtd"
                                value="{{ $value->quantityDevolved }}"
                                name='items[{{ $value->id }}][quantityDevolved]'
                                data-initialvalue="{{ $value->quantityDevolved }}" onclick='destroyMask(event)'
                                onblur='setMaskMoney();focusBlur(event);checkValue({{ $value->id }})'
                                @if ($value->quantityPending == $value->quantityDevolved) readonly @endif></td>
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
              <div class="panel-body">
                <div class="col-md-12 mt-3 form-group">
                  <label>Observação</label>
                  <textarea class="form-control" rows="5" @if ($head->is_locked == 0) disabled @endif name="comments">{{ $head->comments }}</textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-3">
              <div class="panel-body mt-3">
                <div class="table-responsive">
                  <table id="requiredTable"
                    class="table table-default table-striped table-bordered table-hover dataTables-example"
                    style="width: 100%">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Cod. SAP</th>
                        <th>Descrições</th>
                        <th>Qtd</th>
                        <th>Entregue por</th>
                        <th>Recebido por</th>
                        <th>Data</th>
                        <th>Ação</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $cont = 1; ?>
                      @foreach ($historic as $key => $register)
                        <tr>
                          <td style="width: 5%">{{ $cont }}</td>
                          <td style="width: 10%">{{ $register->itemCode }}</td>
                          <td>{{ $register->ItemName }}</td>
                          <td style="width: 10%">{{ $register->quantityServed }}</td>
                          <td style="width: 15%">{{ getNameRequester($register->deliveryUserId) }}</td>
                          <td style="width: 15%">{{ getNameRequester($register->receiverUserId) }}</td>
                          <td style="width: 10%">{{ date('d/m/Y H:i:s', strtotime($register->created_at)) }}</td>
                          <td style="width: 10%">{{ $register->isDevolution ? 'DEVOLUÇÃO' : 'RECEBIMENTO' }}</td>
                        </tr>
                        <?php $cont++; ?>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @if ($head->status != $stockLoan::STATUS_CANCEL && $head->status != $stockLoan::STATUS_CLOSED)
        <div class="col-12 mt-4">
          <button class="btn btn-primary float-end" type="button" id="btn-salvar"
            onclick="validate(event)">Salvar</button>
        </div>
      @endif
    </div>
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


    <?php $aux = true; ?>

    function renderEditButton(code) {
      if (valideCode(code, false)) {
        return "<center><img src='{{ asset('images/add.png') }}' id='addItem-" + code + "' onclick='loadTable(\"" +
          code + "\");chengeIcon(\"" + code + "\")'/></center>";
      } else {
        return "<center><img src='{{ asset('images/addCinza.png') }}'/></center>";
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
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render')
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
        tr.find('td').first().append('<input type="hidden" value="' + code +
          '" data-name="line" name="requiredProducts[' + index + '][codSAP]">');

        $.get('/getProductsSAP/' + code, function(items) {
          for (var i = 0; i < items.length; i++) {
            tr.append($("<td style='width: 10%'>" + items[i].ItemCode + "</td>"));
            tr.append($("<td>" + items[i].ItemName + "</td>"));
            tr.append($("<td><input type='number' value='0' onblur='gerarTotal(" + index + ")' id='qtd-" + index +
              "'  class='form-control' name='requiredProducts[" + index + "][qtd]'></td>"));
            tr.append($("<td>" +
              "<select class='form-control' id='project-" + index +
              "' data-live-search='true' name='requiredProducts[" + index +
              "][projeto]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td>" +
              "<select class='form-control' id='role-" + index + "' name='requiredProducts[" + index +
              "][role]' required > <option value=''>Selecione</option> @foreach ($role as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
              "</td>"));
            tr.append($("<td><img src='{{ asset('images/remover.png') }}' onclick='removeInArray(\"" + items[i]
              .ItemCode +
              "\");removeLinha(this);' style='font-size: 3%;color: #ec0707;padding-left: 16px;'/></td>"));


          }
          table.find('tbody').append(tr);
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
      $("#composicao-" + elemento).remove();
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

    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("Processando...");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'stockLoans')
      formData.append('id', $('input[name="id"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('inventory.stockloan.updateUploads') }}",
          data: formData,
          dataType: 'json',
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('body input[name="_token"]').val()
          }
        })
        .always(function(response) {
          waitingDialog.hide();
          swal({
              title: "Processando",
              text: "Documento sendo salvo. Deseja atualizar a página?",
              icon: "info",
              //buttons: true,
              buttons: ["Não", "Sim"],
              dangerMode: true,
            })
            .then((refresh) => {
              if (refresh) {
                document.location.reload(true);
              }
            });
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
            window.location.href = "{{ route('inventory.stockLoan.remove.upload') }}/" + id + "/" + idRef;
          }
        });
    }

    function checkValue(id) {
      let request = parseFloat($('[name="items[' + id + '][quantity]"]').val().replace('.', '').replace(',', '.'));
      let pending = parseFloat($('[name="items[' + id + '][quantityPending]"]').val().replace('.', '').replace(',', '.'));
      let served = parseFloat($('[name="items[' + id + '][quantityDevolved]"]').val().replace('.', '').replace(',', '.'));

      if (pending > request) {
        $('[name="items[' + id + '][quantityPending]"]').val(request)
        swal("Verifique", "A quantidade inserida não pode ultrapassar a quantidade solicitada!", "error");
      }
      if (served > request) {
        $('[name="items[' + id + '][quantityDevolved]"]').val(request)
        swal("Verifique", "A quantidade inserida não pode ultrapassar a quantidade solicitada!", "error");
      }
    }

    function validate(event) {
      var erros = new Array();

      if ($('#deliveryUser').val().length == 0) {
        erros.push('Informe o usuário que realizou a entrega dos itens! \n');
      }
      if ($('#receiverUser').val().length == 0) {
        erros.push('Informe o usuário que recebeu dos itens! \n');
      }

      // if ($('#transType').val() == "1") {
      //   $(`input[name*="quantityDevolved"]`).css({
      //     'border': '1px solid #CCCCCC'
      //   });
      //   $.each($(`input[name*="quantityPending"]`).not(""), function(index, value) {
      //     if (isNaN(parseFloat($(value).val())) || parseFloat($(value).val()) <= parseFloat($(value).attr(
      //         'data-initialvalue'))) {
      //       $(value).css({
      //         'border': '1px solid #FF0000'
      //       });
      //       erros.push(
      //         'Os itens marcados com a cor vermelha não podem conter a quantidade menor ou igual a quantidade inicial, altere o valor\n'
      //         );
      //     } else {
      //       $(value).css({
      //         'border': '1px solid #CCCCCC'
      //       });
      //     }
      //   });
      // } else if ($('#transType').val() == "2") {
      //   $(`input[name*="quantityPending"]`).css({
      //     'border': '1px solid #CCCCCC'
      //   });
      //   $.each($(`input[name*="quantityDevolved"]`), function(index, value) {
      //     if (isNaN(parseFloat($(value).val())) || parseFloat($(value).val()) <= parseFloat($(value).attr(
      //         'data-initialvalue'))) {
      //       $(value).css({
      //         'border': '1px solid #FF0000'
      //       });
      //       erros.push(
      //         'Os itens marcados com a cor vermelha não podem conter a quantidade menor ou igual a quantidade inicial, altere o valor\n'
      //         );
      //     } else {
      //       $(value).css({
      //         'border': '1px solid #CCCCCC'
      //       });
      //     }
      //   });
      // }

      if (erros.length > 0) {
        event.preventDefault()
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        var table = $('#requiredTable');

        table.find('select').each(function(i, item) {
          $(item).removeAttr('disabled');
        });

        $(event.target).attr('type', 'submit');
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

    function setRoleFull() {
      var val = document.getElementById('roleGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('role-' + i)) {
          document.getElementById('role-' + i).value = val;
        }
      }
    }

    function changeTransType() {
      if ($('#transType').val() == "1") {
        $.each($(`input[name*="quantityDevolved"]`), function(index, value) {
          $(`input[name*="quantityDevolved"]`).val($(`input[name*="quantityDevolved"]`).attr('data-initialvalue'))
        });
      } else if ($('#transType').val() == "2") {
        $.each($(`input[name*="quantityPending"]`), function(index, value) {
          $(`input[name*="quantityPending"]`).val($(`input[name*="quantityPending"]`).attr('data-initialvalue'))
        });
      }
      setMaskMoney()
    }
  </script>
@endsection
