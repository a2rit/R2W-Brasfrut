@extends('layouts.main')

@section('title', 'Empréstimo de estoque')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de empréstimos de ferramentas</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('inventory.stockloan.create') }}">Novo empréstimo</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <form action="{{ route('inventory.stockloan.filter') }}" method="GET" id="needs-validation" enctype="multipart/form-data">
  <div class="accordion" id="filterAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseOne"
          aria-expanded="true" aria-controls="collapseOne">
          Filtros
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
        data-coreui-parent="#filterAccordion">
        <div class="accordion-body">
          <div class="row">
            <div class="col-md-3">
              <label>Código SAP</Label>
              <input type="text" class="form-control" placeholder="I00001" name="codSAP" autocomplete="off"
                value="{{ old('codSAP') }}">
            </div>
            <div class="col-md-3">
              <label>Código WEB</Label>
              <input type="text" class="form-control" placeholder="IN00001" name="codWEB" autocomplete="off"
                value="{{ old('codWEB') }}">
            </div>
            <div class="col-md-3">
              <label>Status</Label>
              <select class="form-control selectpicker" name="status">
                <option value="" selected>Selecione</option>
                <option value='1' {{ old('status') == '1' ? 'selected' : '' }}>ABERTO</option>
                <option value='2' {{ old('status') == '2' ? 'selected' : '' }}>CANCELADO</option>
                <option value='3' {{ old('status') == '3' ? 'selected' : '' }}>PENDENTE</option>
                <option value='4' {{ old('status') == '4' ? 'selected' : '' }}>FECHADO</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Usuário</Label>
              <select class="form-control selectpicker with-ajax-users" name="name">
                @if(!empty(old("name"))) <option value="{{ old("name") }}" selected>{{ getUserName(old("name")) }}</option>@endif
              </select>
            </div>
            <div class="col-md-3">
              <label>Solicitado</Label>
              <select class="form-control selectpicker with-ajax-users" name="nameRequester">
                @if(!empty(old("nameRequester"))) <option value="{{ old("nameRequester") }}" selected>{{ getUserName(old("nameRequester")) }}</option>@endif
              </select>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Data Inicial</label>
                <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off"
                  value="{{ old('data_fist') }}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Data Final</label>
                <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off"
                  value="{{ old('data_last') }}">
              </div>
            </div>
          </div>
            <div class="row mt-2">
              <div class="col-12">
                <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar formulário</button>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</form>
  <div class="table-responsive mt-5">
    @if (isset($items))
    {{ $items->links('pagination::bootstrap-4') }}
    @endif
    <table id="table" class="table table-default table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 10%;">Cod. SAP</th>
          <th style="width: 8%;">Cod. WEB</th>
          <th style="width: 8%;">Data</th>
          <th style="width: 11%;">De</th>
          <th style="width: 11%;">Para</th>
          <th style="width: 12%;">Usuário</th>
          <th style="width: 12%;">Solicitado</th>
          <th style="width: 8%;">Status</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($items))
          <?php $cont = 1; ?>
          @foreach ($items as $key => $value)
            <tr>
              <td>{{ $cont }}</td>
              <td>{{ $value->codSAP }}</td>
              <td>{{ $value->code }}</td>
              <td>{{ formatDate($value->taxDate) }}</td>
              <td>{{ $value->fromWarehouse }}</td>
              <td>{{ $value->toWarehouse }}</td>
              <td>{{ $value->user }}</td>
              <td>
                {{ $value->requester }}
              </td>
              <td>
                @if ($value->status == $value::STATUS_OPEN)
                  <a href="{{ route('inventory.stockloan.edit', $value->id) }}" class="btn btn-primary btn-sm w-100">ABERTO</a>
                @endif
                @if ($value->status == $value::STATUS_PENDING)
                  <a href="{{ route('inventory.stockloan.edit', $value->id) }}"
                    class="btn btn-secondary btn-sm w-100">PENDENTE</a>
                @endif
                @if ($value->status == $value::STATUS_CANCEL)
                  <a href="{{ route('inventory.stockloan.edit', $value->id) }}"
                    class="btn btn-danger btn-sm w-100">CANCELADO</a>
                @endif
                @if ($value->status == $value::STATUS_CLOSED)
                  <a href="{{ route('inventory.stockloan.edit', $value->id) }}" class="btn btn-success btn-sm w-100">FECHADO</a>
                @endif
              </td>
            </tr>
            <?php $cont++; ?>
          @endForeach
        @endif
      </tbody>
    </table>
    @if (isset($items))
    {{ $items->links('pagination::bootstrap-4') }}
    @endif
  </div>

@endsection
@section('scripts')
  <script type="text/javascript">
    $("#table").css("width", "100%");
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });
  </script>
@endsection
