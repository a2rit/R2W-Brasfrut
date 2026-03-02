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



<div class="wrapper wrapper-content">
   <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
           <div class="ibox-title input-group-btn">
            <div class="col-md-6">
              <!-- Dados da empresa -->
              <!-- logo da empresa -->
                     <div style="float:left;">
                     <img src="{{$base64}}" class="img-reponsive" style=" max-width:600px;max-height:105px;width: auto;height: auto;">
                     </div>
              <table  style=" left:1000px; padding-left:0%" >
                <tr>
                  <td  style="width:80%; left:700px; font-size:85%;">  
                      <p> <strong> Yatch Clube da Bahia </strong></p>
                      <p style="margin: 1; font-size:80%; align:justify;"> <strong> Avenida Sete de Setembro,  3252 - Barra </strong></p>
                      <p style="margin: 1; font-size:80%;"><strong> Salvador-BA  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;CEP : 40130-001</strong></p>
                      <p style="margin: 1; font-size:80%;"><strong> EMAIL: compras@icb.com.br</strong></p>
                      <p style="margin: 2;  font-size:80%;"><strong> TELS.:  (71) 2105 - 9124/9125 &nbsp;&nbsp;&nbsp;&nbsp;</strong></p>
                      <p style="margin: 1;  font-size:80%;"><strong> CNPJ: 15.154.354/0001-68</strong></p>
                  </td>
                <td>
                  <td style="float:right;  font-size:50%;">
                    
                    <p style="margin: 0; font-size:175%; text-align:right"><strong>Data:{{formatDate($head->created_at)}} </strong></p>
                    <p style="margin: 0; font-size:175%; text-align:right"><strong>Hora:{{substr($head->created_at, 10,18)}}</strong></p>
                    {{--<p  style="margin: 0; font-size:55%;">Página 1 de 1</p>
                    <p  style="margin: 0;  font-size:55%;">Impresso por: {{auth()->user()->name}}</p>--}}
                  </td>
                     <!-- Dados do Relatorio --> 
                </td>
              </tr>
              </table>
              <table>
              <tr>
                <th colspan="3" style="font-size:15px;text-align:center;">SOLICITAÇÃO DE COMPRA N°:   {{$head->code}} / {{$head->codSAP}}</th>
              </tr>
            </table>
            <table>
              <tr>
                
                {{-- <td style="width:60%; font-size:65%;">
                <p style="font-size:12px;text-indent:1%; "><strong>Parceiro de Negócio: </strong>{{$head->cardName}} &nbsp;&nbsp;&nbsp;</p>
                <p style="font-size:12px;text-indent:1%; "><strong>CNPJ: </strong>{{$head->identification}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>
                 <strong>Contato: </strong> {{$head->contact}} 
                <p style="font-size:12px;text-indent:1%; "><strong>Endereço: </strong>@if(count($address) > 0) {{$address['Street']}} @endif &nbsp;</p>
                <p style="font-size:12px;text-indent:1%; "><strong>Cidade: </strong>@if(count($address) > 0){{$address['City']}} @endif &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; <strong> CEP: </strong> @if(count($address) > 0) {{$address['ZipCode']}}@endif
                <p style="font-size:12px;text-indent:1%;"><strong>Fone: </strong>@if(count($address) > 0){{$address['Phone1']}} @endif &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;  </p>
                <p style="font-size:12px;text-indent:1%; "><strong>Transportadora: </strong> {{$head->transporter}} &nbsp;</p>
                <p style="font-size:12px;text-indent:1%; "><strong>Condição de Pagamento: </strong>{{$payment['PymntGroup']}} &nbsp;</p>
              </td>  --}}
              <td>
                <td style="float:right;  font-size:70%;">
                  <p style="font-size:12px;text-indent:1%; text-align:right "><strong>Data de Documento: {{formatDate($head->created_at)}} </strong></p>
                  {{-- <p style="font-size:12px;text-indent:1%; text-align:right"><strong>Data de Lançamento:  </strong></p>  --}}
                 <p style="font-size:12px;text-indent:1%; text-align:right"><strong>Data Necessária: </strong> {{formatDate($head->requriedDate)}}</p>
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
                   <thead class="thead-item">
                  <tr style="background-color:gray" >
                    <th style="font-size:80%;text-align:center;">Cod.Sap</th>
                    <th style="font-size:80%;text-align:center;">Descrições</th>
                    <th style="font-size:80%;text-align:center;">Qtd.</th>
                    <th style="font-size:80%;text-align:center;">UM</th>
                    {{-- <th style="font-size:80%;text-align:center;">V.Unit</th>
                    <th style="font-size:80%;text-align:center;">Total</th> --}}
                    {{-- <th style="font-size:80%;text-align:center;">%ICMS</th>
                    <th style="font-size:80%;text-align:center;">%IPI</th>
                    <th style="font-size:80%;text-align:center;">V.IPI</th> --}}
                    {{-- <th style="font-size:80%;text-align:center;">Projeto</th> --}}
                    <th style="font-size:80%;text-align:center;">C. de Custo</th>
                    <th style="font-size:80%;text-align:center;">C. de Custo2</th> 
                  </tr>
                  </thead>
                    <tbody>
                    @if(isset($body))
                      @foreach($body as $key => $value)
                        <tr width="10%">
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['itemCode']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['itemName']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{number_format($value['quantity'],2,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['itemUnd']}}</td>
                          {{-- <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{number_format($value['price'],5,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:rigth;">{{number_format($value['lineSum'],2,',','.')}}</td> --}}
                          {{-- <td style="font-size:70%;border-bottom:0px;text-align:center;">{{number_format($obj->getTaxFromSAP($head->codSAP)[0]['ICMS'],2,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{number_format($obj->getTaxFromSAP($head->codSAP)[0]['IPI'],2,',','.')}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{number_format($obj->getTaxFromSAP($head->codSAP)[0]['VIPI'],2,',','.')}}</td> --}}
                          {{-- <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codProject']}}</td> --}}
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codCost']}}</td>
                          <td style="font-size:70%;border-bottom:0px;text-align:center;">{{$value['codCost2']}}</td>
                        </tr>
                      @endForeach
                    @endif
              </tbody>
        </table>
        <div align="right">
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
            {{--
              <p style="font-size: 12px; text-indent: 1%;"><strong>Desp. Importação:</strong> @for($i =0; $i< count($expenses); $i++)
                                                                                              @if($expenses[$i]->expenseCode == '4')
                                                                                                {{number_format($expenses[$i]->lineTotal,2,',','.')}}
                                                                                              @endif
                                                                                            @endfor</p>

            <p style="font-size: 12px; text-indent: 1%;"><strong>Frete:</strong> @for($i =0; $i< count($expenses); $i++)
                                                                                              @if($expenses[$i]->expenseCode == '1')
                                                                                                {{number_format($expenses[$i]->lineTotal,2,',','.')}}
                                                                                              @endif
                                                                                            @endfor</p>
            <p style="font-size: 12px; text-indent: 1%;"><strong>Outros:</strong> @for($i =0; $i< count($expenses); $i++)
                                                                                    @if($expenses[$i]->expenseCode == '3')
                                                                                      {{number_format($expenses[$i]->lineTotal,2,',','.')}}
                                                                                    @endif
                                                                                  @endfor</p>
            <p style="font-size: 12px; text-indent: 1%;"><strong>Seguro:</strong> @for($i =0; $i< count($expenses); $i++)
                                                                                    @if($expenses[$i]->expenseCode == '2')
                                                                                      {{number_format($expenses[$i]->lineTotal,2,',','.')}}
                                                                                    @endif
                                                                                  @endfor</p>
             --}}
            {{-- <p style="font-size: 12px; text-indent: 1%;"><strong>Total da Nota:</strong> {{number_format($head->docTotal,2,',','.')}}</p> --}}
          </div>
        </div>
          <div>
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
              <p style="font-size: 12px; text-indent: 1%;">-Reservemo-nos o direito de tornar sem efeito esta S.C. caso as mercadorias venham em desacordo.</p>
                <p style="font-size: 12px; text-indent: 1%;">-Solicitamos confirmação desta solicitação de compra</p>
                <p style="font-size: 12px; text-indent: 1%;">-Favor indicar o número dessa ordem de Compra na Nota Fiscal.
              </p>
              <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
              <div>
                <p style="font-size: 12px; text-indent: 1%;" >
                  <strong>Observações:</strong> {{$head->observation}}
                </p>
                {{-- <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/> --}}
              </div>
            </div>
          </form>
        </div>
        <!-- Rodape -->
        
        <article>
          <footer id="rodape">   
            <p style="font-size:90%; text-align: center;">{{ $head->solicitante }}</p>
            <div id="references">
              <p style=" font-size: 12px; text-indent: 1%;">
              <img src="{{$base642}}" alt=""  style="width: 8%;"/>
                &nbsp; &nbsp; &nbsp;&nbsp; Desenvolvido pela A2R Inovação em Tecnologia- Tel.: 71 35656598 &nbsp; &nbsp; &nbsp;
              <img src="{{$base641}}" alt="" align="right" style="width: 8%; padding-top:6%" /></p>
              
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
.thead-item{
  border-bottom: thin solid black;
  border-left: thin solid black;
  border-right: thin solid black;
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
div#conteudo-bottom{
  width:1030px;
  height:100px;
  float:bottom;
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
