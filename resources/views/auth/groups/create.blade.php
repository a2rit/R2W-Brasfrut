@extends('layouts.main')
@section('title', 'Grupos de usuários')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Cadastro de grupo de usuário</h3>
  </div>
  <hr>

  <form action="{{ route('user.groups.store') }}" class="form" method="post">
    {!! csrf_field() !!}
    <div class="row">
      <div class="form-group col-6">
        <label>Nome</label>
        <input required name="name" type="text" class="form-control">
      </div>
      <div class="form-group col-6">
        <label>Permissões</label>
        <select class="form-control selectpicker" multiple name="roles[]">
          @foreach ($roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-success btn-sm float-end mt-4">Salvar</button>
  </form>
@endsection

@section('scripts')
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);
  </script>
@endsection
