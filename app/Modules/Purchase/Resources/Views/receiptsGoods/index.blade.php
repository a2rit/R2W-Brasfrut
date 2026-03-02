@extends('layouts.main')

@section('title', 'Recebimento de Mercadoria')

@section('content')

<div class="wrapper wrapper-content">
       <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
              <ol class="breadcrumb">
                <li>
                    <a href={{route('home')}}><i class="fa fa-dashboard"></i>Inicio</a>
                </li>
                <li class="active">
                   <i class="fa fa-shopping-cart"></i> Recebimento de mercadoria
                </li>
              </ol>
               <div class="ibox-content">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="col-md-4">
                        <center><a href="{{route('purchase.receipts.goods.create')}}"><img src="{{asset('images/newDocument.png')}}" style="width: 25%"><strong> Cadastrar</strong></a></center>
                      </div>
                        <div class="col-md-4">
                          <center><a href="{{route('purchase.receipts.goods.report')}}"><img src="{{asset('images/report.png')}}" style="width: 25%"><strong> Relatórios</strong></a></center>
                        </div>
                        <div class="col-md-4">
                            <center><a href="{{route('administration.user.registration.help')}}"><img src="{{asset('images/help.png')}}" style="width: 25%"><strong> Ajuda</strong></a></center>
                        </div>
                    </div>
                  </div>
                      <div class="row" style="padding-top: 2%">
                        <form action="<?php echo route('purchase.receipts.goods.filter'); ?>" method="post" id="needs-validation" onkeydown='keyShowModel(event.keyCode)' enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
                          {!! csrf_field() !!}
                            <div class="ibox-content">
                              <div class="row">
                                <div class="col-md-12">
                                  <div class="col-md-2">
                                    <label>Código SAP</Label>
                                    <input type="text" class="form-control" placeholder="10" name="codSAP" autocomplete="off">
                                  </div>
                                  <div class="col-md-2">
                                    <label>Código WEB</Label>
                                    <input type="text" class="form-control" placeholder="RG0001" name="code" autocomplete="off">
                                  </div>
                                  <div class="col-md-4">
                                    <label>Parceiro</Label>
                                    <input type="text" class="form-control" placeholder="Davi" name="cardName" autocomplete="off">
                                  </div>
                                  <div class="col-md-4">
                                    <label>CNPJ/CPF</Label>
                                    <input type="text" class="form-control" id='cpfcnpj' placeholder="337.214.827-43" onblur="maskBrasilInput();" name="cpf_cnpj" autocomplete="off">
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-2">
                                      <div class="form-group">
                                         <label>Data Inicial</label>
                                         <input type="date" name="data_fist" placeholder="{{DATE('d/m/Y')}}" class="form-control" autocomplete="off">
                                      </div>
                                    </div>
                                  <div class="col-md-2">
                                    <div class="form-group">
                                       <label>Data Final</label>
                                       <input type="date" name="data_last"  placeholder="{{DATE('d/m/Y')}}" class="form-control" autocomplete="off">
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <label>Status</Label>
                                      <select class="form-control" name="status">
                                        <option value='' >Selecione</option>
                                          <option value='1'>Aberto</option>
                                          <option value='0'>Fechado</option>
                                          <option value='2'>Cancelado</option>
                                      </select>
                                  </div>
                                    <div class="col-md-4  -align-right right pull-right right" style="padding-top: 2%">
                                       <div class="form-group pull-right">
                                           <button class="btn btn-primary" type="submit">Filtrar</button>
                                       </div>
                                    </div>
                                </div>
                                   <div class="col-md-12">
                                     <div class="table-responsive">
                                       <table id="table" class="table table-striped table-bordered table-hover dataTables-example" >
                                          <thead>
                                             <tr>
                                               <th style="width: 5%">#</th>
                                               <th>Cod. SAP</th>
                                               <th>Cod. WEB</th>
                                               <th>Nome</th>
                                               <th>CNPJ/CPF</th>
                                               <th>Data</th>
                                               <th>Total</th>
                                               <th>Status</th>
                                             </tr>
                                          </thead>
                                          <tbody>
                                            @if(isset($items))
                                            <?php $cont =1;?>
                                              @foreach($items as $key => $value)
                                               <tr>
                                                 <td style="width: 5%">{{$cont}}</td>
                                                 <td>{{$value->codSAP}}</td>
                                                 <td>{{$value->code}}</td>
                                                 <td>{{$value->cardName}}</td>
                                                 <td>{{$value->identification}}</td>
                                                 <td>{{formatDate($value->taxDate)}}</td>
                                                 <td>{{number_format($value->docTotal,2,',','.')}}</td>
                                                 @if($value->status == 0)
                                                 <td><span  onclick="loadItem('{{$value->id}}')" class="btn btn-success btn-xs"  style=" width: 100%">FECHADO</span></td>
                                                 @endif
                                                 @if($value->status == 1)
                                                 <td><span  onclick="loadItem('{{$value->id}}')" class="btn btn-warning btn-xs" style=" width: 100%">ABERTO</span></td>
                                                 @endif
                                                 @if($value->status == 2)
                                                 <td><span  onclick="loadItem('{{$value->id}}')" class="btn btn-danger btn-xs"  style=" width:100%">CANCELADO</span></td>
                                                 @endif
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

@endsection
@section('scripts')
<script type="text/javascript">
    $("#table").css("width","100%");
    let table = $("#table").DataTable({
        responsive: true,
        buttons: [],
        lengthMenu: [50, 100, 150],
        "searching": false,
         "bInfo":false,
        language: dataTablesPtBr
    });
    function removeMasc(){
      $("#cpfcnpj").unmask();
    }

    $('.datepicker').datepicker({
           format: 'dd/mm/yyyy',
           autoclose: true,
           language: 'pt-BR',
           todayHighlight: true
    });
    function loadItem(id){
      window.location.href = "{{route('purchase.receipts.goods.read')}}"+'/'+id;
    }
</script>
@endsection
