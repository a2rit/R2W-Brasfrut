@extends('layouts.app')

@section('title', 'Importador XML')

@section('content')
<div class="wrapper wrapper-content">
   <form action="{{route('purchase.xml.read.save')}}" method="post" id="needs-validation" enctype="multipart/form-data"
         onsubmit="waitingDialog.show('Carregando...')">
      {!! csrf_field() !!}
      <div class="row">
             <div class="ibox-title input-group-btn ">
               <div class="col-md-8">
                <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                <h5>&nbsp;/&nbsp;</h5>
                <h5><a href="{{route('purchase.order.import.xml')}}"> &nbsp;Importador XML</a>&nbsp;</h5>
                <h5>&nbsp;/&nbsp;</h5>
                <h5>Associar itens</h5>
              </div>
              <div class="col-md-2"></div>
              <div class="col-md-2">
                <a href="{{route('purchase.order.create')}}"><img src="{{asset('images/newDocument.png')}}" class=" img-responsive -align-right right pull-right right" style="width: 24%;"></a>
              </div>
             </div>
              <div class="ibox-content">
                <div class="row">
                      <div class="col-md-4">
                         <div class="form-group">
                             <label>Nome</label>
                             <input type="text" class="form-control" value="{{$xHead[0]->name}}" name="name" readonly>
                         </div>
                      </div>
                         <div class="col-md-4">
                           <div class="form-group">
                               <label>CNPJ</label>
                               <input type="text" class="form-control" value="{{$xHead[0]->cnpj}}" name="cnpj" readonly>
                           </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                              <label>Total</label>
                              <input type="text" class="form-control" value="{{number_format((double)$xHead[0]->docTotal,2,',','.')}}" name="total" readonly>
                          </div>
                       </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Item de estoque</label>
                          <select class="form-control selectpicker" required name="is_inventory_item">
                              <option value="1">Sim</option>
                              <option value="0">Não</option>
                          </select>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Item de venda</label>
                          <select class="form-control selectpicker" required name="is_sales_item">
                              <option value="1">Sim</option>
                              <option value="0">Não</option>
                          </select>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Item de compra</label>
                          <select class="form-control selectpicker" required name="is_purchase_item">
                              <option value="1">Sim</option>
                              <option value="0">Não</option>
                          </select>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Definir contas contábeis por</label>
                          <select class="form-control selectpicker" name="gl_method" required>
                              @foreach($glMethods as $item)
                                  <option value="{{$item["value"]}}">{{$item["name"]}}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Depósito padrão</label>
                          <select class="form-control selectpicker" name="default_warehouse">
                              @foreach($warehouses as $item)
                                  <option value="{{$item["value"]}}">{{$item["name"]}}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <center><label>Item do XML NF-e</label></center>
                              <table id="tableXML" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cod. SAP</th>
                                    <th>Cod. Produto</th>
                                    <th>Nome</th>
                                </tr>
                                </thead>
                                <tbody>
                                  <?php $contItem=1;?>
                                  @if(isset($xItem))
                                  @foreach($xItem as $key => $value)
                                  <tr>
                                    <td>{{$contItem}}</td>
                                    <td style="width: 20%;"><input type="text" class="form-control" value="{{$value->itemCode}}" name="assoc[{{$value->id}}]"></td>
                                    <td>{{$value->codPartners}}</td>
                                    <td>{{$value->name}}</td>
                                    </tr>
                                      <?php $contItem++;?>
                                  @endForeach
                                  @endif
                                </tbody>
                              </table>
                        </div>
                      <div class="col-md-6">
                        <center><label>Items cadastrados</label></center>
                            <table id="tableItems" class="table table-striped table-bordered table-hover">
                              <thead>
                              <tr>
                                  <th>Cod. SAP</th>
                                  <th>Descrição</th>
                              </tr>
                              </thead>
                              <tbody>
                              </tbody>
                            </table>
                      </div>
                </div>

              </div>
           <div class="col-md-12  -align-right right pull-right right">
             <div class="form-group pull-right">
                <div class="hr-line-dashed"></div>
                <button class="btn btn-primary" type="submit">Salvar</button>
                <div class="hr-line-dashed"></div>
                <div class="hr-line-dashed"></div>
             </div>
           </div>
     </div>
   </form>
</div>
@endsection

@section("scripts")
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script>
  $(document).ready(function(){
    $('#tableItems').dataTable().fnDestroy();
    let table = $("#tableItems").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{route('inventory.request.list')}}"
        },
        columns: [
            {name: 'ItemCode', data: 'ItemCode'},
            {name: 'ItemName', data: 'ItemName'}
        ],
        lengthMenu: [5, 10, 30],
        language: dataTablesPtBr,
        "dom": "lfrti"
     });
  });
</script>
@endsection
