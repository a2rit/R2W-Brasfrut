@extends('layouts.main')

@section('content')
    <!-- Page Heading -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Erros de Sincronização
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i>  <a href="/">Home</a>
                </li>
                <li class="active">
                    <i class="fa fa-table"></i> Erros de Sincronização
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <h2>Erros de Sincronização</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <!--<th>Model</th>-->
                        <th>Ponto de Venda</th>
                        <th>Mensagem</th>
                        <th>Data / Hora da NF</th>
                        <th>Ver</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($erros as $erro)
                        <tr>
                            <td>{{$erro->id}}</td>
                            <!--<td>{{$erro->model}}</td>-->
                            <td>{{$erro->ponto_venda_label}}</td>
                            <td>{{$erro->mensagem}}</td>
                            <td>@if($erro->data_nf){{$erro->data_nf->format('d-m-Y / H:i')}}@else - @endif</td>
                            <td><a href="{{route('erros.ver', ['id'=>$erro->id])}}"><button class="btn btn-success">Ver</button></a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $erros->links() }}
            </div>
        </div>
    </div>
@endsection