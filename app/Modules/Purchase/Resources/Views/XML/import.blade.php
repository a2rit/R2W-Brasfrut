@extends('layouts.app')

@section('title', 'Importador XML')

@section('content')
<div class="wrapper wrapper-content">
      <div class="row">
        <div class="col-lg-12">
           <div class="ibox ">
              <div class="ibox-title input-group-btn ">
                 <h5>Importador de XML</h5>
              </div>
            <form action="{{route('purchase.order.read.xml')}}" method="post" id="needs-validation" enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
                {!! csrf_field() !!}
              <div class="ibox-content">
                 <div class="row">
                    <div class="col-md-12">
                       <div class="form-group">
                          <label>Escolha o arquivo:</label>
                          <div class="input-group input-file" name="Fichier1">
                             <input type="text" name="xml"  name='uploadXML' required class="form-control" placeholder='Clique aqui' onchange="valideXML(this)"/>
                             <span class="input-group-btn"><button class="btn btn-default btn-choose" type="button">XML</button></span>
                          </div>
                       </div>
                       <button class="btn btn-primary">Gerar</button>
                    </div>
                 </div>
              </div>
            </form>
              <div class="ibox-content">
                 <div class="row">
                   <form action="{{route('purchase.xml.filter')}}" method="post" onsubmit="waitingDialog.show('Carregando...')">
                     {!! csrf_field() !!}
                       <div class="ibox-content">
                         <div class="row">
                           <div class="col-md-12">
                             <div class="col-md-2">
                               <label>NF</Label>
                               <input type="text" class="form-control" placeholder="10" name="nf" autocomplete="off">
                             </div>
                             <div class="col-md-4">
                               <label>Parceiro</Label>
                               <input type="text" class="form-control" placeholder="Davi" name="name" autocomplete="off">
                             </div>
                             <div class="col-md-4">
                               <label>CNPJ</Label>
                               <input type="text" class="form-control" id='cpfcnpj' placeholder="337.214.827-43" name="cnpj" autocomplete="off">
                             </div>
                             <div class="col-md-2  -align-right right pull-right right" style="padding-top: 3%">
                               <div class="form-group pull-right">
                                   <button class="btn btn-success" type="submit">Filtrar</button>
                               </div>
                            </div>
                           </div>
                         </div>
                         <div class="row">
                              <div class="col-md-12">
                                <div class="table-responsive">
                                  <table id="table" class="table table-striped table-bordered table-hover dataTables-example" >
                                    <thead>
                                       <tr>
                                         <th style="width: 5%">#</th>
                                         <th>RM</th>
                                         <th>Fornecedor</th>
                                         <th>CNPJ</th>
                                         <th>NF</th>
                                         <th>Dt. Doc</th>
                                         <th>Dt. Lan</th>
                                         <th>Valor</th>
                                         <th>Opções</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                      @if(isset($items))
                                      <?php $cont =1;?>
                                        @foreach($items as $key => $value)
                                         <tr>
                                           <td style="width: 5%">{{$cont}}</td>
                                           <td>{{$value->codSAP}}</td>
                                           <td>{{$value->name}}</td>
                                           <td>{{$value->cnpj}}</td>
                                           <td>{{$value->nNF}}</td>
                                           <td>{{formatDate($value->taxDate)}}</td>
                                           <td>{{formatDate($value->created_at)}}</td>
                                           <td>{{number_format($value->docTotal,2,',','.')}}</td>
                                           <td><center><i class="fa fa-folder-open" aria-hidden="true" style="font-size: 25px;" onclick="previews({{$value->id}})"></i></center></td>
                                         </tr>
                                         <?php $cont++;?>
                                        @endForeach
                                      @endif
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                         </div>
                       </div>
                    </form>
                 </div>
              </div>
           </div>
        </div>
     </div>
</div>

<div class="modal inmodal" id="previewModal" tabindex="-1" role="dialog">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h4 class="modal-title">Items</h4>
             </div>
             <div class="modal-body">
                 <div class="row">
                   <div class="table-responsive">
                           <table id="tableModal" class="table table-striped table-bordered table-hover">
                             <thead>
                             <tr>
                               <th style="width: 10%">Nome</th>
                               <th style="width: 45%">Quantidade</th>
                               <th style="width: 10%">Valor Unitário</th>
                               <th style="width: 10%">Total</th>
                             </tr>
                             </thead>
                             {{--<tfoot style="display: table-header-group">
                             <tr>
                                 <th><input type="text" name="code" class="form-control"/></th>
                                 <th><input type="text" name="name" class="form-control"/></th>
                             </tr>
                             </tfoot>--}}
                           </table>
                        </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
@endsection

@section("scripts")
<script>
    $(document).ready(function(){
        $("#cpfcnpj").mask("99.999.999/9999-99");
    });
    function removeMasc(){
      $("#cpfcnpj").unmask();
    }

    $("#table").css("width","100%");
    let table = $("#table").DataTable({
        responsive: true,
        buttons: [],
        lengthMenu: [50, 100, 150],
        "searching": false,
        "bInfo": false,
        language: dataTablesPtBr
    });
    function previews(item){
        $('#tableModal').dataTable().fnDestroy();
        $("#tableModal").DataTable({
          processing: true,
          serverSide: true,
          ajax: {
              url: "{{route('purchase.xml.get.item.modal')}}" + '/'+item
          },
          columns: [
              {name: 'name', data: 'name'},
              {name: 'qCom', data: 'qCom', render: formatNumber, orderable: false},
              {name: 'vUnCom', data: 'vUnCom', render: formatNumber, orderable: false},
              {name: 'vProd', data: 'vProd', render: formatNumber, orderable: false}
          ],
          order: [[1, "desc"]],
          language: dataTablesPtBr,
          paging: false,
          "lengthChange": false,
          "ordering": false,
          "bFilter": false,
          "bInfo": false,
          "searching": false
      });
          $('#previewModal').modal('show');
    }
    function formatNumber(number){
      return parseFloat(number).toFixed(2);
    }
   function valideXML(num){
     var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.xml)$/;
                if (regex.test($("#uploadXML").val().toLowerCase())) {
                   if (typeof (FileReader) != "undefined") {
                   } else {
                     swal("Opss!!! Seu navegador não tem suporte.", " Por favor use outro, recomendamos o Google chrome", "info");
                   }
                 } else {
                 swal("Arquivo inválido", "Por favor faça Upload de um arquivo XML valido.", "error");
                 $("#uploadXML").val("");
               }
   }
   function bs_input_file() {
   	$(".input-file").before(
   		function() {
   			if ( ! $(this).prev().hasClass('input-ghost') ) {
   				var element = $("<input type='file' class='input-ghost' name='uploadXML' id='uploadXML' style='visibility:hidden; height:0' onchange='valideXML(this)'>");
   				element.attr("name",$(this).attr("name"));
   				element.change(function(){
   					element.next(element).find('input').val((element.val()).split('\\').pop());
   				});
   				$(this).find("button.btn-choose").click(function(){
   					element.click();
   				});
   				$(this).find("button.btn-reset").click(function(){
   					element.val(null);
   					$(this).parents(".input-file").find('input').val('');
   				});
   				$(this).find('input').css("cursor","pointer");
   				$(this).find('input').mousedown(function() {
   					$(this).parents('.input-file').prev().click();
   					return false;
   				});
   				return element;
   			}
   		}
   	);
   }
   $(function() {
     bs_input_file();
   });
</script>
@endsection
