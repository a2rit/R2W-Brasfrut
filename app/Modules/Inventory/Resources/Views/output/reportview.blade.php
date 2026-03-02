@extends('relatorios.relatorio_base')
@section('content')

    <div class="ibox-title input-group-btn">
        <div class="col-md-4">
            <h3>
                <center> Relatório de Saída de Mercadoria. </center>
            </h3>
            <?php $cont = 1; ?>
            @if (isset($items))
                @foreach ($items as $key => $value)
                    <table>
                        <thead>
                            <tr width="100%" style=" background-color:#086A87; color:#FFFFFF">
                                <th style="font-size:80%;text-align: center; width: 2%;">
                                </th>
                                <th style="font-size:80%;text-align: center;  width: 5%;">
                                    <center>Cod. Item</center>
                                </th>
                                <th style="font-size:80%;text-align: left;  width: 5%;">
                                    <center>Usuário</center>
                                </th>
                                <th style="font-size:80%;text-align: left;  width: 5%;">
                                    <center>Data do Documento</center>
                                </th>
                                <th style="font-size:80%;text-align: left;  width: 5%;">
                                    <center>Data de Lançamento</center>
                                </th>
                                <th style="font-size:80%;text-align: left;  width: 5%;">
                                    <center>Status</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><center>{{ $cont }}</center></td>
                                <td><center>{{ $value->code }}</center></td>
                                <td><center>
                                    {{ substr($value->name, 0, 25) }}
                                </center></td>
                                <td><center>{{ date('d/m/Y', strtotime($value->DocDate)) }}</center></td>
                                <td><center>{{ date('d/m/Y', strtotime($value->TaxDate)) }}</center></td>
                                <td>
                                    <center>
                                        @if(($value->is_locked != 1) && (is_null($value->message)))
                                            <span onclick="loadItem('{{$value->id}}')"
                                                class="btn btn-warning btn-xs">R2W-B1</span>
                                        @endif
                                        @if(($value->is_locked != 1) && (!is_null($value->message)))
                                            <span onclick="loadItem('{{$value->id}}')"
                                                class="btn btn-success btn-xs">SAP-B1</span>
                                        @endif
                                        @if($value->is_locked == 1)
                                            <span onclick="loadItem('{{$value->id}}')"
                                                class="btn btn-danger btn-xs">SAP-B1</span>
                                        @endif
                                    </center>
                                </td>
                            </tr>
                            @if($tipo == '2')
                                <tr>
                                    <td colspan="8"><center>Items</center></td>
                                </tr>
                                <table>
                                    <thead>
                                        <tr>
                                            <th><center>Cod. Item</center></th>
                                            <th><center>Descrição</center></th>
                                            <th><center>U.M.</center></th>
                                            <th><center>Quantidade</center></th>
                                            <th><center>Projeto</center></th>
                                            <th><center>Centro de custo</center></th>
                                            <th><center>Centro de custo 2</center></th>
                                            <th><center>Depósito</center></th>
                                            <th><center>Conta</center></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($body))
                                            @foreach($body[$key] as  $value)
                                                <tr>
                                                    <td> <center>{{ $value['itemCode'] }}</center></td>
                                                    <td> {{ $value['itemName'] }}</td>
                                                    <td> <center>{{ $value['itemUnd'] }}</center></td>
                                                    <td> <center>{{ $value['quantity'] }}</center></td>
                                                    <td> <center>{{ $value['projectCode'] }}</center></td>
                                                    <td> <center>{{ $value['costCenter'] }}</center></td>
                                                    <td> <center>{{ $value['costCenter2'] }}</center></td>
                                                    <td> <center>{{ $value['wareHouseName'] }}</center></td>
                                                    <td> <center>{{ $value['accountCode'] }}</center></td>
                                                </tr>
                                            @endForeach
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                        </tbody>
                    </table>
                    <?php $cont++; ?>
                @endForeach
            @endif
            <div align="right">
                <hr
                    style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;" />
                <p style="font-size: 12px; text-indent: 1%;">Total : {{ $cont - 1 }}</p>
            </div>
        </div>
    <div>

@endsection