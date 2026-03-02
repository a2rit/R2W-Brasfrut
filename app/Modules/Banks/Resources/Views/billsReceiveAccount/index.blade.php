@extends('layouts.app')

@section('title', 'Contas a Receber por Contas')

@section('content')

<div class="wrapper wrapper-content">
       <div class="row">
         <div class="col-lg-12">
            <div class="ibox ">
               <div class="ibox-title input-group-btn ">
                 <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5> <h5>&nbsp;/&nbsp;</h5> <h5> &nbsp;Contas a Receber por Contas&nbsp;</h5>
               </div>
               <div class="ibox-content">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="col-md-4">
                        <center><a href="{{route('banks.bills.receive.account.create')}}"><img src="{{asset('images/newDocument.png')}}" style="width: 25%"><strong> Cadastrar</strong></a></center>
                      </div>
                        <div class="col-md-4">
                          <center><a href="{{route('banks.bills.receive.account.index')}}"><img src="{{asset('images/report.png')}}" style="width: 25%"><strong> Relatórios</strong></a></center>
                        </div>
                        <div class="col-md-4">
                            <center><a href="{{route('administration.user.registration.help')}}"><img src="{{asset('images/help.png')}}" style="width: 25%"><strong> Ajuda</strong></a></center>
                        </div>
                    </div>
                  </div>
                      <div class="row" style="padding-top: 2%">
                        <form action="<?php echo route('banks.bills.receive.account.filter'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
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
                                  <div class="col-md-3">
                                      <div class="form-group">
                                         <label>Data Inicial</label>
                                         <input type="date" name="data_fist" placeholder="{{DATE('d/m/Y')}}" class="form-control" autocomplete="off">
                                      </div>
                                    </div>
                                  <div class="col-md-3">
                                    <div class="form-group">
                                       <label>Data Final</label>
                                       <input type="date" name="data_last"  placeholder="{{DATE('d/m/Y')}}" class="form-control" autocomplete="off">
                                    </div>
                                  </div>
                                  <div class="col-md-2">
                                    <label>Status</Label>
                                      <select class="form-control" name="status">
                                        <option value='' >Selecione</optin>
                                          <option value='1'>Aberto</optin>
                                          <option value='0'>Fechado</optin>
                                          <option value='2'>Cancelado</optin>
                                      </select>
                                  </div>
                                </div>
                                <div class="col-md-12">
                                  <div class="col-md-3">
                                      <div class="form-group">
                                         <label>Valor Inicial</label>
                                         <input type="desc" name="value_fist" placeholder="0,00" class="form-control money" autocomplete="off">
                                      </div>
                                    </div>
                                  <div class="col-md-3">
                                    <div class="form-group">
                                       <label>Valor Final</label>
                                       <input type="desc" name="value_last"  placeholder="0,00" class="form-control money" autocomplete="off">
                                    </div>
                                  </div>
                                  <div class="col-md-1  -align-right right pull-right right" style="padding-top: 2%">
                                       <div class="form-group pull-right">
                                           <button class="btn btn-primary" type="submit">Filtrar</button>
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
                                               <th>Cod. SAP</th>
                                               <th>Cod. WEB</th>
                                               <th>Data</th>
                                               <th>Valor</th>
                                               <th>Observação</th>
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
                                                 <td>{{formatDate($value->taxDate)}}</td>
                                                 <td>{{number_format($value->docTotal,2,',','.')}}</td>
                                                 <td>{{$value->comments}}</td>
                                                 @if($value->status == 0)
                                                 <td><span onclick="loadItem('{{$value->id}}')" class="btn btn-success btn-xs"  style=" width: 100%">FECHADO</span></td>
                                                 @endif
                                                 @if($value->status == 1)
                                                 <td><span onclick="loadItem('{{$value->id}}')" class="btn btn-warning btn-xs" style=" width: 100%">ABERTO</span></td>
                                                 @endif
                                                 @if($value->status == 2)
                                                 <td><span onclick="loadItem('{{$value->id}}')" class="btn btn-danger btn-xs"  style=" width:100%">CANCELADO</span></td>
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

    $(document).ready(function(){
      $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
    });
    function loadItem(id){
      window.location.href = "{{route('banks.bills.receive.account.read')}}"+'/'+id;
    }
</script>
@endsection
