@extends('layouts.main')
@section('title', 'Requisições')
@section('content')
  <form action="{{ route('inventory.request.connectar') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando... Por favor aguarde!')">
    {!! csrf_field() !!}
    <div class="row">
      <div class="col-md-3">
        <label>Cod Web:</label>
        <input class="form-control" type="text" value="{{ $userData[0]->code }}" readonly name="codeRequest">
        <input class="form-control" type="hidden" value="{{ $userData[0]->id }}" name="id">
      </div>
      <div class="col-md-3">
        <label>Solicitante:</label>
        <input type="text" value="{{ $userData[0]->name }}" class="form-control" disabled>
      </div>
      <div class="col-md-3">
        <label>Data do Documento:</label>
        <input class="form-control" type="date" value="{{ $userData[0]->documentDate }}" disabled name="data">
      </div>
      <div class="col-md-3">
        <label>Data Necessária:</label>
        <input class="form-control" type="date" value="{{ $userData[0]->requiredDate }}" disabled name="data">
      </div>
      <div class="col-md-1 mb-3 mt-3">
        <label>Vincular</label>
        <input type="checkbox" name="vincular" class="form-check-input" style="width: 30px; height: 30px;" checked>
      </div>
    </div>
    <div class="table-responsive">
      <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 2%">#</th>
            <th style="width: 10%">Cod. SAP</th>
            <th style="width: 30%">Descrições</th>
            <th style="width: 10%">Qtd Estoque</th>
            <th style="width: 15%">Qtd Solicitada</th>
            <th style="width: 15%">Status</th>
          </tr>
        </thead>
        <tbody>
          @if (isset($productsData))
            <?php $cont = 1; ?>
            @foreach ($productsData as $key => $value)
              <tr>
                <td>{{ $cont }}</td>
                <td>{{ $value->codSAP }}</td>
                <td>{{ $value->itemname }}</td>
                <td>{{ round($value->inventory, 2) }}</td>
                <td>{{ $value->quantityRequest }}</td>
                <td>{{ $requests::TEXT_STATUS[$value->status] }}</td>
              </tr>
              <?php $cont++; ?>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    <div class="col-md-12 mt-3">
      <label>Observações</label>
      <div class="form-group">
        <textarea class="form-control" rows="2" disabled name="obsSolicitante">{{ $userData[0]->description }}</textarea>
      </div>
    </div>
    <button class="btn btn-primary float-end mt-5" type="submit">Salvar</button>
  </form>

@endsection


@section('scripts')
  <script>
    $('.dataTables-example').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });
  </script>
@endsection
