@extends("layouts.main")

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Lançamentos de Consumo Interno
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i> <a href="/">Home</a>
                </li>
                <li class="active">
                    <i class="fa fa-table"></i> Lançamentos de Consumo Interno
                </li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-responsive">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Data</td>
                    <td>Ponto de Venda</td>
                    <td>Transferência Estoque</td>
                    <td>Pedido de Venda</td>
                </tr>
                </thead>
                <tbody>
                @foreach($lancamentos as $lancamento)
                    <tr>
                        <td>{{$lancamento->id}}</td>
                        <td>{{$lancamento->data->format("d-m-Y")}}</td>
                        <td>{{$lancamento->pv->nome}}</td>
                        <td>{{$lancamento->cod_transferencia}}</td>
                        <td>{{$lancamento->cod_pedido}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection