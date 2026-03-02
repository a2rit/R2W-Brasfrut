@extends('relatorios.relatorio_base')
@section('content')

    <div class="ibox-title input-group-btn">
        <div class="col-md-4">
            <h3>
                <center> Relatório de Pedido de Transferência. </center>
            </h3>
            <?php $cont = 0; ?>
            <!-- Linha -->
            <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;" />
            @if (isset($items))
                <?php $cont = 1; ?>
                @foreach ($items as $key => $value)
                    <table>
                        <thead>
                            <tr width="100%" style=" background-color:#086A87; color:#FFFFFF">
                                <th><center>#</center></th>
                                <th>
                                    <center>Cod. Item</center>
                                </th>
                                <th>
                                    <center> Usuário</center>
                                </th>
                                <th>
                                    <center>Depósito de Origem</center>
                                </th>
                                <th>
                                    <center>Depósito de Destino</center>
                                </th>
                                <th>
                                    <center>Data do documento</center>
                                </th>
                                <th>
                                    <center>Data de lançamento</center>
                                </th>
                                <th>
                                    <center>Status</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td> <center>{{ $cont }}</center></td>
                                <td> <center>{{ $value->code }}</center></td>
                                <td> <center>{{ $value->name }}</center></td>
                                <td> <center>{{ $value->depositoAtual }}</center></td>
                                <td> <center>{{ $value->depositoDestino }}</center></td>
                                <td> <center>{{ date('d/m/Y', strtotime($value->docDate)) }}</center></td>
                                <td> <center>{{ date('d/m/Y', strtotime($value->taxDate)) }}</center></td>
                                <td> <center>{{ $value->docStatus }}</center></td>
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
                                            <th><center>Qtd. Solicitada</center></th>
                                            <th><center>Qtd. Pendente</center></th>
                                            <th><center>Qtd. Estoque</center></th>
                                            <th><center>Qtd. Atendida</center></th>
                                            <th><center>Projeto</center></th>
                                            <th><center>Centro de custo</center></th>
                                            <th><center>Centro de custo 2</center></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($body))
                                            @foreach($body[$key] as  $value)
                                                @php
                                                    $qtdEstoque = explode(".", $value['qtdEstoque']);
                                                    if($qtdEstoque[0] == null)
                                                        $qtdEstoque[0] = 0;
                                                @endphp
                                            <tr>
                                                <td> <center>{{ $value['itemCode'] }}</center></td>
                                                <td>{{ $value['itemName'] }}</td>
                                                <td> <center>{{ $value['itemUnd'] }}</center></td>
                                                <td> <center>{{ $value['quantityRequest'] }}</center></td>
                                                <td> <center>{{ $value['quantityPending'] }}</center></td>
                                                <td> <center>{{ $qtdEstoque[0] }}</center></td>
                                                <td> <center>{{ $value['quantityServed'] }}</center></td>
                                                <td> <center>{{ $value['projectCode'] }}</center></td>
                                                <td> <center>{{ $value['costCenter'] }}</center></td>
                                                <td> <center>{{ $value['costCenter2'] }}</center></td>
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
                <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;" />
                <p style="font-size: 12px; text-indent: 1%;">Total : {{ $cont -1 }}</p>
            </div>
        </div>
        <br>
        <br>
        <br>
        <br>
    </div>

@endsection
