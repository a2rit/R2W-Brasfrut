@extends("layouts.main")

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Selecione o Ponto de Venda para adicionar lançamentos do consumo interno
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i> <a href="/">Home</a>
                </li>
                <li class="active">
                    <i class="fa fa-table"></i> Lançamento de Consumo Interno Diário
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        @foreach($pvs as $pv)
            <div class="col-md-2"><a href="{{route("consumo-interno.lancamento", ["pvId" => $pv->id])}}">
                    <button class="btn btn-success">{{$pv->nome}}</button>
                </a></div>
        @endforeach
    </div>
@endsection