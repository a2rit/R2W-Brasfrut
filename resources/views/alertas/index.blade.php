@extends('layouts.main')
@section('title', 'Alertas')
@section('content')
  <div class="row">
    <div class="col-md-6">
      <h3 class="header-page">Lista alertas</h3>
    </div>
  </div>
  <hr><br>
  <div class="table-responsive">
    {{ $all->links('pagination::bootstrap-4') }}
    <table id="table" class="table table-default table-striped table-bordered table-hover dataTables-example">
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 20%;">Documento</th>
          <th>Descrição</th>
          <th style="width: 10%;">Data/Hora</th>
          <th style="width: 10%;">Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php $cont = 1; ?>
        @foreach ($all as $key => $value)
          <td>{{ $cont }}</td>
          <td>{{ $value->title }}</td>
          <td>{{ $value->text }}</td>
          <td>{{ formatDate($value->created_at) }}</td>
          <td>
            <a href="{{ route('alertas.abrir', $value->id) }}" class="btn @if($value->status == 0) btn-success @else btn-primary @endif  btn-sm w-100">ABRIR</a>
          </td>
          </tr>
          <?php $cont++; ?>
        @endforeach
      </tbody>
    </table>
    {{ $all->links('pagination::bootstrap-4') }}
  </div>
@endsection
