@extends('layouts.main')
@section('title', 'Logs de erros')
@section('content')

  <form action="<?php echo route('settings.logs.errors.filter'); ?>" method="post" id="needs-validation" onsubmit="waitingDialog.show('Carregando...')">
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
            <div class="row mt-2">
              <div class="col-md-2">
                <label>Código Erro</Label>
                <input type="text" class="form-control" placeholder="E0001" name="code" autocomplete="off">
              </div>
              <div class="col-4">
                <label>Usuário</Label>
                <input type="text" class="form-control" placeholder="Username"name="name" autocomplete="off">
              </div>
              <div class="col-3">
                <div class="form-group">
                  <label>Data Inicial</label>
                  <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off">
                </div>
              </div>
              <div class="col-3">
                <div class="form-group">
                  <label>Data Final</label>
                  <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off">
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar
                  formulário</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="table-responsive mt-5">
    <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
      <thead>
        <tr>
          <th>#</th>
          <th>Código</th>
          <th>Descrição</th>
          <th>Data</th>
          <th>Error</th>
          <th>Usuário</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($items))
          <?php $cont = 1; ?>
          @foreach ($items as $key => $value)
            <tr>
              <td>{{ $cont }}</td>
              <td>{{ $value->value }}</td>
              <td>{{ $value->operation }}</td>
              <td>{{ formatDate($value->created_at) }}</td>
              <td>{{ $value->message }}</td>
              <td>{{ $value->name }}</td>
            </tr>
            <?php $cont++; ?>
          @endForeach
        @endif
      </tbody>
    </table>
  </div>
@endsection
@section('scripts')
  <script type="text/javascript">
    $("#table").css("width", "100%");
    let table = $("#table").DataTable({
      responsive: true,
      lengthMenu: [50, 100, 150],
      language: dataTablesPtBr
    });
  </script>
@endsection
