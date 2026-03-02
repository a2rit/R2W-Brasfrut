@extends('layouts.main')

@section('content')
    <!-- Page Heading -->
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Porcentagem de Perda
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i> <a href="/">Home</a>
                </li>
                <li class="active">
                    <i class="fa fa-edit"></i> Adicionar Porcentagem de Perda
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->
    <form action="{{route("porcionamento.porcentagem-perda-post")}}" method="post" class="row">
        {{csrf_field()}}
        <div class="form-group col-md-4">
            <label>Buscar item</label>
            <select class="form-control selectpicker with-ajax-item" data-live-search="true"
                    data-name="item" required name="codigo">
                <option value="">Nada selecionado</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label>Porcentagem padrão</label>
            <input class="form-control" name="porcentagem_base" type="number" min="0" max="100" step="1" required>
        </div>
        <div class="col-md-4 form-group">
            <label>Porcentagem máxima</label>
            <input class="form-control" name="porcentagem_aceita" type="number" min="0" max="100" step="1" required>
        </div>
        <div class="col-md-4 form-group">
            <label>Comentário</label>
            <input type="text" class="form-control" name="comentario">
        </div>
        <div class="col-md-12">
            <button class="btn btn-success" type="submit">Salvar</button>
        </div>
    </form>

@endsection

@section("scripts")
    <script>

        let selectpicker = $('.selectpicker').selectpicker('refresh');
        selectpicker.filter('.with-ajax-item')
            .ajaxSelectPicker(getAjaxSelectPickerOptions('{{route('porcionamento.buscaItem')}}'));
        $('.maskMoney').maskMoney();
        
    </script>
    @endsection