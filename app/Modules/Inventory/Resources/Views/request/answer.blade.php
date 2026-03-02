@extends('layouts.main')
@section('title', 'Requisições')
@section('content')

  <div class="col-12">
    <h3 class="header-page">Requisição interna - atendimento</h3>
  </div>
  <hr>

  <form action="{{ route('inventory.request.store') }}" method="post" id="needs-validation" enctype="multipart/form-data"
    onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <div class="row">
      <div class="col-md-2">
        <label>Cod Web:</label>
        <input class="form-control locked" type="text" value="{{ $userData[0]->code }}" readonly name="codeRequest">
        <input class="form-control" type="hidden" value="{{ $userData[0]->id }}" name="id">
      </div>
      <div class="col-md-3">
        <label>Solicitante:</label>
        <input type="text" value="{{ $userData[0]->name }}" class="form-control" disabled>
      </div>
      <div class="col-md-2">
        <label>Data do Documento:</label>
        <input class="form-control" type="date" value="{{ $userData[0]->documentDate }}" disabled
          name="data">
      </div>
      <div class="col-md-2">
        <label>Data Necessária:</label>
        <input class="form-control" type="date" value="{{ $userData[0]->requiredDate }}" disabled
          name="data">
      </div>
      <div class="col-md-3">
        <label>Status:</label>
        <input class="form-control" type="text" value="{{ $requests::TEXT_STATUS[$userData[0]->codStatus] }}" disabled>
      </div>
    </div>
    <hr>
    <div class="row mb-3 mt-3">
      @if (
          $userData[0]->requesterUser == auth()->user()->id &&
              ($userData[0]->codStatus == $requests::STATUS_WAIT_REQUESTER ||
                  $userData[0]->codStatus == $requests::STATUS_WAIT_CLERK))
        <div class="col-md-1">
          <label>Recebido</label>
            <input type="checkbox" name="receber" class="form-check-input" style="width: 30px; height: 30px;">
        </div>
        @if ($userData[0]->requesterUser == auth()->user()->id && $userData[0]->codStatus == $requests::STATUS_WAIT_REQUESTER)
          <div class="col-md-1">
            <label>Recusar</label>
            <input type="checkbox" name="recusar" id='recuse' onclick="checkRecuse();" class="form-check-input"
              style="width: 30px; height: 30px;">
          </div>
        @endif
      @endif
    </div>
    <div class="table-responsive">
      <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 2%">#</th>
            <th style="width: 15%">Descrições</th>
            <th style="width: 8%">Qtd. Estoque</th>
            <th style="width: 10%">Qtd. Solicitada</th>
            <th style="width: 10%">Qtd Pendente</th>
            <th style="width: 10%">Qtd Atendida</th>
            @if (auth()->user()->tipo != 'A')
              <th style="width: 15%">Status</th>
            @else
              <th style="width: 10%">Solicitação de Compra</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @if (isset($productsData))
            <?php $cont = 1; ?>
            @foreach ($productsData as $key => $value)
              <tr>
                <td>{{ $cont }}</td>
                <td style="max-width: 16em;">
                  <div class="d-flex flex-row" style="max-width: 100%;">
                    <a class="text-warning" href="{{ route('inventory.items.edit', $value->codSAP) }}"
                      target="_blank">
                      <svg class="icon icon-lg">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                      </svg>
                    </a>
                    <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                      title="{{ $value->codSAP }} - {{ $value->itemname }}">{{ $value->codSAP }} -
                      {{ $value->itemname }}</span>
                  </div>
                </td>
                <td>{{ round($value->inventory, 2) }}</td>
                <td>{{ $value->quantityRequest }}</td>
                <td>{{ $value->pendingAmount }}</td>
                @if (
                    $userData[0]->clerkUser == auth()->user()->id &&
                        ($userData[0]->codStatus == $requests::STATUS_CLERK_LINK || $value->pendingAmount > 0))
                  <td>
                    <input type="text" class="form-control qtd" id='idProducts-{{ $cont }}' required
                      min="0"
                      @if ($value->statusCode > '2' || $value->quantityRequest == $value->quantityServed) readonly value='{{ $value->quantityServed }}' @endif
                      onclick='destroyMask(event)' name="item[{{ $cont }}][{{ $value->id }}][quantityServed]"
                      onblur="checkValue('{{ (int) $value->pendingAmount }}', '{{ $value->quantityRequest }}',this,'{{ round($value->inventory, 2) }}'); setMaskMoney();focusBlur(event)">
                  </td>
                @else
                  <td>{{ $value->quantityServed }}</td>
                @endif
                @if (auth()->user()->tipo != 'A')
                  <td>{{ $requests::TEXT_STATUS[$userData[0]->codStatus] }}</td>
                @else
                  @if (round($value->inventory, 2) < $value->quantityRequest && $userData[0]->codStatus == $requests::STATUS_CLERK_LINK)
                    <td class="text-center">
                      <input type="checkbox" checked
                        name="item[{{ $cont }}][{{ $value->id }}][solicitarCompra]" class="form-check-input"
                        style="width: 30px; height: 30px;">
                    </td>
                  @else
                    <td></td>
                  @endif
                @endif
              </tr>
              <?php $cont++; ?>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
    <div class="row mt-4">
      <div class="col-md-6">
        <label>Observações do Solicitante</label>
        <div class="form-group">
          <textarea class="form-control" rows="2" name="obsSolicitante" id='obsSolicitante'
            placeholder="{{ $userData[0]->description }}" @if (auth()->user()->tipo == 'A' || $userData[0]->codStatus != $requests::STATUS_WAIT_REQUESTER) disabled @endif></textarea>
        </div>
      </div>
      <div class="col-md-6">
        <label>Observações do Atendente</label>
        <div class="form-group">
          <textarea class="form-control" rows="2" name="obsAtentente" id='obsAtentente'
            placeholder="{{ $userData[0]->description2 }}" @if ($userData[0]->codStatus != $requests::STATUS_CLERK_LINK || auth()->user()->tipo == 'S') disabled @endif></textarea>
        </div>
      </div>
    </div>
    @if (
        ($userData[0]->requesterUser == auth()->user()->id && $requests::STATUS_WAIT_REQUESTER) ||
            ($userData[0]->clerkUser == auth()->user()->id &&
                ($userData[0]->codStatus == $requests::STATUS_WAIT_REQUESTER ||
                    $userData[0]->codStatus == $requests::STATUS_CLERK_LINK ||
                    $userData[0]->codStatus == $requests::STATUS_NFS_SAP ||
                    $userData[0]->codStatus == $requests::STATUS_WAIT_CLERK ||
                    $userData[0]->codStatus == $requests::STATUS_PARTIAL_ATTENDED)))
      <button class="btn btn-primary mt-5 float-end" type="submit">Salvar</button>
    @endif
  </form>
@endsection
@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script>
    $(document).ready(function() {
      setMaskMoney();
      // $('.money').maskMoney({thousands: '.', decimal: ',', allowZero: true});
    });

    function setMaskMoney() {
      $('.qtd').maskMoney({
        precision: 3,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $('.money').maskMoney({
        precision: 2,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      $('.moneyPlus').maskMoney({
        precision: 4,
        decimal: ',',
        thousands: '.',
        selectAllOnFocus: true
      });
      
      $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"]'), function(index, value) {
        $(value).trigger('mask.maskMoney')
      })
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    $('.dataTables-example').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
    });

    function checkRecuse() {
      if ($('#recuse').prop('checked')) {
        @if (auth()->user()->tipo == 'A')
          $('#obsAtentente').attr('required', true);
          $('#obsAtentente').attr('disabled', false);
        @else
          $('#obsSolicitante').attr('disabled', false);
          $('#obsSolicitante').attr('required', true);
        @endif
      } else {
        @if (auth()->user()->tipo == 'A')
          $('#obsAtentente').attr('required', false);
          $('#obsAtentente').attr('disabled', true);
        @else
          $('#obsSolicitante').attr('required', false);
          $('#obsSolicitante').attr('disabled', true);
        @endif
      }
    }

    function checkValue(pendente, value, input, whs) {
      input = $(input);
      
      if (!isNaN(parseFloat(pendente)) && !isNaN(parseFloat(value)) && !isNaN(parseFloat(input.val())) && !isNaN(
          parseFloat(whs))) {
        if (parseFloat(pendente) > 0) {
          if (parseFloat(pendente) < parseFloat(input.val())) {
            alert("O valor tem que ser menor ou igual ao Pendente!");
            input.val(0);
          }
        } else {
          if (parseFloat(value) < parseFloat(input.val())) {
            alert("O valor tem que ser menor ou igual ao solicitado!");
            input.val(0);
          }
          if ((parseFloat(whs) - parseFloat(input.val())) < 0) {
            alert("A quantidade em estoque não pode ficar negativo! Por favor, efetue uma solicitação de compra.");
            input.val(0);
          }
          if (parseFloat(input.val()) < 0) {
            alert("A quantidade Atendida não pode ser negativa! ");
            input.val(0);
          }
        }

      } else {
        input.val(0);
      }
    }
  </script>
@endsection
