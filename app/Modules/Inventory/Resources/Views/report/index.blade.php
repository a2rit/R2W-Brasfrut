@extends('layouts.main')
@section('title', 'Relatórios de estoque')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Relatório</h5>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="">Categoria</label>
          <select id="report_type" class="form-control selectpicker">
            <option value="1">Relatório de contagem por grupo e listagem para contagem</option>
            <!--<option value="2">Relatório de perdas</option>-->
          </select>
        </div>
        <hr>
        <form class="form-horizontal" id="relatorio_contagem" role="form" method="get"
          action="{{ route('inventory.report.gerar') }}">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="code" class="col-sm-3 col-form-label">Depósito</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="warehouse" required>
                <option value="">Selecione</option>
                @foreach ($warehouses as $key => $value)
                  <option value="{{ $value->WhsCode }}">{{ $value->WhsName }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">Grupo</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="group">
                <option value="">Selecione</option>
                @foreach ($groups as $key => $value)
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
            <label for="status" class="col-sm-3 col-form-label">Tipo</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="type" required>
                <option value="1" selected>Listagem Para Contagem</option>
                <option value="2">Contagem por Grupo</option>
              </select>
            </div>
          </div>
          <div class="row mb-3" style="display: none;">
            <label for="group_id" class="col-sm-3 col-form-label">Formato</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="format" required>
                <option value="1" selected>PDF</option>
                <option value="2">Excel</option>
              </select>
            </div>
          </div>
          <div class="row text-center">
            <button type="submit" class="btn btn-primary d-grid gap-2 col-2 mx-auto">Gerar</button>
          </div>
        </form>

        <form class="form-horizontal" id="relatorio_perdas" role="form" method="get"
          action="{{ route('inventory.report.relatorioPerdas') }}" style="display: none;">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">De</label>
            <div class="col-sm-9">
              <input type="date" class="form-control" name="initialDate" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Até</label>
            <div class="col-sm-9">
              <input type="date" class="form-control" name="lastDate" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Código do item</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="itemCode">
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Grupo</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="group">
                <option value="">Selecione</option>
                @foreach ($groups as $key => $value)
                  <option value="{{ $value->ItmsGrpCod }}">{{ $value->ItmsGrpNam }}</option>
                @endForeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Depósito</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="warehouse">
                <option value="">Selecione</option>
                @foreach ($warehouses as $key => $value)
                  <option value="{{ $value->WhsCode }}">{{ $value->WhsName }}</option>
                @endForeach
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
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);

    $('#report_type').change((event) => {

      if ($(event.target).find(':selected').val() == '1') {
        $('#relatorio_contagem').show();
        $('#relatorio_perdas').hide();
      } else if ($(event.target).find(':selected').val() == '2') {
        $('#relatorio_perdas').show();
        $('#relatorio_contagem').hide();
      }
    });

    $('select[name="type"]').change((event) => {
      if ($(event.target).find(':selected').val() == '1') {
        $('select[name="format"]').closest('.row').hide();
      } else if ($(event.target).find(':selected').val() == '2') {
        $('select[name="format"]').closest('.row').show();
      }

    });
  </script>
@endsection
