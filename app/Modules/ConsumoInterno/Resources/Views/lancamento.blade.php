@extends("layouts.main")

@section("content")
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Lançamento de Consumo Interno Diário - {{$pv->nome}}
            </h1>
            <ol class="breadcrumb">
                <li>
                    <i class="fa fa-dashboard"></i> <a href="/">Home</a>
                </li>
                <li class="active">
                    <i class="fa fa-table"></i> Lançamento de Consumo Interno Diário
                </li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-responsive table-striped">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Centro de Custo</th>
                    <th>Projeto</th>
                    <th>Adicionar/Remover</th>
                    <th>Usuário</th>
                    <th>Data/Hora</th>
                </tr>
                </thead>
                <tbody>
                <?php /** @var $item \App\Modules\ConsumoInterno\Models\Lancamento\Item */ ?>
                @foreach($items as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td>{{$item->cod_sap}}</td>
                        <td>{{$item->descricao}}</td>
                        <td>{{$item->qtd}}</td>
                        <td>{{$item->centro_custo}}</td>
                        <td>{{$item->projeto}}</td>
                        <td>
                            <button onclick="if(confirm('Deletar este item?')){window.location.href='{{route("consumo-interno.excluir", ["id"=>$item->id])}}';}"
                                    class="btn btn-danger">Excluir</button>
                        </td>
                        <td>{{$item->user->name}}</td>
                        <td>{{$item->created_at->format("d-m-Y / H:i")}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td><input type="text" required id="codigo" name="codigo" form="addItem" class="form-control"
                               onfocusout="getItem(this)"></td>
                    <td><input type="text" required id="nome" name="nome" form="addItem" class="form-control"></td>
                    <td><input type="text" data-mask="999" required id="qtd" name="qtd" form="addItem"
                               class="form-control"></td>
                    <td><select class="form-control" required form="addItem" id="centro_custo" name="centro_custo">
                            <option></option>
                            @foreach($regrasDist as $item)
                                <option value="{{$item["valor"]}}">{{$item["nome"]}}</option>
                            @endforeach
                        </select></td>
                    <td><select class="form-control" required form="addItem" id="projeto" name="projeto">
                            <option></option>
                            @foreach($projetos as $item)
                                <option value="{{$item["valor"]}}">{{$item["nome"]}}</option>
                            @endforeach
                        </select></td>
                    <td colspan="3">
                        <button type="submit" form="addItem" class="btn btn-success" disabled id="btn-add">Adicionar
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            <form method="post" action="{{route("consumo-interno.addItem")}}" id="addItem">
                {!! csrf_field() !!}
                <input type="hidden" name="pv_id" value="{{$pvId}}">
            </form>
        </div>
    </div>
@endsection

@section("scripts")
    <script type="application/javascript">
        $("#nome").autocomplete({
            serviceUrl: '{{route("consumo-interno.getItem")}}',
            onSelect: function (suggestion) {
                //alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
                $("#codigo").val(suggestion.data);
                $("#btn-add").prop("disabled", false);
            },
            onSearchStart: function (query) {
                $("#btn-add").prop("disabled", true);
                $("#codigo").val("");
            }
        });

        function getItem(element) {
            var item = $(element);
            if (item.val() !== "") {
                waitingDialog.show("Carregando...");
                $.getJSON('{{route('consumo-interno.getItemByCode')}}', {itemCode: item.val()}, function (data) {
                    if (data.ItemName) {
                        $("#nome").val(data.ItemName);
                    }
                }).always(function (value) {
                    waitingDialog.hide();
                    $("#qtd").focus();
                    validate();
                });
            }
        }

        function validate() {
            var qtd = parseFloat($("#qtd").val());
            var itemCode = $("#codigo").val();
            var name = $("#nome").val();

            if (qtd > 0 && itemCode !== "" && name !== "") {
                $("#btn-add").prop("disabled", false);
            } else {
                $("#btn-add").prop("disabled", true);
            }
        }

        $("input").on("change keyup", validate);
        $("#qtd").maskMoney({precision: 3});
    </script>
@endsection