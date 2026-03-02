@extends('intern-consumption::report.layout')

@section('content')

    @php
        use App\Modules\InternConsumption\Models\InternConsumption;
        /** @var $items InternConsumption[] */
    @endphp
    @foreach($items as $ic)
        <table class="table-bordered">
            <thead>
            <tr>
                <th>Data</th>
                <th>Solicitante</th>
                <th>Autorizador</th>
                <th>Projeto</th>
                <th>Centro de custo</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{$ic->date->format('d-m-Y')}}</td>
                <td>{{$ic->requester_name}}</td>
                <td>{{$ic->authorizer_name}}</td>
                <td>{{$ic->project_name}}</td>
                <td>{{$ic->distribution_rule_name}}</td>
            </tr>
            </tbody>
        </table>
        <table class="table-bordered">
            <thead>
            <tr>
                <th>Cod. Item</th>
                <th>Descrição</th>
                <th>Qtd</th>
                <th>Custo</th>
                <th>Preço</th>
            </tr>
            </thead>
            <tbody>
            @foreach($ic->items as $i)
                <tr>
                    <td>{{$i->code}}</td>
                    <td>{{$i->name}}</td>
                    <td style="white-space: nowrap">{{number_format($i->qty, 3, ',', '.')}}</td>
                    <td style="white-space: nowrap">R$ {{number_format($i->item_cost, 2, ',', '.')}}</td>
                    <td style="white-space: nowrap">R$ {{number_format($i->item_price, 2, ',', '.')}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <br>
        <hr>
    @endforeach
@endsection