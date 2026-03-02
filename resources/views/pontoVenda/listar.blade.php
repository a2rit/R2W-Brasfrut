@extends('layouts.main')
@section('title', 'Ponto de venda')

@section('content')
  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Pontos de Venda</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('pv.cadastro') }}">Ponto de Venda</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <div class="table-responsive">
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th style="width: 5%;">Id</th>
          <th>Nome</th>
          <th style="width: 10%;">Editar</th>
          {{-- <th style="width: 10%;">Excluir</th> --}}
        </tr>
      </thead>
      <tbody>
        @foreach ($pontosVenda as $pv)
          <tr>
            <td>{{ $pv->id }}</td>
            <td>{{ $pv->nome }}</td>
            <td><a href="{{ route('pv.editar', ['id' => $pv->id]) }}" class="btn btn-primary btn-sm w-100">Editar</a>
            </td>
            {{-- <td>Excluir</td> --}}
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
