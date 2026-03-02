@extends('layouts.main')
@section('title', 'Aprovadores')

@section('content')

<div class="col-12">
  <h3 class="header-page">Cadastro de aprovadores</h3>
</div>
<hr>
  <form action="{{ route('administration.user.approver.save') }}" method="post" id="needs-validation"
    onsubmit="$('#loading-modal').modal('show')">
    {!! csrf_field() !!}
    <input type="hidden" name="id" @if (isset($head)) value="{{ $head->id }}" @endif>

    <div class="row">
      <div class="col-md-3">
        <label>Grupo</label>
        <select class="form-control selectpicker" data-live-search="true" name="idLoftedApproveds" required>
          <option value=''>Selecione</option>
          @foreach ($lofted as $key => $value)
            <option value="{{ $value->id }}" @if (isset($head) && $value->id == $head->idLoftedApproveds) selected @endif>
              {{ $value->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label>Nome</label>
        <select class="form-control selectpicker with-ajax-users" data-width="100%" data-live-search="true" data-size="7"
          name="user" required>
          @if (isset($head->nameApproverUser))
            <option value="{{ $head->approverUser }}">{{ $head->nameApproverUser }}</option>
          @endif
        </select>
      </div>
      <div class="col-md-3">
        <label for="nivel">Nivel</label>
        <select name="nivel" required class="form-control selectpicker" data-live-search="true" data-size="6"
          id="nivel">
          <option value=""> </option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Salvar</button>
  </form>

  <div class="col-12 mt-5">
    <h3 class="header-page">Lista de aprovadores</h3>
  </div>
  <hr>
  <div class="table-responsive mt-5">
    <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 10%;">Grupo</th>
          <th>Nome</th>
          <th style="width: 5%;">Nivel</th>
          <th style="width: 15%;">Status</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($items))
          <?php $cont = 1; ?>
          @foreach ($items as $key => $value)
            <tr>
              <td>{{ $cont }}</td>
              <td>{{ $value->nameLoftedApproveds }}</td>
              <td>{{ $value->nameApproverUser }}</td>
              <td>{{ $value->nivel }}</td>
              <td>
                <a href="{{ route('administration.user.approver.edit', $value->id) }}"
                  class='btn btn-primary btn-sm'>Editar
                </a>
                <a style="padding-left: 5%"
                  href="{{ route('administration.user.approver.remove', $value->id) }}"
                  class='btn btn-danger btn-sm'>Remover
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
  <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>
  <script type="text/javascript">
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    $(document).ready(function() {
      $('.moneyPlus').maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 5,
        allowZero: true
      });
      $('.money').maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 2,
        allowZero: true
      });
    });

    selectpicker.filter('.with-ajax-users').ajaxSelectPicker(getAjaxSelectPickerOptions(
      "{{ route('administration.user.get') }}"));
      
  </script>
@endsection
