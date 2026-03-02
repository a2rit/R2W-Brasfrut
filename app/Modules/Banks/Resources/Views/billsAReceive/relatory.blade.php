@extends('layouts.app')
@section('title', 'Contas a Receber')
@section('content')
<div class="wrapper wrapper-content">
   <div class="row">
      <div class="col-lg-12">
         <div class="ibox ">
            <div class="ibox-title input-group-btn ">
               <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
               <h5>&nbsp;/&nbsp;&nbsp;</h5>
               <h5><a href="{{route('banks.bills.receive.index')}}"> Contas a Receber</a> &nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5> &nbsp;Relátorio&nbsp;</h5>
            </div>
            <form action="{{route('banks.bills.receive.get.relatory')}}" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
               {!! csrf_field() !!}
               <div class="ibox-content">
                  <div class="row">
                     <div class="col-md-12">
                        <div class="col-md-4">
                           <label>Parceiro</Label>
                           <select class="form-control selectpicker with-ajax-partner"  data-width="100%" data-live-search="true" data-size="7" name="cardCode">
                            </select>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label>Data Inicial Emissão</label>
                              <input type="date" name="data_fist" class="form-control" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label>Data Final Emissão</label>
                              <input type="date" name="data_last"  class="form-control" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label>Data Inicial Vencimento</label>
                              <input type="date" name="data_fist_venc" class="form-control" autocomplete="off">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label>Data Final Vencimento</label>
                              <input type="date" name="data_last_venc"  class="form-control" autocomplete="off">
                           </div>
                        </div>
                         <div class="col-md-3">
                             <div class="form-group">
                                 <label>Tipo</label>
                                 <select class="form-control selectpicker" required name="type">
                                     <option value='1'>Contas a Receber</option>
                                     <option value='2'>Contas Recebidas</option>
                                 </select>
                             </div>
                         </div>
                        <div class="col-md-2  -align-right right pull-right right" style="padding-top: 2%">
                           <div class="form-group pull-right">
                              <button class="btn btn-primary" type="submit">Gerar</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
@endsection

@section("scripts")
<script type="application/javascript">
        var selectpicker = $('.selectpicker').selectpicker();
        selectpicker.filter('.with-ajax-partner').ajaxSelectPicker(getAjaxSelectPickerOptions("{{route('partners.get.all')}}"));
        
</script>
@endsection
