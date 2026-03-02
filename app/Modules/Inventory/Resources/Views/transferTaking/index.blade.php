@extends('layouts.main')

@section('title', 'Pedido de transfêrencia')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de pedidos de transferência</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('inventory.transferTaking.create') }}">Pedido de transferência</a>
          </li>
          <li><a class="dropdown-item" href="{{ route('inventory.transferTaking.report') }}">Relatório</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <form action="{{ route('inventory.transferTaking.filter') }}" method="GET" id="needs-validation"
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
              <div class="col-md-3">
                <label>Código WEB - Transferência</Label>
                <input type="text" class="form-control" placeholder="TRA0001" name="codWEBTransf" autocomplete="off" value="{{ old('codWEBTransf') }}">
              </div>
              <div class="col-md-2">
                <label>Código WEB</Label>
                <input type="text" class="form-control" placeholder="TRK0001" name="codWEB" autocomplete="off" value="{{ old('codWEB') }}">
              </div>
              <div class="col-md-3">
                <label>Usuário</Label>
                <select class="form-control selectpicker with-ajax-users" name="name">
                  @if(!empty(old("name"))) <option value="{{ old("name") }}" selected>{{ getUserName(old("name")) }}</option>@endif
                </select>
              </div>
              <div class="col-md-2">
                <label>Status</Label>
                <select class="form-control selectpicker" name="status">
                  <option value='0' selected>Selecione</option>
                  <option value='5' {{ old('status') == '5' ? 'selected' : '' }}>PARCIAL</option>
                  <option value='6' {{ old('status') == '6' ? 'selected' : '' }}>RECEBIDO</option>
                  <option value='7' {{ old('status') == '7' ? 'selected' : '' }}>CANCELADO</option>
                  <option value='1' {{ old('status') == '1' ? 'selected' : '' }}>R2W-B1 (Aguardando)</option>
                  <option value='2' {{ old('status') == '2' ? 'selected' : '' }}>SAP-B1 (Sicronizado)</option>
                </select>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Data Inicial</label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Data Final</label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off">
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
  </form>
  <div class="table-responsive mt-5">
    {{ $items->links('pagination::bootstrap-4') }}
    <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
      <thead>
        <tr>
          <th style="width: 3%;">#</th>
          <th style="width: 10%;">Cod. SAP</th>
          <th>Cod. WEB Transferência</th>
          <th style="width: 10%;">Cod. WEB</th>
          <th style="width: 10%;">Data</th>
          <th style="width: 10%;">De</th>
          <th style="width: 10%;">Para</th>
          <th style="width: 20%;">Usuário</th>
          <th style="width: 10%;">Status</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($items))
          <?php $cont = 1; ?>
          @foreach ($items as $key => $value)
            <tr>
              <td>{{ $cont }}</td>
              <td>{{ $value->codSAPTransf }}</td>
              <td>
                @foreach ($value->transfers as $index => $transfer)
                  <a href="{{ route('inventory.transfer.edit', $transfer->id) }}" class="btn btn-primary btn-sm m-1">{{ $transfer->code }}</a>
                @endforeach
              </td>
              <td>{{ $value->code }}</td>
              <td>{{ formatDate($value->created_at) }} - {{ date('H:i', strtotime($value->created_at)) }}</td>
              <td>{{ $value->fromWarehouse }}</td>
              <td>{{ $value->toWarehouse }}</td>
              <td>{{ $value->name }}</td>
              <td>
                @if ($value->status == '4')
                  <a href="{{ route('inventory.transferTaking.edit', $value->id) }}"
                    class="btn btn-primary btn-sm w-100">ABERTO</a>
                @endif
                @if ($value->status == '1')
                  <a href="{{ route('inventory.transferTaking.edit', $value->id) }}"
                    class="btn btn-secondary btn-sm w-100">PARCIAL</a>
                @endif
                @if ($value->status == '3')
                  <a href="{{ route('inventory.transferTaking.edit', $value->id) }}"
                    class="btn btn-danger btn-sm w-100">CANCELADO</a>
                @endif
                @if ($value->status == '2')
                  <a href="{{ route('inventory.transferTaking.edit', $value->id) }}"
                    class="btn btn-success btn-sm w-100">RECEBIDO</a>
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
