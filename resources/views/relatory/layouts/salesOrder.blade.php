<?php
// tentar ver isso depois
$path = public_path($img[0]->diretory);
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
            <div class="col-md-6">
               <!-- logo da empresa -->
                      <div style="float:left;">
                      <img src="{{$base64}}" class="img-reponsive" style=" max-width:600px;max-height:105px;width: auto;height: auto;">
                      </div>
               <!-- Dados da empresa -->
               <table  style=" left:1000px;" >
                <tr>
              <td  style="width:80%; left:700px; font-size:85%;">  @foreach($company as $value)
                <p><strong> {{$value->company}}</strong></p>
                <p style="margin: 1; font-size:55%; align:justify;"><strong> {{$value->address}},  {{$value->number}} - {{$value->neighborhood}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong> {{$value->city}}  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;CEP : {{$value->cep}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong>EMAIL: {{$value->email}} <strong></p>
                <p style="margin: 2;  font-size:55%;"><strong> TELS.: {{$value->telephone}} &nbsp;&nbsp;&nbsp;&nbsp; {{$value->telephone2}}</strong></p>
                <p style="margin: 1;  font-size:55%;"><strong>CNPJ: {{$value->cnpj}}</strong></p>
                @endforeach</td>
                <td>
                  <td style="float:right;  font-size:50%;">
                    <br>
                    <br>
                    <br>
                    <strong><p style="margin: 0; font-size:55%;">Data: {{date("d/m/y")}} </p>
                    <p style="margin: 0; font-size:55%;">Hora: &nbsp;{{substr($head->created_at, 10,18)}}</p>
                   </td>
                     <!-- Dados do Relatorio -->
                </td>
              </tr>
              </table>
              <table>
              <tr>
                <th colspan="3" style="font-size:15px;"><center> PEDIDO DE VENDA N°: {{$head[0]['U_R2W_CODE']}}</center></th>
              </tr>
            </table>
            <table>
              <tr>
                <td style="width:57%; font-size:65%;">
                  <p style="font-size: 12px; text-indent: 1%; "><strong> Parceiro de Negócio: </strong>{{$head[0]['CardName']}} </p>
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Endereço:</strong> &nbsp;  {{$head[0]['StreetS']}} </p>
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Cidade:</strong> {{$head[0]['CityS']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;CEP: {{$head[0]['ZipCodeS']}}
                  <p style="font-size: 12px; text-indent: 1%;"><strong>Fone:</strong> {{$head[0]['Phone1']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;  </p>
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Condição de Pagamento:</strong>{{$head[0]['PymntGroup']}}  &nbsp;</p>
                </td>
              <td>
                <td style="float:right;  font-size:70%;">
                  <p style=" font-size: 12px; text-indent: 1%;"><strong> Data do Documento:</strong> {{formatDate($head[0]['TaxDate'])}}</p>
                  <p style=" font-size: 12px; text-indent: 1%;"><strong> Data de Vencimento:</strong> {{formatDate($head[0]['DocDueDate'])}}</p>
                  <p style=" font-size: 12px; text-indent: 1%;"><strong> Data de Lançamento:</strong> {{formatDate($head[0]['DocDate'])}}</p>
                  <p style="font-size: 12px; text-indent: 1%; "> @if(workCoin())<strong>Moeda:</strong> {{$head[0]['Currency']}}&nbsp;&nbsp;&nbsp; @endif @if(workQuotation())<strong>Cotação:</strong> @endif</p>
                </td>
                   <!-- Dados do Relatorio -->
              </td>
              </tr>
          </table>
                <!-- Titulo do Relatorio -->
                  <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
            <div class="ibox-title input-group-btn">
              <div class="col-md-4" style="padding-top:0%;">


                <!-- Linha -->
                <table>
                   <thead>
                  <tr>
                    <th style="font-size:80%;"><center>Cod.Sap</center></th>
                    <th style="font-size:80%;"><center>Descrições</center></th>
                    <th style="font-size:80%;"><center>Quantidade</center></th>
                    <th style="font-size:80%;"><center>Preço Unitário</center></th>
                    <th style="font-size:80%;"><center>Total</center></th>
                    <th style="font-size:80%;"><center>Utilização</center></th>
                    <th style="font-size:80%;"><center>C. de Custo</center></th>
                    <th style="font-size:80%;"><center>Projeto</center></th>
                  </tr>
                  </thead>
                    <tbody>
                      @foreach($body as $key => $value)
                          <tr width="10%" >
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{$value->itemCode}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{$OSO->getNameItem($value->itemCode)}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value->quantity,1,'.','')}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value->unitPrice,5,',','.')}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value->lineSum,2,',','.')}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{$extras->getLabelUtilization($value->usage)}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{$extras->getLabelDistributionRule($value->costingCode)}}</center></td>
                            <td style="font-size:70%; border-bottom: 0px;"><center>{{$extras->getLabelProject($value->projectCode)}}</center></td>
                          </tr>
                    @endForeach
              </tbody>
        </table>
        <div align="right">
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
                {{--<p style="font-size: 12px; text-indent: 1%;"><strong>Valor Frete: </strong></p>
                <p style="font-size: 12px; text-indent: 1%;"><strong>Valor do Desconto: </strong></p>--}}
                <p style="font-size: 12px; text-indent: 1%;"><strong>Total:</strong> {{number_format($head[0]['DocTotal'],2,',','.')}}</p>
          </div>
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               <div>
                 <p style="font-size: 12px; text-indent: 1%;" >
                   <strong>Observações:</strong> {{$head[0]['Comments']}}
                 </p>
                 <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               </div>
               <br>
               <br>
               <br>
           </div>
         </form>
   </div>
<!-- Rodape -->
<article>
<footer id="rodape">
  <div>
 <p style=" font-size: 12px; text-indent: 1%;">
   <img src="images/img-footer.png" alt=""  style="width: 8%; padding-top:10%"/>
    &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; Desenvolvido pela A2R Inovação em Tecnologia- Tel.: 71 35656598 &nbsp; &nbsp; &nbsp;
       <img src="images/img-footer-sap.png" alt="" align="right" style="width: 8%; padding-top:6%" /></p>

 </div>
</footer>
</article>
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
