@extends('layouts.main')
@section('title', 'Estoque')
@section('content')
<div class="row">
  <div class="col-md-10">
    <h3 class="header-page">Lista de itens</h3>
  </div>
  <div class="col-md-2">
    <div class="dropdown float-end">
      <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
        <svg class="icon">
          <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
        </svg> Novo
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('inventory.items.create') }}">Cadastro de item</a></li>
      </ul>
    </div>
  </div>
</div>
<hr>
<form action="{{ route('inventory.items.filter') }}" method="GET" id="needs-validation" enctype="multipart/form-data">
  <div class="accordion" id="filterAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Filtros
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#filterAccordion">
        <div class="accordion-body">
          <div class="row">
            <div class="col-md-2">
              <label>Código SAP</Label>
              <input type="text" class="form-control" placeholder="I00001" name="codSAP" autocomplete="off" value="{{ old('codSAP') }}">
            </div>
            <div class="col-md-4">
              <label>Nome</Label>
              <input type="text" class="form-control" placeholder="Item" name="name" autocomplete="off" value="{{ old('name') }}">
            </div>
            <div class="col-md-3">
              <label>Grupo</Label>
              <select class="form-control selectpicker" name="group">
                <option value=""></option>
                @foreach ($itemGroups as $key => $value)
                <option value="{{ $value->value }}" {{ old('group') == $value->value ? 'selected' : '' }}>
                  {{ $value->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label>Subgrupo</label>
                <select class="form-control selectpicker" name="subGroup">
                  <option value=""></option>
                  @foreach ($subGroups as $subGroup)
                    <option value="{{ $subGroup->value }}">{{ $subGroup->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row mt-1">
            <div class="col-md-3">
              <label>Depósito</Label>
              <select class="form-control selectpicker" name="warehouse">
                <option value=""></option>
                @foreach ($warehouses as $key => $value)
                <option value="{{ $value->value }}" {{ old('warehouse') == $value->value ? 'selected' : '' }}>
                  {{ $value->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label>Status</Label>
              <select class="form-control selectpicker" name="status">
                <option value=''>SELECIONE</option>
                <option value="Y" {{ old('status') == 'Y' ? 'selected' : '' }}>ATIVO</option>
                <option value="N" {{ old('status') == 'N' ? 'selected' : '' }}>INATIVO</option>
              </select>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-12">
              <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
              <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar formulário</button>
              <button class="btn btn-success btn-sm float-end me-1" onclick="exportExcel(event)" type="button">Exportar para Excel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<div class="col-md-12 mt-5">
  {{ $items->links('pagination::bootstrap-4') }}
  <div class="table-responsive">
    <table id="table" class="table table-default table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th style="width: 2%;">#</th>
          <th style="width: 10%;">Cod. SAP</th>
          <th>Descrição</th>
          <th style="width: 10%;">Status</th>
          <th style="width: 10%;">Estoque</th>
          <th style="width: 10%;">Opção</th>
        </tr>
      </thead>
      <tbody>
        <?php $cont = 1; ?>
        @foreach ($items as $key => $value)
        <tr>
          <td>{{ $cont }}</td>
          <td>{{ $value->ItemCode }}</td>
          <td>{{ $value->ItemName }}</td>
          <td>{{ $value->Status }}</td>
          <td class="text-center">
            <a class='text-primary' href='#' onclick="openModalWHS('{{ $value->ItemCode }}')" data-coreui-toggle="modal" data-coreui-target="#modalWHS">
              <svg class="icon icon-xl">
                <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
              </svg>
            </a>
          </td>
          <td>
            <a href="{{ route('inventory.items.edit', $value->ItemCode) }}" class="btn btn-primary btn-sm w-100">Visualizar</a>
          </td>
        </tr>
        <?php $cont++; ?>
        @endForeach
      </tbody>
    </table>
    {{ $items->links('pagination::bootstrap-4') }}
  </div>
</div>

<div class="modal inmodal" id="modalWHS" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Informações de depósitos</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <div class="table-responsive mt-1 table-default">
          <table id="tableWHS" class="table table-hover table-bordered">
            <thead class="table-secondary">
              <tr>
                <th style="width: 5%">Codigo</th>
                <th style="width: 75%">Depósito</th>
                <th style="width: 20%">quantidade</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
<script type="text/javascript">
  $(".selectpicker").selectpicker(selectpickerConfig);


  function openModalWHS(itemCode) {
    $('#tableWHS').dataTable().fnDestroy();

    $("#tableWHS").DataTable({
      searching: false,
      ajax: {
        url: "{{ route('inventory.get.whs') }}" + '/' + itemCode
      },
      columns: [{
          name: 'WhsCode',
          data: 'WhsCode'
        },
        {
          name: 'WhsName',
          data: 'WhsName'
        },
        {
          name: 'edit',
          data: 'OnHand',
          orderable: false
        }
      ],
      lengthMenu: [5, 15, 30],
      language: dataTablesPtBr
    });
  }

  function renderEditButton(code) {
    return `<center>
                <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");' @if (isset($head->codSAP)) style="display: none;" @endif>
                    <svg class="icon icon-xl">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                    </svg>
                </a>
            </center>`
  }

  function renderWHS(valor) {
    return `<center>
                <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS" @if (isset($head->codSAP)) style="display: none;" @endif>
                <svg class="icon icon-xl">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                </svg>
                </a>
            </center>`
  }

  function exportExcel(event){
    $.ajax({
      type: 'post',
      url: "{{ route('inventory.items.export.filter.excel') }}",
      headers: {
        'X-CSRF-TOKEN': $('body input[name="_token"]').val()
      },
      data: $('#needs-validation').serialize(),
      dataType: 'json',
      success: function(response) {
        waitingDialog.hide();
        if (response.status == 'success') {
          swal({
              title: "Sucesso!",
              text: response.message,
              icon: "success",
              buttons: ["Fechar"],
            });
        } else {
          swal({
            title: "Opss...",
            text: response.message,
            icon: "error",
            buttons: ["Fechar"],
          })
        }
      },
      error: function(response) {
        waitingDialog.hide();
        swal({
          title: "Opss...",
          text: response.message,
          icon: "error",
          buttons: ["Fechar"],
        })
      }
    });
  }

  $('input[name="name"]').autocomplete({
    serviceUrl: '{{ route('inventory.items.itemsSearch') }}',
    onSelect: function(suggestion) {
      if(suggestion.data){
        $('input[name="name"]').val(suggestion.data);
      }
    }
  });

</script>
@endsection