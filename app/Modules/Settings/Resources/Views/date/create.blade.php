@extends('layouts.main')
@section('title', 'Configurações')
@section('content')

<div class="wrapper wrapper-content">
  <form action="{{ route('settings.date.save') }}" method="post" id="needs-validation" enctype="multipart/form-data"  onsubmit="waitingDialog.show('Carregando...')">
    {!! csrf_field() !!}
      <div class="row">
      <div class="ibox-title input-group-btn ">
        <div class="col-md-8">
         <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5><a href="{{route('settings.index')}}"> &nbsp;Configurações</a>&nbsp;</h5>
         <h5>&nbsp;/&nbsp;</h5>
         <h5>Datas</h5>
       </div>
      </div>
      <div class="ibox-content">
        <div class="row">
          <div class="col-md-12">
            <div class="col-md-6">
              <label>Codigção de Pagamento</Label>
              <select name="codSAP" class="form-control" required>
                <option value=''>Selecione</option>
                @foreach($paymentConditions as $key => $value)
                  <option value="{{ $value->value }}">{{ $value->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label>Quantidade</Label>
              <input type="number" class="form-control" name="amount" autocomplete="off" required>
            </div>
          </div>
        </div>
      </div>
   </div>
      <div class="col-md-12  -align-right right pull-right right" style="padding-top: 2%">
        <div class="form-group pull-right">
          <button class="btn btn-primary" type="submit">Salvar</button>
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
                    <th style="width: 8%">#</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                  </tr>
               </thead>
               <tbody>
                 @if(isset($cDate))
                 <?php $cont =1;?>
                   @foreach($cDate as $key => $value)
                    <tr>
                      <td style="width: 8%">{{$cont}}</td>
                      <td>{{$value->codSAP}}</td>
                      <td>{{$value->description}}</td>
                      <td>{{$value->amount}}</td>
                      <td>{{$value->name}}</td>
                      <td onclick="remover('{{$value->id}}')"><span class="btn btn-danger">Remover</span></td>
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
    function remover(id){
      swal({
          title: "Tem certeza que deseja Remover?",
          icon: "warning",
          //buttons: true,
          buttons: ["Fechar", "Sim"],
          dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
             waitingDialog.show('Carregando...');
             window.location.href = "{{route('settings.date.remove')}}"+'/'+id;
         }
       });
    }
</script>
@endsection
