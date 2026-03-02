@extends('layouts.main')
@section('title', 'Adiantamento ao fornecedor')
@section('content')

  <div class="row">
    <div class="col-md-10">
      <h3 class="header-page">Lista de adiantamentos para fornecedores</h3>
    </div>
    <div class="col-md-2">
      <div class="dropdown float-end">
        <button class="btn btn-dark dropdown-toggle" type="button" data-coreui-toggle="dropdown" aria-expanded="false">
          <svg class="icon">
            <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
          </svg> Novo
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{route('purchase.advance.provider.create')}}">Adiantamento para fornecedor</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <hr>

  <div class="row">
    <div class="col-3">
      <div class="card border-top-primary border-top-3 mb-3 text-center">
        <div class="card-header">Abertos</div>
        <div class="card-body">
          <h5 class="card-title">{{$buscaGraph->where('status', 1)->count()}} <span class="small">Adiantamentos</span></h5>
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card border-top-success border-top-3 mb-3 text-center">
        <div class="card-header">Fechados</div>
        <div class="card-body">
          <h5 class="card-title">{{$buscaGraph->where('status', 0)->count()}} <span class="small">Adiantamentos</span></h5>
        </div>
      </div>
    </div>
    <span class="fst-italic text-small">Periodo: últimos 12 meses</span>
  </div>
  <hr>

  <form action="<?php echo route('purchase.advance.provider.search'); ?>" method="GET" id="needs-validation"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Carregando...')">
    {{-- {!! csrf_field() !!} --}}
    <div class="row pt-2">
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
                  <label>Código SAP</Label>
                  <input type="text" class="form-control" placeholder="I0001" name="codSAP" autocomplete="off" value={{ old('codSAP') }}>
                </div>
                <div class="col-md-2">
                  <label>Código WEB</Label>
                  <input type="text" class="form-control" placeholder="PO0001" name="codWEB" autocomplete="off" value={{ old('codWEB') }}>
                </div>
                <div class="col-md-4">
                  <label>Parceiro</Label>
                  <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body" name="nameParceiro">
                    @if(!empty(old("nameParceiro"))) <option value="{{ old("nameParceiro") }}" selected>{{ getPartnerName(old("nameParceiro")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-4">
                  <label>CNPJ/CPF</Label>
                  <input type="text" class="form-control" id='cpfcnpj' onblur="maskBrasilInput();" name="cpf_cnpj" autocomplete="off" value={{ old('cpf_cnpj') }}>
                </div>
                <div class="col-md-4">
                  <label>Usuário</Label>
                  <select class="form-control selectpicker with-ajax-users" name="user">
                    @if(!empty(old("user"))) <option value="{{ old("user") }}" selected>{{ getUserName(old("user")) }}</option>@endif
                  </select>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Inicial</label>
                    <input type="date" name="data_fist" class="form-control" placeholder="Inicial" autocomplete="off" value={{ old('data_fist') }}>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Final</label>
                    <input type="date" name="data_last" class="form-control" placeholder="Final" autocomplete="off" value={{ old('data_last') }}>
                  </div>
                </div>
                <div class="col-md-2">
                  <label>Status</Label>
                  <select class="form-control selectpicker" name="status">
                    <option value=''>Selecione</option>
                    <option value='1' @if((Integer)old('status') === $ODPO::STATUS_OPEN) selected @endif>Aberto</option>
                    <option value='0' @if((Integer)old('status') === $ODPO::STATUS_CLOSE && !empty(old('status'))) selected @endif>Fechado</option>
                  </select>
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
    </div>
  </form>

  <div class="col-md-12 pt-5">
    {{ $items->links('pagination::bootstrap-4') }}
    <div class="table-responsive">
      <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 5%;" class="text-center">#</th>
            <th style="width: 10%;">Cod. SAP</th>
            <th style="width: 10%;">Cod. WEB</th>
            <th style="width: 20%;">Usuário</th>
            <th style="width: 25%;">Fornecedor</th>
            <th style="width: 10%;">Data</th>
            <th style="width: 10%;">Valor</th>
            <th style="width: 10%;">Status</th>
          </tr>
        </thead>
        <tbody class="zoom-cursor">
          @if (isset($items))
            <?php $cont = 1; ?>
            @foreach ($items as $key => $value)
              <tr value="{{ $value->codSAP }}">
                <td class="text-center">{{ $key+1 }}</td>
                <td class="text-center">
                  {{ $value->codSAP }}
                </td>
                <td class="text-center">
                  {{ $value->code }}
                </td>
                <td>
                  {{ $value->name }}
                </td>
                <td style="max-width: 16em;">
                  <div class="d-flex flex-row" style="max-width: 100%;">
                    <a class="text-warning" href="{{route('partners.edit', $value['CardCode'])}}" target="_blank">
                      <svg class="icon icon-lg">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                      </svg>
                    </a>
                    <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="{{ $value->cardCode }} - {{ $value->CardName }}">{{ $value->cardCode }} - {{ $value->CardName }}</span>
                  </div>
                </td>
                <td>
                  {{ formatDate($value->taxDate) }}
                </td>
                <td>
                  {{ number_format($value->docTotal, 2, ',', '.') }}
                </td>
                <td class="exclude" style="width:15%">
                  @if ($value->status == 0)
                    <a href="{{ route('purchase.advance.provider.read', $value->id) }}"
                      class="btn btn-success btn-sm w-100">FECHADO
                    </a>
                  @endif
                  @if ($value->status == 1)
                    <a href="{{ route('purchase.advance.provider.read', $value->id) }}"
                      class="btn btn-primary btn-sm w-100">ABERTO
                    </a>
                  @endif
                  @if ($value->status == 2)
                    <a href="{{ route('purchase.advance.provider.read', $value->id) }}"
                      class="btn btn-warning btn-sm w-100">ESTORNADO
                    </a>
                  @endif
                </td>
              </tr>
              <?php $cont++; ?>
            @endForeach
          @endif
        </tbody>
      </table>
      {{ $items->links('pagination::bootstrap-4') }}
    </div>
  </div>

  <div class="modal" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Pré-visualização</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table id="tablePreview"
                   class="table table-default table-striped table-bordered table-hover dataTables-example">
              <thead>
              <tr>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Preço unit.</th>
                <th>Total</th>
                <th>Projeto</th>
                <th>C. Custo</th>
                <th>C. Custo 2</th>
              </tr>
              </thead>
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

    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));
    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('administration.user.get') }}"));

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true,
      language: 'pt-BR',
      todayHighlight: true
    });

    $(document).ready(function() {
      $('.money').maskMoney({
        thousands: '.',
        decimal: ',',
        allowZero: true
      });
    });

    $('#requiredTable tbody tr > *:not(.exclude)').on('dblclick', function(event) {
      let docNum = $(event.currentTarget).parent().attr('value');
      if(docNum){

        $('#tablePreview').dataTable().fnDestroy();
        $("#tablePreview").DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: `{{ route('purchase.advance.provider.anydata') }}` + '/' + docNum
          },
          columns: [{
            name: 'Dscription',
            data: 'Dscription',
            render: renderItemName
          },
            {
              name: 'Quantity',
              data: 'Quantity',
              render: format_quantitiesDataTable
            },
            {
              name: 'Price',
              data: 'Price',
              render: format_totalsDataTable
            },
            {
              name: 'LineTotal',
              data: 'LineTotal',
              render: format_totalsDataTable
            },
            {
              name: 'Project',
              data: 'Project'
            },
            {
              name: 'OcrCode',
              data: 'OcrCode'
            },
            {
              name: 'OcrCode2',
              data: 'OcrCode2'
            },
          ],
          order: [
            [1, "desc"]
          ],
          language: dataTablesPtBr,
          paging: false,
          "lengthChange": false,
          "ordering": false,
          "bFilter": false,
          "bInfo": false,
          "searching": false
        });

        $('#previewModal').modal('show');
      }
    });

    function renderItemName(value, type, row) {

      return `<a class="text-warning" href="{{ route('inventory.items.edit') }}/${row.ItemCode}" target="_blank">
        <svg class="icon icon-lg">
          <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
        </svg>
      </a>
      <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${row.ItemCode} - ${row.Dscription}">${row.ItemCode} - ${row.Dscription}</span>`;
    }

    function format_totalsDataTable(data, type, row){
      const formatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      });
      return formatter.format(data);
    }

    function format_quantitiesDataTable(data, type, row){
      const formatter = new Intl.NumberFormat('pt-BR').format(data)
      return formatter
    }


  </script>
@endsection
