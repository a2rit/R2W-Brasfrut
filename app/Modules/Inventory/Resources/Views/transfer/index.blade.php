@extends('layouts.main')

@section('title', 'Transfêrencia de estoque')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de transferências</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('inventory.transfer.create') }}">Transferência</a>
          </li>
          <li><a class="dropdown-item" href="{{ route('inventory.transfer.report') }}">Relatório</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <form action="{{ route('inventory.transfer.filter') }}" method="GET" id="needs-validation"
    enctype="multipart/form-data">
    {!! csrf_field() !!}
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
              <div class="col-md-2">
                <label>Código SAP</Label>
                <input type="text" class="form-control" placeholder="1" name="codSAP" autocomplete="off" value="{{ old('codSAP') }}">
              </div>
              <div class="col-md-2">
                <label>Código WEB</Label>
                <input type="text" class="form-control" placeholder="TRA0001" name="codWEB" autocomplete="off" value="{{ old('codWEB') }}">
              </div>
              <div class="col-md-5">
                <label>Usuário</Label>
                <select class="form-control selectpicker with-ajax-users" name="name">
                  @if(!empty(old("name"))) <option value="{{ old("name") }}" selected>{{ getUserName(old("name")) }}</option>@endif
                </select>
              </div>
              <div class="col-md-3">
                <label>Status</Label>
                <select class="form-control selectpicker" name="status">
                  <option selected>Selecione</option>
                  <option value='1' {{ old('status') == '1' ? 'selected' : '' }}>R2W-B1 (Aguardando)</option>
                  <option value='2' {{ old('status') == '2' ? 'selected' : '' }}>SAP-B1 (Sicronizado)</option>
                  <option value='3' {{ old('status') == '3' ? 'selected' : '' }}>SAP-B1 (Pendente)</option>
                  <option value='5' {{ old('status') == '5' ? 'selected' : '' }}>R2W-B1 (Cancelado)</option>
                  <option value='4' {{ old('status') == '4' ? 'selected' : '' }}>Erro</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Inicial</label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off" value="{{ old('data_fist') }}">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Final</label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off" value="{{ old('data_last') }}">
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="form-group d-grid justify-content-end">
                <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="table-responsive mt-5">
      {{ $items->links('pagination::bootstrap-4') }}
      <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 10%;">Cod. SAP</th>
            <th style="width: 10%;">Cod. WEB</th>
            <th style="width: 10%;">Data</th>
            <th style="width: 15%;">De</th>
            <th style="width: 15%;">Para</th>
            <th>Usuário</th>
            <th style="width: 10%;">Status</th>
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
                <td>{{ $value->name }}</td>
                <td>
                  @if ($value->status == '4')
                    <a onclick="loadItem('{{ $value->id }}')" class="btn btn-danger btn-sm w-100">CANCELADO</a>
                  @elseif($value->is_locked == '0' && is_null($value->message))
                    <a onclick="loadItem('{{ $value->id }}')" class="btn btn-primary btn-sm w-100">AGUARDANDO</a>
                  @elseif($value->is_locked == '0' && !is_null($value->message))
                    <a onclick="loadItem('{{ $value->id }}')" class="btn btn-success btn-sm w-100">SINCRONIZADO</a>
                  @elseif($value->dbUpdate == '1' && $value->is_locked == '0')
                  <a onclick="loadItem('{{ $value->id }}')" class="btn btn-info btn-sm w-100">ATUALIZANDO</a>
                  @elseif($value->is_locked == '1')
                    <a onclick="loadItem('{{ $value->id }}')" class="btn btn-danger btn-sm w-100">ERRO</a>
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
  </form>

@endsection
@section('scripts')
  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));
    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    function loadItem(id) {
      window.location.href = "{{ route('inventory.transfer.edit') }}" + '/' + id;
    }
  </script>
@endsection
