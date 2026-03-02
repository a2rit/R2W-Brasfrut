@extends('layouts.main')
@section('title', 'Usuários')

@section('content')
  <div class="container-fluid">
    <form class="d-flex-col justify-content-center align-items-center" role="form" method="POST"
      action="{{ url('/register') }}">
      {{ csrf_field() }}
      <div class="container-fluid w-50">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title text-center mt-2">Cadastro de usuário</h5>
          </div>
          <div class="card-body">
            <div class="row mb-3{{ $errors->has('name') ? ' has-error' : '' }}">
              <label for="name" class="col-sm-3 col-form-label">Nome</label>
              <div class="col-sm-9">
                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}"
                  required autofocus>
                @if ($errors->has('name'))
                  <span class="help-block">
                    <strong>{{ $errors->first('name') }}</strong>
                  </span>
                @endif
              </div>
            </div>

            <div class="row mb-3{{ $errors->has('email') ? ' has-error' : '' }}">
              <label for="email" class="col-sm-3 col-form-label">E-Mail</label>
              <div class="col-sm-9">
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"
                  required>
                @if ($errors->has('email'))
                  <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                  </span>
                @endif
              </div>
            </div>

            <div class="row mb-3{{ $errors->has('ativo') ? ' has-error' : '' }}">
              <label for="ativo" class="col-sm-3 col-form-label">Ativo:</label>
              <div class="col-sm-9">
                <select id="ativo" class="form-control selectpicker" name="ativo" required>
                  <option></option>
                  <option value="1" selected>SIM</option>
                  <option value="0">Não</option>
                </select>
                @if ($errors->has('ativo'))
                  <span class="help-block">
                    <strong>{{ $errors->first('ativo') }}</strong>
                  </span>
                @endif
              </div>
            </div>

            <div class="row mb-3{{ $errors->has('group_id') ? ' has-error' : '' }}">
              <label for="ativo" class="col-sm-3 col-form-label">Grupo:</label>
              <div class="col-sm-9">
                <select id="group_id" class="form-control selectpicker" name="group_id">
                  <option></option>
                  @foreach ($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                  @endforeach
                </select>
                @if ($errors->has('group_id'))
                  <span class="help-block">
                    <strong>{{ $errors->first('group_id') }}</strong>
                  </span>
                @endif
              </div>
            </div>
            <div class="row mb-3{{ $errors->has('password') ? ' has-error' : '' }}">
              <label for="password" class="col-sm-3 col-form-label">Senha</label>
              <div class="col-sm-9">
                <input id="password" type="password" class="form-control" name="password" required>
                @if ($errors->has('password'))
                  <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                  </span>
                @endif
              </div>
            </div>
            <div class="row mb-3">
              <label for="password-confirm" class="col-sm-3 col-form-label">Confirme a senha</label>
              <div class="col-sm-9">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br>

      <!-- Nivel de acesso -->
      <div class="d-flex-col justify-content-center align-items-center mt-2">
        <div class="accordion container-fluid w-50" id="filterAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse"
                data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                Nivel de acesso
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
              data-coreui-parent="#filterAccordion">
              <div class="accordion-body">
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button" data-coreui-toggle="dropdown"
                      aria-expanded="false">
                      Configurações
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="configuration" name="configuration" onclick="setConfiguration()">
                            <label class="form-check-label ms-1 me-4 align-middle" for="dropdownCheck2">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_boot" name="config_boot">
                            <label class="form-check-label ms-1 me-4 align-middle" for="dropdownCheck2">
                              Inicialização
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_sale_point" name="config_sale_point">
                            <label for="config_sale_point" class="form-check-label ms-1 me-4 align-middle">
                              Ponto de venda
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_users" name="config_users">
                            <label for="config_users" class="form-check-label ms-1 me-4 align-middle">
                              Usuários
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_approvers" name="config_approvers">
                            <label for="config_approvers" class="form-check-label ms-1 me-4 align-middle">
                              Aprovadores
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_users_group" name="config_users_group">
                            <label for="config_users_group" class="form-check-label ms-1 me-4 align-middle">
                              Grupos de usuário
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="config_whs_group" name="config_whs_group">
                            <label for="config_whs_group" class="form-check-label ms-1 me-4 align-middle">
                              Grupos de depósito
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Erros de sincronização
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="erros" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;" name="erros" onclick="setErros()">
                            <label for="erros" class="form-check-label ms-1 me-4 align-middle">
                              Visualizar
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Nfcs em contingência
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="nfcs" name="nfcs" onclick="setNfcs()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="nfcs" class="form-check-label ms-1 me-4 align-middle">
                              Visualizar
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Porcionamento
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portioning" name="portioning" onclick="setPortioning()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="portioning" class="form-check-label ms-1 me-4 align-middle">
                              Visualizar
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portion_search" name="portion_search"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="portion_search" class="form-check-label ms-1 me-4 align-middle">
                              Pesquisar nota
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portion_list" name="portion_list" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="portion_list" class="form-check-label ms-1 me-4 align-middle">
                              Listar
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portion_loss" name="portion_loss" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="portion_loss" class="form-check-label ms-1 me-4 align-middle">
                              Porcentagens de perda
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portion_justify" name="portion_justify"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="portion_justify" class="form-check-label ms-1 me-4 align-middle">
                              Justificativa
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="portion_loss_justify" name="portion_loss_justify"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="portion_loss_justify"
                              class="form-check-label ms-1 me-4 align-middle text-break">
                              Porcionamento c/ justificativa e/ou alta perda
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Consumo Interno
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="intern_consumption" name="intern_consumption" class="i-checks"
                              onclick="setInternConsumption()" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="intern_consumption" class="form-check-label ms-1 me-4 align-middle">
                              Acesso
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="intern_consumption_perdas" name="intern_consumption_perdas" class="i-checks" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;"
                              @if (isset($usuario) && $usuario->permissions) @if (!empty(json_decode($usuario->permissions)->intern_consumption_perdas)) checked @endif
                              @endif>
                            <label for="intern_consumption_perdas" class="form-check-label ms-1 me-4 align-middle">
                              Autorizar perdas
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="intern_consumption_eventos" name="intern_consumption_eventos" class="i-checks" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;"
                              @if (isset($usuario) && $usuario->permissions) @if (!empty(json_decode($usuario->permissions)->intern_consumption_eventos)) checked @endif
                              @endif>
                            <label for="intern_consumption_eventos" class="form-check-label ms-1 me-4 align-middle">
                              Autorizar eventos
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Estoque
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventoryx" name="inventoryx" onclick="setInventoryx()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventoryx" class="form-check-label ms-1 me-4 align-middle">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_request" name="inventory_request"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_request" class="form-check-label ms-1 me-4 align-middle">
                              Requisição interna
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_input" name="inventory_input"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_input" class="form-check-label ms-1 me-4 align-middle">
                              Entrada de Mercadoria
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_output" name="inventory_output"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_output" class="form-check-label ms-1 me-4 align-middle">
                              Saida de Mercadoria
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_transfer_taking" name="inventory_transfer_taking"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_transfer_taking" class="form-check-label ms-1 me-4 align-middle">
                              Pedido de transferência de estoque
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_transfer" name="inventory_transfer"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_transfer" class="form-check-label ms-1 me-4 align-middle">
                              Transferência de estoque
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="inventory_stock_loan" name="inventory_stock_loan"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="inventory_stock_loan" class="form-check-label ms-1 me-4 align-middle">
                              Empréstimo de estoque
                            </label>
                          </div>
                        </div>
                      <li>
                        <h6 class="dropdown-header fw-bold">Cadastro de itens</h6>
                      </li>
                      <div class="mb-3">
                        <div class="form-check">
                          <input type="checkbox" id="inventory_items" name="inventory_items"
                            class="form-check-input ms-1" style="width: 20px; height: 20px;">
                          <label for="inventory_items" class="form-check-label ms-1 me-4 align-middle">
                            Acesso ao menu lateral
                          </label>
                        </div>
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input type="checkbox" id="inventory_items_new" name="inventory_items_new"
                            class="form-check-input ms-1" style="width: 20px; height: 20px;">
                          <label for="inventory_items_new" class="form-check-label ms-1 me-4 align-middle">
                            Cadastrar novo item
                          </label>
                        </div>
                      </div>
                      <div class="mb-3">
                        <div class="form-check">
                          <input type="checkbox" id="inventory_items_edit" name="inventory_items_edit"
                            class="form-check-input ms-1" style="width: 20px; height: 20px;">
                          <label for="inventory_items_edit" class="form-check-label ms-1 me-4 align-middle">
                            Editar cadastro
                          </label>
                        </div>
                      </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Contabilidade
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="accounting" name="accounting" onclick="setAccounting()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="accounting" class="form-check-label ms-1 me-4 align-middle">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="account_lcm" name="account_lcm" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="account_lcm" class="form-check-label ms-1 me-4 align-middles">
                              Lançamento contábil manual
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Parceiros de negócios
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="b_partners" onclick="setB_partners()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="b_partners" class="form-check-label ms-1 me-4 align-middle">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="b_partner" name="b_partner" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="b_partner" class="form-check-label ms-1 me-4 align-middles">
                              Parceiros de negócios
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button"
                      data-coreui-toggle="dropdown" aria-expanded="false">
                      Compras
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchasex" onclick="setPurchasex()"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="purchasex" class="form-check-label ms-1 me-4 align-middle">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_request" name="purchase_request"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;" @if (isset($usuario) && $usuario->permissions) @if (json_decode($usuario->permissions)->purchase_request) checked @endif
                              @endif>
                            <label for="purchase_request" class="form-check-label ms-1 me-4 align-middles">
                              Solicitação de compras
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_suggestion_request" name="purchase_suggestion_request"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;" @if (isset($usuario) && $usuario->permissions) @if (json_decode($usuario->permissions)->purchase_suggestion_request) checked @endif
                              @endif>
                            <label for="purchase_suggestion_request" class="form-check-label ms-1 me-4 align-middles">
                              Sugestão de Compras - S.C.
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_quotation" name="purchase_quotation"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;" @if (isset($usuario) && isset(json_decode($usuario->permissions)->purchase_quotation)) @if (json_decode($usuario->permissions)->purchase_quotation) checked @endif
                              @endif>
                            <label for="purchase_quotation" class="form-check-label ms-1 me-4 align-middles">
                              Cotação de compras
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_order" name="purchase_order"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;" @if (isset($usuario) && $usuario->permissions) @if (json_decode($usuario->permissions)->purchase_order) checked @endif
                              @endif>
                            <label for="purchase_order"class="form-check-label ms-1 me-4 align-middles">
                              Pedido de compras
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_suggestion_order" name="purchase_suggestion_order"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;" @if (isset($usuario) && $usuario->permissions) @if (json_decode($usuario->permissions)->purchase_suggestion_order) checked @endif
                              @endif>
                            <label for="purchase_suggestion_order" class="form-check-label ms-1 me-4 align-middles">
                              Sugestão de Compras - S.C.
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_nfc" name="purchase_nfc" class="form-check-input ms-1"
                              style="width: 20px; height: 20px;">
                            <label for="purchase_nfc" class="form-check-label ms-1 me-4 align-middles">
                              Nota fiscal de entrada
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_advance_provider" name="purchase_advance_provider"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="purchase_advance_provider" class="form-check-label ms-1 me-4 align-middles">
                              Adiantamento para fornecedor
                            </label>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" id="purchase_order_budget_relatory" name="purchase_order_budget_relatory"
                              class="form-check-input ms-1" style="width: 20px; height: 20px;">
                            <label for="purchase_order_budget_relatory" class="form-check-label ms-1 me-4 align-middles">
                              Permissão para imprimir espelho de pedido de compras - orçamento
                            </label>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button" data-coreui-toggle="dropdown"
                      aria-expanded="false">
                      TomTicket
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="tomticket" name="tomticket">
                            <label class="form-check-label ms-1 me-4 align-middle" for="tomticket">
                              Acesso ao TomTicket
                            </label>
                          </div>
                        </div>    
                      </li>
                    </ul>
                  </div>
                </div><!-- col-md-12 -->
                <div class="row">
                  <div class="dropdown col-6">
                    <a class="btn dropdown-toggle fw-bold " href="#" role="button" data-coreui-toggle="dropdown"
                      aria-expanded="false">
                      Dashboard
                    </a>
                    <ul class="dropdown-menu w-100">
                      <li>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="dashboard_menu" name="dashboard_menu">
                            <label class="form-check-label ms-1 me-4 align-middle" for="dashboard_menu">
                              Acesso ao menu lateral
                            </label>
                          </div>
                        </div>
                        <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="dashboard_purchase" name="dashboard_purchase">
                            <label class="form-check-label ms-1 me-4 align-middle" for="dashboard_purchase">
                              Compras
                            </label>
                          </div>
                        </div>
                        {{-- <div class="mb-3">
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input ms-1" style="width: 20px; height: 20px;"
                              id="dashboard_finances" name="dashboard_finances">
                            <label class="form-check-label ms-1 me-4 align-middle" for="dashboard_finances">
                              Financeiro
                            </label>
                          </div>
                        </div> --}}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row text-center">
        <button type="submit" class="btn btn-primary d-grid gap-2 col-2 mx-auto mt-3">Salvar</button>
      </div>
    </form>
  </div><!-- panel -->

  <!-- /Nivel de acesso -->


@endsection

@section('scripts')
  <script>
    $(".selectpicker").selectpicker(selectpickerConfig);
    $('#name').autocomplete({
      serviceUrl: "{{ route('usuarios.get') }}",
      onSelect: function(suggestion) {
        //alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
        $(this).val(suggestion.value);
        $("#email").val(suggestion.data);
      }
    });


    function setConfiguration() {
      var check = $('#configuration').prop("checked");
      $('#config_boot').attr("checked", check);
      $('#config_sale_point').attr("checked", check);
      $('#config_users').attr("checked", check);
      $('#config_approvers').attr("checked", check);
      $('#config_users_group').attr("checked", check);
      $('#config_whs_group').attr("checked", check);
    }

    function setErros() {
      var check = $('#erros').prop("checked");
      $('#erros').attr("checked", check);
    }

    function setNfcs() {
      var check = $('#nfcs').prop("checked");
      $('#nfcs').attr("checked", check);
    }

    function setPortioning() {
      var check = $('#portioning').prop("checked");
      $('#portion_search').attr("checked", check);
      $('#portion_list').attr("checked", check);
      $('#portion_loss').attr("checked", check);
      $('#portion_justify').attr("checked", check);
      $('#portion_loss_justify').attr("checked", check);
    }

    function setInternConsumption() {
      var check = $('#intern_consumption').prop("checked");
      $('#intern_consumption').attr("checked", check);
    }

    function setInventoryx() {
      var check = $('#inventoryx').prop("checked");
      $('#inventory_request').attr("checked", check);
      $('#inventory_input').attr("checked", check);
      $('#inventory_output').attr("checked", check);
      $('#inventory_transfer_taking').attr("checked", check);
      $('#inventory_transfer').attr("checked", check);
      $('#inventory_stock_loan').attr("checked", check);
    }

    function setAccounting() {
      var check = $('#accounting').prop("checked");
      $('#account_lcm').attr("checked", check);
    }

    function setB_partners() {
      var check = $('#b_partners').prop("checked");
      $('#b_partner').attr("checked", check);
    }

    function setPurchasex() {
      var check = $('#purchasex').prop("checked");
      $('#purchase_order').attr("checked", check);
      $('#purchase_nfc').attr("checked", check);
    }
  </script>
@endsection
