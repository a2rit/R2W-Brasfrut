@extends('layouts.main')
@section('title', 'Transferência')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Relatório</h5>
      </div>
      <div class="card-body">
        <form class="form d-flex-col justify-content-center align-items-center" role="form" method="POST"
        action="{{ route('inventory.transfer.gerar') }}">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="code" class="col-sm-3 col-form-label">Código</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="code" placeholder='REC00001'>
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
              <select class="form-control selectpicker" name="status">
                <option value=''>Selecione</option>
                <option value='1'>R2W-B1 (Aguardando)</option>
                <option value='2'>SAP-B1 (Sicronizado)</option>
                <option value='3'>SAP-B1 (Pendente)</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <label for="deposito_origem" class="col-sm-3 col-form-label">Depósito de Origem:</label>
            <div class="col-sm-9">
              <select id="deposito_origem" class="form-control selectpicker" name="deposito_origem">
                <option value=''>Selecione</option>
                @foreach ($depositos as $key => $value)
                  <option value='{{ $value['code'] }}'>{{ $value['value'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="deposito_dest" class="col-sm-3 col-form-label">Depósito de Destino:</label>
            <div class="col-sm-9">
              <select id="deposito_dest" class="form-control selectpicker" name="deposito_dest">
                <option value=''>Selecione</option>

                @foreach ($depositos as $key => $value)
                  <option value='{{ $value['code'] }}'>{{ $value['value'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="data_ini" class="col-sm-3 col-form-label">Data Inicial:</label>
            <div class="col-sm-9">
              <input id="data_ini" type="date" class="form-control" name="data_ini">
            </div>
          </div>
          <div class="row mb-3">
            <label for="data_fim" class="col-sm-3 col-form-label">Data Final:</label>
            <div class="col-sm-9">
              <input id="data_fim" type="date" class="form-control" name="data_fim">
            </div>
          </div>
          <div class="row mb-3">
            <label for="tipo" class="col-sm-3 col-form-label">Tipo:</label>
            <div class="col-sm-9">
              <select id="tipo" class="form-control selectpicker" name="tipo">
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
    var selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));

  </script>
@endsection
