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
                <p ><strong> {{$value->company}}</strong></p>
                <p style="margin: 1; font-size:55%; align:justify;"><strong> {{$value->address}},  {{$value->number}} - {{$value->neighborhood}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong> {{$value->city}}  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;CEP : {{$value->cep}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong>EMAIL: {{$value->email}} <strong></p>
                <p style="margin: 2;  font-size:55%;"><strong> TEL.: {{$value->telephone}} &nbsp;&nbsp;&nbsp;&nbsp; {{$value->telephone2}}</strong></p>
                <p style="margin: 1;  font-size:55%;"><strong>CNPJ: {{$value->cnpj}}</strong></p>
                @endforeach</td>
                <td>
                  <td style="float:right;  font-size:50%;">
                    <br>
                    <br>
                    <br>
                    <strong><p style="margin: 0; font-size:55%;">Data: {{date("d/m/y")}} </p>
                    <p  style="margin: 0; font-size:55%;">Hora: &nbsp;{{substr($head->created_at, 10,18)}}</p>
                  {{--
                    <p  style="margin: 0; font-size:55%;">Página 1 de 1</p>
                    <p  style="margin: 0;  font-size:55%;">Impresso por: {{auth()->user()->name}}</p></strong>
                   --}}
                  </td>
                     <!-- Dados do Relatorio -->
                </td>
              </tr>
              </table>
              <table>
              <tr>
                <th colspan="3" style="font-size:15px;text-align:center">RECEBIMENTO DE COMPRA N°: {{$head->code}}</th>
              </tr>
            </table>
            <table>
              <tr>
                <td style="width:60%;font-size:65%;">
                <p style="font-size:12px;text-indent:1%;"><strong>Parceiro de Negócio:</strong> {{$head->cardName}}</p>
                <p style="font-size:12px;text-indent:1%;"><strong>CNPJ:</strong> {{$head->identification}}</p>
                <p style="font-size:12px;text-indent:1%;"><strong>Endereço:</strong> {{$address['Street']}} &nbsp;</p>
                <p style="font-size:12px;text-indent:1%;"><strong>Cidade: </strong>{{$address['City']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<strong> CEP:</strong> {{$address['ZipCode']}}
                <p style="font-size:12px;text-indent:1%;"><strong>Fone:</strong> {{$address['Phone1']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;</p>
                <p style="font-size:12px;text-indent:1%;"><strong>Condição de Pagamento: </strong>{{$payment['PymntGroup']}} &nbsp;</p>
              </td>

              <td>
                <td style="float:right;font-size:70%;">
                  <p style="font-size:12px;text-indent:1%;"><strong>Data do Documento:</strong> {{formatDate($head->taxDate)}}</p>
                  <p style="font-size:12px;text-indent:1%;"><strong>Data de Lançamento:</strong> {{formatDate($head->docDate)}}</p>
                  <p style="font-size:12px;text-indent:1%;"><strong>Data de Vencimento:</strong> {{formatDate($head->docDueDate)}}</p>
                  <p style="font-size:12px;text-indent:1%; "> @if(workCoin())<strong>Moeda:</strong> {{$head->coin}}&nbsp;&nbsp;&nbsp; @endif @if(workQuotation())<strong>Cotação:</strong>  {{number_format($head->quotation,2,',','.')}}@endif</p>
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
                    <th style="font-size:80%;text-align:center;">Cod.Sap</th>
                    <th style="font-size:80%;text-align:center;">Descrições</th>
                    <th style="font-size:80%;text-align:center;">Quantidade</th>
                    <th style="font-size:80%;text-align:center;">Preço Unitário</th>
                    <th style="font-size:80%;text-align:center;">Total</th>
                    <th style="font-size:80%;text-align:center;">Utilização</th>
                    <th style="font-size:80%;text-align:center;">C. de Custo</th>
                    <th style="font-size:80%;text-align:center;">Projeto</th>
                  </tr>
                  </thead>
                    <tbody>
                    @if(isset($body))
                      @foreach($body as $key => $value)
                        <tr width="100%">
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['itemCode']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{$value['itemName']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{number_format($value['quantity'],2,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{number_format($value['price'],5,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{number_format($value['lineSum'],2,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codUse']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codProject']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codCost']}}</td>
                        </tr>
                      @endForeach
                    @endif
                  <tr>

                  </tr>
              </tbody>
        </table>
        <div align="right">
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
              {{-- <p style="font-size:12px;text-indent:1%;">Desp. Importação: @if(isset($expenses[0]->lineTotal) && ($expenses[0]->expenseCode == 4)) {{number_format($expenses[0]->lineTotal,2,',','.')}} @else 0,00 @endif</p>
                <p style="font-size:12px;text-indent:1%;">Frete: @if(isset($expenses[0]->lineTotal) && ($expenses[0]->expenseCode == 1)) {{number_format($expenses[0]->lineTotal,2,',','.')}} @else 0,00 @endif</p>
                <p style="font-size:12px;text-indent:1%;">Outros: @if(isset($expenses[0]->lineTotal) && ($expenses[0]->expenseCode == 3)) {{number_format($expenses[0]->lineTotal,2,',','.')}} @else 0,00 @endif</p>
                <p style="font-size:12px;text-indent:1%;">Seguro: @if(isset($expenses[0]->lineTotal) && ($expenses[0]->expenseCode == 2)) {{number_format($expenses[0]->lineTotal,2,',','.')}} @else 0,00 @endif</p>
                --}}  
              <p style="font-size:12px;text-indent:1%;">Total: {{number_format($head->docTotal,2,',','.')}}</p>
          </div>
          <div>
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
              <p style="font-size: 12px; text-indent: 1%;">-Reservemo-nos o direito de tornar sem efeito esta O.C. caso as mercadorias venham em desacordo ou fora do prazo estipulado para entrega.</p>
                <p style="font-size: 12px; text-indent: 1%;">-Solicitamos confirmação desta solicitação de compra</p>
                <p style="font-size: 12px; text-indent: 1%;">-Favor indicar o número dessa ordem de Compra na Nota Fiscal.
              </p>
          </div>
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               <div>
                 <p style="font-size: 12px; text-indent: 1%;" >
                   <strong>Observações:</strong> {{$head->comments}}
                 </p>
                 <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               </div>
           </div>
         </form>
   </div>
<!-- Rodape -->
<article>
<div style="width: 100%; height: -500px; position: absolute; bottom: 0;left: 200;">
    <hr align="right" style="height:1px; border:none; width:200px; color:#000; background-color:#000; text-indent: 1%; "/>
    <p style="font-size:90%; text-indent:60px">{{ auth()->user()->name }}</p>
 </div>
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
