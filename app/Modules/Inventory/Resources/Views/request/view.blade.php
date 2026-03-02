@extends('layouts.main')
@section('title', 'Requisições')
@section('content')
  @if (isset($userData[0]->is_locked) && $userData[0]->is_locked == 1)
    <div class="alert alert-danger" role="alert">
      {{ $userData[0]->message }}
    </div>
  @endif
  <div class="col-12">
    <h3 class="header-page">Requisição interna - detalhes</h3>
  </div>
  
  <hr>
  <div class="row">
    <div class="col-md-2">
      <label>Cod Web:</label>
      <input class="form-control locked" type="text" value="{{ $userData[0]->code }}" readonly name="codeRequest">
    </div>
    <div class="col-md-2">
      <label>Cod SAP:</label>
      <input class="form-control" type="text" value="{{ $userData[0]->codSAP }}" disabled>
    </div>
    <div class="col-md-2">
      <label>Solicitante:</label>
      <input type="text" value="{{ $userData[0]->name }}" class="form-control" disabled>
    </div>
    <div class="col-md-2">
      <label>Data do documento:</label>
      <input class="form-control" type="text" value="{{ formatDate($userData[0]->documentDate) }}" disabled
        name="data">
    </div>
    <div class="col-md-2">
      <label>Data Necessária:</label>
      <input class="form-control" type="text" value="{{ formatDate($userData[0]->requiredDate) }}" disabled
        name="data">
    </div>
    <div class="col-md-2">
      <label>Status:</label>
      <input class="form-control" type="text" value="{{ $requests::TEXT_STATUS[$userData[0]->codStatus] }}" disabled>
    </div>
  </div>
  @php
    $purchase_requests = $userData[0]->purchase_requests;
  @endphp
  @if (count($purchase_requests) > 0)
    <div class="container mt-3">
      <p class="fw-bolder">Solicitações de compras</p>
      <div class="btn-group">
        @foreach ($userData[0]->purchase_requests as $purchase_request)
          <a href="{{ route('purchase.request.read', $purchase_request->id) }}"
            class="btn btn-primary btn-sm ms-1">{{ $purchase_request->code }}</a>
        @endforeach
      </div>
    </div>
  @endif
  <hr>
  <div class="row">
    <div class="table-responsive">
      <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 2%">#</th>
            <th style="width: 30%">Descrições</th>
            <th style="width: 15%">Qtd. Solicitada</th>
            <th style="width: 10%">Qtd. Atendida</th>
          </tr>
        </thead>
        <tbody>
          @if (isset($productsData))
            <?php $cont = 1; ?>
            @foreach ($productsData as $key => $value)
              <tr>
                <td>{{ $cont }}</td>
                <td style="max-width: 16em;">
                  <div class="d-flex flex-row" style="max-width: 100%;">
                    <a class="text-warning" href="{{ route('inventory.items.edit', $value->codSAP) }}" target="_blank">
                      <svg class="icon icon-lg">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                      </svg>
                    </a>
                    <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                      title="{{ $value->codSAP }} - {{ $value->itemname }}">{{ $value->codSAP }} -
                      {{ $value->itemname }}</span>
                  </div>
                </td>
                <td>{{ $value->quantityRequest }}</td>
                <td>{{ $value->quantityServed }}</td>
              </tr>
              <?php $cont++; ?>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    <div class="row mt-4">
      <div class="col-md-6">
        <label>Observações do Solicitante</label>
        <div class="form-group">
          <textarea class="form-control" rows="2" disabled name="obsevacoes">{{ $userData[0]->description }}</textarea>
        </div>
      </div>
      <div class="col-md-6">
        <label>Observações do Atendente</label>
        <div class="form-group">
          <textarea class="form-control" rows="2" disabled name="obsevacoes">{{ $userData[0]->description2 }}</textarea>
        </div>
      </div>
    </div>
  </div>
</form>
<div class="col-12 mt-5">
  @if ($userData[0]->codStatus == $userData[0]::STATUS_LINK && empty($userData[0]->codSAP))
    <a href="{{ route('inventory.request.cancel', $userData[0]->id) }}" class="btn btn-danger float-end">Cancelar</a>
  @endif
</div>

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
