@extends('layouts.main')
@section('title', 'Ponto de venda')

@section('content')

  <div class="col-12">
    <h3 class="header-page">Cadastro PV</h3>
  </div>

  <!-- /.row -->
  <form role="form" method="post" id="cadastro" action="{{ route('pv.cadastroPost') }}" class="mt-4">
    {!! csrf_field() !!}
    <div class="row">
      <div class="accordion" id="accordionExample">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseOne" aria-expanded="true"
              aria-controls="collapseOne">
              Configurações Gerais
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
            data-coreui-parent="#accordionExample">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Nome do PV</label>
                  <input type="text" name="nome" class="form-control" required id="nome">
                  <small id="emailHelp" class="form-text text-muted">Nome do Ponto de Venda.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Vendedor Padrão</label>
                  <select name="vendedor" class="form-control selectpicker" required id="vendedor">
                    <option></option>
                    @foreach ($parametros['vendedores'] as $vendedor)
                      <option value="{{ $vendedor['valor'] }}">{{ $vendedor['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Vendedor padrão para NFC-e de saída.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Cliente Padrão</label>
                  <select name="cliente" class="form-control selectpicker with-ajax-customer" required id="cliente">
                    <option></option>
                    @if (!empty($pv))
                      <option value="{{ $pv->cliente }}">{{ getPartnerName($pv->cliente) }}</option>
                    @endif
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Cliente padrão para NFC-e de Saída.</small>
                </div>


                <div class="form-group col-md-3">
                  <label>Modelo de Nota Fiscal</label>
                  <select name="modelo_nf" class="form-control selectpicker" required id="modelo_nf">
                    <option></option>
                    @foreach ($parametros['modelosNf'] as $modeloNf)
                      <option value="{{ $modeloNf['valor'] }}" title="{{ $modeloNf['titulo'] }}">{{ $modeloNf['nome'] }}
                      </option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Modelo padrão para NFC-e de Saída.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Série da NFC-e</label>
                  <input class="form-control" type="number" min="1" max="999" required name="serie"
                    id="serie">
                  <small id="emailHelp" class="form-text text-muted">Série da NFC-e.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Pasta de XML's</label>
                  <input type="text" name="pasta_xml" class="form-control" required id="pasta_xml">
                  <small id="emailHelp" class="form-text text-muted">Caminho completo para pasta de XML's.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Pasta de XML's em Contigência</label>
                  <input type="text" name="pasta_xml_contingencia" class="form-control" required
                    id="pasta_xml_contingencia">
                  <small id="emailHelp" class="form-text text-muted">Pasta de XML's em contingência.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Regra de Distribuição NFC-e</label>
                  <select name="regra_distribuicao" class="form-control selectpicker" id="regra_distribuicao">
                    <option></option>
                    @foreach ($parametros['regrasDistribuicao'] as $regrasDistribuicao)
                      <option value="{{ $regrasDistribuicao['valor'] }}">{{ $regrasDistribuicao['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Regra de Distribuição para NFC-e de Saída.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Regra de Distribuição Outros Valores</label>
                  <select name="regra_distribuicao_ov" class="form-control selectpicker"
                    id="regra_distribuicao_ov">
                    <option></option>
                    @foreach ($parametros['regrasDistribuicao'] as $regrasDistribuicao)
                      <option value="{{ $regrasDistribuicao['valor'] }}">{{ $regrasDistribuicao['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Regra de Distribuição Outros Valores.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Projeto Outros Valores</label>
                  <select name="projeto_ov" class="form-control selectpicker" id="projeto_ov">
                    <option></option>
                    @foreach ($parametros['projetos'] as $projeto)
                      <option value="{{ $projeto['valor'] }}">{{ $projeto['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Projeto Outros Valores.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Projeto NFC-e</label>
                  <select name="projeto" class="form-control selectpicker" id="projeto">
                    <option></option>
                    @foreach ($parametros['projetos'] as $projeto)
                      <option value="{{ $projeto['valor'] }}">{{ $projeto['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Projeto para NFC-e de Saída.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Depósito Padrão Saída NFC-e</label>
                  <select name="deposito" class="form-control selectpicker" required id="deposito">
                    <option></option>
                    @foreach ($parametros['depositos'] as $deposito)
                      <option value="{{ $deposito['valor'] }}">{{ $deposito['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Depósito Padrão de saída NFC-e.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Depósito de Serviços</label>
                  <select name="deposito_servico" class="form-control selectpicker" required id="deposito_servico">
                    <option></option>
                    @foreach ($parametros['depositos'] as $deposito)
                      <option value="{{ $deposito['valor'] }}">{{ $deposito['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Depósito de serviços.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Grupo de Serviços</label>
                  <select name="grupo_servico" class="form-control selectpicker" required id="grupo_servico">
                    <option></option>
                    @foreach ($parametros['grupos_item'] as $grupo)
                      <option value="{{ $grupo['valor'] }}">{{ $grupo['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Depósito de serviços.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Utilização NFC-e</label>
                  <select name="utilizacao" class="form-control selectpicker" required id="utilizacao">
                    <option></option>
                    @foreach ($parametros['utilizacoes'] as $utilizacao)
                      <option value="{{ $utilizacao['valor'] }}">{{ $utilizacao['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Utilização para NFC-e de Saída.</small>
                </div>

                <div class="form-group col-md-3">
                  <label>Outros Valores NFC-e</label>
                  <select name="codigo_ov" class="form-control selectpicker" id="codigo_ov">
                    <option></option>
                    @foreach ($parametros['codigoOV'] as $projeto)
                      <option value="{{ $projeto['valor'] }}">{{ $projeto['nome'] }}</option>
                    @endforeach
                  </select>
                  <small id="emailHelp" class="form-text text-muted">Outros valores do item de NFC-e.</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            Cartões de Crédito
          </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">

              @if (isset($pv))
                <div class="form-group col-md-3">
                  <label>NomeColibri</label>
                  <input class="form-control" type="text" id="chave_colibri">
                </div>
                <div class="form-group col-md-3">
                  <label>Forma:</label>
                  <select class="form-control selectpicker" id="valor">
                    <option></option>
                    @foreach ($parametros['formasPagamento'] as $formaPagamento)
                      <option
                        value="{{ json_encode([
                            'CrTypeCode' => $formaPagamento['CrTypeCode'],
                            'CreditCard' => $formaPagamento['CreditCard'],
                            'nome' => $formaPagamento['nome'],
                        ]) }}">
                        {{ $formaPagamento['nome'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <br>
                  <button type="button" class="btn btn-success" onclick="adicionarFormaPag();">Adicionar
                  </button>
                </div>
                <div class="table-responsive mt-4">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th style="width: 5%;">Id</th>
                        <th style="width: 40%;">Nome Colibri</th>
                        <th style="width: 45%;">Nome Sap</th>
                        <th style="width: 10%;">Remover</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($pv->formasPagamento as $formaPagamento)
                        <tr>
                          <td>{{ $formaPagamento->id }}</td>
                          <td>{{ $formaPagamento->chave_colibri }}</td>
                          <td>{{ $formaPagamento->nome }}</td>
                          <td><a href="{{ route('pv.excluirFormaPag', ['id' => $formaPagamento->id]) }}">
                              <button type="button" class="btn btn-danger w-100">Excluir</button>
                            </a></td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <h4>Salve o Ponto de Venda antes de cadastrar os cartões</h4>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseThree"
            aria-expanded="true" aria-controls="collapseThree">
            Configurações Financeiras
          </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">

              <div class="form-group col-md-3">
                <label>Conta Contábil Dinheiro</label>
                <select name="conta_dinheiro" class="form-control selectpicker" required id="conta_dinheiro">
                  <option></option>
                  @foreach ($parametros['contasContabeis'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Conta Contábil Dinheiro.</small>
              </div>

              <div class="form-group col-md-3">
                <label>Conta Contábil Troco</label>
                <select name="conta_troco" class="form-control selectpicker" required id="conta_troco">
                  <option></option>
                  @foreach ($parametros['contasContabeis'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Troco deixado pelo cliente.</small>
              </div>

              <div class="form-group col-md-3">
                <label>Conta Contábil Cheque</label>
                <select name="conta_cheque" class="form-control selectpicker" required id="conta_cheque">
                  <option></option>
                  @foreach ($parametros['contasContabeisCheque'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Conta de cheque.</small>
              </div>

              <div class="form-group col-md-3">
                <label>Conta Contábil PIX</label>
                <select name="conta_pix" class="form-control selectpicker" required id="conta_pix">
                  <option></option>
                  @foreach ($parametros['contasContabeis'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Conta de PIX.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseFour"
            aria-expanded="true" aria-controls="collapseFour">
            Configurações de Imposto
          </button>
        </h2>
        <div id="collapseFour" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label>Código de Imposto NFC-e</label>
                <select name="codigo_imposto" class="form-control selectpicker" id="codigo_imposto">
                  <option></option>
                  @foreach ($parametros['codigosImposto'] as $codigosImposto)
                    <option value="{{ $codigosImposto['valor'] }}">{{ $codigosImposto['valor'] }}
                      - {{ $codigosImposto['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Código de Imposto para NFC-e de Saída.</small>
              </div>

              <div class="form-group col-md-3">
                <label>Código de Imposto Outros Valores</label>
                <select name="codigo_imposto_ov" class="form-control selectpicker" id="codigo_imposto_ov">
                  <option></option>
                  @foreach ($parametros['codigosImposto'] as $codigosImposto)
                    <option value="{{ $codigosImposto['valor'] }}">{{ $codigosImposto['valor'] }}
                      - {{ $codigosImposto['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Código de Imposto Outros Valores.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseFive"
            aria-expanded="true" aria-controls="collapseFive">
            Configurações de Gorjeta
          </button>
        </h2>
        <div id="collapseFive" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">

              <div class="form-group col-md-3">
                <label>Código do item Colibri</label>
                <select name="item_gorjeta_colibri" class="form-control selectpicker" id="item_gorjeta_colibri"
                  required>
                  <option></option>
                  @foreach ($parametros['items_gorjeta'] as $item)
                    <option value="{{ $item['value'] }}">{{ $item['value'] }}
                      - {{ $item['name'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Código do item no Colibri</small>
              </div>

              <div class="form-group col-md-3">
                <label>Código do item SAP</label>
                <select name="item_gorjeta_sap" class="form-control selectpicker" id="item_gorjeta_sap" required>
                  <option></option>
                  @foreach ($parametros['items_gorjeta'] as $item)
                    <option value="{{ $item['value'] }}">{{ $item['value'] }}
                      - {{ $item['name'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Código correspondente no SAP</small>
              </div>

              <div class="form-group col-md-3">
                <label>Conta Contábil Crédito</label>
                <select name="conta_gorjeta_credito" class="form-control selectpicker" required
                  id="conta_gorjeta_credito">
                  <option></option>
                  @foreach ($parametros['contas_gorjeta'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted"></small>
              </div>

              <div class="form-group col-md-3">
                <label>Conta Contábil Débito</label>
                <select name="conta_gorjeta_debito" class="form-control selectpicker" required
                  id="conta_gorjeta_debito">
                  <option></option>
                  @foreach ($parametros['contas_gorjeta'] as $contaContabil)
                    <option value="{{ $contaContabil['valor'] }}">{{ $contaContabil['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted"></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseSix"
            aria-expanded="true" aria-controls="collapseSix">
            Configurações de impressão do consumo interno
          </button>
        </h2>
        <div id="collapseSix" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label>IP da impressora</label>
                <input name="intern_consumption[printer_ip]" class="form-control" id="printer_ip" />
                <small id="emailHelp" class="form-text text-muted">Informe o IP, ex: 192.168.0.1</small>
              </div>
              <div class="form-group col-md-3">
                <label>Porta da impressora</label>
                <input type="number" name="intern_consumption[printer_port]" class="form-control"
                  id="printer_port" />
                <small id="emailHelp" class="form-text text-muted">Informe o número da porta, ex: 9100</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- <div class="panel panel-warning">
                    <div class="panel-heading"><h3 class="panel-title">Consumo Interno</h3></div>
                    <div class="panel-body">
                        <div class="form-group col-md-3">
                            <label for="ci-projeto-">Projeto</label>
                            <select name="ci[projeto]" class="form-control" id="ci-projeto">
                                <option></option>
                                @foreach ($parametros['projetos'] as $projeto)
                                    <option value="{{$projeto['valor']}}">{{$projeto['nome']}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Projeto do Consumo Interno.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ci[utilizacao]">Utilização</label>
                            <select name="ci[utilizacao]" class="form-control" required id="ci-utilizacao">
                                <option></option>
                                @foreach ($parametros['utilizacoes'] as $utilizacao)
                                    <option value="{{$utilizacao['valor']}}">{{$utilizacao['nome']}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Utilização para Consumo Interno.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ci[usuarios]">Usuário Autorizados</label>
                            <select name="ci[usuarios][]" class="form-control selectpicker" required id="ci-usuarios" multiple data-actions-box="true">
                                @foreach (\App\User::all() as $user)
                                    <option value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Usuário Autorizados.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ci-deposito">Depósito de Consumo Interno</label>
                            <select name="ci[deposito]" class="form-control" required id="ci-deposito">
                                <option></option>
                                @foreach ($parametros['depositos'] as $deposito)
                                    <option value="{{$deposito["valor"]}}">{{$deposito["nome"]}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Depósito de Consumo Interno.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Regra de Distribuição</label>
                            <select name="ci[regra_distribuicao]" class="form-control" required id="ci-regra_distribuicao">
                                <option></option>
                                @foreach ($parametros['regrasDistribuicao'] as $regrasDistribuicao)
                                    <option value="{{$regrasDistribuicao['valor']}}">{{$regrasDistribuicao['nome']}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Regra de Distribuição.</small>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Cliente Padrão</label>
                            <select name="ci[cliente]" class="form-control" required id="ci-cliente">
                                <option></option>
                                @foreach ($parametros['clientes'] as $cliente)
                                    <option value="{{$cliente['valor']}}">{{$cliente['nome']}}</option>
                                @endforeach
                            </select>
                            <small id="emailHelp" class="form-text text-muted">Cliente padrão.</small>
                        </div>
                    </div>
                </div> --}}

    <div class="accordion mt-4" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseFive"
            aria-expanded="true" aria-controls="collapseFive">
            Consumo Interno
          </button>
        </h2>
        <div id="collapseFive" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="row">

              <div class="form-group col-md-3">
                <label for="intern_consumption[utilization]">Utilização</label>
                <select name="intern_consumption[utilization]" class="form-control selectpicker" required
                  id="intern_consumption-utilization">
                  <option></option>
                  @foreach ($parametros['utilizacoes'] as $utilizacao)
                    <option value="{{ $utilizacao['valor'] }}">{{ $utilizacao['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Utilização para Consumo Interno.</small>
              </div>
              <div class="form-group col-md-3">
                <label for="intern_consumption[deposit]">Depósito de destino do Consumo Interno</label>
                <select name="intern_consumption[deposit]" class="form-control selectpicker" required
                  id="intern_consumption-deposit">
                  <option></option>
                  @foreach ($parametros['depositos'] as $deposito)
                    <option value="{{ $deposito['valor'] }}">{{ $deposito['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Depósito de destino do Consumo Interno.</small>
              </div>
              <div class="form-group col-md-3">
                <label>Cliente Padrão</label>
                <select name="intern_consumption[card_code]" class="form-control selectpicker with-ajax-customer"
                  required id="intern_consumption-card_code">
                  <option></option>
                  @if (!empty($pv))
                    <option value="{{@$pv->ci_config["card_code"]}}">{{ getPartnerName(@$pv->ci_config["card_code"]) }}</option>
                  @endif
                </select>
                <small id="emailHelp" class="form-text text-muted">Cliente padrão.</small>
              </div>
              <div class="form-group col-md-3">
                <label>Código de Imposto</label>
                <select required name="intern_consumption[tax_code]" class="form-control selectpicker"
                  id="intern_consumption-tax_code">
                  <option></option>
                  @foreach ($parametros['codigosImposto'] as $codigosImposto)
                    <option value="{{ $codigosImposto['valor'] }}">{{ $codigosImposto['valor'] }}
                      - {{ $codigosImposto['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Código de Imposto.</small>
              </div>
              <div class="form-group col-md-3">
                <label>Lista de preço</label>
                <select required name="intern_consumption[price_list]" class="form-control selectpicker"
                  id="intern_consumption-price_list">
                  <option></option>
                  @foreach ($parametros['prices_list'] as $priceList)
                    <option value="{{ $priceList['value'] }}">{{ $priceList['value'] }}
                      - {{ $priceList['name'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Lista de preço.</small>
              </div>

              <div class="form-group col-md-3">
                <label>Vendedor padrão</label>
                <select required name="intern_consumption[seller_code]" class="form-control selectpicker"
                  id="intern_consumption-seller_code">
                  <option></option>
                  @foreach ($parametros['vendedores'] as $seller)
                    <option value="{{ $seller['valor'] }}">{{ $seller['nome'] }}</option>
                  @endforeach
                </select>
                <small id="emailHelp" class="form-text text-muted">Vendedor padrão.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-success float-end mt-5">Salvar</button>
  </form>

@endsection

@section('scripts')
  <script>
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    selectpicker.filter('.with-ajax-customer')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('partners.get.all') }}"));

  </script>
@endsection()
