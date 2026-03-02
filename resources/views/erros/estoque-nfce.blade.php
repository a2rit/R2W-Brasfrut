@extends('layouts.main')
@section('title', 'NFC-e Erros')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Itens com estoque insuficiente NFC-e</h3>
  </div>
  <hr>

  <form action="{{ route('erros.estoque-nfce') }}" method="GET">
    <div class="row">

      <div class="col-md-3">
        <label for="">Ponto de venda</label>
        <select class="form-control selectpicker" name="pv_id">
          <option></option>
          @foreach ($ponto_venda as $value)
            <option value="{{ $value->id }}">{{ $value->nome }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label for="">Item</label>
        <input type="text" class="form-control" name="item">
      </div>
      <div class="col-md-3">
        <label for="">Depósito</label>
        <select class="form-control selectpicker" name="deposito">
          <option></option>
          @foreach ($deposito as $deposito)
            <option value="{{ $deposito->WhsCode }}">{{ $deposito->WhsCode }} - {{ $deposito->WhsName }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-1 -align-right right pull-right right" style="padding-top: 2%">
        <button class="btn btn-primary">Filtrar</button>
      </div>
    </div>
  </form>

  <div class="row mt-5">
    <div class="col-lg-12">
      {{ $items->links('pagination::bootstrap-4') }}
      <div class="table-responsive">
        <table class="table table-default table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th>Ponto de venda</th>
              <th>Item</th>
              <th>Qtd. Solicitada</th>
              <th>Qtd. Necessária</th>
              <th>Qtd. Em produção</th>
              <th>Estoque atual</th>
              <th>Depósito</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $item)
              <tr>
                <td>{{ $item->nome }}</td>
                <td>{{ $item->codigo_sap }} - {{ $item->item }}</td>
                <td>{{ number_format($item->quantidade_solicitada, 3, ',', '.') }}</td>
                <td>{{ number_format($item->quantidade_necessaria, 3, ',', '.') }}</td>
                <td>{{ $item->is_ip ? number_format($item->em_producao, 3, ',', '.') : '-' }}</td>
                <td>{{ number_format($item->estoque_atual, 3, ',', '.') }}</td>
                <td>{{ $item->deposito }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $items->links('pagination::bootstrap-4') }}
    </div>
  </div>

@endsection

@section('scripts')
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);
  </script>
@endsection
