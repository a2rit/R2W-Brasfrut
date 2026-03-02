<!DOCTYPE html>
<html lang="pt-BR">
<head>
   <meta charset="UTF-8">
   <style>
      table {
         border-collapse: collapse;
         width: 100%;
      }
      th#01{
         border-bottom: 0px;
      }
      tr{
         border-bottom: 0px solid #333;
      }
      th, td {
         padding: 1px;
         text-align: left;
         border-bottom: 1px solid #333;
      }
      div#conteudo-left{
         width:1030px;
         height:100px;
         float:left;
      }
      footer#rodape{
         border-top: 2px solid #333;
         bottom: 0;
         left: 0;
         height: 5px;
         position: fixed;
         width: 100%;
      }
      div#conteudo-right{
         width:500px;
         height:400px;
         float:left;
      }
      p{
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
                  <img src="data:image/jpeg;base64,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" class="img-reponsive" style=" max-width:400px;max-height:95px;width: auto;height: auto;">
               </div>
               <!-- Dados da empresa -->
               <table  style=" width:740px;" >
                  <tr>
                     <td  style="width:70%; left:500px; font-size:80%;">
                        <p><strong>YACHT CLUBE DA BAHIA</strong></p>
                        <p style="margin: 0; font-size:70%;"><strong>Av. Sete de Setembro - 3252</strong></p>
                        <p style="margin: 0;  font-size:70%;"><strong>Barra</strong></p>
                        <p style="margin: 0;  font-size:70%;"><strong>Salvador - BA-CEP:40130-001</strong></p>
                        <p style="margin: 0;  font-size:70%;"><strong>(71) 2101-9111</strong></p>
                     </td>
                     <td>
                        <!-- Dados do Relatorio -->
                        <aside style="float:right;  font-size:80%; ">
                           <p>Data:{{date("d/m/y")}}</p>
                        </aside>
                     </td>
                  </tr>
               </table>
               <!-- Linha -->
               <div style="clear: both;"></div>
            </div>
            <!-- Titulo do Relatorio -->
            <div class="ibox-title input-group-btn">
               <div class="col-md-4">
                  <h3>
                     <center> Relatório de Empréstimos</center>
                  </h3>
                  <?php $cont =0;?>
                  @foreach($items as $value)
                     <table>
                        <thead>
                        <tr width="100%" style=" background-color:#086A87; color:#FFFFFF">
                           <td>
                              <center>Código</center>
                           </td>
                           <td>
                              <center>Atendente</center>
                           </td>
                           <td>
                                <center>Solicitante</center>
                            </td>
                            <td>
                                <center>Devolvido</center>
                            </td>
                           <td>
                                <center>Data do Documento</center>
                            </td>
                            <td>
                                <center>Data de Lançamento</center>
                            </td>
                            <td>
                                <center>Status</center>
                             </td>
                        </tr>
                        </thead>
                        <tbody>
                            <tr width="10%">
                                <td>
                                    <center>{{$value['code']}}</center>
                                </td>
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
                                <td></td>
                                <td></td>
                                <td colspan="2"><center>Analítico</center></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <center>Cod</center>
                                </td>
                                <td></td>
                                <td colspan="2">
                                    Descrições
                                </td>
                                <td>
                                    <center>Quantidade</center>
                                </td>
                                <td>
                                    <center>Q. Pendente</center>
                                </td>
                                <td>
                                    <center>Q. Devolvida</center>
                                </td>
                             </tr>
                                @foreach($body[$cont] as $key => $value) 
                                    <tr>
                                       <td style="font-size:100%; border-bottom: 0px;">
                                          <center>{{ $value['itemCode'] }}</center>
                                       </td>
                                       <td></td>
                                       <td colspan="2" style="font-size:100%; border-bottom: 0px;">
                                          {{ $value['itemName'] }}
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
                            @endif
                        </tbody>
                     </table>
                     <?php $cont++;?>
                  @endforeach
                  <div align="right">
                     <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
                     <p style="font-size: 12px; text-indent: 1%;" >Total de Requisições: {{$cont}}</p>
                  </div>
               </div>
               <div>
               </div>
               <br>
               <br>
               <br>
               <br>
            </div>
         </div>
         <!-- Rodape -->
         <article>
            <footer id="rodape">
               <div>
                  <p style=" font-size: 12px; text-indent: 1%;">
                     <img src="{{asset('images/img-footer.png')}}" alt=""  style="width: 8%; padding-top:10%"/>
                     &nbsp;&nbsp; &nbsp; &nbsp; Desenvolvido pela A2R Innovação e Tecnologia-® {{DATE('Y')}} &nbsp; &nbsp;Tel.: 71 35656598 &nbsp; &nbsp; &nbsp;
                     <img src="{{asset('images/img-footer-sap.png')}}" alt="" align="right" style="width: 8%; padding-top:6%" />
                  </p>
               </div>
            </footer>
         </article>
</body>
</html>