@extends('layouts.main')
@section('title', 'Requisições')
@section('content')

    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="ibox ">
                <ol class="breadcrumb">
                    <li>
                        <a href={{route('home')}}><i class="fa fa-dashboard"></i>Inicio</a>
                    </li>
                    <li>
                        <a href="{{route('inventory.request.index')}}"><i class="fa fa-truck"></i> Requisições</a>
                    </li>
                    <li class="active">
                    Listando
                    </li>
                </ol>
            </div>
            <div class="ibox-title input-group-btn ">
                <div class="col-md-2">
                    <a href="{{route('inventory.request.create')}}"><img src="{{asset('images/newDocument.png')}}"
                                                                         class=" img-responsive -align-right right pull-right right"
                                                                         style="width: 24%;"></a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="requiredTable" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Atendente</th>
                            <th>Data Requisição</th>
                            <th>Data Nescessária</th>
                            <th>Status</th>
                            <th>Opções</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($busca))
                            <?php $cont = 1;?>
                            @foreach($busca as $key => $value)
                                <tr>
                                    <td>{{$cont}}</td>
                                    <td>@if(is_null($value->name)) Nenhum @else {{$value->name}} @endif</td>
                                    <td>{{formatDate($value->documentDate)}}</td>
                                    <td>{{formatDate($value->requiredDate)}}</td>
                                    @if($value->value == 'Aguardando atendente')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-xs"
                                                          style="color: #fff;background: #6d6767;border: 1px solid #6d6767;">Aguardando</sapn>
                                            </center>
                                        </td>
                                    @endif
                                    @if($value->value == 'Atendente vinculado')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-primary btn-xs">Vinculado</sapn></center>
                                        </td>
                                    @endif
                                    @if($value->value == 'Parcialmente Atendida')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-warning btn-xs">En. Parcial</sapn></center>
                                        </td>
                                    @endif
                                    @if($value->value == 'Recusada')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-danger btn-xs">{{$value->value}}</sapn>
                                            </center>
                                        </td>
                                    @endif
                                    @if($value->value == 'Recebida')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-success btn-xs">Atendida</sapn></center>
                                        </td>
                                    @endif
                                    @if($value->value == 'Aguardando Solicitante')
                                        <td style="width: 20%">
                                            <center><span class="btn btn-xs"
                                                          style="color: #fff;background: #f859c0;border: 1px solid #f859c0;">Aguardando</sapn>
                                            </center>
                                        </td>
                                    @endif

                                    <td><a href="{{route('inventory.request.searching', ['id'=> $value->idRequest])}}"
                                           class="btn  btn-link  btn-xs" title="Abrir"
                                           style="font-size:large; margin-left: 10%"><i class='fa fa-folder-open'
                                                                                        style='color:blue'></i>
                                            <a href="#" onclick="previews('{{$value->idRequest}}');"
                                               class="btn btn-link btn-xs" title="Visualizar"
                                               style="font-size:large; margin-left: 10%"><i class='fa fa-info-circle'
                                                                                            style='color:blue'></i></td>
                                </tr>
                                <?php $cont++;?>
                            @endForeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </form>
    <div class="modal inmodal" id="previewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pré-visualização</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table id="table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Cod SAP</th>
                                    <th>Descrição</th>
                                    <th>Quantidade</th>
                                </tr>
                                </thead>
                                {{--<tfoot style="display: table-header-group">
                                <tr>
                                    <th><input type="text" name="code" class="form-control"/></th>
                                    <th><input type="text" name="name" class="form-control"/></th>
                                </tr>
                                </tfoot>--}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

@endsection


@section('scripts')
    <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
    <script>
        function previews(idRequest) {
            $('#table').dataTable().fnDestroy();
            $("#table").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{route('inventory.anyData')}}' + '/' + idRequest
                },
                columns: [
                    {name: 'codSAP', data: 'codSAP'},
                    {name: 'itemName', data: 'itemName'},
                    {name: 'quantityRequest', data: 'quantityRequest'}
                ],
                order: [[1, "desc"]],
                language: dataTablesPtBr,
                paging: false,
                "lengthChange": false,
                "ordering": false,
                "bFilter": false,
                "bInfo": false,
                "searching": false
            });
            $('#previewModal').modal('show');
        }

        $('.dataTables-example').DataTable({
            language: dataTablesPtBr,
            paging: true,
            "lengthChange": true,
            "ordering": false,
            "bFilter": false,
            "bInfo": false,
            "searching": true
        });

    </script>

@endsection
