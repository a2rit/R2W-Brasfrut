@extends('layouts.app')
@section('title', 'Cadastro de usuário')
@section('content')
<div class="wrapper wrapper-content">
   <form action="{{route('administration.user.registration.save')}}" method="post" id="needs-validation" onsubmit="$('#loading-modal').modal('show')">
   {!! csrf_field() !!}
   <input type="hidden" name="id" @if(isset($user)) value="{{$user->id}}" @endif >
   <div class="row">
      <div class="col-lg-12">
         <div class="ibox float-e-margins">
            <div class="ibox-title">
               <div>
                  <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5><a href="{{route('administration.user.registration.index')}}"> &nbsp;Cadastro de usuário</a>&nbsp;</h5>
                  @if(isset($user))
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5>Editar</h5>
                  @else
                  <h5>&nbsp;/&nbsp;</h5>
                  <h5>Cadastrar</h5>
                  @endif
               </div>
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-md-3">
                        <label>Nome</label>
                        <input type="text" class="form-control" @if(isset($user)) value="{{$user->name}}" @endif name="name" required>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label>Email</label>
                           <input type="email" name="email"  @if(isset($user)) value="{{$user->email}}" disabled @endif class="form-control" required>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <label>Tipo</label>
                        <select class="form-control" name="tipo" required>
                          <option selected disabled>Selecione</option>
                          <option value="S" @if(isset($user)) @if($user->tipo =='S') selected @endif @endif >Solicitante</option>
                          <option value="A" @if(isset($user)) @if($user->tipo =='A') selected @endif @endif >Atendente</option>
                        </select>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-3">
                        <div class="form-group"><label>Senha</label>
                           <input type="password" name="password" @if(isset($user)) placeholder="*********" @else required @endif  class="form-control">
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group"><label>Confirmação da senha</label>
                           <input type="password" name="passwordCheck" @if(isset($user)) placeholder="*********" @else required @endif class="form-control" >
                        </div>
                     </div>
                     <div class="col-md-1">
                        <div class="form-group">
                           <label>Ativo<input type="checkbox" class="form-control" @if(isset($user) && ($user->status == 1)) checked @endif name="status" ></label>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label>Depósito</label>
                           <select class="form-control" name="whsDefault"  required>
                             <option value=''>Selecione</option>
                             @foreach($whs as $key => $value)
                             <option value="{{$value['value']}}" @if(isset($user->whsDefault) && ($user->whsDefault == $value['value'])) selected @endif>{{$value['value']}} - {{$value['name']}}</option>
                             @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                          <label>Usuário SAP</label>
                          <select class="form-control" name="userClerk"  required>
                            <option value=''>Selecione</option>
                            @foreach($userClerk as $key => $value)
                            <option value="{{$value['value']}}"  @if(isset($user->userClerk) && ($user->userClerk == $value['value'])) selected @endif>{{$value['value']}} - {{$value['name']}}</option>
                            @endforeach
                          </select>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label>Grupos</label>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <!-- Administração -->
                                    <label class="list-group active"><input id="adm" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->administracao) checked @endif @endif name="administracao" class="i-checks" onclick="setAdministration()"/>
                                      ADMINISTRAÇÃO <span data-toggle="collapse" href="#admi"  class="glyphicon glyphicon-chevron-down"></span></label>
                                    <div id="admi" class="panel-collapse collapse" class="list-group-item">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="adm_user" type="checkbox" name="admin_user" @if(isset($user)) @if(json_decode($user->permissions)->admin_user) checked @endif @endif class="i-checks"> Usuário</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="adm_conf" type="checkbox" name="admin_config" @if(isset($user)) @if(json_decode($user->permissions)->admin_config) checked @endif @endif class="i-checks"> Configurações</label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label  class="list-group"><input id="inv" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->estoque) checked @endif @endif name="estoque" onclick="setInventoryCheck()"/> ESTOQUE
                                    <span data-toggle="collapse" href="#cad"  class="glyphicon glyphicon-chevron-down"></span>
                                    </label>
                                    <div id="cad" class="panel-collapse collapse">
                                      <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_item" type="checkbox" name="inv_item" @if(isset($user)) @if(json_decode($user->permissions)->inv_item) checked @endif @endif> Itens e Serviços </label>
                                      <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_input" type="checkbox" name="inv_input" @if(isset($user)) @if(json_decode($user->permissions)->inv_input) checked @endif @endif> Entrada de Mercadoria </label>
                                      {{--
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_stocktaking" type="checkbox" name="inv_stocktaking" @if(isset($user)) @if(json_decode($user->permissions)->inv_stocktaking) checked @endif @endif> Inventário </label>
                                      --}}
                                      <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_request" type="checkbox" name="inv_request" @if(isset($user)) @if(json_decode($user->permissions)->inv_request) checked @endif @endif> Requisição Interna </label>
                                      <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_output" type="checkbox" name="inv_output" @if(isset($user)) @if(json_decode($user->permissions)->inv_output) checked @endif @endif> Saida de Mercadoria </label>
                                      <label class="list-group-item list-group-item-action list-group-item-success"><input id="inv_transfer" type="checkbox" name="inv_transfer" @if(isset($user)) @if(json_decode($user->permissions)->inv_transfer) checked @endif @endif> Transfêrencia </label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group"><input id="par" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->parceiros) checked @endif @endif  name="parceiros" onclick="setPartnerCheck()"/> PARCEIROS DE NEGÓCIOS <span data-toggle="collapse" href="#ven"  class="glyphicon glyphicon-chevron-down"></span></label>
                                    <div id="ven" class="panel-collapse collapse">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="partner" type="checkbox" name="par_cad"  @if(isset($user)) @if(json_decode($user->permissions)->par_cad)) checked @endif @endif>Parceiro de Negócio</label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group"><input id="lcm" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->financeiro) checked @endif @endif name="financeiro" onclick="setLCMCheck()"/> FINANCEIRO
                                    <span data-toggle="collapse" href="#comp"  class="glyphicon glyphicon-chevron-down"></span>
                                    </label>
                                    <div id="comp" class="panel-collapse collapse">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="je" type="checkbox" name="jornal_entry"  @if(isset($user)) @if(json_decode($user->permissions)->jornal_entry) checked @endif @endif> Lançamento Contábil Manual </label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group"> <input id="sales" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->vendas) checked @endif @endif name="vendas" onclick="setSalesCheck()"/> VENDAS
                                    <span data-toggle="collapse" href="#esto"  class="glyphicon glyphicon-chevron-down"></span></label>
                                    <div id="esto" class="panel-collapse collapse">                                        
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="sales_pack" type="checkbox" name="packing_list" @if(isset($user) && isset(json_decode($user->permissions)->packing_list)) @if(json_decode($user->permissions)->packing_list) checked @endif @endif> Packing List</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="sales_order" type="checkbox" name="sale_orders" @if(isset($user) && isset($user)) @if(json_decode($user->permissions)->sale_orders) checked @endif @endif> Pedido</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="sales_invoice" type="checkbox" name="invoice" @if(isset($user) && isset(json_decode($user->permissions)->invoice)) @if(json_decode($user->permissions)->invoice) checked @endif @endif> Fatura</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="sales_inv_exit" type="checkbox" name="invoice_exit" @if(isset($user) && isset(json_decode($user->permissions)->invoice_exit)) @if(json_decode($user->permissions)->invoice_exit) checked @endif @endif> Nota Fiscal de Saida</label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group ">
                                    <input id="purchase" type="checkbox" name="purchase" @if(isset($user)) @if(json_decode($user->permissions)->purchase) checked @endif @endif onclick="setPurchaseCheck()"/> COMPRAS <span data-toggle="collapse" href="#fina"  class="glyphicon glyphicon-chevron-down"></span></label>
                                    <div id="fina" class="panel-collapse collapse">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="purchase_order" type="checkbox" name="purchase_orders" @if(isset($user) && isset(json_decode($user->permissions)->purchase_orders)) @if(json_decode($user->permissions)->purchase_orders) checked @endif @endif> Pedido de Compras</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="purchase_rg" type="checkbox"  name='receipt_goods' @if(isset($user) && isset(json_decode($user->permissions)->receipt_goods)) @if(json_decode($user->permissions)->receipt_goods) checked @endif @endif> Recebimentos de Mercadorias </label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="purchase_invoice" type="checkbox"  name='purchase_invoice' @if(isset($user) && isset(json_decode($user->permissions)->purchase_invoice)) @if(json_decode($user->permissions)->purchase_invoice) checked @endif @endif> Nota Fiscal de Entrada </label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="purchase_ap" type="checkbox"  name='advance_provider' @if(isset($user) && isset(json_decode($user->permissions)->advance_provider)) @if(json_decode($user->permissions)->advance_provider) checked @endif @endif> Adiantamento para Fornecedor </label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="purchase_xml" type="checkbox"  name='import_xml' @if(isset($user) && isset(json_decode($user->permissions)->import_xml)) @if(json_decode($user->permissions)->import_xml) checked @endif @endif> Importador de XML </label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group">
                                    <input id="banks" type="checkbox" @if(isset($user)) @if(json_decode($user->permissions)->banks) checked @endif @endif name="banks" onclick="setBankCheck()"/> BANCO
                                    <span data-toggle="collapse" href="#supo"  class="glyphicon glyphicon-chevron-down"></span>
                                    </label>
                                    <div id="supo" class="panel-collapse collapse">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="bank_br" type="checkbox" name="bill_receives" @if(isset($user)) @if(json_decode($user->permissions)->bill_receives) checked @endif @endif> Contas a receber </label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="bank_ra" type="checkbox"  name='receive_accounts' @if(isset($user)) @if(json_decode($user->permissions)->receive_accounts) checked @endif @endif> Contas a Receber por Conta</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="bank_bp" type="checkbox"  name='bill_plays' @if(isset($user)) @if(json_decode($user->permissions)->bill_plays) checked @endif @endif> Contas a Pagar</label>
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="bank_bpa" type="checkbox"  name='bill_play_account' @if(isset($user)) @if(json_decode($user->permissions)->bill_play_account) checked @endif @endif> Contas a Pagar por Conta</label>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="list-group">
                                    <label class="list-group">
                                    <input id="dashboard" type="checkbox" @if(isset($user) && isset(json_decode($user->permissions)->dashboard)) checked @endif name="dashboard" onclick="setDashBoardCheck()"/> Dashboard
                                    <span data-toggle="collapse" href="#dashboard1"  class="glyphicon glyphicon-chevron-down"></span>
                                    </label>
                                    <div id="dashboard1" class="panel-collapse collapse">
                                       <label class="list-group-item list-group-item-action list-group-item-success"><input id="dashboard_sub" type="checkbox" name="dashboard_sub" @if(isset($user) && isset(json_decode($user->permissions)->dashboard_sub))checked @endif> Dashboard </label>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-md-12  -align-right right pull-right right">
         <div class="hr-line-dashed"></div>
         <div class="form-group pull-right">
            <button class="btn btn-primary" type="submit" >Salvar</button>
         </div>
      </div>
      <div class="col-md-12">
         <div class="hr-line-dashed"></div>
      </div>
   </div>
   </form>
</div>
@endsection
@section('scripts')
<script>
   function setAdministration(){
      var check = $('#adm').prop("checked");
      $('#adm_user').attr("checked", check);
      $('#adm_conf').attr("checked", check);
   }   
   function setInventoryCheck(){
      var check = $('#inv').prop("checked");
      $('#inv_item').attr("checked", check);
      $('#inv_input').attr("checked", check);
      $('#inv_stocktaking').attr("checked", check);
      $('#inv_request').attr("checked", check);
      $('#inv_output').attr("checked", check);
      $('#inv_transfer').attr("checked", check);
   }
   function setPartnerCheck(){
      var check = $('#par').prop("checked");
      $('#partner').attr("checked", check);
   }
   function setLCMCheck(){
      var check = $('#lcm').prop("checked");
      $('#je').attr("checked", check);
   }
   function setSalesCheck(){
      var check = $('#sales').prop("checked");
      $('#sales_pack').attr("checked", check);
      $('#sales_order').attr("checked", check);
      $('#sales_invoice').attr("checked", check);
      $('#sales_inv_exit').attr("checked", check);
   } 
   function setPurchaseCheck(){
      var check = $('#purchase').prop("checked");
      $('#purchase_order').attr("checked", check);
      $('#purchase_rg').attr("checked", check);
      $('#purchase_invoice').attr("checked", check);
      $('#purchase_ap').attr("checked", check);
      $('#purchase_xml').attr("checked", check);
   }
   function setBankCheck(){
      var check = $('#banks').prop("checked");
      $('#bank_br').attr("checked", check);
      $('#bank_ra').attr("checked", check);
      $('#bank_bp').attr("checked", check);
      $('#bank_bpa').attr("checked", check);
   } 
   function setDashBoardCheck(){
      var check = $('#dashboard').prop("checked");
      $('#dashboard_sub').attr("checked", check);
   } 
   
   
</script>
@endsection
