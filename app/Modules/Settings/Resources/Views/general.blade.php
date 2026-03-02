@extends('layouts.main')
@section('title', 'Configurações gerais')
@section('content')
<div class="col-md-10">
	<h3 class="header-page">Configurações de gerais</h3>
</div>
<hr>
  <form action="{{ route('settings.boot.store') }}" method="post" id="needs-validation">
    {!! csrf_field() !!}
    <div class="tabs-container container-fluid">
      <div class="tab-content">
        <ul class="nav nav-tabs" id="myTabs">
          {{-- <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
            <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
          </li> --}}
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
            <a class="nav-link active" data-toggle="tab" href="#tab-2">Documentos de marketing</a>
          </li>
        </ul>
        {{-- <div class="tab-pane active" id="tab-1">
          <div class="panel-body mt-3">
          </div>
        </div> --}}
        <div class="tab-pane active" id="tab-2">
          <div class="panel-body mt-3">
            <div class="card mt-3">
              <h6 class="card-header fw-bolder">Pedido de Compras</h6>
              <div class="card-body">
                <div class="row">
                  <div class="col-6">
                    <label for="">Aprovação pela plafatorma R2W</label>
                    <select name="approvePurchaseOrderR2W" id="" class="form-control selectpicker">
                      <option value="1">SIM</option>
                      <option value="0">NÃO</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label for="">Aprovação pela plafatorma SAP</label>
                    <select name="approvePurchaseOrderSAP" id="" class="form-control selectpicker">
                      <option value="1">SIM</option>
                      <option value="0">NÃO</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4">
      <button class="btn btn-primary float-end" type="submit">Salvar</button>
    </div>
  </form>
@endsection

@section('scripts')
   
  <script src="{!! asset('js/format.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
  
    $.each(@json($settings), function(index, value){
      let element = $(`[name='${value.code}']`);
      element.val(value.value);
      if(element.is('select')){
        element.selectpicker('destroy');
        element.selectpicker(selectpickerConfig).selectpicker('render');
      }
    });
  </script>
@endsection
