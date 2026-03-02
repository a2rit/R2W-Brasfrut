@extends('layouts.main')

@section('title', 'Grupos de depósitos')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Cadastro de grupos de depósitos</h3>
  </div>
  <hr>

  <form action="<?php echo route('grupo.deposito.store'); ?>" method="post" id="needs-validation" onsubmit="waitingDialog.show('Carregando...')"
    class="mb-5">
    {!! csrf_field() !!}
    @isset($head)
      <input type="hidden" class="form-control" name="id" value="{{ $head->id }}">
      @endif
      <div class="row">
        <div class="col-md-6">
          <label>Depósito</Label>
          <select class="form-control selectpicker" name="whsCode" required>
            <option value=''>Selecione</option>
            @foreach ($warehouses as $warehouse)
              <option value="{{ $warehouse->WhsCode }}" @if (isset($head->whsCode) && $warehouse->WhsCode == $head->whsCode) selected @endif>
                {{ $warehouse->WhsName }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label>Tipo</Label>
          <select name="type" class="form-control selectpicker" required>
            <option value=''>Selecione</option>
            <option value="1" @if (isset($head->type) && $head->type == 1) selected @endif>Uso e Consumo</option>
            <option value="2" @if (isset($head->type) && $head->type == 2) selected @endif>Manutenção</option>
            <option value="3" @if (isset($head->type) && $head->type == 3) selected @endif>Eventos</option>
          </select>
        </div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary mt-3" type="submit">Salvar</button>
      </div>
    </form>

    <div class="row mt-5">
      <h3 class="header-page">Lista de grupos de depósitos</h3>
    </div>
    <div class="table-responsive mt-3">
      <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example">
        <thead>
          <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 30%;">Cod. Depósito</th>
            <th style="width: 30%;">Nome Depósito</th>
            <th style="width: 20%;">Tipo</th>
            <th style="width: 15%;">Opções</th>
          </tr>
        </thead>
        <tbody>
          @if (isset($items))
            <?php $cont = 1; ?>
            @foreach ($items as $key => $value)
              <tr>
                <td>{{ $cont }}</td>
                <td>{{ $value->whsCode }}</td>
                <td>{{ $value->whsName }}</td>
                @if ($value->type == 1)
                  <td>{{ $value->type }} - Uso e Consumo</td>
                @endif
                @if ($value->type == 2)
                  <td>{{ $value->type }} - Manutenção</td>
                @endif
                @if ($value->type == 3)
                  <td>{{ $value->type }} - Eventos</td>
                @endif
                <td>
                  <a href="{{ route('grupo.deposito.edit', $value->id) }}" class="btn btn-primary btn-sm">Editar
                  </a>
                  <a href="{{ route('grupo.deposito.delete', $value->id) }}"
                    class="btn btn-danger btn-sm">Remover
                  </a>
                </td>
              </tr>
              <?php $cont++; ?>
            @endForeach
          @endif
        </tbody>
      </table>
    </div>
  @endsection
@section('scripts')
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);
  </script>

@endsection
