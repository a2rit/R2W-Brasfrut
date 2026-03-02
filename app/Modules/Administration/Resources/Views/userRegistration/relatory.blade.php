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
                            <th>IP</th>
                            <th>Navegador</th>
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
               url: '{{route('administration.user.registration.relatory.data')}}'
           },
           columns: [
               {name: 'name', data: 'name'},
               {name: 'ip_address', data: 'ip_address'},
               {name: 'user_agent', data: 'user_agent'},
           ],
           order: [[1, "desc"]],
           lengthMenu: [10, 25, 50, 100],
           language: dataTablesPtBr
       });
   </script>
@endsection
