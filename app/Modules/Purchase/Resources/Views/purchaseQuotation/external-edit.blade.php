<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>R2W - Cotação Externa</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('/images/favicon.ico') }}">
  <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
  <script src="{{mix("js/app.js")}}" type="application/javascript"></script>
</head>

<body>
  <div class="wrapper d-flex flex-column min-vh-100 bg-light">
    <div class="body flex-grow-1 px-3 pt-4">
      <div class="container-fluid">
        @if(!empty($head->external_id))
          
        <form action="{{ route('purchase.quotation.saveExternalQuotation') }}" method="post" id="needs-validation"
          enctype="multipart/form-data" onsubmit="waitingDialog.show('Salvando...')">
          {!! csrf_field() !!}
          <input type="hidden" name="external_id" value="{{ $head->external_id }}">
          <div class="row mb-1">
            <div class="col-md-10">
              @if (isset($head))
                <h3 class="header-page">Cotação de compras - detalhes</h3>
              @endif
            </div>
          </div>
          <hr>
          <div class="row" id='form'>
            <div class="row">
              <div class="col-md-6">
                <label>Parceiro de Negócio</label>
                <select id="cardCode" class="form-control selectpicker with-ajax-partner locked" name="cardCode">
                  <option value="{{ $partner->CardCode }}" selected>{{ $partner->CardName }}</option>
                </select>
              </div>
              <div class="col-md-4">
                <label>Solicitante</label>
                <input type="text" readonly value="{{ $head->name_solicitante }}" class="form-control locked">
              </div>
            </div>

            <div class="row mt-2 mb-3">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Data de Entrega</label>
                  <input type="date" name="dataVencimento" id="dataVencimento" class="form-control"
                    value="{{ $head->data_f }}" 
                    required>
                </div>
              </div>
              <div class="col-md-3">
                <label>Condição de Pagamento</label>
                <select class="form-control selectpicker" required name="paymentTerms" id="conditionPagamentos">
                  <option value=''>Selecione</option>
                  @foreach ($paymentConditions as $key => $value)
                    <option value="{{ $value['GroupNum'] }}" @if (isset($head->paymentTerms) && $head->paymentTerms == $value['GroupNum']) selected @endif>
                      {{ $value['PymntGroup'] }}</option>
                  @endForeach
                </select>
              </div>
            </div>
            <hr>
          </div>
          <div class="row mt-2">
            <div class="tabs-container">
              <ul class="nav nav-tabs" id="myTabs">
                <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
                  <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
                </li>
                <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
                  <a class="nav-link" data-toggle="tab" href="#tab-2">Despesas</a>
                </li>
                <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
                  <a class="nav-link" data-toggle="tab" href="#tab-3">Anexos</a>
                </li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active mt-5" id="tab-1">
                  <div class="panel-body">
                    {{-- <img src="{{asset('images/add.png')}}" data-toggle="modal" onclick="loadingItems();" data-target="#itensModal" @if (isset($head) && ($head->status == '2' || $head->status == '0')) style="display: none;" @endif/> --}}
                    <div class="table-responsive">
                      <table id="requiredTable"
                        class="table table-default table-striped table-bordered table-hover dataTables-example"
                        style="width: 100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Descrição</th>
                            <th>Und.</th>
                            <th>Qtd. Necessária</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $cont = 1;?>
                          @foreach ($body as $key => $value)
                            <tr id="rowTable-{{ $cont }}" data-row="{{ $cont }}"
                              value="{{ $value['id'] }}"
                              @if ($value['status'] == 2) style="background-color: rgb(220, 223, 228);" @endif>
                              <td>{{ $cont }}
                                <input type='hidden' value="{{ $value['idPurchaseQuotation'] }}"
                                  name="requiredProducts[{{ $cont }}][idPurchaseQuotation]">
                                <input type='hidden' name='requiredProducts[{{ $cont }}][idItem]'
                                  id='item-{{ $cont }}' value='{{ $value['id'] }}'>
                              </td>
                              <td style="max-width: 16em;">
                                <div class="d-flex flex-row" style="max-width: 100%;">
                                  <a class="text-warning"
                                    href="{{ route('inventory.items.edit', $value['itemCode']) }}" target="_blank">
                                    <svg class="icon icon-lg">
                                      <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                    </svg>
                                  </a>
                                  <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                    title="{{ $value['itemCode'] }} - {{ $value['itemName'] }}">{{ $value['itemCode'] }}
                                    -
                                    {{ $value['itemName'] }}</span>
                                </div>
                                <input type='hidden' value="{{ $value['itemCode'] }}"
                                  name="requiredProducts[{{ $cont }}][codSAP]">
                                <input type='hidden' value="{{ $value['itemName'] }}"
                                  name="requiredProducts[{{ $cont }}][itemName]">
                              </td>
                              <td>{{ $value['itemUnd'] == null ? '' : $value['itemUnd'] }}<input type='hidden'
                                  value="{{ $value['itemUnd'] }}"
                                  name="requiredProducts[{{ $cont }}][itemUnd]">
                              </td>
                              <td><input onblur="setMaskMoney();gerarTotal({{ $cont }})" type="text"
                                  class="form-control qtd locked min-100" id="qtdNecessaria-{{ $cont }}"
                                  value='{{ $value['qtd'] }}' name="requiredProducts[{{ $cont }}][qtd]"
                                  min="1" readonly></td>
                              <td><input onclick='destroyMask(event)'
                                  onblur="setMaskMoney();gerarTotal({{ $cont }});focusBlur(event)"
                                  id="qtd-{{ $cont }}" type="text" class="form-control qtd min-100" value='{{ $value['qtdP1'] }}'
                                  name="requiredProducts[{{ $cont }}][qtdP1]" min="1"></td>
                              <td><input onclick='destroyMask(event)'
                                  onblur="setMaskMoney();gerarTotal({{ $cont }}); focusBlur(event)"
                                  id="price-{{ $cont }}" type="text"
                                  class="form-control moneyPlus min-100" value='{{ $value['priceP1'] }}'
                                  name="requiredProducts[{{ $cont }}][priceP1]">
                              <td><input id="totalLinha-{{ $cont }}" type="text"
                                  class="form-control locked min-100 money"
                                  name="requiredProducts[{{ $cont }}][totalP1]" value='{{ $value['totalP1'] }}' readonly>
                              </td>
                              <?php $cont++; ?>
                            </tr>
                          @endforeach
                          <input type="hidden" value="{{ $cont - 1 }}" id="cont">
                        </tbody>
                      </table>
                    </div>
                    <div class="row mt-3">
                      <div class="col-md-2">
                        <label>Total de despesas</label>
                        <input type="text" class="form-control money locked" name="totalSemDesconto" readonly
                          @if (isset($head)) value="{{ $head_expenses->sum('lineTotal') }}" @endif
                          id="totalDespesas">
                      </div>
                      <div class="col-md-2">
                        <label>Total</label>
                        <input type="text" class="form-control money locked" name="docTotal"
                          @if (isset($head)) value="{{ $head->docTotal }}" @endif readonly
                          id="totalNota">
                        <input type="hidden" class="form-control" id="docTotal">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-2">
                  <div class="panel-body">
                    <div class="row mt-3">
                      <div class="table-responsive">
                        <table id="tableExpense"
                          class="table table-default table-striped table-bordered table-hover dataTables-example">
                          <thead>
                            <tr>
                              <th style="width: 20%">Despesa</th>
                              <th style="width: 10%">Valor</th>
                              <th style="width: 70%">Observação</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($expenses as $key => $value)
                              @php $expense = $head_expenses->where("expenseCode", "=", $value["code"])->first();
                              @endphp
                              <tr>
                                <td>
                                  {{ $value["value"] }}
                                  <input type="hidden" name="expenses[{{ $key }}][expenseCode]" value="{{ $value["code"] }}">
                                </td>
                                <td>
                                  <input
                                    @if (!empty($expense)) value='{{ number_format($expense->lineTotal) }}' @endif
                                    id='di_total' onclick='destroyMask(event)'
                                    onblur="setMaskMoney();sumAllValues();focusBlur(event)" type="text"
                                    class="form-control money min-100" name="expenses[{{ $key }}][lineTotal]">
                                </td>
                                <td>
                                  <input @if (!empty($expense)) && value='{{ $expense->comments }}' @endif
                                    type="text" class="form-control min-100" name="expenses[{{ $key }}][comments]">
                                </td>
                              </tr>
                            @endForeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="tab-pane" id="tab-3">
                  <div class="panel-body mt-5">
                    <div class="col-md-12">
                      <label>Observação</label>
                      <textarea class="form-control" rows="5" name="obsevacoes" maxlength="200">{{ $head->comments }}</textarea>
                    </div>
                    <div class="row mt-4">
                      <div class="col-md-6 mt-2">
                        <div class="table-responsive">
                          <table
                            class="table table-default table-striped table-bordered table-hover dataTables-example">
                            <thead>
                              <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 80%;">Anexo</th>
                                <th style="width: 15%;">Visualizar</th>
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
                                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}">
                                          </use>
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
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 mt-5">
            <button class="btn btn-primary float-end me-1" onclick="valide(this)" type="button">Enviar</button>
          </div>
        </form>
        @else
            <div style="position: relative; width: 100%; height: 0; padding-top: 56.2225%;
            padding-bottom: 0; box-shadow: 0 2px 8px 0 rgba(63,69,81,0.16); margin-top: 1.6em; margin-bottom: 0.9em; overflow: hidden;
            border-radius: 8px; will-change: transform;">
            <iframe loading="lazy" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;margin: 0;"
              src="https:&#x2F;&#x2F;www.canva.com&#x2F;design&#x2F;DAFzIRPLXiM&#x2F;view?embed" allowfullscreen="allowfullscreen" allow="fullscreen">
            </iframe>
          </div>
          <a href="https:&#x2F;&#x2F;www.canva.com&#x2F;design&#x2F;DAFzIRPLXiM&#x2F;view?utm_content=DAFzIRPLXiM&amp;utm_campaign=designshare&amp;utm_medium=embeds&amp;utm_source=link" target="_blank" rel="noopener">Tudo certo!</a> de a2r it
        @endif
      </div>
    </div>
    @include('layouts.footer')
  </div>
</body>

@if(!empty($head->external_id))
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script src="{{mix("js/bottom-app.js")}}" type="application/javascript"></script>
  <script src="{!! asset('js/format.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpickerConfig = {
      "selectOnTab": true,
      "liveSearch": true,
      "style": "btn-default",
      "size": 5
    }
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    $(document).ready(function() {
      setMaskMoney();
      $('.text-tooltip').tooltip();

      setTimeout(function() {
        sumAllValues();
      }, 1000);

    });

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    $(document).on('changed.bs.select', '.selectpicker', function(event) {
      if (!$(event.target).attr('class').includes('ajax')) {
        $(event.target).selectpicker('toggle');
      }
    });

    $("body").on('keydown', 'input', function(e) {

      var keyCode = e.keyCode || e.which;
      var form = $('#needs-validation');
      if (keyCode == 9 && $('input:focus').length) {
        var focusable = $(form).find('input').filter(':visible');
        var next = focusable.eq(focusable.index(this) + 1);
        $(next).focusin(function() {
          $(this).maskMoney('destroy')
        });
      }
    });

    $.each($('input,textarea,select').not('input[type="hidden"]').filter('[required]'), function(index, value) {
      $(value).parent().find('label').append(
        `<span class="text-orange ms-2 fw-bold text-tooltip" data-coreui-toggle="tooltip" title="Campo obrigatório">*</span>`
        );
    });

    var index = {{ count($body) }};

    var expensesIndex = {{ count($expenses) }}

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

    var aux = 0;

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

    var used = new Array();

    function gerarTotal(code) {
      var qtd = $('#qtd-' + code);
      var qtd_necessaria = parseFloat($('#qtdNecessaria-' + code).val().replace(/[.]/gi, '').replace(/[,]/gi, '.'))

      if (parseFloat(qtd.val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) > qtd_necessaria) {
        qtd.val(qtd_necessaria);
        swal({
            title: "Opss...",
            text: "A quantidade informada não deve ser maior que a quantidade pendente do item!",
            icon: "error",
            button: "Fechar"
        });
        setMaskMoney()
        return
      }
      var preco = document.getElementById('price-' + code).value.replace(/[.]/gi, '').replace(/[,]/gi, '.');
      var total = parseFloat(qtd.val().replace(/[.]/gi, '').replace(/[,]/gi, '.')) * parseFloat(preco);
      if (!isNaN(total)) {
        document.getElementById('totalLinha-' + code).value = (total.toFixed(2));
      }

      setTimeout(function() {
        sumAllValues();
      }, 0500);
      setMaskMoney()
    }

    function sumAllValues() {
      var total = 0;
      var totalDespesas = 0;
      var i = 0;
      var x = 0;

      for (let index = 0; index < expensesIndex; index++) {
        const value = parseFloat($(`input[name="expenses[${index}][lineTotal]"]`).val().replace(/[.]/gi, '').replace(
          /[,]/gi, '.')) || 0;
        totalDespesas += value;
      }

      for (i = 1; i <= index; i++) {
        total += parseFloat(document.getElementById('totalLinha-' + i).value || 0);
      }

      if (isNaN(total)) {
        total = 0;
      }

      total += totalDespesas;
      var x;
      var i;

      totalDoc = total.format(2, ",", ".");
      total = (Math.round((parseFloat(total)) * 100) / 100);
      document.getElementById('totalNota').value = total.format(2, ",", ".");
      //document.getElementById('total_pagameto_modal').value = total.format(2, ",", ".");
      // addExpensesFromValues();
    }

    function valide(button) {
      var erros = new Array();

      if ($('#conditionPagamentos').val() == "") {
        erros.push('Informe a condição de pagamento! \n');
      }

      if ($('#qtd-1').val() == "") {
        erros.push('Informe a quantidade! \n');
      }
      if ($('#cardCode').val() == "") {
        erros.push('Adicione um fornecedor! \n');
      }

      if (index == '0') {
        erros.push('Adicione um item! \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
      } else {
        var table = $('#requiredTable');
        $('input, select').attr('disabled', false);
        $(button).attr('type', 'submit');
      }

    }
  </script>
@endif