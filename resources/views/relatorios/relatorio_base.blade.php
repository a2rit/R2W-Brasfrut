<?php
    $path = public_path("img/logo-nova.png");
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    $path1 = public_path("images/img-footer2.png");
    $type1 = pathinfo($path1, PATHINFO_EXTENSION);
    $data1 = file_get_contents($path1);
    $base641 = 'data:image/' . $type1 . ';base64,' . base64_encode($data1);

    $path2 = public_path("images/a2r-10anos.png");
    $type2 = pathinfo($path2, PATHINFO_EXTENSION);
    $data2 = file_get_contents($path2);
    $base642 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
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

            th,
            td {
                padding: 1px;
                text-align: left;
                border-bottom: 1px solid #333;
            }

            div#conteudo-left {
                width: 1030px;
                height: 100px;
                float: left;
            }

            footer#rodape{
                border-top: 2px solid #333;
                bottom: 30px;
                left: 0;
                height: 5px;
                position: fixed;
                width: 100%;
            }

            footer > #references{
                border-top: 1px solid rgb(165, 160, 160);
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
    </head>

    <body>
        <div class="wrapper wrapper-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox-title input-group-btn">
                        <div class="col-md-6">
                             <!-- logo da empresa -->
                                <div style="float:left;">
                                    <img src="{{$base64}}" class="img-reponsive" style=" max-width:600px;max-height:80px;width: auto;height: auto;">
                                </div>

                            <!-- Dados da empresa -->
                            <table style=" width:88%; ">
                                <tr>
                                    <td  style="width:70%; padding-left: 20px; font-size:80%;">
                                        <p><strong>YACHT CLUBE DA BAHIA</strong></p>
                                        <p style="margin: 0; font-size:70%;"><strong>Av. Sete de Setembro - 3252</strong>
                                        </p>
                                        <p style="margin: 0;  font-size:70%;"><strong>Barra</strong></p>
                                        <p style="margin: 0;  font-size:70%;"><strong>Salvador - BA-CEP:40130-001</strong>
                                        </p>
                                        <p style="margin: 0;  font-size:70%;"><strong>(71) 2101-9111</strong></p>
                                    </td>
                                    <td>
                                        <!-- Dados do Relatorio -->
                                        <aside style="float:right;  font-size:80%; ">
                                            <p>Data:{{ date('d/m/y') }}</p>
                                        </aside>
                                    </td>
                                </tr>
                            </table>

                            <!-- Linha -->
                            <div style="clear: both;">
                                @yield('content')
                            </div>
                            <!-- Fim Linha -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Rodape -->
        <article>
            <footer id="rodape">   
            
            <div>
                <div id="references">
                    <p style=" font-size: 12px; text-indent: 1%;">
                    <img src="{{$base642}}" alt=""  style="width: 8%;"/>
                      &nbsp; &nbsp; &nbsp;&nbsp; Desenvolvido pela A2R Inovação em Tecnologia- Tel.: 71 35656598 &nbsp; &nbsp; &nbsp;
                    <img src="{{$base641}}" alt="" align="right" style="width: 8%; padding-top:6%" /></p>
                    
                </div>
            </footer>
        </article>
    </body>
</html>
