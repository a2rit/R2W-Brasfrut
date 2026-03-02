@extends('layouts.main')
@section('title', 'NFC-e Erros')

@section('content')
  <div class="row">
    <div class="col-8">
      <h3 class="header-page">Erro de Sincronização - detalhes</h3>
    </div>
    @if(empty($erro->pedido_venda))
      <div class="col-4">
        <a href="{{ route('erros.gerar-pedido-venda', $erro->model_id) }}" class="btn btn-primary float-end" type="button">Gerar Pedido de Venda</a>
      </div>
    @endif
  </div>
  <hr>

  <div class="alert alert-danger mb-4">
    <strong>Erro: </strong> {{ $erro->mensagem }}
  </div>
  @if (isset($nf))
    <h4>Nº NF: {{ $nf->numero }} - PV: {{ $erro->pv ? $erro->pv->nome : ''}}</h4>
    <h5>Chave: {{ $nf->chave  }}</h5>
    @if(!empty($erro->pedido_venda))<h5>N° Pedido de Venda: {{ $erro->pedido_venda }}</h5>@endif
    <div class="table-responsive mt-5">
      <table class="table table-default table-bordered table-hover table-striped">
        <thead>
          <tr>
            <th>Line</th>
            <th>Código SAP</th>
            <th>Código PDV</th>
            <th>Tipo</th>
            <th>Nome</th>
            <th>OP</th>
            <th>Status OP</th>
            <th>Qtd Solicitada</th>
            <th>Qtd Em Estoque(Atual)</th>
            <th>Info</th>
          </tr>
        </thead>
        <tbody>
          @php($i = 0)
          @foreach ($nf->itens as $item)
            @php($i++)
            <tr @if ($item->erro_estoque) style="background-color: gold" @endif>
              <td>{{ $i }}</td>
              <td>{{ $item->codigo_sap }}</td>
              <td>{{ $item->codigo_pdv }}</td>
              <td>{{ $item->tipo }}</td>
              <td>{{ $item->nome }}</td>
              <td>{{ $item->codigo_op }}</td>
              <td>{{ $item->status_op }}</td>
              <td>{{ $item->quantidade }}</td>
              <td>{{ $item->qtd_sap }}</td>
              <td>
                @if ($item->erro_estoque)
                  A Quantidade em estoque está abaixo da quantidade solicitada. Depósito: {{ $item->deposito }}
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
    @if ($erro->model === \App\Modules\InternConsumption\Models\InternConsumption::class)
      <div class="table-responsive">
        <h4>Nº: {{ $erro->modelo->id }} - PV: {{ $erro->ponto_venda_label }}</h4>
        <table class="table table-default table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th>Line</th>
              <th>Código SAP</th>
              <th>Código PDV</th>
              <th>Nome</th>
              <th>Qtd Solicitada</th>
              <th>Qtd Em Estoque(Atual)</th>
              <th>Info</th>
            </tr>
          </thead>
          <tbody>
            @php($i = 0)
            @foreach ($nf->itens as $item)
              @php($i++)
              <tr @if ($item->erro_estoque) style="background-color: gold" @endif>
                <td>{{ $i }}</td>
                <td>{{ $item->codigo_sap }}</td>
                <td>{{ $item->codigo_pdv }}</td>
                <td>{{ $item->nome }}</td>
                <td>{{ $item->quantidade }}</td>
                <td>{{ $item->qtd_sap }}</td>
                <td>
                  @if ($item->erro_estoque)
                    A Quantidade em estoque está abaixo da quantidade solicitada. Depósito: {{ $item->deposito }}
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    @if ($erro->model === \App\Models\NFCe\Item::class && $erro->modelo->codigo_op)
      <div class="table-responsive">
        <table class="table table-default table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nome</th>
              <th>Qtd necessária</th>
              <th>Qtd disponível</th>
              <th>Depósito</th>
              <th>Obs</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($erro->modelo->getProductionOrderItems() as $item)
              <tr @if ($item->has_stock_error) style="background-color: gold" @endif>
                <td>{{ $item->ItemCode }}</td>
                <td>{{ $item->ItemName }}</td>
                <td>{{ number_format($item->PlannedQty, 3, ',', '.') }}</td>
                <td>{{ number_format($item->OnHand, 3, ',', '.') }}</td>
                <td>{{ $item->Warehouse }}</td>
                <td>{{ $item->LinesQty > 1 ? "{$item->LinesQty} linhas do mesmo componente na OP" : '' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  @endsection
