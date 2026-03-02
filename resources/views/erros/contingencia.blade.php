@extends('layouts.main')
@section('title', 'NFC-e Erros')

@section('content')

  <div class="col-md-10">
    <h3 class="header-page">NFCe's em contingência</h3>
  </div>
  <hr>

  <div class="table-responsive">
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th>Ponto de Venda</th>
          <th>Chave</th>
          <th>Número</th>
          <th>Data / Hora da NFC</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($xmls as $xml)
          <tr>
            <td>{{ $xml->ponto_venda }}</td>
            <td>{{ $xml->chave }}</td>
            <td>{{ $xml->numero }}</td>
            <td>{{ $xml->data->format('d-m-Y H:i') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
