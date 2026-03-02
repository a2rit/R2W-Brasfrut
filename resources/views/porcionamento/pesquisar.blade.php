@extends('layouts.main')
@section('title', 'Porcionamento')

@section('content')
  <div class="col-12">
    <h3 class="header-page">Porcionamento - Pesquisa de Nota Fiscal</h3>
  </div>
  <hr>

  <div class="col-12">
    <form action="{{ route('porcionamento.pesquisar') }}" method="GET" id="needs-validation" enctype="multipart/form-data">
      <div class="accordion" id="filterAccordion">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-coreui-toggle="collapse"
              data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Filtros
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
            data-coreui-parent="#filterAccordion">
            <div class="accordion-body">
              <div class="row">
                <div class="col-md-5">
                  <label>Parceiro</Label>
                  <select id="cardCode" class="form-control selectpicker with-ajax-partner" data-container="body"
                    name="cardCode">
                    @if (!empty(old('cardCode')))
                      <option value="{{ old('cardCode') }}" selected>{{ getPartnerName(old('cardCode')) }}</option>
                    @endif
                  </select>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Inicial</label>
                    <input type="date" name="data_fist" value="{{ old('data_fist') }}"
                      placeholder="{{ DATE('d/m/Y') }}" class="form-control datepicker" placeholder="Inicial"
                      autocomplete="off">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Data Final</label>
                    <input type="date" name="data_last" value="{{ old('data_last') }}"
                      placeholder="{{ DATE('d/m/Y') }}" class="form-control datepicker" placeholder="Final"
                      autocomplete="off">
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="col-12">
                  <button class="btn btn-primary btn-sm float-end ms-1" type="submit">Filtrar</button>
                  <button class="btn btn-warning btn-sm float-end" onclick="clearForm(event)" type="button">Limpar
                    formulário</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="table-responsive mt-5">
    {{ $notas->links('pagination::bootstrap-4') }}
    <table class="table table-default table-bordered table-responsive" id="tabelaNotas">
      <thead>
        <tr>
          <th style="width: 10%;">Cod SAP</th>
          <th style="width: 10%;">Data Emissão</th>
          <th style="width: 10%;">Número da Nota</th>
          <th>Fornecedor</th>
          <th style="width: 10%;">Opções</th>
        </tr>
      </thead>
      <tbody id="bodyTabelaNotas">
        @foreach ($notas as $nota)
          <tr>
            <td>{{ $nota->DocNum }}</td>
            <td>{{ formatDate($nota->TaxDate) }}</td>
            <td>{{ $nota->Serial }}</td>
            <td>{{ $nota->CardName }}</td>
            <td><button class='btn btn-primary btn-sm w-100' onclick='verItens("{{ $nota->DocNum }}")'>Ver Itens</button>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $notas->links('pagination::bootstrap-4') }}
  </div>

  <div class="modal" id="itensModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Itens</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-default table-striped table-bordered table-hover" id="tabelaItens"
              style="display: none">
              <tr>
                <th>Cod Sap</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Valor</th>
                <th>Deposito</th>
                <th>Porcionar</th>
              </tr>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-partner')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    function pesquisarNotas() {

      $.ajax("{{ route('porcionamento.getNotas') }}", {
        method: "POST",
        data: {
          codigoFornecedor: $("#codigoFornecedor").val(),
          _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
          $("#tabelaNotas").hide();
          $("#tabelaItens").hide();
          $(".notas").remove();
          $(".itensNota").remove();
          waitingDialog.show('Carregando...');
        },
        complete: function(data) {
          $.each(data.responseJSON, function(index, item) {
            tr = $("<tr class='notas'>")
              .append($("<td>" + item.DocNum + "</td>"))
              .append($("<td>" + item.TaxDate + "</td>"))
              .append($("<td>" + item.Serial + "</td>"))
              .append($("<td>" + item.CardName + "</td>"))
              .append($("<td><button class='btn btn-primary btn-sm w-100' onclick='verItens(" + item.DocNum +
                ")'>Ver Itens</button></td>"));
            $("#bodyTabelaNotas").append(tr).show();
          });
        }
      }).done(function() {
        waitingDialog.hide();
      });
    }

    function verItens(docNum) {
      $.ajax("{{ route('porcionamento.getItensNF') }}", {
        method: "POST",
        data: {
          docNum: docNum,
          _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
          $("#tabelaItens").hide();
          $(".itensNota").remove();
          waitingDialog.show('Carregando...');
        },
        complete: function(data) {
          $.each(data.responseJSON, function(index, item) {
            tr = $("<tr class='itensNota'>")
              .append($("<td>" + item.ItemCode + "</td>"))
              .append($("<td>" + item.Dscription + "</td>"))
              .append($("<td>" + parseFloat(item.Quantity).toFixed(3) + " " + item.unitMsr + "</td>"))
              .append($("<td> R$ " + parseFloat(item.Price).toFixed(2) + "</td>"))
              .append($("<td>" + item.WhsCode + "</td>"));
            //.append($("<td><button onclick='porcionar("+ item.LineNum+","+item.DocEntry+")'>Porcionar</button></td>"));

            if (item.porcionado === false) {
              tr.append($("<td><button class='btn btn-primary btn-sm w-100' onclick='porcionar(" + item
                .LineNum + "," + item.DocEntry +
                ")'>Porcionar</button></td>"));
            } else {
              tr.append($("<td><a href='{{ route('porcionamento.ver') }}/" + item.porcionamentoId +
                "'><button class='btn btn-primary btn-sm w-100'>Ver Porcionamento</button></td>"));
            }

            $("#tabelaItens").append(tr).show();
            window.scrollTo(0, document.body.scrollHeight);
          });
        }
      }).done(function() {
        waitingDialog.hide();
        $('#itensModal').modal('show');
      });
    }

    function porcionar(lineNum, docEntry) {
      window.location.href = "{{ route('porcionamento.criar') }}/" + docEntry + "/" + lineNum;
    }

    // $('#codigoFornecedor').autocomplete({
    //   serviceUrl: '{{ route('porcionamento.getFornecedor') }}',
    //   onSelect: function(suggestion) {
    //     if(suggestion.data){
    //       $('#codigoFornecedor').val(suggestion.data);
    //       $('#searchButton').trigger('click')
    //     }
    //   }
    // });
  </script>
@endsection
