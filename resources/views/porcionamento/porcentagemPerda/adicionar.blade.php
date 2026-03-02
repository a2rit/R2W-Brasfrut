@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Porcentagem de Perda</h3>
  </div>
  <hr>

  <form action="{{ route('porcionamento.porcentagem-perda-post') }}" method="post" class="row">
    {{ csrf_field() }}
    @if (!isset($data))
      <div class="form-group col-md-4">
        <label>Buscar item</label>
        <select class="form-control selectpicker with-ajax-item" data-live-search="true" data-name="item" required
          name="codigo">
          <option value="">Nada selecionado</option>
        </select>
      </div>
    @else
      <input type="hidden" name="codigo" value="{{ $data->codigo }}">
    @endif
    <div class="col-md-4 form-group">
      <label>Porcentagem padrão</label>
      <input class="form-control" name="porcentagem_base"
        @if (isset($data)) value="{{ $data->porcentagem_base }}" @endif type="number" min="0"
        max="100" step="1" required>
    </div>
    <div class="col-md-4 form-group">
      <label>Porcentagem máxima</label>
      <input class="form-control" name="porcentagem_aceita"
        @if (isset($data)) value="{{ $data->porcentagem_aceita }}" @endif type="number" min="0"
        max="100" step="1" required>
    </div>
    <div class="col-md-4 form-group">
      <label>Comentário</label>
      <input type="text" class="form-control" name="comentario"
        @if (isset($data)) value="{{ $data->comentario }}" @endif>
    </div>
    <div class="col-md-12">
      <button class="btn btn-primary float-end mt-5" type="submit">Salvar</button>
    </div>
  </form>

@endsection

@section('scripts')
  <script>

    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-item')
      .ajaxSelectPicker(getAjaxSelectPickerOptions('{{ route('porcionamento.buscaItem') }}'));
    $('.maskMoney').maskMoney();
    
  </script>
@endsection
