@extends('layouts.main')

@section('content')
  <div class="container-fluid w-50">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title text-center mt-2">Relatório</h5>
      </div>
      <div class="card-body">
        <form class="form d-flex-col justify-content-center align-items-center" role="form" method="POST"
          action="{{ route('purchase.order.gerar') }}">
          {{ csrf_field() }}
          <div class="row mb-3">
            <label for="code" class="col-sm-3 col-form-label">Código</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="code" placeholder='PO00001'>
            </div>
          </div>
          <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">Usuário</label>
            <div class="col-sm-9">
              <select class="form-control selectpicker with-ajax-users" data-width="100%" data-live-search="true"
                data-size="7" name="name">
                @if (isset($head->nameApproverUser))
                  <option value="{{ $head->approverUser }}">{{ $head->nameApproverUser }}</option>
                @endif
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="status" class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
              <select class="form-control" name="status">
                <option value=' '> Selecione</option>
                <option value="{{ $purchase_order::STATUS_OPEN }}">Aberto</option>
                <option value="{{ $purchase_order::STATUS_CLOSE }}">Fechado</option>
                <option value="{{ $purchase_order::STATUS_CANCEL }}">Cancelado</option>
                <option value="{{ $purchase_order::STATUS_PENDING }}">Pendente</option>
                <option value="{{ $purchase_order::STATUS_REPROVE }}">Reprovado</option>
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
      </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));

  </script>
@endsection
