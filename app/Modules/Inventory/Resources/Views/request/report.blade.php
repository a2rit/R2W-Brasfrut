@extends('layouts.main')
@section('title', 'Requisição interna')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Relatório</h5>
      </div>
      <div class="card-body">
        <form class="form d-flex-col justify-content-center align-items-center" role="form" method="POST"
          action="{{ route('inventory.request.gerar') }}">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">Código</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="code" placeholder='REC00001'>
            </div>
          </div>
          <div class="row mb-3">
            <label for="email" class="col-sm-3 col-form-label">Situação</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker" name="status">
                <option value=''> Selecione</option>
                <option value="{{ $requests::STATUS_WAIT_CLERK }}">Pendente</option>
                <option value="{{ $requests::STATUS_CLERK_LINK }}">Andamento</option>
                <option value="{{ $requests::STATUS_PARTIAL_ATTENDED }}">Parcial</option>
                <option value="{{ $requests::STATUS_REFUSED }}">Cancelado</option>
                <option value="{{ $requests::STATUS_RECEIVED }}">Recebido</option>
                <option value="{{ $requests::STATUS_WAIT_REQUESTER }}">Liberado</option>
                <option value="{{ $requests::STATUS_NFS_SAP }}">Comprado</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="ativo" class="col-sm-3 col-form-label">Solicitante:</label>
            <div class="col-sm-9">
              <select id="ativo" class="form-control selectpicker" name="solicitante">
                <option value=''>Selecione</option>
                @foreach ($solicitante as $key => $value)
                  <option value='{{ $value->id }}'>{{ $value->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="ativo" class="col-sm-3 col-form-label">Atendente:</label>
            <div class="col-sm-9">
              <select id="ativo" class="form-control selectpicker" name="atendente">
                <option value=''>Selecione</option>
                @foreach ($atendente as $key => $value)
                  <option value='{{ $value->id }}'>{{ $value->name }}</option>
                @endforeach
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
            <label for="group_id" class="col-sm-3 col-form-label">Tipo:</label>
            <div class="col-sm-9">
              <select name="tipo" class="form-control selectpicker" required>
                <option value="1" selected>Sintético</option>
                <option value="2">Analítico</option>
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