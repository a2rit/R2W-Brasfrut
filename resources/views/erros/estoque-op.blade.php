@extends('layouts.main')
@section('title', 'NFC-e Erros')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Itens com estoque insuficiente OP</h3>
  </div>
  <hr>

  <form id="filterForm" action="{{ route('erros.estoque-op') }}" method="GET">
    <div class="row">
      <div class="col-md-3">
        <label for="">Item Pai</label>
        <select class="form-control selectpicker with-ajax-item" name="itemPai">
          <option value="">Selecione</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="">Item</label>
        <select class="form-control selectpicker with-ajax-item" name="item">
          <option value="">Selecione</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="">Depósito</label>
        <select class="form-control selectpicker with-ajax-item" name="deposito">
          <option value="">Selecione</option>
          <option></option>
          @foreach ($deposito as $deposito)
            <option value="{{ $deposito->WhsCode }}">{{ $deposito->WhsCode }} - {{ $deposito->WhsName }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 mt-4">
        <button onclick="$('#filterForm').attr('action', '{{route('erros.estoque-op')}}')" class="btn btn-primary btn-sm">Filtrar</button>
        <button type="button" onclick="$('#filterForm').attr('action', '{{route('erros.estoque-op.to-excel')}}').submit()" class="btn btn-success btn-sm">Exportar para Excel</button>
      </div>
    </div>
  </form>

  <div class="col-12 mt-5">
    {{ $items->links('pagination::bootstrap-4') }}
    <div class="table-responsive">
      <table class="table table-default table-bordered table-hover table-striped">
        <thead>
          <tr>
            <th>N° Ordem</th>
            <th>Data</th>
            <th>Item Pai</th>
            <th>Item</th>
            <th>Qtd. Necessária</th>
            <th>Estoque atual</th>
            <th>Depósito</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $item)
            <tr>
              <td>{{ $item->DocNum }}</td>
              <td>{{ formatDate($item->StartDate) }}</td>
              <td>{{ $item->MainItemCode }} - {{ $item->MainItemName }}</td>
              <td>{{ $item->ItemCode }} - {{ $item->ItemName }}</td>
              <td>{{ number_format($item->quantidade_necessaria, 3, ',', '.') }}</td>
              <td>{{ number_format($item->estoque, 3, ',', '.') }}</td>
              <td>{{ $item->WhsName }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $items->links('pagination::bootstrap-4') }}
  </div>

@endsection
@section('scripts')
  <script>
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-item')
        .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.itemsSearch') }}"));

    function exportToExcel(event){
      event.preventDefault();
      $('#filterForm').attr("action", "{{route('erros.estoque-op.to-excel')}}");
      $('#filterForm').submit();
    }

  </script>
@endsection
