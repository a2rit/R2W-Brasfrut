@extends('layouts.main')
@section('title', 'Lançamento contábil Manual')
@section('content')

  @if ($head->is_locked == 1)
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif

  <form action="{{ route('journal-entry.store') }}" method="post" onsubmit="return validate()" id="needs-validation">
    {!! csrf_field() !!}
    <input type="hidden" name="id" value="{{ $head->id }}">
    <div class="row">
      <div class="col-md-2">
        <div class="form-group">
          <label>Cod. SAP</label>
          <input type="text" class="form-control" value="{{ $head->codSAP }}" disabled>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Cod. WEB</label>
          <input type="text" class="form-control" value="{{ $head->code }}" disabled>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          <label>Autor</label>
          <input type="text" class="form-control" value="{{ $head->name }}" disabled>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group"><label>Data de lançamento</label>
          <div class="input-group">
            <input required data-mask="99/99/9999" type="text" name="posting_date" class="form-control datepicker"
              onchange="changePostingDate(this)" value="{{ formatDate($head->posting_date) }}"
              @if ($head->is_locked == 0) disabled @endif>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group"><label>Data de vencimento</label>
          <div class="input-group">
            <input data-mask="99/99/9999" required type="text" name="due_date" class="form-control datepicker"
              value="{{ formatDate($head->due_date) }}" @if ($head->is_locked == 0) disabled @endif>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group"><label>Data do documento</label>
          <div class="input-group">
            <input data-mask="99/99/9999" required type="text" name="doc_date" class="form-control datepicker"
              value="{{ formatDate($head->doc_date) }}" @if ($head->is_locked == 0) disabled @endif>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group"><label>Comentários</label>
          <textarea type="text" name="comments" class="form-control" value="{{ $head->comments }}"
            @if ($head->is_locked == 0) disabled @endif></textarea>
        </div>
      </div>
      @if ($head->nameCancel)
        <div class="col-md-3">
          <div class="form-group"><label>Cancelado</label>
            <input type="text" name="nameCancel" class="form-control" value="{{ $head->nameCancel }}"
              @if ($head->is_locked == 0) disabled @endif>
          </div>
        </div>
      @endif
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
          </tr>
        </thead>
        <tbody>
          <?php $debit = 0;
          $credit = 0; ?>
          @foreach ($body as $key => $value)
            <tr>
              @if (is_null($value->account))
                <td><input type="text" class="form-control" disabled></td>
              @else
                <td>
                  <select data-name="account" class="form-control selectpicker"
                    @if ($head->is_locked == 0) disabled @endif name="lines[{{ $value->id }}][account]">
                    <option value=""></option>
                    @foreach ($accounts as $item)
                      <option value="{{ $item['value'] }}"@if ($value->account == $item['value']) selected @endif>
                        {{ $item['name'] }}</option>
                    @endforeach
                  </select>
                </td>
              @endif
              @if (is_null($value->cardCode))
                <td><input type="text" class="form-control" disabled></td>
              @else
                <td>
                  <select data-name="cardCode" class="form-control selectpicker with-ajax-suppliers"
                    @if ($head->is_locked == 0) disabled @endif name="lines[{{ $value->id }}][cardCode]">
                    <option value="{{ $value->cardCode }}" selected>{{ $value->cardCode }} - {{ getPartnerName($value->cardCode) }}</option>
                  </select>
                </td>
              @endif
              @if (is_null($value->debit))
                <td><input type="text" class="form-control" disabled></td>
              @else
                <td>
                  <input type="text" name="lines[{{ $value->id }}][debit]" value="{{ $value->debit }}"
                    @if ($head->is_locked == 0) disabled @endif class="form-control maskMoney min-100" data-thousands=""
                    data-name="debit" data-decimal="." onchange="validateValues(this)">
                </td>
              @endif
              @if (is_null($value->credit))
                <td><input type="text" class="form-control" disabled></td>
              @else
                <td>
                  <input type="text" name="lines[{{ $value->id }}][credit]" data-name="credit"
                    @if ($head->is_locked == 0) disabled @endif class="form-control maskMoney min-100" data-thousands=""
                    value="{{ $value->credit }}" data-decimal="." onchange="validateValues(this)">
                </td>
              @endif
              <td>
                <select class="form-control selectpicker" @if ($head->is_locked == 0) disabled @endif
                  name="lines[{{ $value->id }}][costCenter]" data-name="costCenter">
                  <option value=""></option>
                  @foreach ($centroCusto as $item)
                    <option value="{{ $item['value'] }}" @if ($value->costCenter == $item['value']) selected @endif>
                      {{ $item['name'] }}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <select class="form-control selectpicker" @if ($head->is_locked == 0) disabled @endif
                  name="lines[{{ $value->id }}][costCenter2]" data-name="costCenter2">
                  <option value=""></option>
                  @foreach ($centroCusto2 as $item)
                    <option value="{{ $item['value'] }}" @if ($value->costCenter2 == $item['value']) selected @endif>
                      {{ $item['name'] }}</option>
                  @endforeach
                </select>
              </td>
              <td><select class="form-control selectpicker" @if ($head->is_locked == 0) disabled @endif
                  name="lines[{{ $value->id }}][project]" data-name="project">
                  <option value=""></option>
                  @foreach ($projects as $item)
                    <option value="{{ $item['value'] }}" @if ($value->project == $item['value']) selected @endif>
                      {{ $item['name'] }}</option>
                  @endforeach
                </select>
              </td>
            </tr>
            <?php
            $debit += $value->debit;
            $credit += $value->credit;
            ?>
          @endForeach
        </tbody>
        <tfoot>
          <tr>
            <th>Totais</th>
            <th></th>
            <th id="debit-total">{{ number_format($debit, 2, '.', ',') }}</th>
            <th id="credit-total">{{ number_format($credit, 2, '.', ',') }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
    @if (isset($head) && $head->codStatus != '4')
      <span class="btn btn-danger float-end mt-5" onclick="cancel()">Cancelar</span>
    @endif

    @if ($head->is_locked == 1 && auth()->user()->id == $head->idUser)
      <button class="btn btn-primary float-end mt-5" type="submit">Salvar</button>
    @endif
  </form>
@endsection

@section('scripts')
  <script type="text/javascript">

    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-suppliers')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    var table = $('#lines-table');
    var index = 0;

    @if (isset($head))
      function cancel() {
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
              window.location.href = "{{ route('journal-entry.canceled', $head->id) }}";
            }
          });
      }
    @endif

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

    function getCashFlow() {
      var type = $("#type option:selected").val();
      var select = $('#cashFlow');
      select.selectpicker('refresh');
      select.html('');
      $.get('/getCashFlowJE' + '/' + type, function(items) {
        for (var i = 0; i < items.length; i++) {
          select.append('<option value="' + items[i].id + '">' + items[i].value + "</option>");
          select.selectpicker('refresh');
        }
      });
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
      $('.with-ajax-suppliers').filter('.with-ajax-suppliers').ajaxSelectPicker(getAjaxSelectPickerOptions(
        "{{ route('inventory.items.suppliersSearch') }}"))
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
