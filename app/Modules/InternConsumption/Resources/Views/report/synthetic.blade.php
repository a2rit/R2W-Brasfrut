@extends('intern-consumption::report.layout')

@section('content')
    <table class="text-center table-striped">
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
        @php
            /** @var $items Collection|InternConsumption[] */
            use App\Modules\InternConsumption\Models\InternConsumption;
            use Illuminate\Support\Collection;
        @endphp
        @foreach($items as $ic)
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
        @endforeach
        </tbody>
    </table>
    <br>
    <hr>
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
