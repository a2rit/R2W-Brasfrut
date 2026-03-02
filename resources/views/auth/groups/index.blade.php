@extends('layouts.main')
@section('title', 'Grupos de usuários')

@section('content')
  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de Grupos de Usuário</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('user.groups.create') }}">Grupo</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <hr>
  <div class="table-responsive mt-4">
    <table class="table table-default table-striped table-bordered table-hover">
      <tr>
        <th style="width: 45%;">Nome</th>
        <th style="width: 45%;">Permissões</th>
        <th style="width: 10%;">Ações</th>
      </tr>
      @foreach ($groups as $group)
        <tr>
          <td>{{ $group->name }}</td>
          <td>{{ $group->roles_string }}</td>
          <td><a href="{{ route('user.groups.edit', $group->id) }}">
              <button class="btn btn-primary btn-sm w-100">Editar</button></a></td>
        </tr>
      @endforeach
    </table>
  </div>
@endsection
