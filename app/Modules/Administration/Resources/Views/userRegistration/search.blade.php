@extends('layouts.app')
@section('title', 'Listagem de Usuários')
@section('content')

<div class="wrapper wrapper-content">
      {!! csrf_field() !!}
      <div class="row">
            <div class="ibox-title input-group-btn ">
              <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5><a href="{{route('administration.user.registration.index')}}"> &nbsp;Cadastro de Usuário</a>&nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5>Listagem</h5>
            </div>
            <div class="ibox-content">
                 <div class="table-responsive">
                   <table id="table" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Usuário</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
               </div>
        </div>
</div>

@endsection

@section('scripts')
   <script>
       var table = $("#table").DataTable({
           processing: true,
           serverSide: true,
           "autoWidth": true,
           ajax: {
               url: '{{route('administration.anyData')}}'
           },
           columns: [
               {name: 'name', data: 'name'},
               {name: 'email', data: 'email'},
               {name: 'admin', data: 'admin', render: setAdmin, orderable: true},
               {name: 'tipo', data: 'tipo', render: setType, orderable: true},
               {name: 'status', data: 'status', render: setStatus, orderable: true},
               {name: 'id', data: 'id', render: renderButton, orderable: true}
           ],
           order: [[1, "desc"]],
           lengthMenu: [10, 25, 50, 100],
           language: dataTablesPtBr
       });

       function setAdmin(code) {
         switch (code) {
           case '1':
              return 'Administrador';
             break;
           default:
            return 'Simples';

         }
       }
       function setType(code) {
         switch (code) {
           case 'A':
              return 'Atendente';
             break;
          case 'S':
              return 'Solicitante';
             break;
           default:
            return '';

         }
       }

       function setStatus(code) {
         switch (code) {
           case '1':
              return '<span class="btn btn-success btn-xs">Ativo</span>';
             break;
          case '0':
              return '<span class="btn btn-danger btn-xs">Inativo</span>';
             break;
           default:
            return '';

         }
       }
       function renderButton(code) {
         return "<a href='{{route('administration.user.registration.edit')}}/"+code+"' class='btn btn-warning btn-xs'>Editar</a>";
       }
       function loadItem(id){
         window.location.href = "{{route('administration.user.registration.edit')}}"+'/'+id;
       }
   </script>
@endsection
