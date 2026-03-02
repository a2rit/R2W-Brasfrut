<?php
// tentar ver isso depois
$path = public_path($img->diretory);
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
?>
<div class="wrapper wrapper-content">
    <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox-title input-group-btn">
                    <!-- Dados da empresa -->
                    <table>
                        <tr>
                            <td style=""><img src="{{$base64}}" class="img-reponsive"
                                                                  style=" max-width:600px;max-height:105px;width: auto;height: auto;">
                            </td>
                            <td style="width:80%; font-size:85%;">
                                <p><strong> {{$company->company}}</strong></p>
                                <p style="margin: 1px; font-size:55%; align:justify;"><strong> {{$company->address}}
                                        , {{$company->number}} - {{$company->neighborhood}}</strong></p>
                                <p style="margin: 1px;  font-size:55%;"><strong> {{$company->city}} &nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;CEP : {{$company->cep}}</strong></p>
                                <p style="margin: 1px;  font-size:55%;"><strong>EMAIL: {{$company->email}} </strong>
                                </p>
                                <p style="margin: 2px;  font-size:55%;"><strong> TELS.: {{$company->telephone}}
                                        &nbsp;&nbsp;&nbsp;&nbsp; {{$company->telephone2}}</strong>
                                </p>
                                <p style="margin: 1px;  font-size:55%;"><strong>CNPJ: {{$company->cnpj}}</strong>
                                </p>
                            </td>
                            <td>
                            <td style="float:right;  font-size:50%;">
                                <br>
                                <br>
                                <br>
                                <strong><p style="margin: 0; font-size:55%; text-align:right; white-space: nowrap">
                                        Data: {{date("d/m/y")}} </p>
                                    <p style="margin: 0; font-size:55%; text-align:right;">Hora:{{substr($head->created_at, 10,18)}}</p>
                                </strong>
                            </td>
                            <!-- Dados do Relatorio -->
                            </td>
                        </tr>
                    </table>
                    <!-- Titulo do Relatorio -->
                    <div class="ibox-title input-group-btn">
                        <div class="col-md-4" style="padding-top:0%;">
                            <table>
                                <tr>
                                    <th colspan="3" style="font-size:15px;text-align:center;"> Relatório <?php
                                        if ($page == 1) {
                                            echo 'Contas a Pagar';
                                        } else {
                                            echo 'Contas Liquidadas';
                                        }?></th>
                                </tr>
                            </table>
                            <!-- Linha -->
                            <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
                            <table>
                                <thead>
                                <tr>
                                    <th style="font-size:80%;text-align:center;">#</th>
                                    <th style="font-size:80%;text-align:center;">TIPO</th>
                                    <th style="font-size:80%;text-align:center;">EMISSÃO</th>
                                    <th style="font-size:80%;text-align:center;">VENCIMENTO</th>
                                    <th style="font-size:80%;text-align:center;">FORNECEDOR</th>
                                    <th style="font-size:80%;text-align:center;">DOC</th>
                                    <th style="font-size:80%;text-align:center;">PARCELA</th>
                                    <th style="font-size:80%;text-align:right;">VALOR</th>
                                </tr>
                                </thead>
                                <thead>
                                </thead>
                                <tbody>
                                <?php $cont = 1; $total = 0;?>
                                @foreach($body->groupBy('DTVENCTO') as $date => $values)
                                    @php $totalDate = 0 @endphp
                                @foreach($values as $key => $value)
                                    @php $totalDate += $value->VALORPAGO; @endphp
                                    <tr width="10%">
                                        <td style="font-size:70%; border-bottom: 0px; width:5%; text-align: center;">{{$cont}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:5%; text-align: center;">{{$value->TIPO}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:15%; text-align: center;">{{formatDate($value->DTEMISSAO)}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:15%; text-align: center;">{{formatDate($value->DTVENCTO)}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:30%; text-align: left;">{{$value->FORNECEDOR}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:10%; text-align: center;">{{$value->NDOC}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:10%; text-align: center;">{{$value->NPARCELA}}</td>
                                        <td style="font-size:70%; border-bottom: 0px; width:10%; text-align: right;">{{number_format($value->VALORPAGO,2,',','.')}}</td>
                                    </tr>
                                    <?php $cont++; $total += $value->VALORPAGO;?>
                                @endForeach
                                    <tr width="10%">
                                        <td style="font-size:70%; border-bottom: 0px; width:5%; text-align: center;"></td>
                                        <td style="font-size:70%; border-bottom: 0px; width:5%; text-align: center;"></td>
                                        <td style="font-size:70%; border-bottom: 0px; width:15%; text-align: center;"></td>
                                        <th style="font-size:70%; border-bottom: 0px; width:15%; text-align: center;">{{formatDate($value->DTVENCTO)}}</th>
                                        <td style="font-size:70%; border-bottom: 0px; width:30%; text-align: left;"></td>
                                        <td style="font-size:70%; border-bottom: 0px; width:10%; text-align: center;"></td>
                                        <th style="font-size:70%; border-bottom: 0px; width:10%; text-align: center;">Total/dia</th>
                                        <th style="font-size:70%; border-bottom: 0px; width:10%; text-align: right;">{{number_format($totalDate,2,',','.')}}</th>
                                    </tr>
                                @endForeach
                                </tbody>
                            </table>
                            <table>
                                <tr>
                                    <th colspan="3"
                                        style="font-size:15px;padding-top:4%; padding-left: 75%; text-align:right;">
                                        <strong>TOTAL:</strong> {{number_format($total,2,',','.')}}</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div>
</div>

<br>
<br>
<br>
<br>
<!-- Rodape -->
<article>
    <footer id="rodape">
        <div>
            <p style=" font-size: 12px; text-indent: 1%;">
                <img src="images/img-footer.png" alt="" style="width: 8%; padding-top:10%"/>
                &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; Desenvolvido pela A2R Inovação em
                Tecnologia- Tel.: 71 35656598 &nbsp; &nbsp; &nbsp;
                <img src="images/img-footer-sap.png" alt="" align="right" style="width: 8%; padding-top:6%"/></p>

        </div>
    </footer>
</article>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th#01 {
        border-bottom: 0px;

    }

    tr {
        border-bottom: 0px solid #333;
    }

    th, td {
        padding: 1px;
        text-align: left;
        border-bottom: 1px solid #333;
    }

    div#conteudo-left {
        width: 1030px;
        height: 100px;
        float: left;
    }

    footer#rodape {
        border-top: 2px solid #333;
        bottom: 0;
        left: 0;
        height: 5px;
        position: fixed;
        width: 100%;
    }

    div#conteudo-right {
        width: 500px;
        height: 400px;
        float: left;
    }

    p {
        webkit-margin-before: 1em;
        webkit-margin-after: 1em;
    }
</style>
