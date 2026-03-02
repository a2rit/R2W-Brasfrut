@extends('relatorios.relatorio_base')
@section('content')

    <div class="ibox-title input-group-btn">
        <table style=" width:88%; ">
            <tr>
                <td>
                    <aside style="float:right;">
                        <h4>
                            Relatório de Contagem:   Data: {{ $current_day }}
                        </h4>
                        <h4>
                            Estoque por grupo de Material:   Hora: {{ $current_time }}
                        </h4>
                    </aside>
                </td>
            </tr>
        </table>
        <div class="col-md-6">
            <?php $cont = 1; ?>
            <table>
                <thead>
                    <tr width="100%" style=" background-color:#086A87; color:#FFFFFF">
                        <th style="font-size:80%;text-align: center; width: 2%;">
                        </th>
                        <th style="font-size:80%;text-align: center;  width: 5%;">
                            <center>Cod. Item</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Descrição</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Und.</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Em Estoque</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Preço</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Total</center>
                        </th>
                    </tr>
                </thead>
            @if (isset($items))
                @foreach ($items as $key => $value)
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #333;"><center>{{ $cont }}</center></td>
                            <td style="border: 1px solid #333;"><center>{{ $value->ItemCode }}</center></td>
                            <td style="border: 1px solid #333;"><center>{{ substr($value->ItemName, 0, 50) }}</center></td>
                            <td style="border: 1px solid #333;"><center>{{ $value->BuyUnitMsr }}</center></td>
                            <td style="border: 1px solid #333;"><center>{{  number_format($value->OnHand, 3, ',', '.') }}</center></td>
                            <td style="border: 1px solid #333;"><center>R$ {{  $value->AvgPrice }}</center></td>
                            <td style="border: 1px solid #333;"><center>R$ {{  $value->Total }}</center></td>
                        </tr>
                    </tbody>
                    <?php $cont++; ?>
                @endForeach
            @endif
            </table>
            <div align="right">
                <hr
                    style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;" />
                <p style="font-size: 12px; text-indent: 1%;">Total : {{ $cont - 1 }}</p>
            </div>
        </div>
    <div>

@endsection