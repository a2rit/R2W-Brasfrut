@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Porcionamentos que ultrapassaram o limite de perda</h3>
  </div>
  <hr>

  <div class="table-responsive mt-5">
    {{ $porcionamentos->links('pagination::bootstrap-4') }}
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th>Fornecedor</th>
          <th>Item</th>
          <th>Código de entrada</th>
          <th>Código de saída</th>
          <th>Ver</th>
          <th>Ciente</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($porcionamentos as $porcionamento)
          <tr>
            <td>{{ $porcionamento->nome_fornecedor }}</td>
            <td>{{ $porcionamento->nome_item }}</td>
            <td>{{ $porcionamento->cod_entrada }}</td>
            <td>{{ $porcionamento->cod_saida }}</td>
            <td><a href="{{ route('porcionamento.ver', $porcionamento) }}">
                <button class="btn btn-primary btn-sm w-100">Ver</button>
              </a></td>
            <td>
              @if (!$porcionamento->autorizado && Auth::user()->hasRole('Porcionamento.autorizar'))
                <a
                  onclick="if(confirm('Ciente?')){window.location.href = '{{ route('porcionamento.excluir', $porcionamento) }}';}">
                  <button class="btn btn-warning btn-sm w-100">Ciente</button>
                </a>
              @else
                Sim
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $porcionamentos->links('pagination::bootstrap-4') }}
  </div>
@endsection
