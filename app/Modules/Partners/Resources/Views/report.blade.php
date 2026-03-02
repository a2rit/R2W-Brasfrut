@extends('layouts.main')
@section('title', 'Relatórios de Parceiro de Negócio')

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
            <option value="1">Relatório Sintético e Analítico (Contratos)</option>
          </select>
        </div>
        <hr>
        <form id="relatorio-sintetico" value="1" role="form" method="GET" action="{{ route('partners.relatory') }}">
          <div class="row mb-3">
            <label for="code" class="col-sm-3 col-form-label">N° do contrato</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="contractNumber" placeholder='102030'>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-3 control-label">Fornecedor</label>
            <div class="col-md-9">
              <select class="form-control selectpicker with-ajax-suppliers" name="cardCode"></select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-3 control-label">Status</label>
            <div class="col-md-9">
              <select class="form-control selectpicker" name="partnerStatus" id="partnerStatus">
                <option value="">Todos</option>
                <option value="Y">Ativo</option>
                <option value="N">Inativo</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-3 control-label">Grupo</label>
            <div class="col-md-9">
              <select class="form-control selectpicker" name="partnerGroup" id="partnerGroup">
                <option value="">Selecione</option>
                @foreach ($groups as $group)
                  <option value="{{ $group['value'] }}">{{ $group['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-3 control-label">Característica</label>
            <div class="col-md-9">
              <select class="form-control selectpicker" name="partnerCharacteristic" id="partnerCharacteristic">
                <option value="">Selecione</option>
                @foreach ($properties as $index => $property)
                  <option value="QryGroup{{ $property['value'] }}">{{ $property['name']}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 col-form-label">Data Inicial:</label>
            <div class="col-sm-9">
              <input id="initialDate" type="date" class="form-control" name="initialDate">
            </div>
          </div>
          <div class="row mb-3">
            <label for="group_id" class="col-sm-3 col-form-label">Data Final:</label>
            <div class="col-sm-9">
              <input id="lastDate" type="date" class="form-control" name="lastDate">
            </div>
          </div>
          <div class="row mb-3">
            <label for="tipo" class="col-sm-3 col-form-label">Tipo</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="tipo" id="tipo">
                <option value='1' selected>Sintético</option>
                <option value='2'>Analítico</option>
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

      $('form').submit(function(e) {
        $(e.target).append($(`<input type="hidden" name="category" value="${$(e.target).attr('value')}">`))
      })
    </script>
  @endsection
