@extends('layouts.main')
@section('title', 'Configurações')
@section('content')

    <div class="wrapper wrapper-content">
        <form action="{{route('settings.cash.flow.save')}}" method="post" id="needs-validation"
              onkeydown='keyShowModel(event.keyCode)' enctype="multipart/form-data"
              onsubmit="waitingDialog.show('Carregando...')">
            {!! csrf_field() !!}
            <div class="row">
                <div class="ibox-title input-group-btn ">
                    <div class="col-md-8">
                        <h5><a href="{{route('home')}}">Inicio</a> &nbsp;</h5>
                        <h5>&nbsp;/ &nbsp </h5>
                        <h5><a href="{{route('settings.index')}}"> &nbsp;Configurações</a>&nbsp;</h5>
                        <h5>&nbsp;/&nbsp;</h5>
                        <h5>Fluxo de caixa</h5>
                        <h5>&nbsp;/&nbsp;</h5>
                        @if(isset($head))
                            <h5>Editando</h5>
                            <input type="hidden" name="id" value="{{$head->id}}">
                        @else
                            <h5>Cadastrando</h5>
                        @endif
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <label>Descrição</Label>
                                <input type="text" class="form-control" name="description" @if(isset($head->description)) value="{{$head->description}}" @endif autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label>Módulo</Label>
                                <select class="form-control" name="module">
                                    <option value='C' @if(isset($head->module) && ($head->module == 'C')) selected @endif>COMPRA</option>
                                    <option value='V' @if(isset($head->module) && ($head->module == 'V')) selected @endif>VENDA</option>
                                </select>
                            </div>
                            <div class="col-md-3  -align-right right pull-right right" style="padding-top: 2%">
                                <div class="form-group pull-right">
                                    <button class="btn btn-primary" type="submit">Salvar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="ibox-title input-group-btn ">
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="table" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                        <tr>
                            <th style="width: 8%">#</th>
                            <th>Descrição</th>
                            <th>Módulo</th>
                            <th>Usuário</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($busca))
                            <?php $cont = 1; ?>
                            @foreach($busca as $key => $value)
                                <tr>
                                    <td style="width: 8%">{{$cont}}</td>
                                    <td style="width: 30%">{{$value->description}}</td>
                                    <td>{{$value->module}}</td>
                                    <td style="width: 20%">{{$value->name}}</td>
                                    <td style="width: 15%">{{formatDate($value->created_at)}}</td>

                                    @if($value->status == 0)
                                        <td><span class="btn btn-success"
                                                  onclick="upStatus('{{$value->id}}')">Ativar</span>
                                        </td>
                                    @else
                                        <td><span class="btn btn-danger" onclick="downStatus('{{$value->id}}')">Desativar</span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                                            <a href="{{route('settings.cash.flow.read',$value->id)}}"
                                               class="btn btn-warning">Editar</a>
                                        </td>
                                    @endif
                                </tr>
                                <?php $cont++;?>
                            @endForeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>
        <div class="hr-line-dashed"></div>
    </div>
@endsection
@section('scripts')
    <script src="{!! asset('js/jquery.mask.min.js') !!}" type="text/javascript"></script>

    <script type="text/javascript">
        $("#table").css("width", "100%");
        let table = $("#table").DataTable({
            responsive: true,
            lengthMenu: [50, 100, 150],
            language: dataTablesPtBr
        });

        $('.datepicker').datepicker({
            closeText: 'Fechar',
            prevText: '&#x3c;Anterior',
            nextText: 'Pr&oacute;ximo&#x3e;',
            currentText: 'Hoje',
            monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            dayNames: ['Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        });
        $(document).ready(function () {
            $('.money').maskMoney({thousands: '.', decimal: ',', allowZero: true, prefix: 'R$'});
        });

        function downStatus(id) {
            swal({
                title: "Tem certeza que deseja desativar?",
                icon: "warning",
                //buttons: true,
                buttons: ["Fechar", "Desativar"],
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        waitingDialog.show('Carregando...');
                        window.location.href = '{{route('settings.cash.flow.status.update')}}' + '/' + id;
                    }
                });
        }

        function upStatus(id) {
            window.location.href = '{{route('settings.cash.flow.status.update')}}' + '/' + id;
        }
    </script>
@endsection
