@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Relatório</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('inventory.stockloan.reports.gerar') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="name" class="col-md-4 control-label">Código</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="code" placeholder='REC00001'>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="col-md-4 control-label">Status</label>
                                <div class="col-md-6">
                                    <select class="form-control" name="status">
                                        <option value='0' selected >Selecione</optin>
                  
                                            <option value='2'>SAP-B1 (Sicronizado)</optin>
                                            
                                            <option value='4'>SAP-B1 (Devolvido)</optin>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ativo" class="col-md-4 control-label">Atendente:</label>
                                <div class="col-md-6">
                                    <select id="ativo" class="form-control" name="atendente">
                                        <option value=''>Selecione</option>
                                        @foreach($atendentes as $key => $value)
                                            @if(!is_null($value[0]->requester))
                                                <option value='{{ $value[0]->name }}'>{{ $value[0]->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ativo" class="col-md-4 control-label">Solicitante:</label>
                                <div class="col-md-6">
                                    <select id="ativo" class="form-control" name="solicitante">
                                        <option value=''>Selecione</option>
                                        @foreach($solicitantes as $key => $value)
                                            @if(!is_null($value[0]->requester))
                                                <option value='{{ $value[0]->requester }}'>{{ $value[0]->solicitante_firstName.' '.$value[0]->solicitante_middleName.' '.$value[0]->solicitante_lastName }}</option>
                                            @endif'
                                        @endforeach
                                    </select>                                
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ativo" class="col-md-4 control-label">Devolvido:</label>
                                <div class="col-md-6">
                                    <select id="ativo" class="form-control" name="devolvido">
                                        <option value=''>Selecione</option>
                                        @foreach($devolvidos as $key => $value)
                                            @if(!is_null($value[0]->returner))
                                                <option value='{{ $value[0]->returner }}'>{{ $value[0]->devolvido_firstName.' '.$value[0]->devolvido_middleName.' '.$value[0]->devolvido_lastName }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- 
                            <div class="form-group">
                                <label for="ativo" class="col-md-4 control-label">Departamento:</label>
                                <div class="col-md-6">
                                    <select id="ativo" class="form-control" name="departamento">
                                        <option value=''>Selecione</option>
                                        @foreach($departamento as $key => $value)
                                            <option value='{{ $value->value }}'>{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
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
                                <label for="group_id" class="col-md-4 control-label">Tipo:</label>
                                <div class="col-md-6">
                                   <select name="tipo" class="form-control">
                                    <option value="1" selected>Sintético</option>    
                                    <option value="2">Analítico</option>    
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
