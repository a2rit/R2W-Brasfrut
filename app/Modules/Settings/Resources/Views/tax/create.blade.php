@extends('layouts.main')
@section('title', 'Impostos')
@section('content')
<div class="wrapper wrapper-content">
   <form action="{{route('settings.tax.store')}}" method="post" id="needs-validation" onsubmit="waitingDialog.show('Carregando...')">
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
                         <select class="form-control selectpicker" data-width="100%" data-live-search="true" name="codSAP" id="codSAP">
                          @foreach($tax as $key => $value)
                            <option value="{{$value->value}}" @if(isset($head->codSAP) && $head->codSAP == $value->value) selected @endif>{{$value->value}} - {{$value->name}}</option>
                          @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>ICMS</Label>
                        <input type="text" required class="form-control money" @if(isset($head->ICMS)) value="{{ number_format($head->ICMS,2,',','.')}}" @endif name="ICMS" id="ICMS">
                    </div>
                    <div class="col-md-2">
                        <label>IPI</Label>
                        <input type="text" required class="form-control money" @if(isset($head->IPI)) value="{{ number_format($head->IPI,2,',','.') }}" @endif name="IPI">
                    </div>
                    <div class="col-md-2">
                        <label>COFINS</Label>
                        <input type="text" required class="form-control money" @if(isset($head->COFINS)) value="{{number_format($head->COFINS,2,',','.') }}" @endif name="COFINS">
                    </div>
                    <div class="col-md-2">
                        <label>PIS</Label>
                        <input type="text" required class="form-control money" @if(isset($head->PIS)) value="{{ number_format($head->PIS,2,',','.') }}" @endif name="PIS">
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
                        <th>Cod. SAP</th>
                        <th>Name</th>
                        <th>ICMS</th>
                        <th>IPI</th>
                        <th>COFINS</th>
                        <th>PIS</th>
                        <th>Opções</th>
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
@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
 $(document).ready(function(){
       $('.money').maskMoney({thousands:'.', decimal:',', allowZero:true});
 });
$("#table").css("width","100%");
 let table = $("#table").DataTable({
         processing: true,
         responsive: true,
         ajax: {
             url: "{{route('settings.tax.all')}}"
         },
         columns: [
             {name: 'codSAP', data: 'codSAP'},
             {name: 'name', data: 'name'},
             {name: 'ICMS', data: 'ICMS', render: renderNumber, orderable: false},
             {name: 'IPI', data: 'IPI', render: renderNumber, orderable: false},
             {name: 'COFINS', data: 'COFINS', render: renderNumber, orderable: false},
             {name: 'PIS', data: 'PIS', render: renderNumber, orderable: false},
             {name: 'edit', data: 'id', render: renderEditButton, orderable: false}
         ],
         lengthMenu: [5, 10, 30], 
         responsive: true,
         language: dataTablesPtBr
      });
   function renderEditButton(code) {
         return "<a href='{{route('settings.tax.edit')}}/"+code+"'><span class='btn btn-warning btn-xs'>Editar</span></a>"+"<br><br>"+
                "<span class='btn btn-danger btn-xs' onclick='remove("+code+")'>Remover</span>";
   } 
   function renderNumber(number) {
         return parseFloat(number).format(2, ",", ".");
   }  
   
   function remove(code) {
              swal({
                  title: "Tem certeza que deseja Remover?",
                  text: "Esta operação não pode ser desfeita!",
                  icon: "warning",
                  buttons: true,
                  buttons: ["Não", "Sim"],
                  dangerMode: true,
              })
              .then((willDelete) => {
                if (willDelete) {
                     waitingDialog.show('Removendo...')
                     window.location.href = "{{route('settings.tax.remove')}}"+'/'+code;
                 }
               });
  }
</script>
@endsection