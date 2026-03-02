@extends('layouts.main')
<?php /** @var \App\Models\Porcionamento $porcionamento */?>

@section('content')
  <!-- Page Heading -->
  <div class="col-12">
    <h3 class="header-page">Porcionamento - Ver</h3>
  </div>
  <hr>
  <!-- /.row -->

  <div class="row">
    <div class="col-md-12"><strong>Nota fiscal </strong> {{ $porcionamento->nota_fiscal }} de
      {{ $porcionamento->cod_fornecedor }}
      - {{ $porcionamento->nome_fornecedor }}</div>
    <div class="col-md-12">
      <strong>Porcentagem de perda: </strong>
      <span>{{ number_format($porcionamento->porcentagem_perdas_value, 2, ',', '.') }} %</span>
    </div>
    @if ($porcionamento->justificativa)
      <div class="col-md-12">
        <strong>Justificativa: </strong>
        <span>{{ $porcionamento->justificativa }}</span>
      </div>
    @endif
    @if ($porcionamento->usuarioAutorizador)
      <div class="col-md-12">
        <strong>Ciente por: </strong>
        <span>{{ $porcionamento->usuarioAutorizador->name }}</span>
        <strong> em: </strong>
        <span>{{ $porcionamento->data_autorizacao->format('d-m-Y h:i') }}</span>
      </div>
    @endif
  </div>
  <div class="table-default mt-3">
    <table class="table table-bordered table-responsive">
      <tr>
        <th>Cod Sap</th>
        <th>Nome Item</th>
        <th>Quantidade</th>
        <th>Depósito de Origem</th>
        <th>Qtd na Origem</th>
        <th>Preço</th>
        <th>Cod Entrada</th>
        <th>Cod Saída</th>
        <th>Cod Reavaliação</th>
      </tr>
      <tr>
        <td>{{ $porcionamento->cod_item }}</td>
        <td>{{ $porcionamento->nome_item }}</td>
        <td>{{ number_format($porcionamento->quantidade, 3, ',', '.') }} {{ $porcionamento->unidade_medida }}</td>
        <td>{{ $porcionamento->deposito }}</td>
        <td class="@if(!$porcionamento->cod_saida && bccomp($porcionamento->quantidade, $porcionamento->source_quantity, 3) > 0) text-bg-danger @endif">
            {{ number_format($porcionamento->source_quantity, 3, ',', '.') }} {{ $porcionamento->unidade_medida }}
        </td>
        <td>R$ {{ number_format($porcionamento->preco, 2, ',', '.') }}</td>
        <td>{{ $porcionamento->cod_entrada }}</td>
        <td>{{ $porcionamento->cod_saida }}</td>
        <td>{{ $porcionamento->cod_reavaliacao }}</td>
      </tr>
    </table>
  </div>

  <strong class="col-md-12 title panel-title">Principal</strong>
  <div class="table-default">
    <table class="table table-bordered table-responsive" id="principal">
      <tr>
        <th style="width: 7%;">Cod Sap</th>
        <th>Descrição</th>
        <th style="width: 10%;">Utilização</th>
        <th style="width: 10%;">Produção</th>
        <th style="width: 10%;">Depósito de Destino</th>
        <th style="width: 10%;">Tipo</th>
        <th style="width: 5%;">Link</th>
      </tr>
      @foreach ($porcionamento->itens as $item)
        <tr style="background-color: {{ $item->cor_linha }}">
          <td>{{ $item->cod_item }}</td>
          <td>{{ $item->nome_item }}</td>
          <td>{{ number_format($item->quantidade_gasta, 3, ',', '.') }}</td>
          <td>{{ number_format($item->quantidade_produzida, 3, ',', '.') }}</td>
          <td>{{ $item->deposito }}</td>
          <td>{{ $item->tipo }}</td>
          <td>
            @if ($item->url)
              <a href="{{ $item->url }}"><button class="btn btn-primary">Perda</button></a>
            @endif
          </td>
        </tr>
      @endforeach
    </table>
  </div>
  @if (!$porcionamento->autorizado && Auth::user()->hasRole('Porcionamento.autorizar'))
    <div class="col-md-12">
      <a href="{{ route('porcionamento.autorizar', $porcionamento) }}">
        <button class="btn btn-warning" type="button">Ciente</button>
      </a><br>
    </div>
  @endif
  @if ($porcionamento->cod_entrada == '' || $porcionamento->cod_saida == '')
    <div class="col-md-12">
      <br>
      <a href="{{ route('porcionamento.salvarSap', ['id' => $porcionamento->id]) }}" class="btn btn-success">Cadastrar
        no SAP</a>
    </div>
  @endif
@endsection
