@extends('layouts.app')
@section('title', 'Lançamento Contábil manual')
@section('content')
<div class="wrapper wrapper-content">
   <form action="<?php echo route('journal-entry.reports.filter'); ?>" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
      {!! csrf_field() !!}
      <div class="row">
         <div class="ibox-title input-group-btn ">
            <div class="col-md-8">
               <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5><a href="{{route('journal-entry.index')}}">&nbsp;Lançamento Contábil manual&nbsp;</a></h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5>Listando</h5>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-2">
               <a href="{{route('journal-entry.create')}}"><img src="{{asset('images/newDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
            </div>
         </div>
         <div class="ibox-content">
            <div class="row">
               <div class="col-md-2">
                  <label>Código SAP</Label>
                  <input type="text" class="form-control" placeholder="I0001" name="codSAP" autocomplete="off">
               </div>
               <div class="col-md-2">
                  <label>Código WEB</Label>
                  <input type="text" class="form-control" placeholder="PO0001" name="codWEB" autocomplete="off">
               </div>
               <div class="col-md-2">
                  <div class="form-group">
                     <label>Data Inicial</label>
                     <input type="text" name="data_fist" class="form-control datepicker" placeholder="Inicial" autocomplete="off">
                  </div>
               </div>
               <div class="col-md-2">
                  <div class="form-group">
                     <label>Data Final</label>
                     <input type="text" name="data_last" class="form-control datepicker" placeholder="Final" autocomplete="off">
                  </div>
               </div>
               <div class="col-md-4">
                  <label>Parceiro</Label>
                  <select data-name="cardCode" class="form-control selectpicker" data-live-search="true"  name="cardCode" onchange="disabledItem(this)" data-size="5">
                     <option value=""></option>
                     @foreach($partner as $item)
                     <option value="{{$item['value']}}">{{$item['value']}} - {{$item["name"]}}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="row">
               <div class="col-md-2">
                  <div class="form-group">
                     <label>Projeto</label>
                     <select class="form-control selectpicker" name="project" id="project">
                        <option value=""></option>
                        @foreach($projects as $item)
                        <option value="{{$item["value"]}}">{{$item["name"]}}</option>
                        @endforeach
                     </select>
                  </div>
               </div>
               <div class="col-md-2">
                  <label>Regra de distribuição</label>
                  <select class="form-control selectpicker" name="distribution_rule" id="distribution_rule">
                     <option value=""></option>
                     @foreach($distributionRules as $item)
                     <option value="{{$item["value"]}}">{{$item["name"]}}</option>
                     @endforeach
                  </select>
               </div>
               <div class="col-md-2">
                 <label>Status</Label>
                 <select class="form-control selectpicker" data-live-search="true"  name="status">
                   <option value='0' selected >Selecione</optin>
                   <option value='1'>R2W-B1 (Aguardando)</optin>
                   <option value='2'>SAP-B1 (Sicronizado)</optin>
                   <option value='3'>SAP-B1 (Pendente)</optin>
                 </select>
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
                    <th>Projeto</th>
                    <th>Regra de distribuição</th>
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
                      <td>{{formatDate($value->posting_date)}}</td>
                      <td>{{$value->project}}</td>
                      <td>{{$value->distribution_rule}}</td>
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
           format: 'dd/mm/yyyy',
           autoclose: true,
           language: 'pt-BR',
           todayHighlight: true
       });
      $(document).ready(function(){
        $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true, prefix: '{{getCoin()}}'});
      });

    function loadItem(id){
        window.location.href = "{{route('journal-entry.edit')}}"+'/'+id;
    }
</script>
@endsection
