@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Porcionamentos</h3>
  </div>
  <hr>

  <form action="<?php echo route('porcionamento.listar'); ?>" method="get" id="needs-validation" class="mb-5"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
    {{-- {!! csrf_field() !!} --}}
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
              <div class="col-md-4">
                <label>Fornecedor</Label>
                <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body"
                  name="fornecedor"></select>
              </div>
              <div class="col-md-4">
                <label>Item</Label>
                <input type="text" class="form-control" placeholder="00001" name="item">
              </div>
              <div class="col-md-2">
                <label>Cód. Entrada</Label>
                <input type="text" class="form-control" name="codeEntrada">
              </div>
              <div class="col-md-2">
                <label>Cód. Saída</Label>
                <input type="text" class="form-control" name="codeSaida">
              </div>
            </div>
            <div class="row pt-2">
              <div class="col-md-2">
                <label>Cod. Reavaliação</Label>
                <input type="text" name="codeReavaliacao" class="form-control">
              </div>
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
  </form>

  <div class="table-responsive mt-4">
    {{ $porcionamentos->links('pagination::bootstrap-4') }}
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th>Fornecedor</th>
          <th>Item</th>
          <th>Código de entrada</th>
          <th>Código de saída</th>
          <th>Código de reavaliação</th>
          <th>Ver</th>
          <th>Excluir</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($porcionamentos as $porcionamento)
          <tr>
            <td>{{ $porcionamento->nome_fornecedor }}</td>
            <td>{{ $porcionamento->nome_item }}</td>
            <td>{{ $porcionamento->cod_entrada }}</td>
            <td>{{ $porcionamento->cod_saida }}</td>
            <td>{{ $porcionamento->cod_reavaliacao }}</td>
            <td><a href="{{ route('porcionamento.ver', ['id' => $porcionamento->id]) }}"><button
                  class="btn btn-primary">Ver</button></a></td>
            <td><a
                onclick="if(confirm('Excluir?')){window.location.href = '{{ route('porcionamento.excluir', ['id' => $porcionamento->id]) }}';}"><button
                  class="btn btn-danger">Excluir</button></a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $porcionamentos->links('pagination::bootstrap-4') }}
  @endsection

  @section('scripts')
    <script type="text/javascript">
    
      let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
      selectpicker.filter('.with-ajax-partner')
        .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    </script>
  @endsection
