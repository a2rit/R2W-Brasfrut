@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Justificativas</h3>
  </div>
  <hr>

  <form method="post" action="{{ route('porcionamento.justificativas.adicionar') }}">
    {!! csrf_field() !!}
    <div class="row">
      <div class="form-group col-md-10">
        <input required type="text" name="justificativa" class="form-control">
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary" type="submit">Adicionar</button>
      </div>
    </div>
  </form>
  
  <div class="table-responsive mt-5">
    {{ $itens->links('pagination::bootstrap-4') }}
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th style="width: 5%;">Id</th>
          <th>Justificativa</th>
          <th style="width: 10%;">Excluir</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($itens as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->justificativa }}</td>
            <td><a
                onclick="if(confirm('Excluir?')){window.location.href = '{{ route('porcionamento.justificativa.excluir', $item) }}';}"><button
                  class="btn btn-danger btn-sm w-100">Excluir</button></a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $itens->links('pagination::bootstrap-4') }}
  </div>
@endsection
