@extends('relatorios.relatorio_base')
@section('content')

    <div class="ibox-title input-group-btn">
        <table style=" width:88%; ">
            <tr>
                <td>
                    <h4>
                        Contador:
                    </h4>
                    <h4>
                        Conferente:
                    </h4>
                    <h4>
                        Depósito: @if(isset($items[0])) {{ $items[0]->WhsCode }} - {{ $items[0]->WhsName }} @endif
                    </h4>
                </td>
                <td>
                    <aside style="float:right;">
                        <h4>
                            Lista para contagem de Estoque:   Data: {{ $current_day }}
                        </h4>
                        <h4>
                            Por grupo de Material:   Hora: {{ $current_time }}
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
                            <center>Cont. 1</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Cont. 2</center>
                        </th>
                        <th style="font-size:80%;text-align: left;  width: 5%;">
                            <center>Cont. 3</center>
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
                            <td style="border: 1px solid #333;"></td>
                            <td style="border: 1px solid #333;"></td>
                            <td style="border: 1px solid #333;"></td>
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