@extends('layouts.app')
@section('title', 'Contas a Pagar por Contas')
@section('content')
<div class="wrapper wrapper-content">
   <form action="<?php echo route('banks.bills.pay.account.save'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Salvando...')">
      {!! csrf_field() !!}
      <div class="row" id='form'>
         <div class="col-lg-12">
            <div class="ibox">
               <div class="ibox-title input-group-btn">
                  <div class="col-md-8">
                     <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                     <h5>&nbsp;/&nbsp;</h5>
                     <h5><a href="{{route('banks.bills.pay.account.index')}}"> &nbsp;Contas a Pagar por Contas</a>&nbsp;</h5>
                     <h5>&nbsp;/&nbsp;</h5>
                     <h5>Cadastrar</h5>
                  </div>
                  <div class="col-md-2"></div>
                  <div class="col-md-2">
                     <a href="{{route('banks.bills.pay.account.index')}}"><img src="{{asset('images/searchDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
                  </div>
               </div>
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-lg-6">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins">@if(isset($head)) {{formatDate($head->taxDate)}} @else{{DATE('d/m/Y')}}@endif</h1>
                              <div class="stat-percent font-bold text-danger"><i class="fa fa-calendar" style="font-size:36px"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-6">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins" id="totalHeader">@if(isset($head)) {{number_format($head->docTotal,2,',','.')}}@else 0,00 @endif</h1>
                              <div class=" no-margins stat-percent font-bold text-info ">
                                 <i class="fa fa-dollar" style="font-size:36px"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  @if(isset($head))
                  <div class="row">
                    <div class="col-md-3">
                          <label>Cod. SAP</label>
                          <input type="taxt" value="{{$head->codSAP}}" disabled class="form-control">
                      </div>
                       <div class="col-md-3">
                          <label>Cod.WEB</label>
                          <input type="taxt" value="{{$head->code}}" disabled class="form-control">
                        </div>
                  </div>
                  @endif
                  <div class="row" style="padding-top:1%">
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Data do Documento</label>
                           <input type="date" name="taxDate" @if(isset($head)) readonly @endif  value="{{DATE('Y-m-d')}}" class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Data de Lançamento</label>
                          <input type="date" name="docDate" @if(isset($head)) readonly @endif  value="{{DATE('Y-m-d')}}" class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-2">
                      <div class="form-group">
                         <label>Data de Vencimento</label>
                         <input type="date" name="docDueDate" @if(isset($head)) readonly @endif  value="{{DATE('Y-m-d')}}" class="form-control" required>
                       </div>
                     </div>
                     @if(workCashFlow())
                     <div class="col-md-2">
                       <div class="form-group">
                          <label>Fluxo de Caixa</label>
                          <select class="form-control selectpicker" @if(isset($head) ) disabled @endif data-live-search="true" data-size="5" id="cashFlow" name="cashFlow" required='required' >
                             <option value=''>SELECIONE</option>
                             @foreach($cashFlow as $key => $value)
                             <option value="{{$value->id}}" @if(isset($head) && ($head->getCashFlowLabel()) && ($head->getCashFlowLabel() == $value->id)) selected @endif>{{$value->value}}</option>
                             @endForeach
                          </select>
                       </div>
                     </div>
                     @endif
                    <div class="col-md-4 -align-right right pull-right right">
                       <a href="" data-toggle="modal" data-target="#paymentModal"><img src="{{asset('images/pay.png')}}" class="img-responsive -align-right right pull-right right"></a>
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
                  <li><a data-toggle="tab" href="#tab-2">Anexos</a></li>
               </ul>
               <div class="tab-content">
                  <div class="tab-pane active" id="tab-1">
                     <div class="panel-body">
                        <div class="table-responsive">
                           <table id="requiredTable" class="table table-striped table-bordered table-hover dataTables-example" style="width: 100%;">
                              <thead>
                                 <tr>
                                    <th style="width: 5%"><img src="{{asset('images/add.png')}}" data-toggle="modal" onclick="loadingItems();" data-target="#itensModal"/></th>
                                    <th style="width: 15%">Conta contábil</th>
                                    <th style="width: 30%">Descrições</th>
                                    <th style="width: 15%">Valor</th>
                                    <th>Projeto</th>
                                    <th>Centro de Custo</th>
                                    <th style="width: 5%">Opções</th>
                                 </tr>
                              </thead>
                              <tbody>
                                @if(isset($body))
                                  <?php $cont=1; ?>,
                                  @foreach($body as $key => $value)
                                    <tr>
                                      <td>{{$cont}}</td>
                                      <td>{{$value['accountCode']}}</td>
                                      <td>{{$value['decription']}}</td>
                                      <td>{{number_format($value['sumPaid'],2,',','.')}}</td>
                                      <td>{{$value['projectCode']}}</td>
                                      <td>{{$value['profitCenter']}}</td>
                                      <td></td>
                                    </tr>
                                  @endforeach
                                @endif
                              </tbody>
                           </table>
                           <div class="modal inmodal" id="itensModal" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog">
                                 <div class="modal-content">
                                    <div class="modal-header">
                                       <h4 class="modal-title">Adicionar Itens</h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="table-responsive">
                                          <table  id="table" class="table table-striped table-bordered table-hover" style="width: 100%;">
                                             <thead>
                                                <tr>
                                                   <th style="width: 10%">Cod. SAP</th>
                                                   <th style="width: 55%">Descrições</th>
                                                   <th style="width: 25%">Opções</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                                <tr>
                                                   <td></td>
                                                   <td></td>
                                                   <td></td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                             <label>Projeto Principal</label>
                             <select class='form-control' @if(isset($head) ) disabled @endif data-live-search='true' name='project' id='projectGlobal' onchange="setProjecFull()" >
                                <option value=''>Selecione</option>
                                @foreach($projeto as $keys => $values)
                                <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                @endForeach
                             </select>
                          </div>
                          <div class="col-md-3">
                             <label>Centro de Custo Principal</label>
                             <select class='form-control' @if(isset($head) ) disabled @endif data-live-search='true' name='role' id='roleGlobal' onchange="setRoleFull()" >
                                <option value=''>Selecione</option>
                                @foreach($role as $keys => $values)
                                <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option>
                                @endForeach
                             </select>
                          </div>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane" id="tab-2">
                     <div class="panel-body">
                        <div class="col-md-12">
                           <label>Observação</label>
                           <textarea class="form-control" rows="5" name="obsevacoes" @if(isset($head)) readonly @endif>@if(isset($head)) {{$head->comments}}@endif</textarea>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @if(!isset($head))
          <div class="col-md-12  -align-right right pull-right right">
             <div class="form-group pull-right">
                <div class="hr-line-dashed"></div>
                <button class="btn btn-primary" type="button" id="btn-save" onclick="validateTabs()">Salvar</button>
                <div class="hr-line-dashed"></div>
             </div>
          </div>
          @else
          <div class="col-md-12  -align-right right pull-right right">
             <div class="form-group pull-right">
                <div class="hr-line-dashed"></div>
                @if($head->status != 2) <?php //2 cancelado ?>
                <span class="btn btn-danger" onclick="cancel();">Cancelar</span>
                @endif
                <div class="hr-line-dashed"></div>
             </div>
          </div>
           @endif
      </div>
   </form>
</div>
<div class="modal inmodal" id="paymentModal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
   <div class="modal-dialog" style="width: 70%;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><spanaria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Adicionar Pagamento</h4>
         </div>
         <div class="modal-body">
            <input type="hidden" name="line">
            <div class="row">
               <div class="col-lg-12">
                  <div class="tabs-container">
                     <ul class="nav nav-tabs" id="modalPayment">
                        @if(false)<li><a data-toggle="tab" href="#Mtab-1">Cheque</a></li>@endif
                        <li><a data-toggle="tab" href="#Mtab-2">Transferência</a></li>
                        @if(false)<li><a data-toggle="tab" href="#Mtab-3">Cartão de Crédito</a></li>@endif
                        <li class="active"><a data-toggle="tab" href="#Mtab-4">Dinheiro</a></li>
                        @if(false)<li><a data-toggle="tab" href="#Mtab-5">Outros</a></li>@endif
                     </ul>
                     <div class="tab-content">
                        @if(false)<div class="tab-pane" id="Mtab-1">
                           <div class="panel-body">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label>Conta Contábil</label>
                                        <select class="form-control selectpicker" @if(isset($head)) disabled @endif  name="conta_cheque" id="conta_cheque" data-live-search='true'>
                                          <option selected disabled >Selecione</option>
                                          @foreach($account as $item)
                                              <option value="{{$item['value']}}">{{$item["name"]}}</option>
                                          @endforeach
                                       </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Data de Vencimento</label>
                                          <input type="text" class="form-control" name="dt_vencimento_cheque" id="dt_vencimento_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Valor</label>
                                          <input type="text" class="form-control money" name="valor_cheque" value="0" id="valor_cheque" onblur="setTotal();">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Nome do Banco</label>
                                          <select class="form-control selectpicker with-ajax-bank" data-width="100%" data-live-search="true" data-size="7" name="nome_banco_cheque" id="nome_banco_cheque">
                                          </select>
                                       </div>
                                    </div>
                                </div>
                            </div>
                               <div class="row">
                                  <div class="col-md-12">
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Filial</label>
                                          <input type="text" class="form-control" name="filial_cheque" id="filial_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Conta</label>
                                          <input type="text" class="form-control" name="numero_conta_cheque" id="numero_conta_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Nº Cheque</label>
                                          <input type="text" class="form-control" name="numero_cheque" id="numero_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <lebel>Endosso</label>
                                          <select class="form-control seleckpicker" data-live-source='true' name="endosso_cheque" id="endosso_cheque">
                                             <option value="N" selected>Não</option>
                                             <option value="Y">Sim</option>
                                          </select>
                                       </div>
                                    </div>
                                </div>
                            </div>
                           </div>
                        </div>@endif
                        <div class="tab-pane" id="Mtab-2">
                           <div class="panel-body">
                             <div class="row">
                               <div class="col-md-12">
                                 <div class="col-md-3">
                                    <div class="form-group">
                                      <label>Conta Contábil</label>
                                      <select class="form-control selectpicker" @if(isset($head)) disabled @endif name="conta_transferencia" id="conta_transferencia" data-live-search='true'>
                                        <option selected disabled >Selecione</option>
                                        @foreach($account as $item)
                                            <option value="{{$item['value']}}"  @if(isset($payment) && (count($payment)> 0) && (trim($payment[0]->transfer) == 'Y') && ($payment[0]->transferAccount == $item['value'])) selected @endif>{{$item["name"]}}</option>
                                        @endforeach
                                     </select>
                                    </div>
                                 </div>
                                 <div class="col-md-3">
                                    <div class="form-group">
                                       <label>Data</label>
                                       <input type="date" autocomplete="off" @if(isset($head)) disabled @endif @if(isset($head)) disabled @endif  @if(isset($payment) && (trim($payment[0]->transfer) == 'Y')) value="{{$payment[0]->trasnferDate}}" @endif class="form-control" name="dt_transferencia" id="dt_transferencia">
                                    </div>
                                 </div>
                                 <div class="col-md-3">
                                    <div class="form-group">
                                      <label>Valor</label>
                                      <input type="text" class="form-control money" @if(isset($head)) disabled @endif @if(isset($head)) disabled @endif  @if(isset($payment) && (trim($payment[0]->transfer) == 'Y')) value="{{number_format($payment[0]->transferSum,2,',','.')}}" @else value="0" @endif value="0" id="total_transfrencia" name="total_transfrencia" onblur="setTotal();">
                                    </div>
                                 </div>
                                 <div class="col-md-3">
                                    <div class="form-group">
                                      <label>Referência</label>
                                      <input type="text" class="form-control" @if(isset($head)) disabled @endif @if(isset($payment) && (trim($payment[0]->transfer) == 'Y')) value="{{$payment[0]->transferReference}}"  @endif  name="referencia_transferencia" id="referencia_transferencia">
                                    </div>
                                 </div>
                               </div>
                            </div>
                           </div>
                        </div>
                        @if(false)<div class="tab-pane" id="Mtab-3">
                             <div class="panel-body">
                               <div class="row">
                                 <div class="col-md-12">
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Conta Contábil</label>
                                        <select class="form-control selectpicker"  name="conta_cartao" id="conta_cartao" data-live-search='true'>
                                          <option value="NULL">Selecione</option>
                                          @foreach($account as $item)
                                              <option value="{{$item['value']}}">{{$item["name"]}}</option>
                                          @endforeach
                                       </select>
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Nome do Cartão</label>
                                         <select class="form-control"  name="name_cartao" id="name_cartao">
                                            <option value="NULL">Selecione</option>
                                            @foreach($cartao as $key => $value)
                                            <option value="{{$value['code']}}">{{$value['code']}} - {{$value['value']}}</option>
                                            @endForeach
                                         </select>
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Número do Cartão</label>
                                        <input type="text" class="form-control" name="num_cartao">
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Valor</label>
                                        <input type="text" class="form-control money" value="0" id='total_credito' onblur="setTotal();" name="total_credito">
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Nº Parcelas</label>
                                        <input type="number" class="form-control" id='parcelas_cartao' name="parcelas_cartao">
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group"><label>Data de Validade</label>
                                          <div class="input-group">
                                              <input required data-mask="99/99/9999" type="text" name="dt_validade_cartao" class="form-control" value="{{date('d/m/Y')}}">
                                              <div class="input-group-addon">
                                                  <span class="glyphicon glyphicon-th"></span>
                                              </div>
                                          </div>
                                      </div>
                                    </div>
                                 </div>
                              </div>
                             </div>
                        </div>@endif
                        <div class="tab-pane active" id="Mtab-4">
                           <div class="panel-body">
                             <div class="row">
                               <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                      <label>Conta Contábil</label>
                                      <select class="form-control selectpicker" @if(isset($head)) disabled @endif name="conta_dinheiro" id="conta_dinheiro" data-live-search='true'>
                                          <option value="NULL">Selecione</option>
                                        @foreach($account as $item)
                                            <option value="{{$item['value']}}" @if(isset($payment) && (trim($payment[0]->money) == 'Y') && ($payment[0]->cashAccount == $item['value'])) selected @endif>{{$item["name"]}}</option>
                                        @endforeach
                                     </select>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                      <label>Valor</label>
                                      <input type="text" class="form-control money" @if(isset($head)) disabled @endif  @if(isset($payment) && (trim($payment[0]->money) == 'Y')) value="{{number_format($payment[0]->cashSum,2,',','.')}}" @else value="0" @endif id='total_dinheiro' onblur="setTotal();" name="total_dinheiro">
                                    </div>
                                 </div>
                               </div>
                             </div>
                           </div>
                        </div>
                        @if(false)<div class="tab-pane" id="Mtab-5">
                             <div class="panel-body">
                               <div class="row">
                                 <div class="col-md-12">
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Conta Contábil</label>
                                        <select class="form-control selectpicker"  name="conta_outros" id="conta_outros" data-live-search='true'>
                                          <option value="NULL">Selecione</option>
                                          @foreach($account as $item)
                                              <option value="{{$item['value']}}">{{$item["name"]}}</option>
                                          @endforeach
                                       </select>
                                      </div>
                                   </div>
                                   <div class="col-md-4">
                                      <div class="form-group">
                                        <label>Valor</label>
                                        <input type="text" class="form-control money" value="0" id='total_outros' onblur="setTotal();" name="total_outros">
                                      </div>
                                   </div>
                                 </div>
                              </div>
                             </div>
                        </div>@endif
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer">
           <div class="col-md-9">
               <div class="form-group">
                 <div class="col-md-3">
                     <center><label>Total</label></center>
                     <input type="text" class="form-control money" id='total_pagameto_modal' name="docTotal" readonly @if(isset($head->docTotal)) value="{{number_format($head->docTotal,2,',','.')}}" @else value="0" @endif>
                 </div>
               <div class="col-md-3">
                 <input type="hidden" class="form-control money" id='total_pagameto' disabled value="0">
               </div>
               </div>
           </div>
           <div class="col-md-3">
              <button type="button" form="contactForm" class="btn btn-white" data-dismiss="modal">Cancelar</button>
              <button type="submit" form="contactForm" onclick="addPayment();" class="btn btn-primary">Adicionar</button>
           </div>
         </div>
      </div>
   </div>
</div>

@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
   $(document).ready(function(){
     setMaskMoney();
   });
   function setMaskMoney(){
     $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
   }
   $('.dataTables-example').DataTable({
       language: dataTablesPtBr,
       paging: false,
       "lengthChange": false,
       "ordering": false,
       "bFilter": false,
       "bInfo": false,
       "searching": false
   });
   var totalDoc =0;
   function sumNumbers(code){
     clearNumber(code);
       var valor = document.getElementById('vl-'+code).value;
       var total = parseFloat(valor.replace('.','').replace(',','.'));
       if(!isNaN(total)){
         document.getElementById('vl-'+code).value = total.format(2, ",", ".");
         sumAllValues();
       }
   }
   function sumAllValues(){
     var totalHeader = 0;
     var x;

     for(i = 1; i < index; i++){
       if(document.getElementById('vl-'+i)){
         x = document.getElementById('vl-'+i).value;
         x = parseFloat(x.replace('.','').replace(',','.'));
         if(!isNaN(x)){
           totalHeader += parseFloat(x);
         }
       }
     }

     document.getElementById('total_pagameto_modal').value = totalHeader.format(2, ",", ".");
     document.getElementById('totalHeader').innerHTML = totalHeader.format(2, ",", ".");
    }
   function clearNumber(code){
     var total = document.getElementById('vl-'+code).value;
    document.getElementById('vl-'+code).value = total.replace(/[-]/g, '');
   }
   function loadingItems(){
     $('#table').dataTable().fnDestroy();
     let table = $("#table").DataTable({
         processing: true,
         serverSide: true,
         responsive: true,
         ajax: {
             url: "{{route('banks.bills.pay.account.get')}}"
         },
         columns: [
             {name: 'AcctCode', data: 'AcctCode'},
             {name: 'AcctName', data: 'AcctName'},
             {name: 'edit', data: 'AcctCode', render: renderEditButton, orderable: false}
         ],
         lengthMenu: [5, 10, 30], "dom": "lfrti",
        language: dataTablesPtBr
      });
   }
   function chengeIcon(element){
     document.getElementById('addItem-'+element).src="{{asset('images/addCinza.png')}}";
   }
   var aux = 0;
   <?php $aux = true;?>
   function renderEditButton(code) {
       if(valideCode(code, false)){
         return "<center><img src='{{asset('images/add.png')}}' id='addItem-"+code+"' onclick='loadTable(\""+code+"\");chengeIcon(\""+code+"\")'/></center>";
       }else{
         return "<center><img src='{{asset('images/addCinza.png')}}'/></center>";
       }
   }
   var used = new Array ();
   var index = 1;
   function loadTable(code){

     if(!valideCode(code, true)){
         alert('Opss!!! o item já foi selecionado anteriormente');
     }else{
       var table = $('#requiredTable');
       if(index == 1){
         $('#requiredTable tbody > tr').remove();
       }
       var tr = $("<tr id='rowTable-" + index + "'>");
       tr.append($('<td>'+index+'</td>'));
       tr.find('td').first().append('<input type="hidden" value="' + code + '" data-name="line" name="items[' + index + '][accountCode]">');
       tr.append($("<td>"+code+"</td>"));
       tr.append($("<td style='width: 40%'><input type='text' style='width: 100%' onclick='setMaskMoney();' class='form-control' name='items[" + index + "][decription]'</td>"));
       tr.append($("<td style='width: 12%'><input type='text' style='width: 100%' onclick='setMaskMoney();' value='0' id='vl-"+index+"' onblur='sumNumbers("+index+");' class='form-control money' name='items["+ index + "][sumPaid]'></td>"));
       tr.append($("<td style='width: 12%'>"
                  +"<select class='form-control' data-live-search='true' id='project-"+ index + "' name='items["+ index + "][projectCode]' required > <option value=''>Selecione</option> @foreach($projeto as $keys => $values) <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option> @endForeach</select>"
                  +"</td>"));
       tr.append($("<td>"
                 +"<select class='form-control' id='role-"+ index + "' name='items["+ index + "][profitCenter]' required > <option value=''>Selecione</option> @foreach($role as $keys => $values) <option value='{{$values['value']}}'>{{$values['value']}} - {{$values['name']}}</option> @endForeach</select>"
                 +"</td>"));
       tr.append($("<td id='itemTable-" + index + "'><img src='{{asset('images/remover.png')}}' onclick='removeInArray(\""+code+"\");removeLinha(this);' style='font-size: 3%;color: #ec0707;padding-left: 16px;'/></td>"));
       table.find('tbody').append(tr);
       index++;
     }

   }
   function removeLinha(elemento){
     var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function(){
        tr.remove();
      });
      $("#composicao-" + elemento).remove();
   }
   function valideCode(code, mark=true){
       if(used.indexOf(code) == '-1'){
         if(mark){
           used.push(code);
         }
         return true;
       }else{
        return false;
       }
   }
   function removeInArray(code){
     var aux = used.indexOf(code);
     if(aux != -1){
       used.splice(aux,1);
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
         if(document.getElementById('role-'+i)){
           document.getElementById('role-'+i).value = val;
         }
     }  
   }
var payment = false;
   /*use in modal*/
   function addPayment() {
      payment = true;
             var modal = $('#paymentModal');
             var form = $('#form');
             var totalMoney = document.getElementById('total_dinheiro').value;
             var conta_dinheiro = $("#conta_dinheiro option:selected").val();

             var dt_transferencia = document.getElementById('dt_transferencia').value;
             var totalTransfer = document.getElementById('total_transfrencia').value;
             var referencia_transferencia = document.getElementById('referencia_transferencia').value;
             var conta_transferencia = $("#conta_transferencia option:selected").val();
             @if(false)
             var name_cartao = $("#name_cartao option:selected").val();
             var totalCard = document.getElementById('total_credito').value;
             var parcelas_cartao = document.getElementById('parcelas_cartao').value;
             var totalOther = document.getElementById('total_outros').value;
             var conta_outros = $("#conta_outros option:selected").val();
             var conta_cartao = $("#conta_cartao option:selected").val();

             var conta_cheque = $("#conta_cheque option:selected").val();
             var dt_vencimento_cheque = document.getElementById('dt_vencimento_cheque').value;
             var valor_cheque = document.getElementById('valor_cheque').value;
             var nome_banco_cheque = $("#nome_banco_cheque option:selected").val();
             var filial_cheque = document.getElementById('filial_cheque').value;
             var numero_conta_cheque = document.getElementById('numero_conta_cheque').value;
             var numero_cheque = document.getElementById('numero_cheque').value;
             var endosso_cheque = $("#endosso_cheque option:selected").val();
             @endif

             form.append($('<td><input type="hidden" name="payment[total_dinheiro]" value="' + totalMoney.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[conta_dinheiro]" value="' + conta_dinheiro.trim() + '">'));

             form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[total_transfrencia]" value="' + totalTransfer.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() + '">'));
             @if(false)
             form.append($('<td><input type="hidden" name="payment[name_cartao]" value="' + name_cartao.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[total_credito]" value="' + totalCard.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[parcelas_cartao]" value="' + parcelas_cartao.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[total_outros]" value="' + totalOther.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[conta_outros]" value="' + conta_outros.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[conta_cartao]" value="' + conta_cartao.trim() + '">'));
             form.append($('<td><input type="hidden" name="payment[conta_cheque]" value="' + conta_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[dt_vencimento_cheque]" value="' + dt_vencimento_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[valor_cheque]" value="' + valor_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[nome_banco_cheque]" value="' + nome_banco_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[filial_cheque]" value="' + filial_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[numero_conta_cheque]" value="' + numero_conta_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[numero_cheque]" value="' + numero_cheque + '">'));
             form.append($('<td><input type="hidden" name="payment[endosso_cheque]" value="' + endosso_cheque + '">'));
             @endif
             modal.modal('hide');
             return false;
          }
   function setTotal(){
              var totalMoney = 0; var totalTransfer = 0; var totalCard = 0; var totalOther = 0;

               totalMoney = document.getElementById('total_dinheiro').value;
               totalTransfer = document.getElementById('total_transfrencia').value;
               @if(false)
               totalCard = document.getElementById('total_credito').value;
               totalOther = document.getElementById('total_outros').value;
               totalCheque = document.getElementById('valor_cheque').value;
               var total = parseFloat(totalMoney.replace('.','').replace(',','.')) + parseFloat(totalTransfer.replace('.','').replace(',','.')) + parseFloat(totalCard.replace('.','').replace(',','.')) + parseFloat(totalOther.replace('.','').replace(',','.')) + parseFloat(totalCheque.replace('.','').replace(',','.'));
              @endif
              var total = parseFloat(totalMoney.replace('.','').replace(',','.')) + parseFloat(totalTransfer.replace('.','').replace(',','.'));

              if(!isNaN(total)){
                document.getElementById('total_pagameto').value = total.format(2, ",", ".");
              }else{
                document.getElementById('total_pagameto').value = total.format(2, ",", ".");
              }
          }
          @if(isset($head))
          function cancel() {
                swal({
                    title: "Tem certeza que deseja cancelar?",
                    text: "Esta operação não pode ser desfeita!",
                    icon: "warning",
                    //buttons: true,
                    buttons: ["Fechar", "Cancelar"],
                    dangerMode: true,
                })
                .then((willDelete) => {
                  if (willDelete) {
                       waitingDialog.show('Cancelando...')
                       window.location.href = "{{route('banks.bills.pay.account.cancel', $head['id'])}}";
                   }
                 });
            }
            @endif

            function checkCashFlow(){
               var erros = new Array();
               if ($('#cashFlow').val() == '') {
                  erros.push('O fluxo de caixa não pode ficar vazio \n');
               }
               if (erros.length > 0) {
                  return erros;
               } else {
                  return -1;
               }
            }

            function validateTabs() {
            var erros = new Array();
            var input = $("#needs-validation").find("*:invalid").first();
            var tabPane = input.closest('.tab-pane').first();
            if (tabPane.length === 1) {
                $('#myTabs').find('a[href="#' + tabPane.attr('id') + '"]').tab('show');
            }
            
            if (index <= 1) {
                erros.push('Informe ao menos 1 item');
            }
            if (payment == false) {
                erros.push('O pagamento não pode ficar vazio');
            }
            if (checkCashFlow() != -1) {
                erros.push(checkCashFlow());
            }
            if (erros.length > 0) {
                swal("Os seguintes erros foram encontrados: ", erros.toString(), "error")
            } else {
                $('#btn-save').attr('type', 'submit');
            }
        }

</script>
@endsection
