@extends('layouts.main')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Relatório</h5>
      </div>
      <div class="card-body">
        <div class="form-group col-12">
          <label for="">Categoria</label>
          <select id="report_type" class="form-control" name="category">
            <option value="1">Relatório Sintético e Analítico</option>
            <option value="2">Relatório Análise de compras</option>
          </select>
        </div>
        <hr>
        <form id="relatorio-sintetico" value="1" role="form" method="POST"
          action="{{ route('purchase.ap.invoice.gerar') }}">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="code" class="col-sm-3 col-form-label">Código</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="code" placeholder='II00001'>
            </div>
          </div>
          <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">Usuário</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker with-ajax-users" name="name"></select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
              <select class="form-control" name="status">
                <option value=''> Selecione</option>
                <option value="{{ $incoing_invoice::STATUS_OPEN }}">Aberto</option>
                <option value="{{ $incoing_invoice::STATUS_CLOSE }}">Fechado</option>
                <option value="{{ $incoing_invoice::STATUS_CANCEL }}">Cancelado</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 col-form-label">Data Inicial:</label>
            <div class="col-sm-9">
              <input id="group_id" type="date" class="form-control" name="data_ini">
            </div>
          </div>
          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 col-form-label">Data Final:</label>
            <div class="col-sm-9">
              <input id="group_id" type="date" class="form-control" name="data_fim">
            </div>
          </div>
          <div class="row mb-3">
            <label for="tipo" class="col-sm-3 col-form-label">Tipo</label>
            <div class="col-sm-9">
              <select class="form-control" name="tipo">
                <option value='1' selected>Sintético</option>
                <option value='2'>Analítico</option>
              </select>
            </div>
          </div>
          <div class="row text-center">
            <button type="submit" class="btn btn-primary d-grid gap-2 col-2 mx-auto">Gerar</button>
          </div>
        </form>

        <form id="relatorio-analise" value="2" role="form" method="POST"
          action="{{ route('purchase.ap.invoice.gerar') }}" style="display: none;">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label class="col-sm-3 control-label">Fornecedor</label>
            <div class="col-md-9">
              <select class="form-control selectpicker with-ajax-suppliers" name="partner"></select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="code" class="col-sm-3 control-label">Depósitos</label>
            <div class="col-md-9">
              <select class="form-control selectpicker" name="warehouse">
                <option value="">Selecione</option>
                @foreach ($warehouses as $key => $value)
                  <option value="{{ $value['WhsCode'] }}">{{ $value['WhsName'] }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="code" class="col-sm-3 control-label">Item de</label>
            <div class="col-md-9">
              <select class="form-control selectpicker with-ajax-item" name="item"></select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="code" class="col-sm-3 control-label">Item até</label>
            <div class="col-md-9">
              <select class="form-control selectpicker with-ajax-item" name="untilItem"></select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="code" class="col-sm-3 control-label">Grupos de item</label>
            <div class="col-md-9">
              <select class="form-control selectpicker" name="group">
                <option value="">Selecione</option>
                @foreach ($itemGroups as $key => $value)
                  <option value="{{ $value->ItmsGrpCod }}">{{ $value->ItmsGrpNam }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">Características</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="property">
                <option value="">Selecione</option>
                @foreach ($itemProperties as $key => $itemProperty)
                  <option value="QryGroup{{ $itemProperty->value }}">{{ $itemProperty->name }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 control-label">Data Inicial:</label>
            <div class="col-md-9">
              <input id="group_id" type="date" class="form-control" name="data_ini" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 control-label">Data Final:</label>
            <div class="col-md-9">
              <input id="group_id" type="date" class="form-control" name="data_fim" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="tipo" class="col-sm-3 control-label">Formato</label>
            <div class="col-md-9">
              <select class="form-control" name="tipo">
                <option value='1' selected>PDF</option>
                <option value='2'>Excel</option>
              </select>
            </div>
          </div>
          <div class="row text-center">
            <button type="submit" class="btn btn-primary d-grid gap-2 col-2 mx-auto">Gerar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endsection

  @section('scripts')
    <script type="text/javascript">
      var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

      selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
        "{{ route('administration.user.get') }}"));
      selectpicker.filter('.with-ajax-suppliers')
        .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));
      selectpicker.filter('.with-ajax-item')
        .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.itemsSearch') }}"));

      $('#report_type').change((event) => {

        if ($(event.target).find(':selected').val() == '1') {
          $('#relatorio-sintetico').show();
          $('#relatorio-analise').hide();
        } else if ($(event.target).find(':selected').val() == '2') {
          $('#relatorio-analise').show();
          $('#relatorio-sintetico').hide();
        }
      })

      $('form').submit(function(e) {
        $(e.target).append($(`<input type="hidden" name="category" value="${$(e.target).attr('value')}">`))
      })
    </script>
  @endsection
