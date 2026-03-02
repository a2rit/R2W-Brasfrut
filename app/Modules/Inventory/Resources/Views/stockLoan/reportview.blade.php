@extends('relatorios.relatorio_base')
@section('content')

    <div class="ibox-title input-group-btn">
        <div class="col-md-4">
            <h3>
                <center> Relatório de Empréstimos</center>
            </h3>
            <?php $cont = 1;?>
            @foreach($items as $key => $value)
                <table>
                    <thead>
                        <tr width="100%" style=" background-color:#086A87; color:#FFFFFF">
                            <th><center></center></th>
                            <th>
                                <center>Atendente</center>
                            </th>
                            <th>
                                <center>Solicitante</center>
                            </th>
                            <th>
                                <center>Devolvido</center>
                            </th>
                            <th>
                                <center>Cód. Item</center>
                            </th>
                            <th>
                                <center>Data do Documento</center>
                            </th>
                            <th>
                                <center>Data de Lançamento</center>
                            </th>
                            
                            <th>
                                <center>Status</center>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr width="10%">
                            <td><center>{{ $cont }}</center></td>
                            <td>
                                <center>{{$value['name']}}</center>
                            </td>
                            <td>
                                <center>{{$value['solicitante_name']}}</center>
                            </td>
                            <td>
                                <center>{{$value['devolvido_name']}}</center>
                            </td>
                            <td>
                                <center>{{$value['code']}}</center>
                            </td>
                            <td>
                                <center>{{ date('d/m/Y', strtotime($value['taxDate'])) }}</center>
                            </td>
                            <td>
                                <center>{{date('d/m/Y', strtotime($value['docDate'])) }}</center>
                            </td>
                            <td>
                                <center>{{$value['docStatus']}}</center>
                            </td>
                        </tr>
                        @if($tipo == "2")
                            <tr>
                                <td colspan="8"><center>Item</center></td>
                            </tr>
                            <table>
                                <thead>
                                    <tr>
                                        <th>
                                            <center>Cod</center>
                                        </th>
                                        <th>
                                            <center>Descrição</center>
                                        </th>
                                        <th>
                                            <center>U.M.</center>
                                        </th>
                                        <th>
                                            <center>Qtd.</center>
                                        </th>
                                        <th>
                                            <center>Qtd. Pendente</center>
                                        </th>
                                        <th>
                                            <center>Qtd. Devolvida</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($body[$key] as $key => $value) 
                                        <tr>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                <center>{{ $value['itemCode'] }}</center>
                                            </td>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                {{ $value['itemName'] }}
                                            </td>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                <center>{{ $value['itemUnd'] }}</center>
                                            </td>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                <center>{{ $value['quantity'] }}</center>
                                            </td>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                <center>{{ $value['quantityPending'] }}</center>
                                            </td>
                                            <td style="font-size:100%; border-bottom: 0px;">
                                                <center>{{ $value['quantityDevolved'] }}</center>
                                            </td>
                                        </tr>
                                    @endForeach
                                </tbody>
                            </table>
                        @endif
                    </tbody>
                </table>
                <?php $cont++;?>
            @endforeach
            <div align="right">
                <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
                <p style="font-size: 12px; text-indent: 1%;" >Total de Requisições: {{$cont - 1}}</p>
            </div>
        </div>
    <div>

@endsection