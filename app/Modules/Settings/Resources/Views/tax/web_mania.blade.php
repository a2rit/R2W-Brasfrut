@extends('layouts.main')
@section('title', 'Impostos')
@section('content')
<div class="wrapper wrapper-content">
   <form action="{{route('settings.tax.web.mania.save')}}" method="post" id="needs-validation" onsubmit="waitingDialog.show('Carregando...')">
      {!! csrf_field() !!}
      <div class="row">
         <div class="ibox-title input-group-btn ">
            <div class="col-md-8">
               <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5><a href="{{route('settings.index')}}"> &nbsp;Configurações</a>&nbsp;</h5>
               <h5>&nbsp;/&nbsp;</h5>
               <h5><a href="{{route('settings.tax.index')}}"> &nbsp;Impostos</a>&nbsp;</h5>
               <h5>&nbsp; &nbsp;/&nbsp;&nbsp;</h5>
               @if(isset($head))
               <h5>Editar</h5>
               <input type="hidden" name="id" value="{{$head->id}}">
               @else
               <h5>Cadastrar</h5>
               @endif
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-2">
            </div>
         </div>
         <div class="ibox-content">
            <div class="row">
               <div class="col-md-12">
					          <div class="col-md-4">
                      <label>Cod. SAP</label>
                         <select class="form-control selectpicker" data-width="100%" data-live-search="true" name="sap_code" id="sap_code">
                          @foreach($tax as $key => $value)
                            <option value="{{$value->value}}" @if(isset($head->sap_code) && $head->sap_code == $value->value) selected @endif>{{$value->value}} - {{$value->name}}</option>
                          @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Referência</Label>
                        <input type="text" required class="form-control" @if(isset($head->webmania_code)) value="{{$head->webmania_code}}" @endif name="webmania_code" id="webmania_code">
                    </div>
                    <div class="col-md-6">
                        <label>Descrição</Label>
                        <input type="text" required class="form-control" @if(isset($head->description)) value="{{$head->description}}" @endif name="description">
                    </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <div class="col-md-3  -align-right right pull-right right" style="padding-top: 3%">
                    <div class="form-group pull-right">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                    </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>
<div class="wrapper wrapper-content">
  <div class="row">
    <div class="ibox-content">
      <div class="row">
            <div class="col-md-12">
              <table id="table" class="table table-striped table-bordered table-hover dataTables-example" >
                  <thead>
                    <tr>
                        <th>#</th>
                        <th>Cod. SAP</th>
                        <th>Referência</th>
                        <th style="width:60%">Descrição</th>
                        <th>Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(isset($ltax))
                      <?php $cont=1;?>
                      @foreach($ltax as $key => $value)
                        <tr>
                            <td>{{$cont}}</td>
                            <td>{{$value->sap_code}}</td>
                            <td>{{$value->webmania_code}}</td>
                            <td>{{$value->description}}</td>
                            <td><span class="btn btn-danger btn-xs" onclick="remove({{$value->id}})">Desabilitar</span></td>
                        </tr>
                      <?php $cont++;?>
                      @endforeach
                    @endif
                  </tbody>
              </table>
            </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
   function remove(code) {
              swal({
                  title: "Tem certeza que deseja Desabilitar?",
                  text: "Esta operação não pode ser desfeita!",
                  icon: "warning",
                  buttons: true,
                  buttons: ["Não", "Sim"],
                  dangerMode: true,
              })
              .then((willDelete) => {
                if (willDelete) {
                     waitingDialog.show('Desabilitar...')
                     window.location.href = "{{route('settings.tax.remove.web.mania')}}"+'/'+code;
                 }
               });
  }
</script>
@endsection