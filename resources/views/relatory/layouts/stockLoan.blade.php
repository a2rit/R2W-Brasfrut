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
                                    <center>{{$head['code']}}</center>
                                </td>
                                <td>
                                    <center>{{$head['name']}}</center>
                                </td>
                                <td>
                                    <center>{{$head['solicitante_name']}}</center>
                                </td>
                                <td>
                                    <center>{{$head['devolvido_name']}}</center>
                                </td>
                                <td>
                                    <center>{{ date('d/m/Y', strtotime($head['taxDate'])) }}</center>
                                </td>
                                <td>
                                    <center>{{date('d/m/Y', strtotime($head['docDate'])) }}</center>
                                </td>
                                <td>
                                    <center>{{$head['docStatus']}}</center>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                     <?php $cont++;?>
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