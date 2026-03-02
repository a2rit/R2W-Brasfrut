<?php
$page = 'currency_quote';
?>
@extends('layouts.main')
@section('title', 'Configurações')
@section('content')
<div class="wrapper wrapper-content">
  <form action="{{route('settings.currency.quote.create')}}" method="post" id="needs-validation" onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
      <div class="row">
      <div class="ibox-title input-group-btn ">
        <div class="col-md-8">
         <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5><a href="{{route('settings.index')}}"> &nbsp;Configurações</a>&nbsp;</h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5>Cotação</h5>
         @if(isset($head))
          <h5>&nbsp;/&nbsp;</h5>
          <h5>Editando</h5>
          <input type="hidden" name="id" value="{{$head->id}}">
         @else
          <h5>&nbsp;/&nbsp;</h5>
          <h5>Cadastrando</h5>
         @endif 
         
       </div>
       <div class="col-md-2"></div>
       <div class="col-md-2">
        </div>
      </div>
      <div class="ibox-content">
        <div class="row">
          <div class="col-md-4">
                 <div class="form-group"><label>Data de lançamento</label>
                     <div class="input-group">
                         <input required type="date" name="posting_date" class="form-control" 
                         @if(isset($head)) value="{{$head->posting_date}}" @else value="{{DATE('Y-m-d')}}" @endif>
                         <div class="input-group-addon">
                             <span class="glyphicon glyphicon-th"></span>
                         </div>
                     </div>
                 </div>
             </div>
            <div class="col-md-4">
              <label>Moeda</Label>
              <select class="form-control selecktpicker" name="coin">
                <option value="EUR" @if(isset($head) && $head->coin == 'EUR') value="{{$head->coin}}" selected @endif>EURO</option>
                <option value="USA" @if(isset($head) && $head->coin == 'USA') value="{{$head->coin}}" selected @endif>DOLAR USA</option>
              </select>
            </div>
            <div class="col-md-4">
              <label>Cotação</Label>
                <input type="text" name="rate" @if(isset($head)) value="{{number_format($head->rate,2,',','.')}}" @else value="0,00" @endif class="form-control money" id='cotacao'>
            </div>
        </div>
           <div class="col-md-12  -align-right right pull-right right" style="padding-top: 2%">
             <div class="form-group pull-right">
               <button class="btn btn-primary" type="submit">Salvar</button>
             </div>
          </div>
        <div class="row">
          <div class="col-md-12">
            <table id="table" class="table table-striped table-bordered table-hover dataTables-example" >
               <thead>
                  <tr>
                    <th>#</th>
                    <th>Data</th>
                    <th>Moeda</th>
                    <th>Valor</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                  </tr>
               </thead>
               <tbody>
                 @if(isset($items))
                 <?php $cont =1;?>
                   @foreach($items as $key => $value)
                   <tr>
                      <td>{{$cont}}</td>
                      <td>{{formatDate($value->posting_date)}}</td>
                      <td>{{$value->coin}}</td>
                      <td>{{number_format($value->rate,2,',','.')}}</td>
                      <td>{{$value->name}}</td>
                      <td><a href="{{route('settings.currency.quote.read', $value->id)}}" class="btn btn-warning btn-xs">Editar</a></td>
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
@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
    @if(isset($error) && ($error))
      swal({
             title: "Existe uma cotação cadastrada no SAP",
             text: "Deseja copiar para o R2W?",
             icon: "warning",
             //buttons: true,
             buttons: ["Não", "Sim"],
             dangerMode: true,
         })
      .then((willDelete) => {
           if (willDelete) {
                waitingDialog.show('Cancelando...');
                window.location.href = "{{route('settings.currency.quote.response')}}";
            }
      });
    @endif
    $("#table").css("width","100%");
    let table = $("#table").DataTable({
        responsive: true,
        buttons: [
        ],
        lengthMenu: [50, 100, 150],
        "searching": false,
         "bInfo":false,
        language: dataTablesPtBr
    });

    $(document).ready(function(){
       $.get('/getCurrencyQuote', function (items) {
         if(!items){
            $('#cotacao').val('0,00');
         }else{
            $('#cotacao').val(items);
         }
       });
       $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
     });
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'pt-BR',
        todayHighlight: true
    });
</script>
@endsection
