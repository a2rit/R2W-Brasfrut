@extends('layouts.main')
@section('title', 'Entrada de Mercadorias')
@section('content')

<div class="wrapper wrapper-content">
  <form action="<?php echo route('inventory.input.filter'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
      <div class="row">
      <div class="ibox-title input-group-btn ">
        <div class="col-md-8">
         <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5><a href="{{route('inventory.input.index')}}">&nbsp;Entrada de Mercadorias&nbsp;</a></h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5>Listando</h5>
       </div>
       <div class="col-md-2"></div>
       <div class="col-md-2">
         <a href="{{route('inventory.input.create')}}"><img src="{{asset('images/newDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
       </div>
      </div>
      <div class="ibox-content">
        <div class="row">
          <div class="col-md-12">
              <div class="col-md-2">
                  <label>Cod. SAP</Label>
                  <input type="text" class="form-control" placeholder="01" name="codSAP"
                         autocomplete="off">
              </div>
              <div class="col-md-2">
                  <label>Cod. WEB</Label>
                  <input type="text" class="form-control" placeholder="IN0001"
                         name="codWEB" autocomplete="off">
              </div>
              <div class="col-md-2">
                  <label>Usuário</Label>
                  <input type="text" class="form-control" placeholder="R2W"
                         name="name" autocomplete="off">
              </div>
              <div class="col-md-2">
                  <label>Data Inicial</Label>
                  <input type="date" name="data_fist" class="form-control"
                         placeholder="Inicial" autocomplete="off">
              </div>
              <div class="col-md-2">
                  <label>Data Final</Label>
                  <input type="date" name="data_last" class="form-control"
                         placeholder="Final" autocomplete="off">
              </div>

              <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control" name="status">
                      <option value='0' selected>Selecione</option>
                      <option value='1'>R2W-B1 (Aguardando)</option>
                      <option value='2'>SAP-B1 (Sicronizado)</option>
                      <option value='3'>SAP-B1 (Pendente)</option>
                  </select>
              </div>
          </div>
      </div>
        </div>
          <div class="row">
            <div class="col-md-12">
              <div class="col-md-4">
                <div class="form-group">
                   <label>Data Inicial</label>
                   <input type="text" name="data_fist" class="form-control datepicker" placeholder="Inicial" autocomplete="off">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                   <label>Data Final</label>
                   <input type="text" name="data_last" class="form-control datepicker" placeholder="Final" autocomplete="off">
                </div>
              </div>
            </div>
          </div>
      </div>
   </div>
      <div class="col-md-12  -align-right right pull-right right" style="padding-top: 2%">
        <div class="form-group pull-right">
          <button class="btn btn-primary" type="submit">Filtrar</button>
        </div>
     </div>
   </form>
</div>

<div class="wrapper wrapper-content">
   <div class="row">
      <div class="ibox-title input-group-btn ">
      </div>
      <div class="ibox-content">
         <div class="table-responsive">
            <table id="table" class="table table-striped table-bordered table-hover dataTables-example" >
               <thead>
                  <tr>
                    <th>#</th>
                    <th>Cod. SAP</th>
                    <th>Cod. WEB</th>
                    <th>Data</th>
                    <th>Deposito</th>
                    <th>Usuário</th>
                    <th>Status</th>
                  </tr>
               </thead>
               <tbody>
                 @if(isset($items))
                 <?php $cont =1;?>
                   @foreach($items as $key => $value)
                   <tr onclick="loadItem('{{$value->id}}')">
                      <td>{{$cont}}</td>
                      <td>{{$value->codSAP}}</td>
                      <td>{{$value->code}}</td>
                      <td>{{$value->TaxDate}}</td>
                      <td>{{$value->warehouse}}</td>
                      <td>{{$value->name}}</td>
                      @if($value->docStatus == 'AGUARDANDO')
                      <td><span class="btn btn-warning btn-xs">R2W-B1</span></td>
                      @endif
                      @if($value->docStatus == 'SINCRONIZADO')
                      <td><span class="btn btn-success btn-xs">SAP-B1</span></td>
                      @endif
                      @if($value->docStatus == 'ERROR')
                      <td><span class="btn btn-danger btn-xs">SAP-B1</span></td>
                      @endif
                      @if($value->docStatus == 'ATUALIZANDO')
                      <td><span class="btn btn-warning btn-xs">R2W-B1</span></td>
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
   <div class="hr-line-dashed"></div>
   <div class="hr-line-dashed"></div>
</div>
@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
    $("#table").css("width","100%");
    let table = $("#table").DataTable({
        responsive: true,
        lengthMenu: [50, 100, 150],
        language: dataTablesPtBr
    });

    $('.datepicker').datepicker({
      dayNames: [ "Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sabado" ],
           // Dias cortos traducido
           dayNamesMin: [ "Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab" ],
           // Nombres largos de los meses traducido
           monthNames: [ "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro" ],
           // Nombres cortos de los meses traducido
           monthNamesShort: [ "Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez" ],
     });
    $(document).ready(function(){
      $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true, prefix: '{{getCoin()}}'});
    });
    function loadItem(id){
      window.location.href = "{{route('inventory.input.edit')}}"+'/'+id;
    }
</script>
@endsection
