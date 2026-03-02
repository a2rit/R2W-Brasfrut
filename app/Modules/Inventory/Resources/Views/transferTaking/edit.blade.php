@extends('layouts.main')
@section('title', 'Pedido de transfêrencia')
@section('content')
  @if ($head->status == 2)
    <div class="alert alert-success">
      Pedido Recebido Total - {{ $head->message }}
    </div>
  @elseif($head->status == 1)
    <div class="alert alert-warning">
      Pedido Recebido Parcial - {{ $head->message }}
    </div>
  @elseif ($head->status == 3)
    <div class="alert alert-danger">
      Pedido de transferencia cancelado - {{ $head->message }}
    </div>
  @else
    <div class="alert alert-danger">
      Pedido Pendente - {{ $head->message }}
    </div>
  @endif
  <form action="{{ route('inventory.transferTaking.store') }}" method="post" id="needs-validation"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="row">
      <div class="col-10">
        <h3 class="header-page">Pedido de transferência- detalhes</h3>
      </div>
      @if (isset($head) && !empty($head->codSAP))
        <div class="col-2">
          <div class="dropdown float-end">
            <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown"
              aria-expanded="false">
              <svg class="icon">
                <use xlink:href="{{ asset('icons_assets/custom.svg#printer') }}"></use>
              </svg> Imprimir
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="{{ route('inventory.transferTaking.print', $head->id) }}">PDF</a></li>
            </ul>
          </div>
        </div>
      @endif
    </div>
    <hr><br>
    <div class="row">
      <div class="col-md-2">
        <label>Cod. WEB</label>
        <input type="text" value="{{ $head->code }}" readonly class="form-control">
        <input type="hidden" value="{{ $head->id }}" name="idTransferTaking">
      </div>
      <div class="col-md-2">
        <label>Usuário</label>
        <input type="text" value="{{ $head->name }}" readonly class="form-control">
      </div>
      <div class="col-md-2">
        <label>Data do documento</label>
        <input type="date" value="{{ $head->taxDate }}" class="form-control">
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Data de lançamento:</label>
          <input type="date" class="form-control" value="{{ $head->docDate }}">
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Status:</label>
          <input type="text" class="form-control" value="{{ $head->docStatus }}" readonly>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Depósito origem:</label>
          <select id='fromWhs' class='form-control selectpicker' name='fromWarehouse'
            @if ($head->status == '2' || auth()->user()->tipoTransf == 'A' || isset($head->status)) readonly @endif>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $keys => $values)
              <option value='{{ $values['code'] }}' @if ($values['code'] == $head->fromWarehouse) selected @endif>
                {{ $values['code'] }} - {{ $values['value'] }}</option>
            @endForeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>Depósito destino:</label>
          <select id='toWhs' class='form-control selectpicker' name='toWarehouse'
            @if ($head->status == '2' || auth()->user()->tipoTransf == 'A' || isset($head->status)) readonly @endif>
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
      @if ($transfers->isNotEmpty())
        <h5>Documentos associados</h5>
        <div class="col-md-3">
          <div class="form-group">
            <label for="status">Transferências</label>
            <div>
              @foreach ($transfers as $transfer)
                <a class="btn btn-primary mt-1" target="_blank"
                  href="{{ route('inventory.transfer.edit', $transfer->id) }}">{{ $transfer->code }}</a>
              @endforeach
            </div>
          </div>
        </div>
      @elseif($head->codWEBTransf)
        <h5>Documentos associados</h5>
        <div class="col-md-2">
          <div class="form-group">
            <label>Cod. Transferência:</label>
            <a type="text" class="btn btn-primary" target="_blank"
              href="{{ route('inventory.transfer.edit', $head->idTransf) }}" readonly>{{ $head->codWEBTransf }}</a>
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
          <div class="panel-body mt-4">
            <a href='#' class='text-dark mb-2' onclick="openModal()" data-coreui-toggle="modal"
              data-coreui-target="#itensModal">
              <svg class="icon icon-xxl">
                <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
              </svg>
            </a>
            <div class="table-responsive">
              <table id="requiredTable"
                class="table table-default table-striped table-bordered table-hover dataTables-example">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Descrições </th>
                    <th>U.M.</th>
                    <th>Qtd. Solicitada </th>
                    <th>Qtd. Pendente</th>
                    <th>Qtd. Estoque</th>
                    <th>Qtd. Atendida</th>
                    <th>Projeto</th>
                    <th>Centro de custo</th>
                    <th>Centro de custo2</th>
                    <th>Opções</th>
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
                          </div>
                          <input type='hidden' value='{{ $value['itemName'] }}'
                            name='items[{{ $value['id'] }}][itemName]'>
                          <input type='hidden' value='{{ $value['itemCode'] }}'
                            name='items[{{ $value['id'] }}][itemCode]'>
                          <input type='hidden'value="{{ $head->id }}" name='items[{{ $value['id'] }}][id]'>
                        </td>
                        <td>
                          <p>{{ $value['itemUnd'] }}</p>
                        </td>
                        <td><input type="text" class="form-control qtd min-100 locked" @if (auth()->user()->tipoTransf == 'A' || isset($head->status))  @endif
                            @if ($head->status == '2') readonly @endif
                            name='items[{{ $value['id'] }}][quantityRequest]' value="{{ $value['quantityRequest'] }}"
                            onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'></td>

                        <td><input type="text" class="form-control qtd min-100 locked" readonly
                            @if ($head->status == '2') readonly @endif
                            name='items[{{ $value['id'] }}][quantityPending]' value="{{ $value['quantityPending'] }}"
                            onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'></td>

                        <td><input type="text" class="form-control min-100 locked" readonly
                            name='items[{{ $value['id'] }}][qtdEstoque]' value="{{ $value['qtdEstoque'] }}"
                            onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'></td>

                        {{-- verificar se é atendente ou solicitante --}}
                        <td><input type="text" class="form-control qtd min-100"
                            @if ($head->status == '2') readonly value="{{ $value['quantityServed'] }}" @elseif(auth()->user()->tipoTransf == 'A') value="0" @endif
                            @if (auth()->user()->tipoTransf != 'A') readonly value="{{ $value['quantityServed'] }}" @endif
                            @if ($value['quantityPending'] == '0') readonly @endif
                            name='items[{{ $value['id'] }}][quantityServed]' onclick='destroyMask(event)'
                            onblur="setMaskMoney();focusBlur(event); checkValue({{ $value['id'] }})"></td>

                        <td>
                          <select class='form-control selectpicker' style="width: 150px;" data-container="body"
                            @if (auth()->user()->tipoTransf == 'A' || isset($head->status)) readonly @endif
                            name='items[{{ $value['id'] }}][projectCode]'
                            @if ($head->status == '2') readonly @endif>
                            <option></option>
                            @foreach ($projeto as $keys => $values)
                              <option value='{{ $values['code'] }}' @if ($values['code'] == $value['projectCode']) selected @endif>
                                {{ $values['value'] }}
                              </option>
                            @endForeach
                          </select>
                        </td>
                        <td>
                          <select class='form-control selectpicker' style="width: 150px;" data-container="body"
                            @if (auth()->user()->tipoTransf == 'A' || isset($head->status)) readonly @endif
                            name='items[{{ $value['id'] }}][centroCusto]'
                            @if ($head->status == '2') readonly @endif>
                            <option></option>
                            @foreach ($centroCusto as $keys => $values)
                              <option value='{{ $values['value'] }}'
                                @if ($values['value'] == $value['costCenter']) selected @endif>{{ $values['value'] }} -
                                {{ $values['name'] }}</option>
                            @endForeach
                          </select>
                        </td>
                        <td>
                          <select class='form-control selectpicker' style="width: 150px;" data-container="body"
                            @if (auth()->user()->tipoTransf == 'A' || isset($head->status)) readonly @endif
                            name='items[{{ $value['id'] }}][centroCusto2]'
                            @if ($head->status == '2') readonly @endif>
                            <option></option>
                            @foreach ($centroCusto2 as $keys => $values)
                              <option value='{{ $values['value'] }}'
                                @if ($values['value'] == $value['costCenter2']) selected @endif>{{ $values['value'] }} -
                                {{ $values['name'] }}</option>
                            @endForeach
                          </select>
                        </td>
                        <td class="text-center">
                          <a class="text-danger" onclick='removeLinha(this);' type="button">
                            <svg class="icon icon-xl">
                              <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                            </svg>
                          </a>
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
          <div class="panel-body">
            <div class="col-md-12 mt-3 form-group">
              <label>Observação</label>
              <textarea class="form-control" rows="5" @if ($head->status == '2') readonly @endif name="comments">{{ $head->comments }}</textarea>
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
      <div class="col-md-12 mt-5">
        @if ($head->status != '2' && $head->status != '3')
          <button class="btn btn-primary float-end" type="submit">Salvar</button>
          <button class="btn btn-danger float-end me-1" value="{{ $head->id }}"
            onclick="cancelTransfer(event)">Cancelar</button>
        @endif
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

    <?php $aux = true; ?>

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
      //console.log(prince);
      if (!isNaN(prince)) {
        document.getElementById('price-' + code).value = prince.format(2, ",", ".");
        sumAllValues();
      }
    }
    var index = 1;

    function loadTable(code) {
      var table = $('#requiredTable');
      var depositoAtual = $("#fromWhs :selected").val();

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
          tr.append($("<td><p>" + items[i]['BuyUnitMsr'] + "</p> </td>"));
          tr.append($("<td><input required type='text' class='form-control qtd' id='items[" + index +
            "][quantityRequest]' onchange='valideQtd(event, " + items[i].ONHAND +
            ")' onclick='destroyMask(event)' onblur='setMaskMoney();focusBlur(event)'  name='items[" + index +
            "][quantityRequest]' required></td>"));

          tr.append($("<td></td>"));
          tr.append($("<td>" +
            "<input class='form-control' id='items[" + index +
            "][qtdEstoque]' name='items[" + index +
            "][qtdEstoque]' required value='" + items[i].ONHAND + "'/>" +
            "</td>"));
          tr.append($("<td></td>"));
          tr.append($("<td>" +
            "<select class='form-control selectpicker' id='project-" + index +
            "' style='width:170px' name='items[" + index +
            "][projectCode]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['value'] }}</option> @endForeach</select>" +
            "</td>"));

          // tr.append($("<td>"
          //     + "<select class='form-control' id='role-" + index + "' name='items[" + index + "][role]' required > <option value=''>Selecione</option> @foreach ($role as $keys => $values) <option value='{{ $values['code'] }}'>{{ $values['code'] }} - {{ $values['value'] }}</option> @endForeach</select>"
          //     + "</td>")); 
          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='role-" + index + "' name='requiredProducts[" +
            index +
            "][centroCusto]' required onchange='validLine(" + index +
            ");' style='width: 170px;' > <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='role2-" + index + "' name='requiredProducts[" +
            index +
            "][centroCusto2]' style='width: 170px;' > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
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
    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {
        tr.remove();
      });
      //$("#composicao-" + elemento).remove();
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

    function chengeIcon(element) {
      document.getElementById('addItem-' + element).src = "{{ asset('images/addCinza.png') }}";
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
            window.location.href = "{{ route('inventory.transferTaking.remove.upload') }}/" + id + "/" + idRef;
          }
        });
    }


    function goTransfer() {
      $('#needs-validation').attr('action', '');
      $('#needs-validation').attr('action', '{{ route('inventory.transferTaking.go.transfer') }}');
      $('#needs-validation').submit();
      // $('#btn-save').attr('type', 'submit');

    }

    function checkValue(id) {
      request = $('[name="items[' + id + '][quantityRequest]"]');
      served = $('[name="items[' + id + '][quantityServed]"]');
      pending = $('[name="items[' + id + '][quantityPending]"]');

      pendente = parseFloat(pending.val().replace(',', '.') - served.val().replace(',', '.')).toFixed(3);

      if (pendente < 0) {
        alert("O valor tem que ser menor ou igual ao pendente!");
        served.val('');
      } else {

      }


      // if (!isNaN(parseFloat(pendente)) && !isNaN(parseFloat(value)) && !isNaN(parseFloat(input.value)) && !isNaN(parseFloat(whs))) {
      //     if (parseFloat(pendente) > 0) {
      //         if (parseFloat(pendente) < parseFloat(input.value)) {
      //             alert("O valor tem que ser menor ou igual ao Pendente!");
      //             input.value = 0;
      //         }
      //     } else {
      //         if (parseFloat(value) < parseFloat(input.value)) {
      //             alert("O valor tem que ser menor ou igual ao solicitado!");
      //             input.value = 0;
      //         }
      //         if ((parseFloat(whs) - parseFloat(input.value)) < 0) {
      //             alert("A quantidade em estoque não pode ficar negativo! Por favor, efetue uma solicitação de compra.");
      //             input.value = 0;
      //         }
      //         if (parseFloat(input.value) < 0) {
      //             alert("A quantidade Atendida não pode ser negativa! ");
      //             input.value = 0;
      //         }
      //     }

      // } else {
      //     input.value = 0;
      // }

    }

    function openModal() {
      $('#table').DataTable().clear()
      $("#itensModal").modal('show');
      let table = $("#table").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        ajax: {
          url: "{{ route('inventory.request.list.whs') }}",
          data: function(d) {
            d.purchase = true;
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
            name: 'OnHand',
            data: 'ItemCode',
            render: renderWHSButton,
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


    function cancelTransfer(event) {
      event.preventDefault();
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
            waitingDialog.show('Cancelando...');
            window.location.href = "{{ route('inventory.transferTaking.cancel', $head->id) }}";
          }
        });
    }

    function cancelTransfer2(event) {
      event.preventDefault();
      let id = $(event.target).attr('value');

      if (id) {
        $.ajax({
          type: 'post',
          url: "{{ route('inventory.transferTaking.cancel') }}",
          headers: {
            'X-CSRF-TOKEN': $('body input[name="_token"]').val()
          },
          data: {
            'id': id
          },
          dataType: 'json',
          success: function(response) {
            waitingDialog.hide();
            if (response.status == 'success') {
              swal({
                  title: "Sucesso!",
                  text: response.message,
                  icon: "success",
                  buttons: ["Fechar", "Voltar para a pagina principal"],
                })
                .then((result) => {
                  if (result) {
                    window.location.href = "/inventory/transferTaking/index";
                  }
                });
            } else {
              swal({
                title: "Opss...",
                text: response.message,
                icon: "error",
                buttons: ["Fechar"],
              })
            }
          },
          error: function(response) {
            waitingDialog.hide();
            swal({
              title: "Opss...",
              text: response.message,
              icon: "error",
              buttons: ["Fechar"],
            })
          }
        });
      } else {
        swal({
          title: "Opss...",
          text: "Ocorreu um erro, atualize a pagina",
          icon: "error",
          buttons: ["Fechar"],
        })
      }
    }

    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("O processo pode demorar um pouco, aguarde até que seja concluído!");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'transfersTaking')
      formData.append('id', $('input[name="idTransferTaking"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('inventory.transferTaking.updateUploads') }}",
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


    function valideQtd(event, qtdOnHand) {
      let requested_quantity = parseFloat(event.target.value)
      if (requested_quantity > qtdOnHand) {
        alert('A quantidade solicitada não deve ser maior que a quantidade do item em estoque')
        event.target.value = ''
        return
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
  </script>
@endsection
