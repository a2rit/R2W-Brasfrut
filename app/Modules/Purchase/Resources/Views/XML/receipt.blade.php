@extends('layouts.app')
@section('title', 'Recebimento de Mercadorias')
@section('content')

<div class="wrapper wrapper-content">
     <form action="{{route('purchase.receipts.goods.xml.save')}}" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Salvando...')">
        {!! csrf_field() !!}
      <div class="row" id='form'>
         <div class="col-lg-12">
            <div class="ibox">
               <div class="ibox-title input-group-btn">
               <div class="col-md-8">
                  <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5><a href="{{route('purchase.receipts.goods.index')}}"> &nbsp;Pedido de compra</a>&nbsp;</h5>
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5>Cadastrar</h5></div>
                  <div class="col-md-2"></div>
                  <div class="col-md-2">
                    <a href="{{route('purchase.receipts.goods.search')}}"><img src="{{asset('images/searchDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
                  </div>
               </div>
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins">{{formatDate($head[0]->DocumentDate)}}</h1>
                              <div class="stat-percent font-bold text-danger"><i class="fa fa-calendar" style="font-size:36px"></i>
                              </div>
                              <small>Criação</small>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins">Pendente</h1>
                              <div class="stat-percent font-bold text-navy"><i class="fa fa-tag" style="font-size:36px"></i>
                              </div>
                              <small>Status</small>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins" id="totalHeader">{{number_format($head[0]->docTotal,2,',','.')}}</h1>
                              <div class=" no-margins stat-percent font-bold text-info ">
                                 <i class="fa fa-dollar" style="font-size:36px"></i>
                              </div>
                              <small>Total</small>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Cod. SAP</label>
                          <input class="form-control" name="codSAP" disabled>
                       </div>
                     </div>
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Cod. WEB</label>
                          <input class="form-control" name="codWEB" disabled>
                       </div>
                     </div>
                     <div class="col-md-4">
                        <label>Parceiro de Negócio</label>
                           <input type="text" class="form-control" id="parceiroNegocio" value="{{$head[0]->name}}" required name="parceiroNegocio" disabled>
                           <input type="hidden" name="codPN" value="{{$head[0]->cnpj}}">
                     </div>
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Fluxo de caixa</label>
                          <select class="form-control selectpicker" data-live-search="true" data-size="5" name="cashFlow" required='true' >
                             <option value=''>SELECIONE</option>
                             @foreach($cashFlow as $key => $value)
                               <option value="{{$value->id}}">{{$value->value}}</option>
                              @endForeach
                          </select>
                       </div>
                     </div>
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Moeda</label>
                          <select class="form-control selectpicker" name="coin" required='true'>
                               <option value=''>SELECIONE</option>
                               <option value="R$">Real</option>
                               <option value="EUR">Euro</option>
                          </select>
                       </div>
                     </div>
                  </div>
                  <div class="row" style="padding-top:1%">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label>Data do Documento</label>
                           <input type="text" name="dataDocumento"  value="{{formatDate($head[0]->DocumentDate)}}" class="form-control datepicker" class="form-control datepicker" required>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label>Data de Lançamento</label>
                           <input type="text" name="dataLancamento" value="{{formatDate($head[0]->DocumentLancament)}}"  class="form-control datepicker" class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label>Data de Vencimento</label>
                           <input type="text" name="dataVencimento"  value="{{formatDate($head[0]->DocumentVencimento)}}" class="form-control datepicker" class="form-control" required>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label>Condição de Pagamento</label>
                           <select class="form-control selectpicker" data-live-search="true" required data-size="10" required name="condPagamentos">
                              <option value=''>Selecione</option>
                              @foreach($paymentConditions as $key => $value)
                              <option value="{{$value['GroupNum']}}">{{$value['PymntGroup']}}</option>
                              @endForeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group"><label>Valor Frete</label>
                           <input type="text" id="vlFrete" value="{{number_format($head[0]->totalFrete,2,',','.')}}" readonly name="valorFrete" class="form-control money" value="0">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group"><label>Valor do Desconto</label>
                           <input type="text" id="vlDesconto" value="{{number_format($head[0]->totalDesconto,2,',','.')}}" readonly name="valorDesconto" class="form-control percentual" maxlength="2" value="0">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-12">
            <div class="tabs-container">
               <ul class="nav nav-tabs" id="myTabs">
                 <li class="active"><a data-toggle="tab" href="#tab-1">Geral</a></li>
                 <li><a data-toggle="tab" href="#tab-2">Impostos</a></li>
                 <li><a data-toggle="tab" href="#tab-3">Anexo</a></li>
               </ul>
               <div class="tab-content">
                  <div class="tab-pane active" id="tab-1">
                     <div class="panel-body">
                        <div class="table-responsive">
                           <table id="requiredTable" class="table table-striped table-bordered table-hover dataTables-example" style="width: 100%;">
                              <thead>
                                 <tr>
                                    <th style="width: 2%">#</th>
                                    <th style="width: 8%">Cod. SAP</th>
                                    <th style="width: 20%">Descrições</th>
                                    <th style="width: 8%">Quantidade</th>
                                    <th style="width: 12%">Preço Unitário</th>
                                    <th style="width: 8%">Total</th>
                                    <th>Utilização</th>
                                    <th>Projeto</th>
                                    <th>Centro de Custo</th>
                                    <th style="width: 6%">CFOP</th>
                                 </tr>
                              </thead>
                              <tbody>
                                <?php $cont=1;?>
                                @foreach($body as $key => $value)
                                  <tr>
                                    <td>{{$cont}}</td>
                                    <td>{{$value->itemCode}}<input type="hidden" name="items[{{$cont}}][codSAP]" value="{{$value->itemCode}}"></td>
                                    <td>{{$value->itemName}}<input type="hidden" name="items[{{$cont}}][itemName]" value="{{$value->itemName}}"></td>
                                    <td>{{number_format($value->qCom,1,',','.')}}<input type="hidden" name="items[{{$cont}}][qtd]" value="{{number_format($value->qCom,1,',','.')}}"></td>
                                    <td>{{number_format($value->vUnCom,2,',','.')}}<input type="hidden" name="items[{{$cont}}][preco]" value="{{number_format($value->vUnCom,2,',','.')}}"></td>
                                    <td>{{number_format($value->vProd,2,',','.')}}<input type="hidden" name="items[{{$cont}}][price]" value="{{number_format($value->vProd,2,',','.')}}"></td>
                                    <td style="width: 10%">
                                      <select class='form-control'name='items[{{$cont}}][use]' id="use-{{$cont}}" required >
                                        <option value=''>Selecione</option>
                                        @foreach($use as $keys => $values)
                                        <option value='{{$values['code']}}'>{{$values['code']}} - {{$values['value']}}</option>
                                        @endForeach
                                      </select>
                                    </td>
                                    <td style="width: 12%">
                                      <select class='form-control' data-live-search='true' name='items[{{$cont}}][projeto]' id="projeto-{{$cont}}" required >
                                        @foreach($projeto as $keys => $values)
                                          <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                        @endForeach
                                      </select>
                                    </td>
                                    <td>
                                      <select class='form-control' name='items[{{$cont}}][rule]' id="rule-{{$cont}}" required>
                                        @foreach($role as $keys => $values)
                                          <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                        @endForeach
                                     </select>
                                    </td>
                                    <td>{{$value->CFOP}}<input type="hidden" name="items[{{$cont}}][CFOP]" value="{{$value->CFOP}}"></td>
                                  </tr>
                                    <?php $cont++;?>
                                @endForeach
                              </tbody>
                           </table>
                           <div class="col-md-3">
                              <label>Utilização Principal</label>
                              <select class='form-control' data-live-search='true' name='use' id='useGlobal' onchange="setUseFull()" >
                                 <option value=''>Selecione</option>
                                 @foreach($use as $keys => $values)
                                 <option value='{{$values['code']}}'>{{$values['code']}} - {{$values['value']}}</option>
                                 @endForeach
                              </select>
                           </div>
                           <div class="col-md-3">
                              <label>Projeto Principal</label>
                              <select class='form-control' data-live-search='true' name='project' id='projectGlobal' onchange="setProjecFull()" >
                                 <option value=''>Selecione</option>
                                 @foreach($projeto as $keys => $values)
                                 <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                 @endForeach
                              </select>
                           </div>
                           <div class="col-md-3">
                              <label>Centro de Custo Principal</label>
                              <select class='form-control' data-live-search='true' name='role' id='roleGlobal' onchange="setRoleFull()" >
                                 <option value=''>Selecione</option>
                                 @foreach($role as $keys => $values)
                                 <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                 @endForeach
                              </select>
                           </div>
                           <div class="col-md-3">
                              <div class="form-group">
                                 <label>Total</label>
                                 <input type="text" class="form-control" value="{{number_format($head[0]->docTotal,2,',','.')}}" disabled id="totalNota">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane" id="tab-2">
                     <div class="panel-body">
                          <div class="col-md-2">
                              <div class="form-group">
                                 <label>Tipo</label>
                                 <select class="form-control" required='true' name="type_tax">
                                    <option value=''>Selecione</option>
                                    <option value="-1">Manual</option>
                                    <option value="-2" selected>Externo</option>
                                </select>
                              </div>
                          </div>
                          <div class="col-md-3">
                              <div class="form-group">
                                 <label>Número NF</label>
                                 <input type="text" class="form-control" required name="number_nf">
                              </div>
                          </div>
                          <div class="col-md-2">
                              <div class="form-group">
                                 <label>Serie</label>
                                 <input type="text" class="form-control" name="serie">
                              </div>
                          </div>
                          <div class="col-md-2">
                              <div class="form-group">
                                 <label>Subserie</label>
                                 <input type="text" class="form-control" name="sserie">
                              </div>
                          </div>
                          <div class="col-md-3">
                             <div class="form-group">
                                <label>Modelo</label>
                                <select class="form-control" data-live-search="true" data-size="10" required name="model">
                                   <option  value=''>Selecione</option>
                                   @foreach($model as $key => $value)
                                    <option value="{{$value['NfmCode']}}">{{$value['NfmName']}} - {{$value['NfmDescrip']}}</option>
                                   @endForeach
                               </select>
                             </div>
                         </div>
                     </div>
                  </div>
                  <div class="tab-pane" id="tab-3">
                    <div class="panel-body">
                       <div class="col-md-6">
                          <label>Observação</label>
                          <textarea class="form-control" rows="5" name="obsevacoes"></textarea>
                       </div>
                       <div class="col-md-6" style="padding-top: 5%">
                          <!-- image-preview-filename input [CUT FROM HERE]-->
                          <div class="input-group image-preview">
                             <input type="text" class="form-control image-preview-filename" disabled="disabled"> <!-- don't give a name === doesn't send on POST/GET -->
                             <span class="input-group-btn">
                                <!-- image-preview-clear button -->
                                <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                <span class="glyphicon glyphicon-remove"></span> Remover
                                </button>
                                <!-- image-preview-input -->
                                <div class="btn btn-default image-preview-input">
                                   <span class="glyphicon glyphicon-folder-open"></span>
                                   <span class="image-preview-input-title">Abrir</span>
                                   <input type="file" multiple name="input-file-preview[]"/> <!-- rename it -->
                                </div>
                             </span>
                          </div>
                          <!-- /input-group image-preview [TO HERE]-->
                       </div>
                    </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-12  -align-right right pull-right right">
            <div class="form-group pull-right">
               <div class="hr-line-dashed"></div>
               <button class="btn btn-primary" type="submit">Salvar</button>
               <div class="hr-line-dashed"></div>
            </div>
         </div>
      </div>
</form>
</div>
<div class="modal inmodal" id="gastosExtras" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span
               aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Adicionar Despesas</h4>
         </div>
         <div class="modal-body">
            <form onsubmit="exPensesAdditional(); return false;" id="expensesForm">
               <input type="hidden" name="line">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label>Tipo</label>
                        <select class="form-control selectpicker" data-live-search="true" data-size="10" required id="cPagamentos" name="condPagamentos">
                           <option>Selecione</option>
                           @foreach($typeOut as $key => $value)
                           <option value="{{$value['code']}}">{{$value['value']}}</option>
                           @endForeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <label>Valor</Label>
                     <input type="text" class="form-control money" id="vFrete" name="valorFrete">
                  </div>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" form="expensesForm" class="btn btn-white" data-dismiss="modal">Cancelar
            </button>
            <button type="submit" form="expensesForm" class="btn btn-primary">Adicionar</button>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
       $(document).ready(function(){
         $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
       });
       function changeClassification() {
              // Service
              if ($("[name='classification']:checked").val() === '1') {
                  $("#info-transfer").show();
                  $("#info-money").hide();
                  $("[name='source']").prop('required', false);
                  $("[name='material_type']").prop('required', false);
              } else {
                  $("#info-transfer").hide();
                  $("#info-money").show();
                  $("[name='source']").prop('required', true);
                  $("[name='material_type']").prop('required', true);
              }
          }
       $(document).on('click', '#close-preview', function(){
           $('.image-preview').popover('hide');
           // Hover befor close the preview
           $('.image-preview').hover(
         function () {
            $('.image-preview').popover('show');
         },
          function () {
            $('.image-preview').popover('hide');
         }
        );
        });
       $(function() {
         // Create the close button
         var closebtn = $('<button/>', {
             type:"button",
             text: 'x',
             id: 'close-preview',
             style: 'font-size: initial;',
         });
         closebtn.attr("class","close pull-right");
         // Set the popover default content
         $('.image-preview').popover({
             trigger:'manual',
             html:true,
             title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
             content: "There's no image",
             placement:'bottom'
         });
         // Clear event
         $('.image-preview-clear').click(function(){
             $('.image-preview').attr("data-content","").popover('hide');
             $('.image-preview-filename').val("");
             $('.image-preview-clear').hide();
             $('.image-preview-input input:file').val("");
             $(".image-preview-input-title").text("Browse");
         });
         // Create the preview image
         $(".image-preview-input input:file").change(function (){
             var img = $('<img/>', {
                 id: 'dynamic',
                 width:250,
                 height:200
             });
             var file = this.files[0];
             var reader = new FileReader();
             // Set preview image into the popover data-content
             reader.onload = function (e) {
                 $(".image-preview-input-title").text("Change");
                 $(".image-preview-clear").show();
                 $(".image-preview-filename").val(file.name);
                 img.attr('src', e.target.result);
                 $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
             }
             reader.readAsDataURL(file);
         });
       });
       var index = '{{$cont}}';
       function setUseFull(){
         var val = document.getElementById('useGlobal').value;
         var i;
         for(i = 1; i <= index; i++){
             if(document.getElementById('use-'+i)){
               document.getElementById('use-'+i).value = val;
             }
         }
       }
       function setProjecFull(){
        var val = document.getElementById('projectGlobal').value;
        var i;
        for(i = 1; i <= index; i++){
            if(document.getElementById('project-'+i)){
              document.getElementById('project-'+i).value = val;
            }
        }
      }
      function setRoleFull(){
       var val = document.getElementById('roleGlobal').value;
       var i;
       for(i = 1; i <= index; i++){
           if(document.getElementById('rule-'+i)){
             document.getElementById('rule-'+i).value = val;
           }
       }
     }
</script>
@endsection
