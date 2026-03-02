@extends('layouts.main')
@section('title', 'Usuários')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de usuários</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('register') }}">Usuário</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <form method="POST" action="{{ route('usuarios.filtrar') }}">
    {{ csrf_field() }}
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
              <div class="col-4">
                <input type="text" class="form-control" name="name" placeholder="Nome" value="{{old('name')}}">
              </div>
              <div class="col-4">
                <input type="email" class="form-control" name="email" placeholder="E-mail" value="{{old('email')}}">
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-12">
                <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar formulário</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
  <div class="table-responsive mt-5">
    {{ $usuarios->links('pagination::bootstrap-4') }}
    <table class="table table-default table-bordered table-hover table-striped">
      <thead>
        <tr>
          <th style="width: 5%;">Id</th>
          <th style="width: 35%;">Nome</th>
          <th style="width: 35%;">E-Mail</th>
          <th style="width: 20%;">Grupo</th>
          <th style="width: 5%;">Editar</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($usuarios as $usuario)
          <tr>
            <td>{{ $usuario->id }}</td>
            <td>{{ $usuario->name }}</td>
            <td>{{ $usuario->email }}</td>
            <td>
              @if ($usuario->group)
                {{ $usuario->group->name }}
              @endif
            </td>
            <td>
              <a href="{{ route('usuarios.editar', ['id' => $usuario->id]) }}" class="btn btn-primary btn-sm w-100">VER</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $usuarios->links('pagination::bootstrap-4') }}
  </div>
  <script>

    $('input[name="name"]').autocomplete({
      serviceUrl: '{{ route('administration.user.get') }}?type=name',

      onSelect: function(suggestion) {
        if(suggestion.data){
          $('input[name="name"]').val(suggestion.data);
        }
      }
    });

    $('input[name="email"]').autocomplete({
      serviceUrl: '{{ route('administration.user.get') }}?type=email',

      onSelect: function(suggestion) {
        if(suggestion.data){
          $('input[name="email"]').val(suggestion.data);
        }
      }
    });


  </script>
@endsection
