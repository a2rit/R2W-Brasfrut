@extends('layouts.main')
@section('title', 'Entrada de mercadoria')
@section('content')

  <div class="col-12">
    <h3 class="header-page">Entrada de mercadorias - cadastro</h3>
  </div>
  <hr>

  <form action="{{ route('inventory.input.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="container-fluid">
      <div class="row mt-4">
        <div class="col-md-3">
          <label>Usuário</label>
          <input type="text" value="{{ Auth::user()->name }}" readonly class="form-control">
        </div>
        <div class="col-md-2">
          <label>Data atual</label>
          <input type="text" value="{{ DATE('d/m/Y') }}" readonly class="form-control">
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Data de lançamento:</label>
            <input class="form-control" type="date"
              @if (isset($dt)) value="{{ $dt }}" @endif required name="data">
          </div>
        </div>
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
              <a href='#' class='text-dark' onclick="openModal()" data-coreui-toggle="modal"
                data-coreui-target="#itensModal">
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <div class="table-responsive mt-2">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%">
                  <thead>
                    <tr>
                      <th style="width: 2%;">#</th>
                      <th style="width: 10%;">Descrições</th>
                      <th style="width: 10%;">Quantidade</th>
                      <th style="width: 10%;">Preço Unit.</th>
                      <th style="width: 10%;">Total</th>
                      <th style="width: 10%;">Projeto</th>
                      <th style="width: 10%;">C. de Custo</th>
                      <th style="width: 10%;">C. de Custo2</th>
                      <th style="width: 10%;">Depósito</th>
                      <th style="width: 10%;">Conta</th>
                      <th style="width: 8%;">Opções</th>
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
                      <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                        - {{ $values['name'] }}</option>
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
                <div class="col-md-2">
                  <label>Depósito Principal</label>
                  <select class='form-control selectpicker' name='acct' id='whsGlobal' onchange="setWHSFull()">
                    <option value=''>Selecione</option>
                    @foreach ($warehouses as $keys => $values)
                      <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                        - {{ $values['name'] }}</option>
                    @endForeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label>Conta Principal</label>
                  <select class='form-control selectpicker' name='acct' id='contaGlobal' onchange="setContaFull()">
                    <option value=''>Selecione</option>
                    @foreach ($acct as $keys => $values)
                      <option value='{{ $values['value'] }}'>{{ $values['name'] }}</option>
                    @endForeach
                  </select>
                </div>
              </div>

              <div class="col-md-2 mt-3">
                <div class="form-group">
                  <label>Total</label>
                  <input type="text" class="form-control" value="0" disabled id="totalNota">
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-2">
            <div class="panel-body">
              <div class="col-md-12 mt-3 form-group">
                <label>Observação</label>
                <textarea class="form-control" rows="5" name="obsevacoes" maxlength="200"></textarea>
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
      <div class="col-12 mt-5">
        <button class="btn btn-primary float-end" type="button" id="btn-save" onclick="valide()">Salvar</button>
      </div>
    </div>
  </form>


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


    $(function() {
      // Create the close button
      var closebtn = $('<button/>', {
        type: "button",
        text: 'x',
        id: 'close-preview',
        style: 'font-size: initial;',
      });
      closebtn.attr("class", "close pull-right");
      // Set the popover default content
      $('.image-preview').popover({
        trigger: 'manual',
        html: true,
        title: "<strong>Preview</strong>" + $(closebtn)[0].outerHTML,
        content: "There's no image",
        placement: 'bottom'
      });
      // Clear event
      $('.image-preview-clear').click(function() {
        $('.image-preview').attr("data-content", "").popover('hide');
        $('.image-preview-filename').val("");
        $('.image-preview-clear').hide();
        $('.image-preview-input input:file').val("");
        $(".image-preview-input-title").text("Browse");
      });
      // Create the preview image
      $(".image-preview-input input:file").change(function() {
        var img = $('<img/>', {
          id: 'dynamic',
          width: 250,
          height: 200
        });
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function(e) {
          $(".image-preview-input-title").text("Change");
          $(".image-preview-clear").show();
          $(".image-preview-filename").val(file.name);
          img.attr('src', e.target.result);
          $(".image-preview").attr("data-content", $(img)[0].outerHTML).popover("show");
        }
        reader.readAsDataURL(file);
      });
    });


    <?php $aux = true; ?>

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

    function gerarTotal(code) {
      clearNumber(code);
      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = parseFloat(qtd.replace('.', '').replace(',', '.')) * parseFloat(preco.replace('.', '').replace(',',
        '.'));

      if (!isNaN(total)) {
        document.getElementById('totalLinha-' + code).value = total.format(2, ",", ".");

        setTimeout(function() {
          sumAllValues();
        }, 0500);
      }
    }

    function clearNumber(code) {
      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = document.getElementById('totalLinha-' + code).value;
      document.getElementById('qtd-' + code).value = qtd.replace(/[-]/g, '');
      document.getElementById('price-' + code).value = preco.replace(/[-]/g, '');
      document.getElementById('totalLinha-' + code).value = total.replace(/[-]/g, '');
    }


    function gerarPrecoUnitario(code) {
      sumAllValues();
      gerarTotal(code);
    }

    var index =
      @if (isset($body))
        {{ $cont + 1 }}
      @else
        1
      @endif ;

    function loadTable(code) {
      var table = $('#requiredTable');

      if (index == 1) {
        $('#requiredTable tbody > tr').remove();
      }

      var tr = $("<tr id='rowTable-" + index + "'>");
      tr.append($('<td>' + index + '</td>'));

      $.get('/getProductsSAP/' + code, function(items) {

        for (var i = 0; i < items.length; i++) {
          var ultimoPreco = parseFloat(items[i].LstEvlPric).toFixed(4);

          tr.append($(`<td style="max-width: 16em;">
                                    <div class="d-flex flex-row" style="max-width: 100%;">
                                        <a class="text-warning" href="{{ route('inventory.items.edit') }}/${items[i].ItemCode}" target="_blank">
                                            <svg class="icon icon-lg">
                                                <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                            </svg>
                                            </a>
                                        <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${items[i].ItemCode} - ${items[i].ItemName}">${items[i].ItemCode} - ${items[i].ItemName}</span>
                                    </div>
                                    <input type='hidden' value='${items[i].ItemName}' name='items[${items[i].ItemCode}][itemName]' >
                                    <input type='hidden' value='${items[i].ItemCode}' name='items[${items[i].ItemCode}][itemCode]' >
                                </td>`));
          tr.append($(
            "<td><input style='width: 100%;min-width: 100px;' onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" +
            index + ");focusBlur(event)' id='qtd-" + index +
            "' type='text' class='form-control qtd' name='items[" + items[i].ItemCode + "][quantity]'></td>"));
          tr.append($(
            "<td><input style='width: 100%;min-width: 100px;' onclick='destroyMask(event)' onblur='setMaskMoney();gerarTotal(" +
            index + ");focusBlur(event)'  type='text' id='price-" + index + "' value='" + ultimoPreco.replace('.',
              ',') + "' class='form-control moneyPlus' value='0' name='items[" + items[i].ItemCode +
            "][price]'></td>"));

          tr.append($("<td><input style='width: 100%; min-width: 100px;' required readonly id='totalLinha-" + index +
            "'  onclick='setMaskMoney()' onblur='gerarPrecoUnitario(" + index +
            ")' type='text' class='form-control money locked' name='items[" + items[i].ItemCode +
            "][total]' value='0'></td>"));
          tr.append($("<td>" +
            "<select style='width: 250px;' class='form-control selectpicker' data-container='body' id='project-" +
            index + "' name='items[" + items[i].ItemCode +
            "][projectCode]' required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ substr($values['name'], 0, 10) }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select style='width: 250px;' class='form-control selectpicker' data-container='body' id='role-" +
            index + "' name='items[" + items[i].ItemCode + "][costCenter]' required onchange='validLine(" +
            index +
            ");'> <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select style='width: 250px;' class='form-control selectpicker' data-container='body' id='role2-" +
            index + "' name='items[" + items[i].ItemCode +
            "][costCenter2]' > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select style='width: 250px;' class='form-control selectpicker' data-container='body' id='whs-" +
            index + "' name='items[" + items[i].ItemCode + "][whsCode]' required onchange='setContaLine(" +
            index +
            ")' > <option value=''>Selecione</option> @foreach ($warehouses as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td>" +
            "<select style='width: 250px;' class='form-control selectpicker' data-container='body' id='conta-" +
            index + "' name='items[" + items[i].ItemCode +
            "][account]' required > <option value=''>Selecione</option> @foreach ($acct as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['name'] }}</option> @endForeach</select>" +
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


    function valide() {
      var erros = new Array();

      if (index == '1') {
        erros.push('Adicione um item \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        $('#btn-save').attr('type', 'submit');
      }

    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {
        tr.remove();

        setTimeout(function() {
          sumAllValues();
        }, 0600);
      });
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
          total = parseFloat(total) + parseFloat(x.replace(/[.]/g, "").replace(',', '.'));
        }

      }

      document.getElementById('totalNota').value = total.format(2, ",", ".");

    }

    function setContaLine(index) {
      var whs = $('#whs-' + index).val();
      var conta = $('#conta-' + index);

      $.getJSON('/inventory/get/acc/from/whs/' + whs, function(data) {
        if (data) {
          conta.val(data[0]['IncreasAc']);
        } else {
          alert('não existe conta padrão cadastrada para esse depósito');
        }
      });
    }


    function setProjecFull() {
      let val = $('#projectGlobal').val();

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

    function setWHSFull() {
      let val = $('#whsGlobal').val();
      setTimeout(() => {
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('whs-' + i)) {
            document.getElementById('whs-' + i).value = val;
          }
        }

        $(`select[id*='whs-']`).selectpicker('destroy');
        $(`select[id*='whs-']`).selectpicker(selectpickerConfig).selectpicker('render');
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
      setTimeout(() => {
        for (i = 1; i <= index; i++) {
          if (document.getElementById('conta-' + i)) {
            document.getElementById('conta-' + i).value = val;
          }
        }
        $(`select[id*='conta-']`).selectpicker('destroy');
        $(`select[id*='conta-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
    }
  </script>
@endsection
