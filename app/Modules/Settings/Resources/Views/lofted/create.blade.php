@extends('layouts.main')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Cadastro de alçadas de aprovadores</h3>
  </div>
  <hr>

  <form action="{{ route('settings.lofted.save') }}" method="post" id="needs-validation"
    onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
    <input type="hidden" name="id" @if (isset($head)) value="{{ $head->id }}" @endif>

    <div class="row">
      <div class="col-md-3">
        <label>Nome:</label>
        <input type="text" class="form-control" @if (isset($head->name)) value="{{ $head->name }}" @endif
          name="name" required>
      </div>
      <div class="col-md-3">
        <label>Documento</label>
        <select class="form-control selectpicker" name="document" required>
          <option value=''>Selecione</option>
          @foreach ($doc as $key => $value)
            <option value="{{ $value['value'] }}" @if (isset($head) && $value['value'] == $head->docNum) selected @endif>
              {{ $value['name'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label>De:</label>
        <input type="text" class="form-control money"
          @if (isset($head->first)) value="{{ number_format($head->first, 2, ',', '.') }}" @else value="0,00" @endif
          name="first" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
      </div>
      <div class="col-md-3">
        <label>Até:</label>
        <input type="text" class="form-control money"
          @if (isset($head->last)) value="{{ number_format($head->last, 2, ',', '.') }}" @else value="0,00" @endif
          name="last" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-md-3">
        <label>Aprovadores:</label>
        <input type="number" class="form-control"
          @if (isset($head->quantity)) value="{{ (int) $head->quantity }}" @else value="0" @endif name="quantity"
          required>
      </div>
      <div class="col-md-3">
        <label>Centro de Custo:</label>
        <select class="form-control selectpicker" name="cost_center_id" required>
          <option value=''>Selecione</option>
          @foreach ($centroCusto as $keys => $values)
            <option value="{{ $values['value'] }}" @if (isset($head) && $values['value'] == $head->cost_center_id) selected @endif>
              {{ $values['value'] }} - {{ $values['name'] }}
            </option>
          @endForeach
        </select>
      </div>
      <div class="col-md-3">
        <label>Centro de Custo 2:</label>
        <select class="form-control selectpicker" name="cost_center_2_id">
          <option value=''>Selecione</option>
          @foreach ($centroCusto2 as $keys => $values)
            <option value="{{ $values['value'] }}" @if (isset($head) && $values['value'] == $head->cost_center_2_id) selected @endif>
              {{ $values['value'] }} - {{ $values['name'] }}
            </option>
          @endForeach
        </select>
      </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Salvar</button>
  </form>


  <div class="col-12 mt-5">
    <h3 class="header-page">Lista de alçadas de aprovadores</h3>
  </div>
  <hr>
  <div class="table-responsive mt-5">
    <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
      <thead>
        <tr>
          <th style="width: 5%">#</th>
          <th style="width: 15%">Nome</th>
          <th style="width: 15%">Documento</th>
          <th style="width: 10%">De</th>
          <th style="width: 10%">Até</th>
          <th style="width: 5%">Aprovadores</th>
          <th style="width: 15%">C. de Custo</th>
          <th style="width: 15%">C. de Custo 2</th>
          <th style="width: 10%">Opções</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($items))
          <?php $cont = 1; ?>
          @foreach ($items as $key => $value)
            <tr>
              <td>{{ $cont }}</td>
              <td>{{ $value->name }}</td>
              <td>
                {{ $value::DOCUMENTS_TEXTS[$value->docNum] }}
              </td>
              <td>{{ number_format($value->first, 2, ',', '.') }}</td>
              <td>{{ number_format($value->last, 2, ',', '.') }}</td>
              <td>{{ (int) $value->quantity }}</td>
              <td>
                @foreach ($centroCusto as $keys => $values)
                  @if ($values['value'] == $value['cost_center_id'])
                    {{ $values['value'] }} - {{ $values['name'] }}
                  @endif
                @endForeach
              </td>
              <td>
                @foreach ($centroCusto2 as $keys => $values)
                  @if ($values['value'] == $value['cost_center_2_id'])
                    {{ $values['value'] }} - {{ $values['name'] }}
                  @endif
                @endForeach
              </td>
              <td>
                <a href="{{ route('settings.lofted.edit', $value->id) }}" class='btn btn-primary btn-sm me-1'>Editar</a>
                <a href="{{ route('settings.lofted.remove', $value->id) }}" class='btn btn-danger btn-sm'>Remover</a>
              </td>
            </tr>
            <?php $cont++; ?>
          @endForeach
        @endif
      </tbody>
    </table>
  </div>

@endsection

@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));

    $(document).ready(function() {
      setMaskMoney();
    });

    function setMaskMoney(target = null) {
      $('.money').maskMoney({
        precision: 2,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });

      if (target) {
        $(target).trigger('mask.maskMoney');
      } else {
        $.each(
          $('input[class*="money"]'),
          function(index, element) {
            element = $(element);
            let value = element.val().length && !isFloat(element.val()) ? replaceToFloat(element.val()) : parseFloat(
              element.val());

            if (element.hasClass("money")) {
              element.val(value.format(2, ",", "."));
            }
            element.trigger("mask.maskMoney");
          }
        );
      }
    }

    function isFloat(value) {
      return typeof value === 'string' && /^-?\d*\.?\d+$/.test(value);
    }

    function replaceToFloat(number_string) {
      let replaced_value = number_string
        .replace(/[.]/gi, "")
        .replace(/[,]/gi, ".");
      return parseFloat(replaced_value) || 0;
    };

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
      $(event.target).select()
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }
  </script>
@endsection
