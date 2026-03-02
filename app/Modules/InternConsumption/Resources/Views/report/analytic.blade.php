@extends('intern-consumption::report.layout')

@section('content')
    @php
        use App\Modules\InternConsumption\Models\InternConsumption;
        use \Illuminate\Support\Collection;
        /** @var $items Collection|InternConsumption[] */
    @endphp
    @foreach($items as $ic)
        <table class="text-center">
            <thead>
            <tr style="background-color:#086A87; color:#FFFFFF">
                <th>Data</th>
                <th>Status</th>
                <th>Solicitante</th>
                <th>Autorizador</th>
                <th>Projeto</th>
                <th>C. Custo</th>
                <th>C. Custo 2</th>
                <th>LCM</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{$ic->date->format('d/m/Y')}}</td>
                <td>{{$ic->status_label}}</td>
                <td>{{$ic->requester_name}}</td>
                <td>{{$ic->authorizer_name}}</td>
                <td>{{$ic->project_name}}</td>
                <td>{{$ic->distribution_rule_name}}</td>
                <td>{{$ic->distribution_rule2_name ? $ic->distribution_rule2_name : '-'}}</td>
                <td>{{$ic->manual_account_entry_id}}</td>
                <td>{{number_format($ic->total, 2, ',', '.')}}</td>
            </tr>
            </tbody>
        </table>
        <table class="text-center">
            <thead>
            <tr style="background-color:#848484; color:#FFFFFF">
                <th>Cod. Item</th>
                <th>Descrição</th>
                <th>Qtd</th>
                <th>Custo</th>
                <th>Preço</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($ic->items as $i)
                <tr>
                    <td>{{$i->code}}</td>
                    <td>{{$i->name}}</td>
                    <td style="white-space: nowrap">{{number_format($i->qty, 3, ',', '.')}}</td>
                    <td style="white-space: nowrap">R$ {{number_format($i->item_cost, 2, ',', '.')}}</td>
                    <td style="white-space: nowrap">R$ {{number_format(($i->total / $i->qty), 2, ',', '.')}}</td>
                    <td style="white-space: nowrap">R$ {{number_format($i->total, 2, ',', '.')}}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">Totais</th>
                <th style="white-space: nowrap">{{number_format($ic->items->sum('qty'), 3, ',', '.')}}</th>
                <th style="white-space: nowrap">{{--R$ {{number_format($ic->items->sum('item_cost'), 2, ',', '.')}}--}}</th>
                <th style="white-space: nowrap">{{--R$ {{number_format($ic->items->sum('item_price'), 2, ',', '.')}}--}}</th>
                <th style="white-space: nowrap">R$ {{number_format($ic->items->sum('total'), 2, ',', '.')}}</th>
            </tr>
            </tfoot>
        </table>
        <hr>
    @endforeach
    <br>
    <table class="text-center">
        <thead>
        <tr style="background-color:#086A87; color:#FFFFFF">
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>R$ {{number_format($items->sum('total'), 2, ',', '.')}}</th>
        </tr>
        </tbody>
    </table>
@endsection
