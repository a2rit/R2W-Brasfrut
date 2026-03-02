@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Porcionamento - Criar</h3>
  </div>
  <hr>

  <div class="row">
    <div class="col-md-12"><strong>Fornecedor: </strong> {{ $item->CardCode }} - {{ $item->CardName }}</div>
    <div class="col-md-12"><strong>NFe: </strong> {{ $item->Serial }}</div>
  </div>

  <div class="table-responsive mt-3">
    <table class="table table-default table-bordered table-responsive">
      <tr>
        <th>Cod Sap</th>
        <th>Nome Item</th>
        <th>Quantidade / Utilizado</th>
        <th>Depósito de Origem</th>
        <th>Qtd na Origem</th>
        <th>Valor Total NF</th>
        <th>Valor Kg limpo</th>
        <th>Valor Kg sujo</th>
        <th>Peso Limpo / Perdas</th>
        <th>Perdas / Aproveitamento %</th>
      </tr>
      <tr>
        <td>{{ $item->ItemCode }}</td>
        <td>{{ $item->Dscription }}</td>
        <td>{{ number_format($item->Quantity, 3) }} / <span id="utilizado"></span> {{ $item->unitMsr }}</td>
        <td>{{ $item->WhsCode }}</td>
        <td class="@if(bccomp($item->Quantity, $item->OnHand, 3) > 0) text-bg-danger @endif">
          {{ number_format($item->OnHand, 3) }} {{ $item->unitMsr }}
        </td>
        <td>R$ {{ number_format($item->Price * $item->Quantity, 2) }}</td>
        <td>R$ <input style="width: 6em" type="text" readonly id="valorKgLimpo" disabled></td>
        <td>R$ <input style="width: 6em" type="text" id="valorKgSujo" disabled
            value="{{ number_format($item->Price, 4, '.', '') }}"></td>
        <td><span id="infoPeso"></span></td>
        <td><span id="infoPercentual"></span></td>
      </tr>
    </table>
  </div>

  <div class="table-responsive mt-4">
    <strong class="title panel-title">Porções Úteis</strong>
    <table class="table table-default table-bordered table-responsive" id="principal">
      <tr>
        <th>Cod Sap</th>
        <th>Descrição</th>
        <th>Produção</th>
        <th>UmO</th>
        <th>Custo</th>
        <th>Utilização</th>
        <th>Um</th>
        <th>Depósito de Destino</th>
        <th>Custo Unitário</th>
        <th>Ações</th>
      </tr>
    </table>
  </div>

  <div class="col-12">
    <button class="btn btn-primary" onclick="addLinha('#principal', 'Principal', 'calcularCustoPrimario')">
      Adicionar Linha
    </button>
  </div>

  <div class="table-responsive mt-4">
    <strong class="title panel-title">Aparas Aproveitáveis</strong>
    <table class="table table-default table-bordered table-responsive" id="aproveitaveis">
      <tr>
        <th>Cod Sap</th>
        <th>Descrição</th>
        <th>Produção</th>
        <th>UmO</th>
        <th>Custo</th>
        <th>Utilização</th>
        <th>Um</th>
        <th>Depósito de Destino</th>
        <th>Custo Unitário</th>
        <th>Ações</th>
      </tr>
    </table>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" onclick="addLinha('#aproveitaveis', 'Aproveitáveis', 'calcularCustoAA')">
      Adicionar Linha
    </button>
  </div>

  <div class="table-responsive mt-4">
    <strong class="title panel-title" id="perdasTitle">Perdas</strong>
    <table class="table table-default table-bordered table-responsive" id="secundario">
      <tr>
        <th>Cod Sap</th>
        <th>Descrição</th>
        <th>Produção</th>
        <th>Um</th>
        <th>Custo</th>
        <th>Utilização</th>
        <th>UmO</th>
        <th>Depósito de Destino</th>
        <th>Custo Unitário</th>
        <th>Ações</th>
      </tr>
    </table>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" onclick="addLinha('#secundario', 'Secundário', 'calcularCustoSecundario')">
      Adicionar Linha
    </button>
  </div>

  <div class="row mt-4">
    <div class="col-md-3">
      <div class="form-group col-lg-12">
        <label>Projeto</label>
        <select name="projeto" class="form-control selectpicker" id="projeto">
          <option></option>
          @foreach ($parametros['projetos'] as $projeto)
            <option value="{{ $projeto->valor }}">{{ $projeto->nome }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="col-md-3">
      <div class="form-group col-lg-12">
        <label>Regra de Distribuição</label>
        <select name="regra_distribuicao" class="form-control selectpicker" id="regra_distribuicao">
          <option></option>
          @foreach ($parametros['regrasDistribuicao'] as $projeto)
            <option value="{{ $projeto->valor }}">{{ $projeto->nome }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-group col-lg-12">
        <label>Quantidade devolvida:</label>
        <input class="form-control" type="text" id="qtdDevolvida">
      </div>
    </div>
    <div class="col-md-4 form-group justificativaSelect" style="display: none">
      <label for="">Justificativa</label>
      <select onchange="changeJustificativa()" class="form-control" id="justificativaSelect" name="justificativaSelect">
        <option value=""></option>
        @foreach (\App\Models\Porcionamento\Justificativa::all() as $justificativa)
          <option value="{{ $justificativa->justificativa }}">{{ $justificativa->justificativa }}</option>
        @endforeach
        <option value="outra">Digitar outra</option>
      </select>
    </div>
    <div class="col-md-4 form-group justificativa" style="display: none">
      <label for="">Digite sua justificativa:</label>
      <input type="text" id="justificativa" minlength="20" maxlength="200" name="justificativa" class="form-control">
    </div>
    <div class="col-md-2">
      <br>
      <button class="btn btn-primary" onclick="salvar();">Salvar</button>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    var _item = {!! json_encode($item) !!};
    var contador = 1;
    var buscando = false;
    var pPerdas;
    var _pPerda = false;
    $("#qtdDevolvida").mask("0999.000");

    function addLinha(tabela, tipo, funcao) {
      if (parseFloat($("#valorKgSujo").val()) <= 0 || isNaN(parseFloat($("#valorKgSujo").val()))) {
        alert("Informe o valor do Kg sujo.");
        $("#valorKgSujo").focus();
        return false;
      }

      if (typeof funcao === 'undefined') {
        funcao = 'calcularCusto'
      }
      var tr = $("<tr id='linha-" + contador + "'>")
        .append($("<td id='td-" + contador + "'><input class='itensPrincipais form-control' data-tipo='" + tipo +
          "' data-linha='" + contador + "' type='text' data-ok=false" +
          " id='codItem-" + contador + "'></td>"))
        .append($("<td id='descricao-" + contador + "'></td>"))
        .append($("<td><input class='form-control' onblur='" + funcao + "(" + contador +
          "); atualizarTudo();' id='producao-" + contador + "' disabled type='text'></td>"))
        .append($("<td id='um-" + contador + "'></td>"))
        .append($("<td id='custo-" + contador + "'></td>"))
        .append($("<td><input class='form-control' id='utilizacao-" + contador + "' onblur='" + funcao + "(" + contador +
          "); atualizarTudo();' disabled type='text'></td>"))
        .append($("<td>{{ $item->unitMsr }}</td>"))
        .append($("<td><select class='form-control selectpicker' data-container='body' id='deposito-" + contador + "'>{!! $depositos !!}</select></td>"))
        .append($("<td><input class='form-control' onblur='calcularCusto(" + contador +
          "); atualizarTudo();' id='precoUnitario-" + contador + "' disabled type='text'></td>"))
        .append($(`<td class="text-center">
                      <a type="button" class="text-danger" onclick='deletarLinha(${contador})'>
                        <svg class="icon icon-xl">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                        </svg>
                      </a>
                    </td>`));

      $(tabela).append(tr);
      $("#utilizacao-" + contador).mask("0999.000");
      $("#producao-" + contador).mask("0999.000");

      $('#codItem-' + contador).autocomplete({
        serviceUrl: '{{ route('porcionamento.getItens') }}',
        onSelect: function(suggestion) {
          //alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
          $(this).val(suggestion.data);
          buscarItem(suggestion.data, $(this).data("linha"), tipo);
        }
      });

      $('#codItem-' + contador).keydown(function(event) {
        if (event.which === 9 || event.which === 13) {
          buscarItem($(this).val(), $(this).data("linha"), tipo);
        }
      });

      $('.selectpicker').selectpicker(selectpickerConfig);
      contador++;
    }

    function deletarLinha(index) {
      $("#linha-" + index).remove();
      atualizarTudo();
    }

    function buscarItem(codItem, linha, tipo) {
      if (codItem == "" || buscando) {
        return false;
      }
      buscando = true;
      $.ajax("{{ route('porcionamento.getItem') }}", {
        method: "GET",
        data: {
          itemCode: codItem
        },
        beforeSend: function() {
          waitingDialog.show('Carregando...');
          $("#codItem-" + linha).prop('disabled', true);
        },
        complete: function(data) {
          if (data.responseJSON.ItemCode) {
            if (tipo === 'Principal' && data.responseJSON.pPerda) {
              _pPerda = data.responseJSON.pPerda;
            }
            $("#descricao-" + linha).html(data.responseJSON.ItemName);
            $("#um-" + linha).html(data.responseJSON.SalUnitMsr);
            $("#producao-" + linha).prop('disabled', false);
            $("#utilizacao-" + linha).prop('disabled', false);
            $("#custo-" + linha).prop('disabled', false);
            $("#codItem-" + linha).prop('disabled', true).data('ok', true);
            $("#deposito-" + linha).val(data.responseJSON.DfltWH);
            $("#precoUnitario-" + linha).val(parseFloat(data.responseJSON.AvgPrice).toFixed(4));

            if ($("#codItem-" + linha).data("tipo") == "Secundário") {
              $("#precoUnitario-" + linha).prop('disabled', false)
                .mask("09999.0000");
            }
          } else {
            $("#codItem-" + linha).prop('disabled', false);
            $("#descricao-" + linha).html("Item não encontrado!");
          }
          waitingDialog.hide();
          $("#utilizacao-" + linha).focus();
          buscando = false;
        },
        error: function() {
          waitingDialog.hide();
        }
      });
    }

    function salvar() {
      var itens = [];
      $(".itensPrincipais").each(function(index, item) {
        var linha = $(item).data("linha");

        if ($(item).data("ok") === false) {
          deletarLinha(linha);
          return true;
        }
        itens.push({
          codSap: $("#codItem-" + linha).val(),
          nome: $("#descricao-" + linha).html(),
          utilizacao: parseFloat($("#utilizacao-" + linha).val()),
          producao: parseFloat($("#producao-" + linha).val()),
          deposito: $("#deposito-" + linha).val(),
          custo: parseFloat($("#custo-" + linha).html()),
          tipo: $("#codItem-" + linha).data("tipo")
        });
      });
      if (validar(itens) === true && confirm("Salvar?")) {
        // enviar via AJAX
        waitingDialog.show('Salvando...');
        $.ajax("{{ route('porcionamento.salvar') }}", {
          method: "POST",
          data: {
            _token: "{{ csrf_token() }}",
            itens: itens,
            item: _item,
            regra_distribuicao: $("#regra_distribuicao").val(),
            projeto: $("#projeto").val(),
            devolvido: $("#qtdDevolvida").val(),
            justificativa: $("#justificativa").val(),
            justificativaSelect: $("#justificativaSelect").val()
          },
          complete: function(data) {
            if (data.responseJSON.sucesso === true) {
              window.location.href = "{{ route('porcionamento.ver') }}/" + data.responseJSON.porcionamento_id;
            } else {
              alert(data.responseJSON.msg);
              waitingDialog.hide();
            }
          }
        });
      }
    }

    function validar(itens) {
      var totalUtilizado = 0;
      if (!isNaN(parseFloat($("#qtdDevolvida").val()))) {
        totalUtilizado = totalUtilizado + parseFloat($("#qtdDevolvida").val());
      }
      var erro = false;
      $(itens).each(function(index, item) {
        totalUtilizado = totalUtilizado + item.utilizacao;

        if (item.utilizacao <= 0 || isNaN(item.utilizacao) || item.producao <= 0 || isNaN(item.producao)) {
          alert("Valores de Utilização e Produção não podem ser ZERO ou Vazio!");
          erro = true;
          return false;
        }

        if (item.deposito == "") {
          alert("Defina o depósito de destino!");
          erro = true;
          return false;
        }
      });

      if ($("#regra_distribuicao").val() == "" || $("#projeto").val() == "") {
        alert("Informe o projeto e regra de distribuição!");
        return false;
      }

      if (_pPerda) {
        if (pPerdas >= _pPerda.porcentagem_base) {
          if ($('#justificativaSelect').val() === 'outra' && $('#justificativa').val().length < 1) {
            alert('Informe uma justificativa');
            return false;
          }
          if ($('#justificativaSelect').val() === '') {
            alert('Informe uma justificativa');
            return false;
          }
        }
      }

      if (erro === true) {
        return false;
      }

      if (parseFloat(_item.Quantity).toFixed(3) === totalUtilizado.toFixed(3)) {
        return true;
      } else if (parseFloat(_item.Quantity) > parseFloat(totalUtilizado.toFixed(3))) {
        alert("Deve utilizar tudo!");
        return false;
      } else if (parseFloat(_item.Quantity) < parseFloat(totalUtilizado.toFixed(3))) {
        alert("A soma de utilização está maior que a quantidade comprada!");
        return false;
      }
      return true;

    }

    function calcularCusto(linha) {
      var prod = parseFloat($("#producao-" + linha).val());
      var precoUnitario = parseFloat($("#precoUnitario-" + linha).val());
      $("#custo-" + linha).html((precoUnitario * prod).toFixed(4));
    }

    function calcularCustoPrimario(linha) {
      var pesoUnt = parseFloat($("#utilizacao-" + linha).val()) / parseFloat($("#producao-" + linha).val());
      // pesoUnt * valorKgLimpo
      var custo = pesoUnt * valorKgLimpo();
      $("#precoUnitario-" + linha).val(custo.toFixed(4));
      calcularCusto(linha);
    }

    function calcularCustoAA(linha) {
      // (peso * kgSujo) * 0,2
      var custo = (parseFloat($("#utilizacao-" + linha).val()) * parseFloat($("#valorKgSujo").val())) * 0.2;
      $("#precoUnitario-" + linha).val(custo.toFixed(4));
      //calcularCusto(linha); //

      $("#custo-" + linha).html(custo.toFixed(4));
    }

    function calcularCustoSecundario(linha) {
      //var custo = parseFloat($("#utilizacao-"+linha).val()) * parseFloat($("#valorKgSujo").val());
      //$("#precoUnitario-"+linha).val(custo.toFixed(4));
      calcularCusto(linha);
    }

    function valorKgLimpo() {
      // varlorTotalDaNota - (Custo total de aparas aproveitaveis) / (total utilizado em primario)
      var valor = (valorTotalNF() - custoTotalAparasAproveitaveis()) / totalUtilizadoPrimario();
      $("#valorKgLimpo").val(valor.toFixed(4));
      return valor;
    }

    function valorTotalNF() {
      return parseFloat($("#valorKgSujo").val()) * parseFloat(_item.Quantity);
    }

    function totalUtilizadoPrimario() {
      var itens = $(".itensPrincipais");
      var total = 0;
      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Principal') {
          total += parseFloat($("#utilizacao-" + $(this).data('linha')).val());
        }
      });
      return total;
    }

    function totalUtilizadoSecundario() {
      var itens = $(".itensPrincipais");
      var total = 0;
      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Secundário') {
          total += parseFloat($("#utilizacao-" + $(this).data('linha')).val());
        }
      });
      return total;
    }

    function custoTotalAparasAproveitaveis() {
      var itens = $(".itensPrincipais");
      var total = 0;
      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Aproveitáveis') {
          total += parseFloat($("#custo-" + $(this).data('linha')).html());
        }
      });
      return total;
    }

    function atualizarTudo() {
      var itens = $(".itensPrincipais");
      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Aproveitáveis') {
          calcularCustoAA($(this).data('linha'));
        }
      });

      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Principal') {
          calcularCustoPrimario($(this).data('linha'));
        }
      });

      itens.each(function(index, item) {
        if ($(this).data('tipo') == 'Secundário') {
          calcularCustoSecundario($(this).data('linha'));
        }
      });

      // Total utilizado
      var totalUtilizado = 0;
      itens.each(function(index, item) {
        totalUtilizado += parseFloat($("#utilizacao-" + $(this).data('linha')).val());
      });
      $("#utilizado").html(totalUtilizado.toFixed(3));

      $("#infoPeso").html(totalUtilizadoPrimario().toFixed(3) + " / " + totalUtilizadoSecundario().toFixed(3));
      pPerdas = ((totalUtilizadoSecundario() / _item.Quantity) * 100).toFixed(2);
      var pUso = (100 - pPerdas).toFixed(2);
      $("#infoPercentual").html(pPerdas + " / " + pUso);
      checkPerda();
    }

    function changeJustificativa() {
      if ($('#justificativaSelect').val() === 'outra') {
        $('#justificativa').prop('required', true);
        $('.justificativa').show();
      } else {
        $('#justificativa').prop('required', false);
        $('.justificativa').hide();
      }
    }

    function checkPerda() {
      if (_pPerda) {
        if (pPerdas >= _pPerda.porcentagem_base) {
          $('.justificativaSelect').show();
          $('#justificativaSelect').prop('required', true);
          if (pPerdas >= _pPerda.porcentagem_aceita) {
            $('#perdasTitle').css('backgroundColor', '#ff2e17').html('Perdas - Este porcionamento requer autorização!');
          } else {
            $('#perdasTitle').css('backgroundColor', '#ffff00').html(
              'Perdas - Este porcionamento requer uma justificativa!');
          }
        } else {
          $('.justificativaSelect').hide();
          $('#justificativaSelect').prop('required', false);
          $('#perdasTitle').css('backgroundColor', '#ffffff').html('Perdas');
        }
      }
    }
  </script>
@endsection
