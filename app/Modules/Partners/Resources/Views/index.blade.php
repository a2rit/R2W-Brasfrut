@extends('layouts.main')

@section('title', 'Parceiro de Negócios')

@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de parceiros de negócios</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('partners.create') }}">Parceiro de negócios</a></li>
          <li><a class="dropdown-item" href="{{ route('partners.relatory') }}">Relátorio</a></li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <div class="row pt-2">
    <form action="<?php echo route('partners.filter'); ?>" method="get" id="needs-validation" class="mb-5"
      enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
      {{-- {!! csrf_field() !!} --}}
      <div class="accordion" id="filterAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-coreui-toggle="collapse"
              data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Filtros
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
            data-coreui-parent="#filterAccordion">
            <div class="accordion-body">
              <div class="row">
                <div class="col-md-2">
                  <label>Cod. SAP</Label>
                  <input type="text" class="form-control" placeholder="F00001" name="codSAP" autocomplete="off" value="{{ old('codSAP') }}">
                </div>
                <div class="col-md-4">
                  <label>Fornecedor</Label>
                  <input type="text" name="name" class="form-control searchPN" placeholder="Pedro" autocomplete="off" value="{{ old('name') }}">
                </div>
                <div class="col-md-3">
                  <label>CNPJ/CPF</Label>
                  <input type="text" class="form-control" id='cpfcnpj' onfocus="removeMasc();"
                    onblur="maskBrasilInput();" name="cnpj_cpf" autocomplete="off" value="{{ old('cnpj_cpf') }}">
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control selectpicker" id="type" name="type">
                      <option value=''>Selecione um tipo</option>
                      @foreach ($types as $item)
                        <option value="{{ $item['value'] }}" {{ old('type') == $item['value'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                  <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar formulário</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      {{ $items->links('pagination::bootstrap-4') }}
      <table id="table" class="table table-default table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Cod. SAP</th>
            <th>Nome</th>
            <th>CNPJ/CPF</th>
            <th>Tipo</th>
            <th>Opções</th>
          </tr>
        </thead>
        <tbody>
          @if (isset($items))
            <?php $cont = 1; ?>
            @foreach ($items as $key => $value)
              <tr>
                <td>{{ $cont }}</td>
                <td>{{ $value['CardCode'] }}</td>
                <td>{{ $value['CardName'] }} - {{ $value['CardFName'] }}</td>
                @if (!empty($value['CNPJ']))
                  <td>{{ $value['CNPJ'] }}</td>
                @else
                  <td>{{ $value['CPF'] }}</td>
                @endif
                @if ($value['CardType'] == 'S')
                  <td>Fornecedor</td>
                @endif
                @if ($value['CardType'] == 'C')
                  <td>Cliente</td>
                @endif
                @if ($value['CardType'] == 'L')
                  <td>Cliente Potencial</td>
                @endif
                <td><a href="{{ route('partners.edit', $value['CardCode']) }}"
                    class='btn btn-primary btn-sm w-100'>Visualizar</a></td>
              </tr>
              <?php $cont++; ?>
            @endForeach
          @endif
        </tbody>
      </table>
      {{ $items->links('pagination::bootstrap-4') }}
    </div>
  </div>

@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>

  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
          .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    function removeMasc() {
      $("#cpfcnpj").unmask();
    }



    $('.searchPN').autocomplete({
      serviceUrl: '{{ route('partners.get.provider') }}',
      onSelect: function(suggestion) {
        if(suggestion.data){
          $('#codigoFornecedor').val(suggestion.data);
        }
      }
    });
  </script>
@endsection
