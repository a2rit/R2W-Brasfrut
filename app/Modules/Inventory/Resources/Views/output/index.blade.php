@extends('layouts.main')

@section('title', 'Saida de Mercadorias')

@section('content')

<div class="row">
  <div class="col-md-10">
    <h3 class="header-page">Lista de saídas de mercadorias</h3>
  </div>
  <div class="col-md-2">
    <div class="dropdown float-end">
      <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
        <svg class="icon">
          <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
        </svg> Novo
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('inventory.output.create') }}">Saída</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('inventory.output.report') }}">Relatório</a></li>
      </ul>
    </div>
  </div>
</div>
<hr>
<div class="row mt-4">
  <form action="{{ route('inventory.output.filter') }}" method="post" id="needs-validation" enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
    <div class="accordion" id="filterAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Filtros
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#filterAccordion">
          <div class="accordion-body">
            <div class="row">
              <div class="col-md-2">
                <label>Cod. SAP</Label>
                <input type="text" class="form-control" placeholder="I00001" name="codSAP" autocomplete="off" value="{{ old('codSAP') }}">
              </div>
              <div class="col-md-2">
                <label>Cod. WEB</Label>
                <input type="text" class="form-control" placeholder="IN0001" name="codWEB" autocomplete="off" value="{{ old('codWEB') }}">
              </div>
              <div class="col-md-4">
                <label>Usuário</Label>
                <select class="form-control selectpicker with-ajax-users" name="usuario">
                  @if(!empty(old("usuario"))) <option value="{{ old("usuario") }}" selected>{{ getUserName(old("usuario")) }}</option>@endif
                </select>
              </div>
              <div class="col-md-2">
                <label>Data Inicial</Label>
                <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off" value="{{ old('data_fist') }}">
              </div>
              <div class="col-md-2">
                <label>Data Final</Label>
                <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off" value="{{ old('data_last') }}">
              </div>
              <div class="col-md-3">
                <label>Status</Label>
                <select class="form-control selectpicker" name="status">
                  <option value='0' selected>Selecione</option>
                  <option value='1' {{ old('status') == '1' ? 'selected' : '' }}>R2W-B1 (Aguardando)</option>
                  <option value='2' {{ old('status') == '2' ? 'selected' : '' }}>SAP-B1 (Sicronizado)</option>
                  <option value='3' {{ old('status') == '3' ? 'selected' : '' }}>SAP-B1 (Pendente)</option>
                </select>
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
</div>
</form>
<div class="table-responsive mt-5">
  {{ $items->links('pagination::bootstrap-4') }}
  <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
    <thead>
      <tr>
        <th>#</th>
        <th>Cod. SAP</th>
        <th>Cod. WEB</th>
        <th>Usuario</th>
        <th>Data</th>
        <th>Status</th>
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
        <td>{{ $value->name }}</td>
        <td>{{ formatDate($value->TaxDate) }}</td>
        <td>
          @if ($value->is_locked != 1 && is_null($value->message))
          <a href="{{ route('inventory.output.edit', $value->id) }}" class="btn btn-warning btn-sm w-100">R2W-B1</a>
          @endif
          @if ($value->is_locked != 1 && !is_null($value->message))
          <a href="{{ route('inventory.output.edit', $value->id) }}" class="btn btn-success btn-sm w-100">SAP-B1</a>
          @endif
          @if ($value->is_locked == 1)
          <a href="{{ route('inventory.output.edit', $value->id) }}" class="btn btn-danger btn-sm w-100">SAP-B1</a>
          @endif
        </td>
      </tr>
      <?php $cont++; ?>
      @endForeach
      @endif
    </tbody>
  </table>
  {{ $items->links('pagination::bootstrap-4') }}
</div>

@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
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