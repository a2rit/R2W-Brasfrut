@extends('relatorios.relatorio_base')

@section('content')
<div class="wrapper wrapper-content">
   <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <div class="row">
         <div class="col-lg-12">
           <div class="ibox-title input-group-btn">
            <div class="col-md-6">
               <table  style=" left:1000px;" >
                <tr>
              <td  style="width:80%; left:700px; font-size:85%;">  @foreach($company as $value)
                <p ><strong> {{$value->company}}</strong></p>
                <p style="margin: 1; font-size:55%; align:justify;"><strong> {{$value->address}},  {{$value->number}} - {{$value->neighborhood}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong> {{$value->city}}  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;CEP : {{$value->cep}}</strong></p>
                <p style="margin: 1; font-size:55%;"><strong>EMAIL: {{$value->email}} <strong></p>
                <p style="margin: 2;  font-size:55%;"><strong>TEL.: {{$value->telephone}} &nbsp;&nbsp;&nbsp;&nbsp; {{$value->telephone2}}</strong></p>
                <p style="margin: 1;  font-size:55%;"><strong>CNPJ: {{$value->cnpj}}</strong></p>
                @endforeach</td>
                <td>
                  <br>
                  <br>
                     <!-- Dados do Relatorio -->
                </td>
              </tr>
              </table>
              <table>
              <tr>
                <th colspan="3" style="font-size:15px;"><center> Nota Fiscal de Entrada N°: {{$head_expenses->sequenceSerial}}</center></th>
              </tr>
            </table>
            <table>
              <tr>
                <td style="width:60%; font-size:65%;">
                <p style="font-size: 12px; text-indent: 1%; "><strong>Parceiro de Negócio:</strong> {{$head->cardName}} </p>
                <p style="font-size: 12px; text-indent: 1%; "><strong>Endereço:</strong> {{$address['Street']}} &nbsp;</p>
                <p style="font-size: 12px; text-indent: 1%; "><strong>Cidade: </strong>{{$address['City']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<strong> CEP:</strong> {{$address['ZipCode']}}
                <p style="font-size: 12px; text-indent: 1%;"><strong>Fone:</strong> {{$address['Phone1']}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;</p>
                <p style="font-size: 12px; text-indent: 1%; "><strong>Condição de Pagamento: </strong>{{$payment['PymntGroup']}} &nbsp;</p>
              </td>
              <td>
                <td style="float:right;  font-size:70%;">
                  <p style=" font-size: 12px; text-indent: 1%;"><strong>Data do Documento:</strong> {{formatDate($head->taxDate)}}</p>
                  <p style=" font-size: 12px; text-indent: 1%;"><strong>Data de Lançamento:</strong> {{formatDate($head->docDate)}}</p>
                  <p style=" font-size: 12px; text-indent: 1%;"><strong>Data de Vencimento:</strong> {{formatDate($head->docDueDate)}}</p>
                  <p style="font-size: 12px; text-indent: 1%; "> @if(workCoin())<strong>Moeda:</strong> {{$head->coin}}&nbsp;&nbsp;&nbsp; @endif @if(workQuotation())<strong>Cotação:</strong>  {{number_format($head->quotation,2,',','.')}}@endif</p>
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
                    <th style="font-size:80%;"><center>Imposto retido</center></th>
                    <th style="font-size:80%;"><center>Quantidade</center></th>
                    <th style="font-size:80%;"><center>Preço Unitário</center></th>
                    <th style="font-size:80%;"><center>Total</center></th>
                    <th style="font-size:80%;"><center>Utilização</center></th>
                    <th style="font-size:80%;"><center>C. de Custo 1</center></th>
                    <th style="font-size:80%;"><center>C. de Custo 2</center></th>
                    <th style="font-size:80%;"><center>Projeto</center></th>
                  </tr>
                  </thead>
                  
                    <tbody>
                    @if(isset($body))

                    <?php $cont = 1;?>
                      @foreach($body as $key => $value)
                        <tr width="10%">
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['itemCode']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['itemName']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format(isset($withheld_taxes_items[$value['itemId']]) ? $withheld_taxes_items[$value['itemId']]->sum('Value') : 0,2,',','.')}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value['quantity'],2,',','.')}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value['price'],2,',','.')}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format(((Float)$value['lineSum'] - ((Float)isset($withheld_taxes_items[$value['itemId']]) ? $withheld_taxes_items[$value['itemId']]->sum('Value') : 0)),2,',','.')}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['codUse']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{substr($value['codCost'], 0,25)}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{substr($value['codCost2'], 0,25)}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{substr($value['codProject'], 0,15)}}</center></td>
                        </tr>
                        <?php $cont++ ?>
                      @endForeach
                    @endif
                  <tr>
                  </tr>
              </tbody>
        </table>
        <div >
          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
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
            <p style="font-size: 12px; text-indent: 1%;"><strong>Total sem descontos:</strong> {{number_format($head->docTotal,2,',','.')}}</p>
            <p style="font-size: 12px; text-indent: 1%;"><strong>Imposto retido:</strong> {{$withheld_taxes_items['total']}}</p>
            <p style="font-size: 12px; text-indent: 1%;"><strong>Valor adiantado:</strong> {{number_format($advancePayments->sum('DocTotal') - $advancePayments->sum('DpmAppl'),2,',','.')}}</p>
            <p style="font-size: 12px; text-indent: 1%;"><strong>Total a pagar:</strong> {{number_format($head->total_a_pagar,2,',','.')}}</p>
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
    <p style="font-size:90%; text-indent:60px">{{ getUserName($head->idUser) }}</p>
 </div>

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
@endsection
