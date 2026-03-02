@extends('layouts.main')

@section('title', 'Lançamento Contábil Manual')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lançamento Contábil Manual</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('journal-entry.create') }}">Lançamento</a>
          </li>
          {{-- <li><a class="dropdown-item" href="{{ route('journal-entry.index') }}">Relatório</a></li> --}}
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <form action="<?php echo route('journal-entry.filter'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
    <div class="accordion" id="filterAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-coreui-toggle="collapse"
            data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Filtros
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#filterAccordion">
          <div class="accordion-body">
            <div class="row">
              <div class="col-md-2">
                <label>Código SAP</Label>
                <input type="text" class="form-control" placeholder="I0001" name="codSAP" autocomplete="off">
              </div>
              <div class="col-md-2">
                <label>Código WEB</Label>
                <input type="text" class="form-control" placeholder="JE0001" name="codWEB" autocomplete="off">
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Inicial</label>
                  <input type="text" name="data_fist" class="form-control datepicker" placeholder="Inicial"
                    autocomplete="off">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Final</label>
                  <input type="text" name="data_last" class="form-control datepicker" placeholder="Final" autocomplete="off">
                </div>
              </div>
              <div class="col-md-4">
                <label>Usuário</Label>
                <input type="text" name="name" class="form-control" placeholder="R2W" autocomplete="off">
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option selected></option>
                    <option class="btn-success" value='2'>SAP-B1 (Sicronizado)</option>
                    <option class="btn-danger" value='4'>SAP-B1 (Cancelado)</option>
                  </select>
                </div>
                <div class="row mt-2">
                  <div class="form-group d-grid justify-content-end">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
                  </div>
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
          <th style="width: 5%;">#</th>
          <th style="width: 10%;">Cod. SAP</th>
          <th style="width: 10%;">Cod. WEB</th>
          <th style="width: 15%;">Data</th>
          <th style="width: 50%;">Usuário</th>
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
              <td>{{ formatDate($value->posting_date) }}</td>
              <td>{{ $value->name }}</td>
              <td>
                @if ($value->docStatus == 'CANCELADO')
                  <a href="{{ route('journal-entry.edit', $value->id) }}" class="btn btn-danger btn-sm w-100">SAP-B1</a>
                @endif
                @if ($value->docStatus == 'AGUARDANDO')
                  <a href="{{ route('journal-entry.edit', $value->id) }}" class="btn btn-warning btn-sm w-100">R2W-B1</a>
                @endif
                @if ($value->docStatus == 'SINCRONIZADO')
                  <a href="{{ route('journal-entry.edit', $value->id) }}" class="btn btn-success btn-sm w-100">SAP-B1</a>
                @endif
                @if ($value->docStatus == 'ERROR')
                  <a href="{{ route('journal-entry.edit', $value->id) }}" class="btn btn-danger btn-sm w-100">SAP-B1</a>
                @endif
                @if ($value->docStatus == 'ATUALIZANDO')
                  <a href="{{ route('journal-entry.edit', $value->id) }}" class="btn btn-warning btn-sm w-100">R2W-B1</a>
                @endif
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
  $(".selectpicker").selectpicker(selectpickerConfig)
    
    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });
  </script>
@endsection
