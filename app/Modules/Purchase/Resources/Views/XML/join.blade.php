@extends('layouts.app')
@section('title', 'Importador XML')
@section('content')
<div class="wrapper wrapper-content">
   <form action="{{route('purchase.xml.join.save')}}" method="post" id="needs-validation" enctype="multipart/form-data"
      onsubmit="waitingDialog.show('Carregando...')">
      {!! csrf_field() !!}
      <div class="row">
         <div class="ibox-title input-group-btn ">
            <div class="col-md-8">
               <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5><a href="{{route('purchase.order.import.xml')}}"> &nbsp;Importador XML</a>&nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5>Associar ao Pedido de compra</h5>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-2">
               <a href="{{route('purchase.order.create')}}"><img src="{{asset('images/newDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
            </div>
         </div>
         <div class="ibox-content">
            <div class="row">
               <div class="col-md-4">
                  <div class="form-group">
                     <label>Nome Fantasia</label>
                     <input type="text" class="form-control" value="{{$head[0]->name}}" disabled>
                  </div>
               </div>
               <div class="col-md-2">
                  <div class="form-group">
                     <label>CNPJ</label>
                     <input type="text" class="form-control" value="{{$head[0]->cnpj}}" disabled>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <label>XML</label>
                     <input type="text" class="form-control" value="{{$head[0]->chNFe}}" disabled>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <div class="form-group">
                     <div class="table-responsive">
                        <label>Pedidos de compras aberto</label>
                        <table class="table table-striped table-bordered table-hover dataTables-example" style="padding-top: 5%;">
                           <thead>
                              <tr>
                                 <th>#</th>
                                 <th>Cod. WEB</th>
                                 <th>Cod. SAP</th>
                                 <th>Data</th>
                                 <th>Total</th>
                                 <th>Autor</th>
                                 <th>Observação</th>
                                 <th>Detalhes</th>
                              </tr>
                           </thead>
                           <tbody>
                              @if(isset($OPOR))
                              <?php  $aux=1;?>
                              @foreach($OPOR as $key => $value)
                              <tr>
                                 <td><input type="checkbox" value="{{$value->id}}" id="assoc-{{$aux}}" name="assoc" onclick="checkPurchaseOpen({{$aux}})"></td>
                                 <td>{{$value->code}}</td>
                                 <td>{{$value->codSAP}}</td>
                                 <td>{{formatDate($value->taxDate)}}</td>
                                 <td>{{number_format($value->docTotal,2,',','.')}}</td>
                                 <td>{{$value->name}}</td>
                                 <td>{{$value->comments}}</td>
                                 <td>
                                    <center><span class="btn btn-default" onclick="loadModel('{{$value->codSAP}}')"><i class="fa fa-arrows-alt" aria-hidden="true"></i></span></center>
                                 </td>
                              </tr>
                              <?php  $aux++;?>
                              @endforeach
                              @endif
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <label>Observação</label>
                <textarea class="form-control" disabled rows='5'>{{$head[0]->comments}}</textarea>
              </div>
            </div>
         </div>
         <div class="col-md-12  -align-right right pull-right right">
            <div class="form-group pull-right">
              <div class="hr-line-dashed"></div>
               <button class="btn btn-primary" type="submit">Continuar</button>
            </div>
         </div>
      </div>
   </form>
</div>

<div class="modal inmodal" id="previewModal" style="width: 100%;">
   <div class="modal-dialog modal-lg" style="width: 92%;">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title">Itens</h4>
         </div>
         <div class="modal-body">
            <form onsubmit="addItems(); return false;" id="itemsForm">
               <div class="row">
                  <div class="col-md-6">
                     <center><label>Pedido de Compra</label></center>
                     <table id="table" class="table table-striped table-bordered table-hover">
                        <thead>
                           <tr>
                              <th>Cod. SAP</th>
                              <th>Descrição</th>
                              <th>Quantidade</th>
                              <th>Preço</th>
                           </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                  </div>
                  <div class="col-md-6">
                     <center><label>XML NF-e</label></center>
                     <table id="table" class="table table-striped table-bordered table-hover">
                        <thead>
                           <tr>
                              <th>Cod. Produto</th>
                              <th>Nome</th>
                              <th>Quantidade</th>
                              <th>Preço</th>
                              <th>Total</th>
                           </tr>
                        </thead>
                        <tbody>
                        @if(isset($item))
                           @foreach($item as $key => $value)
                           <tr>
                              <td>{{$value->codPartners}}</td>
                              <td>{{$value->name}}</td>
                              <td>{{$value->qCom}}</td>
                              <td>{{number_format($value->vUnCom,2,',','.')}}</td>
                              <td>{{number_format($value->vProd,2,',','.')}}</td>
                           </tr>
                           @endForeach
                           @endif
                        </tbody>
                     </table>
                  </div>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" form="itemsForm" class="btn btn-white" data-dismiss="modal">Fechar</button>
         </div>
      </div>
   </div>
</div>
@endsection
@section("scripts")
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script>
   var contador = @if(isset($aux)) {{$aux}} @else 1 @endif;
   function checkPurchaseOpen(id){
      var is_checked_purchase = 0;
      var selected  = $('#assoc-'+id).is(':checked');
      
      if(selected){
         for(var i=1;i<contador; i++){
            if($('#assoc-'+i).is(':checked')){
                  is_checked_purchase++;
               }
            }
            if(is_checked_purchase > 1){
               swal ( "Só pode selecionar apenas um pedido de compra!" ,  "" ,  "error" );
               $('#assoc-'+id).prop('checked',false);
            }
         }
   }
   function loadModel(code){
     $('#table').dataTable().fnDestroy();
     $("#table").DataTable({
       processing: true,
       serverSide: true,
       ajax: {
           url: "{{route('purchase.xml.get.items')}}"+'/'+code
       },
       columns: [
           {name: 'ItemCode', data: 'ItemCode'},
           {name: 'Dscription', data: 'Dscription'},
           {name: 'Quantity', data: 'Quantity', render: formatNumber, orderable: false},
           {name: 'Price', data: 'Price', render: formatMoney, orderable: false}
       ],
       "dom": "lfrti",
       "bFilter": false,
       "bInfo": false,
       "bLengthChange": false,
       language: dataTablesPtBr
   });
       $('#previewModal').modal();
   }
   function addItems() {
       var form = $('#itemsForm');
       var modal = $('#previewModal');
       var head = $('#needs-validation');


       form.find('select').each(function (index, item) {
         head.append("<input type='hidden' value='"+item.value+"' name='join["+item.id+"]'>");

       });

       form.find('select').val('');
       modal.modal('hide');
       return false;
   }
   function formatNumber(number){
       return parseFloat(number).format(2, ",", ".");
   }
   function formatMoney(number){
       return parseFloat(number).format(2, ",", ".");
   }
</script>
@endsection
