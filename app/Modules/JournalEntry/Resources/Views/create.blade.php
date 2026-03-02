@extends('layouts.main')

@section('title', 'Lançamento contábil manual')

@section('content')


  <div class="col-md-10">
    <h3 class="header-page">Lançamento Contábil Manual - cadastro</h3>
  </div>
  <hr>

  <form action="{{ route('journal-entry.save') }}" method="post" onsubmit="return validate()" id="needs-validation">
    {!! csrf_field() !!}
    <div class="row">
      <div class="col-md-4">
        <div class="form-group"><label>Data de lançamento</label>
          <div class="input-group">
            <input required data-mask="99/99/9999" type="text" name="posting_date" class="form-control datepicker"
              onchange="changePostingDate(this)" value="{{ date('d/m/Y') }}">
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group"><label>Data de vencimento</label>
          <div class="input-group">
            <input data-mask="99/99/9999" required type="text" name="due_date" class="form-control datepicker"
              value="{{ date('d/m/Y') }}">
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group"><label>Data do documento</label>
          <div class="input-group">
            <input data-mask="99/99/9999" required type="text" name="doc_date" class="form-control datepicker"
              value="{{ date('d/m/Y') }}">
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group"><label>Comentários</label>
          <textarea type="text" name="comments" class="form-control"></textarea>
        </div>
      </div>
    </div>
    <div class="table-responsive mt-5">
      <table class="table table-default table-striped table-bordered table-hover" id="lines-table">
        <thead>
          <tr>
            <th style="width: 15%">Conta</th>
            <th style="width: 15%">Parceiro</th>
            <th style="width: 10%">Débito</th>
            <th style="width: 10%">Crédito</th>
            <th style="width: 15%">Centro de Custo</th>
            <th style="width: 15%">Centro de Custo2</th>
            <th style="width: 15%">Projeto</th>
            <th style="width: 5%">Remover</th>
          </tr>
        </thead>
        <tbody>
          <tr id="line-index" style="display: none" data-index="">
            <td>
              <select data-name="account" class="form-control selectpicker" name="lines[index][account]"
                onchange="disabledItem(this)" data-container="body">
                <option value=""></option>
                @foreach ($accounts as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td>
              <select data-name="cardCode" class="form-control selectpicker with-ajax-suppliers"
                name="lines[index][cardCode]" onchange="disabledItem(this)" data-container="body">
                <option value=""></option>
              </select>
            </td>
            <td>
              <input type="text" name="lines[index][debit]" class="form-control maskMoney min-100" data-thousands=""
                data-name="debit" data-decimal="." onchange="validateValues(this)">
            </td>
            <td>
              <input type="text" name="lines[index][credit]" data-name="credit" class="form-control maskMoney min-100"
                data-thousands="" data-decimal="." onchange="validateValues(this)">
            </td>
            <td>
              <select class="form-control selectpicker" name="lines[index][costCenter]" data-container="body"
                data-name="costCenter">
                <option value=""></option>
                @foreach ($centroCusto as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td>
              <select class="form-control selectpicker" name="lines[index][costCenter2]" data-container="body"
                data-name="costCenter2">
                <option value=""></option>
                @foreach ($centroCusto2 as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td>
              <select class="form-control selectpicker" data-container="body" name="lines[index][project]" data-name="project">
                <option value=""></option>
                @foreach ($projects as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td class="text-center">
              <a class="text-danger" onclick="removeLine(this)"
                type="button">
                <svg class="icon icon-xl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                </svg>
              </a>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th>Totais</th>
            <th></th>
            <th id="debit-total"></th>
            <th id="credit-total"></th>
            <th colspan="3">
              <button class="btn btn-success" type="button" onclick="addLine()">Adicionar
                linha
              </button>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
    <button class="btn btn-primary float-end mt-5" type="submit">Salvar</button>
  </form>
@endsection

@section('scripts')
  <script type="text/javascript">

    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-suppliers')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    var table = $('#lines-table');
    var index = 0;

    function disabledItem(item) {
      var tr = $(item).closest('tr');

      var accountInput = tr.find('[data-name=account]').first();
      var account = parseFloat(accountInput.val());

      var partnerInput = tr.find('[data-name=cardCode]').first();
      var partner = parseFloat(partnerInput.val().substring(1));

      if (isNaN(account) && isNaN(partner)) {
        partnerInput.prop('disabled', false).prop('required', false);
        accountInput.prop('disabled', false).prop('required', false);
        return;
      }
      if (!isNaN(account) && isNaN(partner)) {
        partnerInput.prop('disabled', true).prop('required', false).val('');
        accountInput.prop('disabled', false).prop('required', true);
      }
      if (isNaN(account) && !isNaN(partner)) {
        partnerInput.prop('disabled', false).prop('required', true);
        accountInput.prop('disabled', true).prop('required', false).val('');
      }
    }

    function addLine() {

      let tr = $('<tr></tr>');

      tr.append(`<td>
              <select data-name="account" class="form-control selectpicker" name="lines[${index}][account]"
                data-container="body">
                <option value=""></option>
                @foreach ($accounts as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>`)
            tr.append(`<td>
              <select data-name="cardCode" class="form-control selectpicker with-ajax-suppliers"
                name="lines[${index}][cardCode]" data-container="body">
                <option value=""></option>
              </select>
            </td>`)
            tr.append(`<td>
              <input type="text" name="lines[${index}][debit]" class="form-control maskMoney min-100" data-thousands=""
                data-name="debit" data-decimal="." onchange="validateValues(this)">
            </td>`)
            tr.append(`<td>
              <input type="text" name="lines[${index}][credit]" data-name="credit" class="form-control maskMoney min-100"
                data-thousands="" data-decimal="." onchange="validateValues(this)">
            </td>`)
            tr.append(`<td>
              <select class="form-control selectpicker" name="lines[${index}][costCenter]" data-container="body"
                data-name="costCenter">
                <option value=""></option>
                @foreach ($centroCusto as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>`)
            tr.append(`<td>
              <select class="form-control selectpicker" name="lines[${index}][costCenter2]" data-container="body"
                data-name="costCenter2">
                <option value=""></option>
                @foreach ($centroCusto2 as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>`)
            tr.append(`<td>
              <select class="form-control selectpicker" data-container="body" name="lines[${index}][project]" data-name="project">
                <option value=""></option>
                @foreach ($projects as $item)
                  <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>`)
            tr.append(`<td class="text-center">
              <a class="text-danger" onclick="removeLine(this)"
                type="button">
                <svg class="icon icon-xl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                </svg>
              </a>
            </td>`)

      table.find('tbody').append(tr);
      index++;

      
      $('.selectpicker').selectpicker(selectpickerConfig).selectpicker('render');
      $('.with-ajax-suppliers').filter('.with-ajax-suppliers').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"))
      $('.maskMoney').maskMoney();
    }

    function removeLine(input) {
      var tr = $(input).closest('tr');
      tr.remove();
      setTotals();
    }

    function validateValues(input) {
      setTotals();
      var tr = $(input).closest('tr');

      var creditInput = tr.find('[data-name=credit]').first();
      var credit = parseFloat(creditInput.val());

      var debitInput = tr.find('[data-name=debit]').first();
      var debit = parseFloat(debitInput.val());

      if (isNaN(credit) && isNaN(debit)) {
        debitInput.prop('disabled', false).prop('required', false);
        creditInput.prop('disabled', false).prop('required', false);
        return;
      }
      if (!isNaN(credit) && isNaN(debit)) {
        debitInput.prop('disabled', true).prop('required', false).val('');
        creditInput.prop('disabled', false).prop('required', true);
      }
      if (isNaN(credit) && !isNaN(debit)) {
        debitInput.prop('disabled', false).prop('required', true);
        creditInput.prop('disabled', true).prop('required', false).val('');
      }
    }

    function setTotals() {
      $('#credit-total').html(getCreditsTotal().toFixed(2));
      $('#debit-total').html(getDebitsTotal().toFixed(2));
    }

    function getCreditsTotal() {
      var credits = $('input[data-name=credit]');
      var creditTotal = 0;
      credits.each(function(i, item) {
        var value = parseFloat($(item).val());
        if (!isNaN(value)) {
          creditTotal += value;
        }
      });
      return creditTotal;
    }

    function getDebitsTotal() {
      var debits = $('input[data-name=debit]');
      var debiitTotal = 0;
      debits.each(function(i, item) {
        var value = parseFloat($(item).val());
        if (!isNaN(value)) {
          debiitTotal += value;
        }
      });
      return debiitTotal;
    }

    function validate() {
      var credit = parseInt((getCreditsTotal() * 100).toFixed(0));
      var debit = parseInt((getDebitsTotal() * 100).toFixed(0));

      if (credit === 0 || debit === 0 || credit !== debit) {
        sweetAlert('Os valores de crédito e débito devem ser iguais e maiores que zero!');
        return false;
      }
      waitingDialog.show('Salvando...');
    }

    function changePostingDate(item) {
      $('[name=doc_date]').val(item.value);
    }

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });
  </script>
@endsection
