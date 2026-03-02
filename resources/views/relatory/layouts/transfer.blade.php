<?php
// tentar ver isso depois
$path = public_path("img/logo-nova.png");
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
                  <td  style="width:80%; left:700px; font-size:85%;">  
                    <p> <strong> Yatch Clube da Bahia </strong></p>
                    <p style="margin: 1; font-size:80%; align:justify;"> <strong> Avenida Sete de Setembro,  3252 - Barra </strong></p>
                    <p style="margin: 1; font-size:80%;"><strong> Salvador-BA  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;CEP : 40130-001</strong></p>
                    <p style="margin: 1; font-size:80%;"><strong> EMAIL: compras@icb.com.br</strong></p>
                    <p style="margin: 2;  font-size:80%;"><strong> TELS.:  (71) 2105 - 9124/9125 &nbsp;&nbsp;&nbsp;&nbsp;</strong></p>
                    <p style="margin: 1;  font-size:80%;"><strong> CNPJ: 15.154.354/0001-68</strong></p>
                </td>
                td>
                  <td style="float:right;  font-size:100%;">
                    <br>
                    <br>
                    <br>
                    <strong><p style="margin: 0; font-size:80%;">Data: {{date("d/m/y")}} </p>
                    <p  style="margin: 0; font-size:80%;">Hora: &nbsp;{{substr($head->created_at, 10,18)}}</p>
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
                  <th colspan="3" style="font-size:15px;"><center>TRANSFÊNCIA N°: {{$head->code}}</center></th>
                </tr>
              </table>
              <table>
                <tr>
                  <td style="width:55%; font-size:65%;">
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Cod.SAP:</strong>  {{$head->codSAP}} &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;</p>
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Usuário:</strong>  {{$user->name}}&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;</p>
                  <p style="font-size: 12px; text-indent: 1%; "><strong>Deposito Atual:</strong> {{$head->toWarehouse}}&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; <strong>Deposito Destino:</strong> {{$head->fromWarehouse}} </p>
                </td>
                <td>
                  <td style="float:right;  font-size:65%;">
                    <p style=" font-size: 12px; text-indent: 1%;"><strong> Data do Documento:</strong>  {{formatDate($head->taxDate)}}</p>
                    <p style=" font-size: 12px; text-indent: 1%;"><strong> Data de Lançamento:</strong>  {{formatDate($head->docDate)}}</p>
                  </td>
                     <!-- Dados do Relatorio -->
                </td>
                </tr>
            </table>
                <!-- Linha -->
              <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px;"/>
                <table>
                   <thead>
                  <tr>
                    <th style="font-size:80%;"><center>Cod.Sap</center></th>
                    <th style="font-size:80%;"><center>Descriçao</center></th>
                    <th style="font-size:80%;"><center>Quantidade</center></th>
                    <th style="font-size:80%;"><center>Usuario</center></th>
                    <th style="font-size:80%;"><center>Projeto</center></th>
                    <th style="font-size:80%;"><center>Centro de Custo</center></th>
                    <th style="font-size:80%;"><center>Centro de Custo2</center></th>
                  </tr>
                  </thead>
                  <thead>
                 </thead>
                    <tbody>
                      @if(isset($body))
                      <?php $cod =1; $total = 0;?>
                        @foreach($body as $key => $value)
                        <tr width="10%">
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['itemCode']}}<center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['itemName']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{number_format($value['quantity'], 3, ',', '.')}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$user->name}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['projectCode']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['distributionRule']}}</center></td>
                          <td style="font-size:70%; border-bottom: 0px;"><center>{{$value['distributionRule2']}}</center></td>
                        </tr>
                    <?php $cod++;?>
                  @endForeach
                @endif
              </tbody>
                </table>
          </div>

          <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               <div>
                 <p style="font-size: 12px; text-indent: 1%;" >
                   OBSERVAÇÂO: {{$head->comments}}
                 </p>
                 <hr style="height:1px; border:none; color:#000; background-color:#000; margin-top: 0px; margin-bottom: 0px; text-indent: 1%;"/>
               </div>
               <br>
               <br>
               <br>
               <br>
           </div>
         </form>
        </div>
          <div>
        </div>

            <br>
            <br>
            <br>
            <br>
        </div>
      </form>
</div>
<!-- Rodape -->
<article>
  <footer id="rodape">   
    <?php
  // tentar ver isso depois
  $path1 = public_path("images/img-footer2.png");
  $type1 = pathinfo($path1, PATHINFO_EXTENSION);
  $data1 = file_get_contents($path1);
  $base641 = 'data:image/' . $type1 . ';base64,' . base64_encode($data1);
  
  // tentar ver isso depois
  $path2 = public_path("images/a2r-10anos.png");
  $type2 = pathinfo($path2, PATHINFO_EXTENSION);
  $data2 = file_get_contents($path2);
  $base642 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
  ?>
  
    <div>
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
