@extends('layouts.main')
@section('title', 'Grupos de usuários')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Editando grupo {{ $group->name }}</h3>
  </div>
  <hr>
  
  <form action="{{ route('user.groups.update', $group->id) }}" class="container-fluid" method="post">
    {!! csrf_field() !!}
    <div class="row">
      <div class="form-group col-6">
        <label>Nome</label>
        <input required name="name" type="text" value="{{ $group->name }}" class="form-control">
      </div>
      <div class="form-group col-6">
        <label>Permissões</label>
        <select class="form-control selectpicker" multiple name="roles[]">
          @foreach ($roles as $role)
            <option @if ($group->roles()->find($role->id)) selected @endif value="{{ $role->id }}">{{ $role->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-12 mt-4">
      <button type="submit" class="btn btn-primary float-end ms-1">Salvar</button>
      <button type="button" class="btn btn-danger float-end" onclick="destroy()">Excluir</button>
    </div>
  </form>
@endsection

@section('scripts')
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);

    function destroy() {
      swal({
        title: `Tem certeza que deseja excluir este grupo?`,
        icon: "warning",
        buttons: true,
        dangerMode: true,
      }).then((willDelete) => {
        if (willDelete) {
          window.location.href = '{{ route('user.groups.destroy', $group->id) }}'
        }
      });

    }
  </script>
@endsection
