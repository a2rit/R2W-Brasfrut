@extends('layouts.app')
@section('title', 'Contas a Receber')
@section('content')
<div class="wrapper wrapper-content">
   <form action="<?php echo route('banks.bills.receive.save'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Salvando...')">
      {!! csrf_field() !!}
      <div class="row" id='form'>
         <div class="col-lg-12">
            <div class="ibox">
              <div class="ibox-title input-group-btn">
                <div class="col-md-8">
                   <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                   <h5>&nbsp;/&nbsp;</h5>
                   <h5><a href="{{route('banks.bills.receive.index')}}"> &nbsp;Contas a Receber</a>&nbsp;</h5>
                   <h5>&nbsp;/&nbsp;</h5>
                   <h5>Cadastrar</h5>
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-2">
                   <a href="{{route('banks.bills.receive.search')}}"><img src="{{asset('images/searchDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
                </div>
              </div>
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins">@if(isset($head->taxDate)) {{formatDate($head->taxDate)}} @else {{DATE('d/m/Y')}} @endif</h1>
                              <div class="stat-percent font-bold text-danger"><i class="fa fa-calendar" style="font-size:36px"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins">@if(isset($head->status) && ($head->status == 0)) Fechado @endif @if(isset($head->status) && ($head->status == 1)) Aberto @endif @if(isset($head->status) && ($head->status == 2)) Cancelado @endif @if(!isset($head->status)) Pendente @endif</h1>
                              <div class="stat-percent font-bold text-navy"><i class="fa fa-tag" style="font-size:36px"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                           <div class="ibox-content">
                              <h1 class="no-margins" id="totalHeader">@if(isset($head->docTotal)) {{number_format($head->docTotal,2,',','.')}} @else 0,00 @endif</h1>
                              <input type="hidden" name="docTotal" id="docTotal" value="0">
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
                      <div class="input-group">
                        <label for="">Cod. SAP</label>
                        <input type="text" class="form-control" disabled value="{{$head->codSAP}}">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="input-group">
                        <label for="">Cod. WEB</label>
                        <input type="text" class="form-control" disabled value="{{$head->code}}">
                      </div>
                    </div>
                  </div>
                  @endif
                  <div class="row">
                     <div class="col-md-6">
                        <label>Parceiro de Negócio</label>
                        <div class="input-group">
                           <input type="text" class="form-control" id="parceiroNegocio" @if(isset($head->cardName)) value="{{$head->cardName}}" @endif required name="parceiroNegocio" placeholder="Clique na lupa para pesquisar o parceiro de negócio" disabled>
                           <!-- Abertura do modal-->
                           <div class="input-group-addon">
                              <a href="" data-toggle="modal" data-target="#pnModal"><i class="glyphicon glyphicon-search"></i></a>
                           </div>
                        </div>
                        @if(!isset($head))
                        <div class="modal inmodal" id="pnModal" tabindex="-1" role="dialog" aria-hidden="true">
                           <div class="modal-dialog modal-lg">
                              <div class="ibox-content">
                                 <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                 <div>
                                    <h4 class="modal-title">Pesquisa de Parceiros de Negócio</h4>
                                 </div>
                                 <div class="ibox-content">
                                    <label>Pesquisar</label>
                                    <input type="text" id="seachPN" class="form-control">
                                 </div>
                                 <span class="btn btn-success" onclick="loadTablePN()" data-toggle="collapse" >Pesquisar</span>
                                 <div class="ibox-content">
                                    <div class="table-responsive">
                                       <div id="resulSearch" style="display:none;">
                                          <table id="tableResult" class="table table-striped">
                                             <thead>
                                                <tr>
                                                   <th>Codigo SAP</th>
                                                   <th>Nome</th>
                                                   <th>CNPJ/CPF</th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        @endif
                     </div>
                     <div class="col-md-2"></div>
                     <div class="col-md-2">
                         <div class="form-group">
                             <label>Moeda</label>
                             <select class="form-control selectpicker" @if(isset($head)) disabled @endif data-live-search="true" name="coin">
                               <option value="EUR" @if(isset($head->coin) && ($head->coin == 'EUR')) selected @endif>Euro</option>
                               <option value="R$" @if(isset($head->coin) && ($head->coin == 'R$')) selected @endif>Real</option>
                               <option value="$" @if(isset($head->coin) && ($head->coin == '$')) selected @endif>Dólar</option>
                             </select>
                         </div>
                     </div>
                     <div class="col-md-2">
                        <label>Cotação - EUR</label>
                       <input type="text" class="form-control money" @if(isset($head)) disabled @endif name="cotacao" @if(isset($head->quotation)) value="{{number_format($head->quotation,2,',','.')}}" @else value="{{getCoin()}}" @endif id='cotacao'>
                     </div>
                  </div>
                  <div class="row" style="padding-top:1%">
                     <div class="col-md-3">
                            <div class="form-group"><label>Data do Documento</label>
                                <div class="input-group">
                                    <input required type="date" @if(isset($head)) disabled @endif name="taxDate" @if(isset($head->taxDate)) value="{{$head->taxDate}}" @endif class="form-control" value="{{date('Y-m-d')}}">
                                </div>
                            </div>
                      </div>
                      <div class="col-md-3">
                             <div class="form-group"><label>Data de Lançamento</label>
                                 <div class="input-group">
                                     <input required type="date" @if(isset($head)) disabled @endif name="docDate" @if(isset($head->docDate)) value="{{$head->docDate}}" @endif class="form-control" value="{{date('Y-m-d')}}">
                                 </div>
                             </div>
                       </div>
                       <div class="col-md-3">
                              <div class="form-group"><label>Data de Vencimento</label>
                                  <div class="input-group">
                                      <input required type="date" @if(isset($head)) disabled @endif name="docDueDate" @if(isset($head->docDueDate)) value="{{$head->docDueDate}}" @endif class="form-control" value="{{date('Y-m-d')}}">
                                  </div>
                              </div>
                        </div>
                     <div class="col-md-3 -align-right right pull-right right">
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
                  <li class="active"><a data-toggle="tab" href="#tab-1">Lançamentos</a></li>
                  <li><a data-toggle="tab" href="#tab-2">Anexos</a></li>
               </ul>
               <div class="tab-content">
                  <div class="tab-pane active" id="tab-1">
                     <div class="panel-body">
                        <div class="table-responsive">
                           <table id="requiredTable" class="table table-striped table-bordered table-hover dataTables-example" style="width: 100%;">
                              <thead>
                                 <tr>
                                    <th style="width: 2%"></th>
                                    <th style="width: 10%">Tipo</th>
                                    <th style="width: 10%">Nº Doc</th>
                                    <th style="width: 15%">Emissão</th>
                                    <th style="width: 15%">Vencimento</th>
                                    <th style="width: 15%">Titulo</th>
                                    <th style="width: 10%">Nº Parcela</th>
                                    <th style="width: 10%">Valor</th>
                                    <th style="width: 10%">Observação</th>
                                 </tr>
                              </thead>
                              <tbody>
                                @if(isset($invoice))
                                <?php $cont=1; ?>
                                  @foreach($invoice as $key => $value)
                                    <tr>
                                      <td>{{$cont}}</td>
                                      <td>{{$value->type}}</td>
                                      <td>{{$value->docNum}}</td>
                                      <td>{{formatDate($value->docDate)}}</td>
                                      <td>{{formatDate($value->dueDate)}}</td>
                                      <td>{{$value->serial}}</td>
                                      <td>{{$value->parcel}}</td>
                                      <td>{{number_format($value->lineSum,2,',','.')}}</td>
                                      <td>{{$value->description}}</td>
                                    </tr>
                                    <?php $cont++; ?>
                                  @endForeach
                                @endif
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane" id="tab-2">
                     <div class="panel-body">
                        <div class="col-md-12">
                           <label>Observação</label>
                           <textarea class="form-control" rows="5" @if(isset($head)) disabled @endif name="comments">@if(isset($head->comments)) {{$head->comments}} @endif</textarea>
                        </div>
                        @if(false);
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
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         </div>
        @if(!isset($head))
         <div class="col-md-12  -align-right right pull-right right">
            <div class="form-group pull-right">
               <div class="hr-line-dashed"></div>
               <button class="btn btn-primary" type="submit">Salvar</button>
               <div class="hr-line-dashed"></div>
            </div>
         </div>
         @else
         <div class="col-md-12  -align-left left pull-left left">
            <div class="form-group pull-left">
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
                                          <label>Data de Vencimento</label>
                                          <input type="text" class="form-control" name="dt_vencimento_cheque" id="dt_vencimento_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label>Valor</label>
                                          <input type="text" class="form-control money" name="valor_cheque" value="0" id="valor_cheque" onblur="setTotal();">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label>Nome do Banco</label>
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
                                          <label>Filial</label>
                                          <input type="text" class="form-control" name="filial_cheque" id="filial_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label>Conta</label>
                                          <input type="text" class="form-control" name="numero_conta_cheque" id="numero_conta_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label>Nº Cheque</label>
                                          <input type="text" class="form-control" name="numero_cheque" id="numero_cheque">
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group">
                                          <label>Endosso</label>
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
                                            <option value="{{$item['value']}}"  @if(isset($payment) && (trim($payment[0]->transfer) == 'Y') && ($payment[0]->transferAccount == $item['value'])) selected @endif>{{$item["name"]}}</option>
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
                     <input type="text" class="form-control money" id='total_pagameto_modal' disabled @if(isset($head->docTotal)) value="{{number_format($head->docTotal,2,',','.')}}" @else value="0" @endif>
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
         $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
       });
       $('.dataTables-example').DataTable({
         language: dataTablesPtBr,
         paging: false,
         "lengthChange": false,
         "ordering": false,
         "bFilter": false,
         "bInfo": false,
         "searching": false
      });
       var aux = 0;
       function loadTablePN(){
         var campo = document.getElementById('seachPN').value;
         if(campo != ''){
           var table = $('#tableResult');
           var tr;
           var teste;
           $('#tableResult tbody > tr').remove();
           $.get('/getPN/' + campo, function (items) {
                   for(var i=0;i< items.length; i++){
                        tr = $("<tr id='rowTablePN-" + aux +"'  onclick='setInTable(\""+items[i].CardCode+"\")'>");
                        tr.append($("<td style='width: 10%'>"+items[i].CardCode+"</td>"));
                        tr.append($("<td style='width: 20%'>"+items[i].CardName+"</td>"));
                        if(items[i].TaxId0 != ''){
                          tr.append($("<td style='width: 20%'>"+items[i].TaxId0+"</td>"));
                        }else{
                          tr.append($("<td style='width: 20%'>"+items[i].TaxId4+"</td>"));
                        }
                        table.find('tbody').append(tr);
                        aux++;
                  }

           });

           if(document.getElementById('resulSearch').style.display == 'block'){
               document.getElementById('resulSearch').style.display = 'none'
           }else{
             document.getElementById('resulSearch').style.display = 'block'
           }
         }else{
           alert('campo busca está em branco!');
         }
       }
       function setInTable(code){
        $('#needs-validation').append($('<input type="hidden" value="' + code + '" data-name="line" name="codPN">'));
          $.get('/getNamePN/' + code, function (items) {
              for(var i=0;i< items.length; i++){
                document.getElementById('parceiroNegocio').value =  items[i].CardName;
                $('#needs-validation').append($('<input type="hidden" value="' + items[i].CardName + '" name="cardName">'));
                $('#needs-validation').append($('<input type="hidden" value="' + items[i].CardCode + '" name="cardCode">'));
                if(items[i].TaxId0 != ''){
                  $('#needs-validation').append($('<input type="hidden" value="' + items[i].TaxId0 + '" name="identification">'));
                }else{
                  $('#needs-validation').append($('<input type="hidden" value="' + items[i].TaxId4 + '" name="identification">'));
                }
              }

          });
          $('#pnModal').modal('hide');
          loadTable(code);
       }
         <?php $aux = true;?>
       var index = 1;
       function loadTable(code){
               var table = $('#requiredTable');
               $('#requiredTable tbody > tr').remove();
              $.get("{{route('banks.bills.receive.invoices')}}" +'/'+ code, function (items) {
                      for(var i=0;i< items.length; i++){
                        var tr = $("<tr id='rowTable-" + index + "'>");
                        tr.append($("<td style='width: 3%'><input type='checkbox' onclick='sumNumbers(\""+formatNumber(items[i].VALOR)+"\", "+i+");'  id='check-"+i+"' name='accounts["+i+"][check]'></td>"));
                        tr.append($("<td style='width: 8%'>"+items[i].TIPO+"<input type='hidden' value='"+items[i].TIPO+"' name='accounts["+i+"][type]'></td>"));
                        tr.append($("<td style='width: 8%'>"+items[i].TRS+"<input type='hidden' value='"+items[i].RI+"' name='accounts["+i+"][docEntry]'><input type='hidden' value='"+items[i].TRS+"' name='accounts["+i+"][docNum]'></td>"));
                        tr.append($("<td style='width: 8%'>"+formDate(items[i].EMISSAO)+"<input type='hidden' value='"+items[i].EMISSAO+"' name='accounts["+i+"][docDate]'></td>"));
                        tr.append($("<td style='width: 8%'>"+formDate(items[i].VENCTO)+"<input type='hidden' value='"+items[i].VENCTO+"' name='accounts["+i+"][dueDate]'></td>"));
                        if(items[i].NNF == null){
                          tr.append($("<td style='width: 5%'><input type='hidden' value='"+items[i].NNF+"' name='accounts["+i+"][serial]'></td>"));
                        }else{
                          tr.append($("<td style='width: 5%'>"+items[i].NNF+"<input type='hidden' value='"+items[i].NNF+"' name='accounts["+i+"][serial]'></td>"));
                        }
                        tr.append($("<td style='width: 8%'>"+items[i].NPARC+"<input type='hidden' value='"+items[i].NP+"' name='accounts["+i+"][installmentId]'><input type='hidden' value='"+items[i].NPARC+"' name='accounts["+i+"][parcel]'></td>"));
                        tr.append($("<td style='width: 8%'>"+formatNumber(items[i].VALOR)+"<input type='hidden' value='"+items[i].VALOR+"' name='accounts["+i+"][lineSum]'></td>"));
                        if(items[i].OBSERVACOES == null){
                          tr.append($("<td style='width: 20%'><input type='hidden' value='"+items[i].OBSERVACOES+"' name='accounts["+i+"][description]'></td>"));
                        }else{
                          tr.append($("<td style='width: 20%'>"+items[i].OBSERVACOES+"<input type='hidden' value='"+items[i].OBSERVACOES+"' name='accounts["+i+"][description]'></td>"));
                        }
                        table.find('tbody').append(tr);
                        index++;
                      }
               });
       }
       var totalDoc=0;
       function sumNumbers(valor, item){
         if($('#check-'+item).is(':checked')){
           totalDoc += parseFloat(valor.replace('.','').replace(',','.'));
             console.log('+'+totalDoc);
         }else{
            console.log('TOTAL:'+totalDoc);
           totalDoc -= parseFloat(valor.replace('.','').replace(',','.'));
             console.log('-'+valor);
         }
         document.getElementById('totalHeader').innerHTML= totalDoc.format(2, ",", ".");
         document.getElementById('total_pagameto_modal').value = totalDoc.format(2, ",", ".");
         document.getElementById('docTotal').value = totalDoc.format(2, ",", ".");

       }

       function addPay(){
         var form = $('#addressForm');
         var input = $('#needs-validation');
         var values = {};
         form.find('input,select').each(function(index,item){
          input.append($('<input type="hidden" value="' + item + '" data-name="line" name="pagamento">'));
          });
       }
       function formDate(date){
           var aux = (date.substring(0,10)).split('-');
           return aux[2]+'/'+aux[1]+'/'+aux[0];
         }
       function formatNumber(number){
           return parseFloat(number).format(2, ",", ".");
       }

       var cSDN =0;var revo =0;
       function setDataNumber(input){
        var valor = input.getAttribute("data-parcela");
        var idParcela = input.value;
          if(input.checked){
            $('#needs-validation').append($('<input type="hidden" data-id="'+cSDN+'" id="p'+cSDN+'" value="' + valor + '" name="idParcela['+cSDN+']['+idParcela+']">'));
            cSDN =cSDN + 1;
          }else{
           var id = input.getAttribute("data-id");
           $('#needs-validation').append($('<input type="hidden" data-id="'+id+'" id="p'+id+'" value="' +id+ '" name="removeID['+revo+']">'));
           revo += 1;
         }

       }
        /*selecionar todos os checkbok*/
        function allCheck(){
          if($('#allCheckBox').is(':checked')){
            $('input[type="checkbox"]').prop("checked",  true);
          }else{
            $('input[type="checkbox"]').prop("checked",  false);
          }
        }
        var selectpicker = $('.selectpicker').selectpicker();
        selectpicker.filter('.with-ajax-bank').ajaxSelectPicker(getAjaxSelectPickerOptions('{{route('banks.get.all')}}'));
        
       /*use in modal*/
       function addPayment() {
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
                    window.location.href = "{{route('banks.bills.receive.cancel', $head['id'])}}";
                }
              });
         }
         @endif

</script>
@endsection
