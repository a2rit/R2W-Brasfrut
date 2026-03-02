@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Relatório</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('purchase.receipts.goods.gerar') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="code" class="col-md-4 control-label">Código</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="code" placeholder='REC00001'>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="usuario" class="col-md-4 control-label">Parceiro</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" placeholder='Parceiro'>
                                </div>
                            </div>
                           <div class="form-group">
                                <label for="status" class="col-md-4 control-label">Status</label>
                                <div class="col-md-6">
                                    <select class="form-control" name="status">
                                        <option value='' >Selecione</option>
                                        <option value='1'>Aberto</option>
                                        <option value='0'>Fechado</option>
                                        <option value='2'>Cancelado</option>
                                        <option value='3'>Pendente</option>
                                        <option value='4'>Reprovado</option>       
                                    </select>
                                </div>
                            </div>
                          
                            <div class="form-group">
                                <label for="group_id" class="col-md-4 control-label">Data Inicial:</label>
                                <div class="col-md-6">
                                    <input id="group_id" type="date" class="form-control" name="data_ini">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="group_id" class="col-md-4 control-label">Data Final:</label>
                                <div class="col-md-6">
                                    <input id="group_id" type="date" class="form-control" name="data_fim">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tipo" class="col-md-4 control-label">Tipo</label>
                                <div class="col-md-6">
                                    <select class="form-control" name="tipo">
                                        <option value='1' selected>Sintético</option>
                                        <option value='2'>Analítico</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Gerar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
