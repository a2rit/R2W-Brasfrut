@extends('layouts.main')

@section('title', 'Cadastro de Parceiro de Negócio')

@section('content')
  <form id="needs-validation" onsubmit="validateTabs()">
    {!! csrf_field() !!}
    <input type="hidden" name="id">
    @if (!empty($partner->message))
      <div class="alert alert-danger alert-dismissible">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ $partner->message }}
      </div>
    @endif
    <div class="container-fluid">
      <h3>Cadastro de parceiro de negócios</h3>
      <hr>
      <div class="row">
        @isset($partner)
          <div class="col-md-2">
            <div class="form-group"><label>Cod. SAP</label>
              <input type="text" class="form-control" maxlength="100" required placeholder="Nome" readonly
                name="code">
            </div>
          </div>
          <div class="col-md-5">
          @else
            <div class="col-md-6">
              @endif
              <div class="form-group"><label>Nome</label>
                <input type="text" maxlength="100" required placeholder="Nome" name="name" class="form-control">
              </div>
            </div>
            @isset($partner)
              <div class="col-md-5">
              @else
                <div class="col-md-6">
                  @endif
                  <div class="form-group"><label>Nome fantasia</label>
                    <input type="text" maxlength="100" required placeholder="Nome fantasia"
                      name="fantasy_name"class="form-control">
                  </div>
                </div>
                <div class="col-6">
                  <label>Tipo</label>
                  <select class="form-control selectpicker" id="type" required name="type" onchange="changeType()">
                    @foreach ($types as $item)
                      <option value="{{ $item['value'] }}" selected>{{ $item['name'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-6">
                  <label>Grupo</label>
                  <select class="form-control selectpicker" required name="group" id="groupSelect">
                    <option value="">Selecione um grupo</option>
                    @foreach ($groups as $item)
                      <option value="{{ $item['value'] }}">{{ $item['value'] }} - {{ $item['name'] }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <br>
              <div class="tabs-container container-fluid">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  {{-- <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1"><a
                      class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a></li> --}}
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-4"><a
                      class="nav-link active" data-toggle="tab" href="#tab-4">Geral / Contabilidade</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2"><a
                      class="nav-link" data-toggle="tab" href="#tab-2">Pessoas de contato</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3"><a
                      class="nav-link" data-toggle="tab" href="#tab-3">Endereços</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-6"><a
                      class="nav-link" data-toggle="tab" href="#tab-6">Formas de pagamento</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-8"><a
                      class="nav-link" data-toggle="tab" href="#tab-8">Dados bancários</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-9"><a
                      class="nav-link" data-toggle="tab" href="#tab-9">Contratos</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-10"><a
                      class="nav-link" data-toggle="tab" href="#tab-10">Anexos</a></li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-5"><a
                      class="nav-link" data-toggle="tab" href="#tab-5">Observações</a></li>
                </ul>
                <div class="container-fluid">

                  <div class="tab-content">
                    <br>

                    {{-- <!-- TAB-1 -->
                    <div class="tab-pane active" id="tab-1">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-6">
                            <div class="form-group">
                              <label>Telefone</label>
                              <input type="text" data-mask="(00)0000-00009" class="form-control telephone"
                                name="telephone" />
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="form-group">
                              <label>E-mail</label>
                              <input type="email" class="form-control email" name="email" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div> --}}

                    <!-- TAB-2 -->
                    <div class="tab-pane" id="tab-2">
                      <div class="panel-body">
                        <div class="col-3">
                          <button type="button" class="btn btn-primary" onclick="openModalContact()">
                            Adicionar pessoa de contato
                          </button>
                        </div>
                        <br>
                        <div class="table-responsive">
                          <table class="table table-default table-striped table-bordered table-hover text-center"
                            id="contactsTable">
                            <thead>
                              <tr>
                                <th style="width: 30%">Nome</th>
                                <th style="width: 20%">Telefone</th>
                                <th style="width: 30%">Email</th>
                                <th style="width: 10%">Editar</th>
                                <th style="width: 10%">Remover</th>
                              </tr>
                            </thead>
                            <tbody>

                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- TAB-3 -->
                    <div class="tab-pane" id="tab-3">
                      <div class="panel-body">
                        <div class="col-3">
                          <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                            data-coreui-target="#addressModal">
                            Adicionar endereço
                          </button>
                        </div>
                        <br>
                        <div class="table-responsive">
                          <table class="table table-default table-striped table-bordered table-hover text-center"
                            id="addressTable">
                            <thead>
                              <tr>
                                <th style="width: 150px;">Ident.</th>
                                <th>Tipo</th>
                                <th>Indicador da IE</th>
                                <th>CEP</th>
                                <th>Tipo de logradouro</th>
                                <th>Logradouro</th>
                                <th>Número</th>
                                <th>Bairro</th>
                                <th>Complemento</th>
                                <th>Cidade</th>
                                <th>Estado</th>
                                <th>País</th>
                                <th>Editar</th>
                                <th>Remover</th>
                              </tr>
                            </thead>
                            <tbody>

                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- TAB-4 -->
                    <div class="tab-pane active" id="tab-4">
                      <div class="panel-body">
                        <div class="row">
                          <p class="fw-bold">Geral</p>
                          <div class="col-6">
                            <div class="form-group">
                              <label>Telefone</label>
                              <input type="text" data-mask="(00)0000-00009" class="form-control telephone"
                                name="telephone" />
                            </div>
                          </div>
                          <div class="col-6">
                            <div class="form-group">
                              <label>E-mail</label>
                              <input type="email" class="form-control email" name="email" />
                            </div>
                          </div>
                        </div>
                        <hr>

                        <div class="row mt-4">
                          <p class="fw-bold">Contabilidade</p>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>CNPJ</label>
                              <input type="text" data-mask="99.999.999/9999-99" class="form-control cnpj"
                                id="cnpj"name="cnpj" onchange="searchCNPJ(event)" />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>CPF</label>
                              <input type="text" data-mask="999.999.999-99" class="form-control cpf" id="cpf" />
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="cnae">CNAE</label>
                              <select class="form-control selectpicker" name="cnae" id="cnae">
                                <option value=""></option>
                                @foreach ($cnae as $key => $value)
                                  <option value="{{ $value['AbsId'] }}">
                                    {{ substr($value['CNAECode'] . ' - ' . $value['Descrip'], 0, 50) }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="row mt-2">
                          <div class="col-md-3">
                            <label for="contaControle">Contas a pagar</label>
                            <select class="form-control selectpicker" name="contaControles" id="contaControle"
                              @if (Route::currentRouteName() == 'partners.edit') readonly @endif>
                              <option value=""></option>
                              @foreach ($acctControl as $key => $values)
                                <option value='{{ $values['value'] }}' @if (isset($partner) && $partner->contaContabil == $values['value']) selected @endif>
                                  {{ $values['name'] }}</option>
                              @endforeach
                            </select>
                          </div>

                          <div class="col-md-3">
                            <label for="contaContabil">Adiantamentos</label>
                            <select class="form-control selectpicker" name="contaContabils" id="contaContabil"
                              @if (Route::currentRouteName() == 'partners.edit') readonly @endif>
                              <option value=""></option>
                              @foreach ($acct as $key => $values)
                                <option value='{{ $values['value'] }}' @if (isset($partner) && $partner->contaControle == $values['value']) selected @endif>
                                  {{ $values['name'] }}</option>
                              @endforeach
                            </select>
                          </div>

                          <div class="col-md-3">
                            <label for="contaControle">Conta a pagar do boleto</label>
                            <select class="form-control selectpicker" name="bill_exchange_account_payable"
                              id="contaControle" @if (Route::currentRouteName() == 'partners.edit') readonly @endif>
                              <option value=""></option>
                              @foreach ($acctControl as $key => $values)
                                <option value='{{ $values['value'] }}' @if (isset($partner) && $partner->bill_exchange_account_payable == $values['value']) selected @endif>
                                  {{ $values['name'] }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label for="contaControle">Ativos boleto C/P</label>
                            <select class="form-control selectpicker" name="assets_bill_exchange_account_payable"
                              id="assets_bill_exchange_account_payable" @if (Route::currentRouteName() == 'partners.edit') readonly @endif>
                              <option value=""></option>
                              @foreach ($acctControl as $key => $values)
                                <option value='{{ $values['value'] }}' @if (isset($partner) && $partner->assets_bill_exchange_account_payable == $values['value']) selected @endif>
                                  {{ $values['name'] }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="row mt-2">
                          <div class="form-group">
                            <label>Isento Inscrição Estadual
                              <input type="checkbox" id="iie" class='form-check-input'
                                style='width: 20px; height: 20px;' onchange="changeIie()" /></label>
                          </div>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label>Inscrição Estadual</label>
                                <input type="text" data-mask="999999999999999" id="ie" name="ie"
                                  class="form-control" />
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label>IE Substituição Tributária</label>
                                <input type="text" data-mask="999999999999999" class="form-control" name="ie_st" />
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group">
                                <label>Inscrição municipal</label>
                                <input type="text" data-mask="999999999999999" class="form-control" name="im" />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- TAB-6 -->
                    <div class="tab-pane" id="tab-6">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-md-4">
                            <label for="paymentForm">Forma de pagamento principal</label>
                            <select name="paymentForm" id="paymentForm" required class="form-control selectpicker"
                              onchange="selectPaymentFormFull(event)">
                              <option value="">Selecione</option>
                              @foreach ($paymentForms as $key => $value)
                                <option value="{{ $value['value'] }}">{{ $value['name'] }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label for="paymentTerms">Condições de pagamento</label>
                            <select name="paymentTerms" id="paymentTerms" required class="form-control selectpicker">
                              <option value="">Selecione</option>
                              @foreach ($paymentConditions as $key => $value)
                                <option value="{{ $value['GroupNum'] }}">{{ $value['PymntGroup'] }}</option>
                              @endForeach
                            </select>
                          </div>
                        </div>
                        <div class="row mt-4">
                          <div class="table-responsive">
                            <div class="table">
                              <table class="table table-default table-striped table-bordered table-hover text-center"
                                id="paymentFormsTable">
                                <thead>
                                  <tr class="">
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>incluir</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach ($paymentForms as $key => $value)
                                    <tr>
                                      <td>{{ $value['value'] }}</td>
                                      <td>{{ $value['name'] }}</td>
                                      <td>
                                        <input type="checkbox" class='form-check-input' style='width: 20px; height: 20px;'
                                          @if (isset($partner)) @foreach ($partner->payments()->get() as $key => $values)
                                              @if ($values->description == $value['value']) checked @endif
                                          @endforeach
                                  @endif
                                  value="{{ $value['value'] }}" name="paymentForms[{{ $value['name'] }}][code]">
                                  </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- TAB-5 -->
                    <div class="tab-pane" id="tab-5">
                      <div class="panel-body">
                        <div class="col-md-12">
                          <label for="">Observações</label>
                          <textarea class="form-control" name="comments" maxlength="254"></textarea>
                        </div>
                      </div>
                    </div>

                    <!-- TAB-8 -->
                    <div class="tab-pane" id="tab-8">
                      <div class="panel-body">
                        <div class="col-md-3">
                          <button type="button" onclick="openBankAccountModal()" class="btn btn-primary">Adicionar dados
                            bancários</button>
                        </div>
                        <br>
                        <div class="table-responsive">
                          <table class="table table-default table-striped table-bordered table-hover text-center"
                            id="bankAccountsTable">
                            <thead>
                              <tr>
                                <th>Banco</th>
                                <th>Agência</th>
                                <th>Digito da agência</th>
                                <th>Conta</th>
                                <th>Digito da conta</th>
                                <th>Obs.</th>
                                <th>Editar</th>
                                <th>Apagar</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php $bank_cont = 0; ?>
                              @if (isset($bankaccounts) && isset($partner))
                                @foreach ($partner->bankaccounts()->get() as $p_bankaccount)
                                  <tr id="bankaccount-{{ $bank_cont }}"
                                    @if ($partner->default_bankcode == $p_bankaccount->BankCode) style="font-weight: bold;" @endif>
                                    <td>
                                      {{ $p_bankaccount->BankCode }} - {{ $p_bankaccount->BankName }}

                                      <input type="hidden" data-name="BankCode"
                                        name="bankaccount[{{ $bank_cont }}][BankCode]"
                                        value="{{ $p_bankaccount->BankCode }}">
                                    </td>
                                    <td>
                                      {{ $p_bankaccount->Branch }}
                                      <input type="hidden" data-name="Branch"
                                        name="bankaccount[{{ $bank_cont }}][Branch]"
                                        value="{{ $p_bankaccount->Branch }}">
                                    </td>
                                    <td>
                                      {{ $p_bankaccount->City }}
                                      <input type="hidden" data-name="City" name="bankaccount[{{ $bank_cont }}][City]"
                                        value="{{ $p_bankaccount->City }}">
                                    </td>
                                    <td>
                                      {{ $p_bankaccount->Account }}
                                      <input type="hidden" data-name="Account"
                                        name="bankaccount[{{ $bank_cont }}][Account]"
                                        value="{{ $p_bankaccount->Account }}">
                                    </td>
                                    <td>
                                      {{ $p_bankaccount->ControlKey }}
                                      <input type="hidden" data-name="ControlKey"
                                        name="bankaccount[{{ $bank_cont }}][ControlKey]"
                                        value="{{ $p_bankaccount->ControlKey }}">
                                    </td>
                                    <td>
                                      {{ $p_bankaccount->Street }}
                                      <input type="hidden" data-name="Street"
                                        name="bankaccount[{{ $bank_cont }}][Street]"
                                        value="{{ $p_bankaccount->Street }}">
                                    </td>
                                    <td>
                                      <a href='#' onclick='editBankAccount({{ $bank_cont }})'>
                                        <svg class="icon icon-xl">
                                          <use xlink:href="{{ asset('icons_assets/custom.svg#edit') }}"></use>
                                        </svg>
                                      </a>
                                    </td>
                                    <td>
                                      <input type='checkbox' class='form-check-input' style='width: 20px; height: 20px;'
                                        value='1' name='bankaccount[{{ $bank_cont }}][delete]' data-name='delete'>
                                    </td>
                                  </tr>
                                  <?php $bank_cont++; ?>
                                @endforeach
                              @endif
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- CONTRATOS -->
                    <div class="tab-pane" id="tab-9">
                      <div class="panel-body">
                        <a class="btn btn-primary" onclick="addContract(event)">Adicionar contrato</a>
                        <div class="table-responsive mt-3">
                          <table class="table table-default table-striped table-bordered table-hover text-center"
                            id="contractsTable">
                            <thead>
                              <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 10%">Cod. WEB</th>
                                <th style="width: 15%">N° Contrato</th>
                                <th style="width: 15%">Data início</th>
                                <th style="width: 15%">Data término</th>
                                <th style="width: 10%">Dias restantes</th>
                                <th style="width: 15%">Valor do contrato</th>
                                <th style="width: 15%">Valor residual</th>
                                <th style="width: 10%">Histórico</th>
                                <th style="width: 10%">Opções</th>
                              </tr>
                            </thead>
                            <tbody>
                              @php $contractIndex=1; @endphp
                              @if (isset($partner))
                                @foreach ($partner->contracts as $contract)
                                  <tr>
                                    <td>
                                      {{ $contractIndex }}
                                      <input type="hidden" name="contracts[{{ $contractIndex }}][id]"
                                        value="{{ $contract->id }}">
                                    </td>
                                    <td>{{ $contract->code }}</td>
                                    <td>
                                      <input type="text" value="{{ $contract->contractNumber }}" class="form-control"
                                        name="contracts[{{ $contractIndex }}][contractNumber]">
                                    </td>
                                    <td>
                                      <input type="date" value="{{ $contract->startDate }}" class="form-control"
                                        name="contracts[{{ $contractIndex }}][startDate]">
                                    </td>
                                    <td>
                                      <input type="date" value="{{ $contract->endDate }}" class="form-control"
                                        name="contracts[{{ $contractIndex }}][endDate]">
                                    </td>
                                    <td>
                                      @php
                                        $differenceDates = differenceBetweenTwoDatesOutputDays(
                                            \Carbon\Carbon::now()->format('Y-m-d'),
                                            $contract->endDate,
                                        );
                                      @endphp
                                      <input type="text"
                                        value="@if ($differenceDates > 0) {{ $differenceDates }} @else 0 @endif"
                                        class="form-control locked" readonly>
                                    </td>
                                    <td>
                                      <input type="text" value="{{ $contract->amount }}"
                                        class="form-control money locked" name="contracts[{{ $contractIndex }}][amount]"
                                        onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
                                    </td>
                                    <td>
                                      <input type="text" value="{{ $contract->residualAmount }}"
                                        class="form-control money locked"
                                        name="contracts[{{ $contractIndex }}][residualAmount]" onclick='destroyMask(event)'
                                        onblur="setMaskMoney();focusBlur(event)" readonly>
                                    </td>
                                    <td>
                                      <a class='btn btn-primary' href='#' data-coreui-toggle="modal"
                                        data-coreui-target="#contractUsageHistory-modal" value="{{ $contract->code }}"
                                        data-lineNum="{{ $contractIndex }}">
                                        <svg class="icon">
                                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                                        </svg>
                                      </a>
                                    </td>
                                    <td>
                                      <a class="btn btn-danger" onclick="removeContract(this)" data-contract_code="{{ $contract->code }}" type="button">
                                        <svg class="icon">
                                          <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                        </svg>
                                      </a>
                                    </td>
                                  </tr>
                                  @php $contractIndex++;@endphp
                                @endforeach
                              @endif
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- ANEXOS -->
                    <div class="tab-pane" id="tab-10">
                      <div class="panel-body">
                        <div class="row mt-4">
                          <div class="col-md-6 mt-2">
                            <div class="table-responsive">
                              <table
                                class="table table-default table-striped table-bordered table-hover dataTables-example w-100">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th>Anexo</th>
                                    <th>Visualizar</th>
                                    <th>Remover</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @if (!empty($partner))
                                    <?php $upCont = 1; ?>
                                    @foreach ($partner->uploads as $key => $item)
                                      <tr id="lineUp-{{ $upCont }}">
                                        <td style="width: 10%;">{{ $upCont }}</td>
                                        <td style="width: 70%;">
                                          {{ preg_split('/[;=]/', $item->diretory)[1] }}
                                        </td>
                                        <td style="width: 10%;" class="text-center">
                                          <a href="{{ $item->diretory }}" target="_blank" class="btn btn-primary">
                                            <svg class="icon">
                                              <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                                            </svg>
                                          </a>
                                        </td>
                                        <td style="width: 10%;" class="text-center">
                                          <a class="text-danger" onclick="removeLine(this)" type="button">
                                            <svg class="icon icon-xl">
                                              <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                            </svg>
                                          </a>
                                        </td>
                                      </tr>
                                      <?php $upCont++; ?>
                                    @endforeach
                                  @endif
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="col-md-6 mt-2">
                            <!-- image-preview-filename input [CUT FROM HERE]-->
                            {{-- <input type="text" class="form-control image-preview-filename"
                                                             readonly="readonly"> --}}
                            <!-- don't give a name === doesn't send on POST/GET -->
                            <span class="input-group-btn w-100">
                              <!-- image-preview-input -->
                              <div class="btn btn-default image-preview-input w-100">
                                <span class="image-preview-input-title">Abrir</span>
                                <input class="form-control" type="file" multiple name="input-file-preview[]" />
                                <!-- rename it -->
                              </div>
                            </span>
                            <!-- /input-group image-preview [TO HERE]-->
                            <br>
                            <div class="col-md-12 mt-2 d-md-flex justify-content-md-end">
                              <a class="btn btn-danger image-preview-clear me-1" type="button" style="display:none;">
                                Limpar anexos
                              </a>
                              @if (isset($partner))
                                <a class="btn btn-primary w-25 me-2" onclick="updateUploads(event)">Atualizar anexos</a>
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="col-12 mt-5">
                <button class="btn btn-primary float-end" type="button" id="btn-salvar"
                  onclick="validateTabs()">Salvar</button>
              </div>
            </div>
      </form>


      <!-- ADDRESS MODAL -->
      <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" data-coreui-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Adicionar endereço</h5>
              <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ibox-content">
              <form onsubmit="addAddress(event)" id="addressForm">
                <input type="hidden" name="line">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Identificação</label>
                      <input required type="text" class="form-control" id="name_adr" name="name" />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Tipo</label>
                      <select name="type" id="type_adr" required class="form-control selectpicker">
                        <option value=""></option>
                        <option value="0">Entrega</option>
                        <option value="1">Cobrança</option>
                        <option value="2">Entrega e Cobrança</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label for="country">Indicador da IE</label>
                    <select name="U_SKILL_indIEDest" id="U_SKILL_indIEDest" class="form-control selectpicker" required>
                      <option value=""></option>
                      <option value="1"> Contribuinte ICMS</option>
                      <option value="2"> Contribuinte isento de IE</option>
                      <option value="9"> Não contribuinte com ou sem IE</option>
                    </select>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>CEP</label>
                      <input type="text" data-mask="99.999-999" class="form-control" name="postcode" id="postcode"
                        required onchange="getAddress()" />
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Tipo de logradouro</label>
                      <input required type="text" placeholder="Rua, Travessa, etc." class="form-control"
                        name="typeofaddress" id="typeofaddress" />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Logradouro</label>
                      <input required type="text" class="form-control" name="street" id="street" />
                    </div>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-2">
                    <div class="form-group">
                      <label>Número</label>
                      <input required type="text" class="form-control" name="number" id="number" />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Bairro</label>
                      <input required type="text" class="form-control" name="neighborhood" id="neighborhood" />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Complemento</label>
                      <input type="text" class="form-control" name="complement" id="complement" />
                    </div>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Cidade</label>
                      <input required type="text" class="form-control" name="city" id="city" />
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Estado</label>
                      <select name="state" id="state" required class="form-control selectpicker">
                        @foreach ($states as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="country">País</label>
                      <select onchange="changeCountry()" name="country" id="country" class="form-control selectpicker"
                        required>
                        @foreach ($countries as $country)
                          <option @if ($country['value'] === 'BR') selected @endif value="{{ $country['value'] }}">
                            {{ $country['label'] }}</option>
                        @endforeach
                      </select>
                    </div>
                    <input type="hidden" class="form-control" name="county" id="county" value="" />
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" form="contactForm" class="btn btn-white"
                data-coreui-dismiss="modal">Cancelar</button>
              <button type="submit" form="addressForm" class="btn btn-primary">Adicionar</button>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->

      <div class="modal inmodal" id="contactModal" tabindex="-1" role="dialog" data-coreui-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content animated bounceInRight">
            <div class="modal-header">
              <h5 class="modal-title">Adicionar pessoa de contato</h5>
              <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form onsubmit="addContact(); return false;" id="contactForm">
                <input type="hidden" name="line">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Nome</label>
                      <input required type="text" class="form-control" name="name" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Telefone</label>
                      <input type="text" data-mask="(99) 99999-9999" class="form-control telephone" name="telephone" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>E-mail</label>
                      <input type="email" class="form-control" name="email" />
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" form="contactForm" class="btn btn-white" data-coreui-dismiss="modal">Cancelar
              </button>
              <button type="submit" form="contactForm" class="btn btn-primary"
                data-coreui-dismiss="modal">Adicionar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- BANK ACCOUNTS MODAL -->
      <div class="modal" tabindex="-1" role="dialog" id="bankAccountModal" data-coreui-backdrop="static">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Adicionar dados bancários</h5>
              <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form onsubmit="saveBankAccounts(event); return false;" id="bankAccountForm">
                <input type="hidden" name="line">
                <div class="row form-group">
                  <div class="col-md-4">
                    <label for="">Banco</label>
                    <select name="BankCode" id="" class="form-control selectpicker">
                      <option value="" selected>Selecione</option>
                      @foreach ($bankaccounts as $bankaccount)
                        <option value="{{ $bankaccount['BankCode'] }}">
                          {{ $bankaccount['BankCode'] }} - {{ $bankaccount['BankName'] }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="">Agência</label>
                    <input type="number" class="form-control" name="Branch">
                  </div>

                  <div class="col-md-4">
                    <label for="">Digito da agência</label>
                    <input type="number" class="form-control" name="City" max="99" maxlength="1">
                  </div>
                </div>
                <div class="row form-group mt-2">
                  <div class="col-md-4">
                    <label for="">N° da conta</label>
                    <input type="number" class="form-control" name="Account">
                  </div>
                  <div class="col-md-4">
                    <label for="">Digito da conta</label>
                    <input type="number" class="form-control" name="ControlKey" max="99" maxlength="1">
                  </div>
                </div>
                <div class="row form-group mt-2">
                  <div class="col-md-12">
                    <p for="">Observações</p>
                    <textarea class="form-control" name="Street" cols="30" rows="5" maxlength="100"></textarea>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-white" data-coreui-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="contractUsageHistory-modal">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Histórico de utilização do contrato - NFE</h5>
              <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              {!! csrf_field() !!}
              <div class="row" style="margin-bottom: 10px;">
                <div class="col-md-3">
                  <label for="">Valor inicial:</label>
                  <input type="text" class="form-control money locked" id="initialAmount" readonly>
                </div>
                <div class="col-md-3">
                  <label for="">Valor residual:</label>
                  <input type="text" class="form-control money locked" id="residualAmount" readonly>
                </div>
                <div class="col-md-3">
                  <label for="">Valor utilizado NFE:</label>
                  <input type="text" class="form-control money locked" id="amountUsed" readonly>
                </div>
              </div>
              <hr>
              <div class="table-responsive">
                <table id="contractUsageHistory-table"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <th style="width: 10%;">Cod. SAP</th>
                    <th style="width: 10%;">Cod. WEB</th>
                    <th style="width: 10%;">N° NFE</th>
                    <th style="width: 30%;">Usuario</th>
                    <th style="width: 25%;">Data</th>
                    <th style="width: 15%;">Total</th>
                  </thead>
                  <tbody>
                    {{-- dados adicionados por javascript --}}
                  </tbody>
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

      <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
      <script type="application/javascript">

      $(document).ready(() => {
        setMaskMoney();
      });

      function setMaskMoney() {
        $('.qtd').maskMoney({
          precision: 3,
          decimal: ',',
          thousands: '.',
          selectAllOnFocus: true
        });
        $('.money').maskMoney({
          precision: 2,
          decimal: ',',
          thousands: '.',
          selectAllOnFocus: true
        });
        $('.moneyPlus').maskMoney({
          precision: 4,
          decimal: ',',
          thousands: '.',
          selectAllOnFocus: true
        });
        $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"]'), function(index, value) {
          $(value).trigger('mask.maskMoney')
        })
      }

      function destroyMask(event) {
        $(event.target).maskMoney('destroy')
      }

      function focusBlur(event) {
        $(event.target).trigger('mask.maskMoney')
      }


      @if(!isset($partner))
        let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
      @endif

      $(document).ready(function(){
          $('.telephone').mask("(00)0000-00009");
          $('.cpf').mask("999.999.999-99");
          $('.cnpj').mask("99.999.999/9999-99");
      });

      function changeCountry() {
          if($('#country').val() !== 'BR') {
              $('#state').prop('required', false).val('').prop('readonly', true);
          } else {
              $('#state').prop('required', true).prop('readonly', false);
          }
      }

      function changeIie() {
          if ($("#iie").prop('checked') === true) {
              $("#ie").val('Isento').prop('readonly', true);
          } else {
              $("#ie").val('').prop('readonly', false);
          }
      }

      function getAddress() {
          var postcode = $("#postcode").val().replace(/\D/g, '');
          if (postcode.length !== 8) {
              return;
          }
          
          $.getJSON('https://viacep.com.br/ws/' + postcode + '/json/', function (data) {
              if (data.erro === true) {
                  alert('CEP inválido!');
              }
              $('#street').val(data.logradouro);
              $('#neighborhood').val(data.bairro);
              $('#complement').val(data.complemento);
              $('#city').val(data.localidade);
              $('#state').val(data.uf);
              $('#country').val('BR');
              getTypeOfAddress(data.ibge);
              $('#state, #country').selectpicker('destroy');
                $('#state, #country').selectpicker(selectpickerConfig).selectpicker('render');
          });
      }

      function getTypeOfAddress(ibge){
          $.getJSON('/partners/get/type/of/address/'+ibge, function(data){
              $('#city').val(data[0].name);
              $('#county').val(data[0].code);
          });
      }

      var addressIndex = 0;

      // function typeAddress(){
      //     let valor=$('#type_adr').val();

      //     if(valor == '2'){
      //         $('#name_adr').val('ENTREGA E COBRANÇA');
      //         $('#name_adr').prop('readonly',true);
      //     }else{
      //         $('#name_adr').val('');
      //         $('#name_adr').prop('readonly',false);
      //     }
      // }
      function addAddress(event = null) {

        if(event !== null){
          event.preventDefault()
        }

        var form = $('#addressForm');
        var values = {};
        form.find('input,select').each(function (index, item) {
            if(!item.name) {
              return;
            }
            values[item.name] = item.value || ' - ';
        });

        if (values.type === "2") {

            values.type = "0";
            values.name = "ENTREGA"
            addAddressToTable(values);

            values.type = "1";
            values.name = "COBRANÇA"
            addAddressToTable(values);

        } else {
            addAddressToTable(values);
        }
        
        if($('#addressModal').hasClass('show')){
          $('#addressModal').modal('hide')
        }
      }

      $('.selectpicker').on('focus', function(event){
        $(event.target).selectpicker('destroy');
        $(event.target).selectpicker(selectpickerConfig).selectpicker('render');
      });

      function addAddressToTable(items) {

          var form = $('#addressForm');
          var table = $('#addressTable');
          var tr = $("<tr id='address-" + addressIndex + "'>");
          var types = [];
          types[0] = 'Entrega';
          types[1] = 'Cobrança';

          var IndicadorIE = [];
          IndicadorIE[1] = 'Contribuinte ICMS';
          IndicadorIE[2] = 'Contribuinte isento de IE';
          IndicadorIE[9] = 'Não contribuinte com ou sem IE';

          let trueFalseLabel = [];
          trueFalseLabel[0] = 'Não';
          trueFalseLabel[1] = 'Sim';

          if (!validateAddress(items.name, items.type)) {
              alert("Não pode existir nome e tipo iguais!");
              return false;
          }

          $.each(items, function (index, value) {
            var td = $('<td>');
            if (index === 'line' || index === 'county') {
                return true;
            }
            var label = value;
            if (index === 'type') {
                label = types[value];
            }

            if (index === 'U_SKILL_indIEDest'){
                label = IndicadorIE[value];
            }
            
            td.html(label);

            if(index === 'name'){
              td.append($('<p style="padding-left: 200px;"></p>'))
            }else{

            }
            td.append($('<p style="padding-left: 150px;"></p>'))

            var input = $('<input type="hidden" data-name="' + index + '">');
            input.val(value);

            input.attr('name', 'addresses[' + addressIndex + '][' + index + ']');

            td.append(input);
            tr.append(td);
          });
          tr.find('td').first().append('<input type="hidden" value="' + items.line + '" data-name="line" name="addresses[' + addressIndex + '][line]">');
          tr.find('td').first().append('<input type="hidden" value="' + items.county + '" data-name="county" name="addresses[' + addressIndex + '][county]">');
          tr.append($("<td class='text-center'><a href='#' onclick='editAddress(" + addressIndex + ")' data-coreui-toggle='modal' data-coreui-target='#addressModal'><svg class='icon icon-xl'><use xlink:href='{{ asset('icons_assets/custom.svg#edit') }}'></use></svg></a></td>"));
          

          tr.append($("<td class='text-center'><input type='checkbox' class='form-check-input' style='width: 20px; height: 20px;' value='1' name='addresses[" + addressIndex + "][delete]' data-name='delete'></td>"));

          addressIndex++;
          table.find('tbody').append(tr);
          form.find('input,select').val('');
          return false;

      }

      function validateAddress(name, type) {
          var table = $('#addressTable');
          var valid = true;
          table.find("tr").each(function (index, tr) {
              var _name = $(tr).find("input[data-name=name]").val();
              var _type = $(tr).find("input[data-name=type]").val();
              if (name.trim() === _name && _type === type.trim()) {
                  valid = false;
                  return false;
              }
          });
          return valid;
      }

      function editAddress(id) {
          var form = $('#addressForm');
          var tr = $('#address-' + id);
          tr.find('input').each(function (index, item) {
              form.find('[name=' + $(item).data('name') + ']').val(item.value);
          });
          
          form.attr('onsubmit', "updateAddress(" + id + "); return false;");
          changeCountry();
          form.find('.selectpicker').selectpicker('refresh')
      }

      function updateAddress(id) {
          $("#address-" + id).remove();
          addAddress();
      }

      function changeType() {
          var groups = @json($groups);
          var types = [];
          types[0] = 'C';
          types[1] = 'S';
          types[2] = 'C'; // lead is customer
          //types[2] = 'L';
          var select = $('#groupSelect');
          select.html('');
          var type = $("#type").val();
          $.each(groups, function (index, item) {
              if (item.type !== types[type]) {
                  return true;
              }
              var option = $("<option>");
              option.html(item.name);
              option.val(item.value);
              select.append(option);
          });
          select.selectpicker('destroy')
          select.selectpicker(selectpickerConfig).selectpicker('render')
      }

      var contactIndex = 0;

      function openModalContact() {
          var form = $('#contactForm');
          form.find('input').val('');
          $('#contactModal').modal('show').find('button[type=submit]').html('Adicionar');
      }

      function addContact() {
          var tr = $("<tr id='contact-" + contactIndex + "'>");
          var form = $('#contactForm');
          var table = $('#contactsTable');
          var line = '';

          var values = {};
          form.find('input,select').each(function (index, item) {
              values[item.name] = item.value;
          });
          if (!validateContact(values.name)) {
              alert("Não pode existir nomes iguais!");
              return false;
          }

          form.find('input').each(function (index, item) {
              if (item.name === 'line') {
                  line = item.value;
                  return true;
              }
              tr.append($('<td><input type="hidden" data-name="' + item.name + '" name="contacts[' + contactIndex + '][' + item.name + ']" value="' + item.value.trim() + '">' + item.value + '</td>'));
          });

          tr.find('td').first().append('<input type="hidden" value="' + line + '" data-name="line" name="contacts[' + contactIndex + '][line]">');
          
          tr.append($("<td class='text-center'><a href='#' onclick='editContact(" + contactIndex + ");'><svg class='icon icon-xl'><use xlink:href='{{ asset('icons_assets/custom.svg#edit') }}'></use></svg></td>"));

          tr.append($("<td class='text-center'><input type='checkbox' class='form-check-input' style='width: 20px; height: 20px;' value='1' name='contacts[" + contactIndex + "][delete]' data-name='delete'></td>"));

          contactIndex++;

          table.find('tbody').append(tr);
          form.find('input').val('');
          $('#contactModal').hide();
          return false;
      }

      function validateContact(name) {
          var table = $('#contactsTable');
          var valid = true;
          table.find("tr").each(function (index, tr) {
              var _name = $(tr).find("input[data-name=name]").val();
              if (name.trim() === _name) {
                  valid = false;
                  return false;
              }
          });
          return valid;
      }

      function updateUploads(event) {
        event.preventDefault();
        waitingDialog.show("O processo pode demorar um pouco, aguarde até que seja concluído!");
        let formData = new FormData();
        $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
          formData.append('input-file-preview[]', value)
        })
        formData.append('table', 'partners')
        formData.append('id', $('input[name="id"]').val())

        $.ajax({
            type: 'POST',
            url: "{{ route('partners.update.uploads') }}",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            headers: {
              'X-CSRF-TOKEN': $('body input[name="_token"]').val()
            }
          })
          .always(function(response) {
            waitingDialog.hide();
            swal({
                title: "Processando",
                text: "Documento sendo salvo. Deseja atualizar a página?",
                icon: "info",
                //buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
              })
              .then((refresh) => {
                if (refresh) {
                  document.location.reload(true);
                }
              });
          });
      }

      function removeUpload(id, idRef) {
        swal({
            title: "Tem certeza que deseja excluir o arquivo?",
            text: "Esta operação não pode ser desfeita!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              waitingDialog.show('Excluindo...')
              window.location.href = "{{ route('partners.remove.uploads') }}/" + id + "/" + idRef;
            }
          });
      }

      function editContact(id) {
          var form = $('#contactForm');
          var tr = $('#contact-' + id);
          tr.find('input').each(function (index, item) {
              form.find('[name=' + $(item).data('name') + ']').val(item.value);
          });

          form.attr('onsubmit', "updateContact(" + id + "); return false;");
          $('#contactModal').modal('show')
      }

      function updateContact(index) {
          $("#contact-" + index).remove();
          addContact();
      }

      function changeCepOption(event){
        let element = $(event.target)
        if(element.val() == 0){
          $('#postcode').attr('disabled', false)
        }else{
          $('#postcode').attr('disabled', true)
        }
      }

      function validateTabs() {
          
          var erros = new Array();
          var input = $("#needs-validation").find("*:invalid").first();
          var tabPane = input.closest('.tab-pane').first();
          if($('#paymentForm').val() == ''){
              erros.push('Informe uma forma de pagamento principal\n');
          }
          if($('#paymentTerms').val() == ''){
              erros.push('Informe uma condição de pagamento principal\n');
          }
  
          let cont = 0;
          $('#paymentFormsTable tbody tr').each(function(index, value){
              if($('#paymentForm option:selected').text() == $(value).find('td:eq(1)').text() && $(value).find('td:eq(2) input').is(':checked')){
                  cont+=1;
                  return
              }
          })
          if(cont == 0){
              erros.push('É necessário selecionar opção na tabela igual a opção selecionada na forma de pagamento principal\n');
          }
          
          if(($('#cnpj').val() != '' && $('#cnae').val() == '')){
              erros.push('É necessário informar o CNAE\n');
          }
       
          if (tabPane.length === 1) {
              $('#myTabs').find('a[href="#' + tabPane.attr('id') + '"]').tab('show');
          }
          
          if($('#cpf').val() == '' && $('#cnpj').val() == ''){
              erros.push('Informe um CPF ou CNPJ \n');
          }

          if($('#groupSelect').val() == ''){
            erros.push('Informe o grupo do parceiro\n');
          }
          
          if($('#contactsTable tbody').find('tr').length == '0'){
              erros.push('Adicione ao menos uma pessoa de contato \n');
          }
          if($('#addressTable tbody').find('tr').length == '0'){
              erros.push('Adicione ao menos um endereço \n');
          }
          if (erros.length > 0) {
              swal("Os seguintes erros foram encontrados: ", erros.toString(), "error")
              return
          } else {
              $('#btn-salvar').attr('type', 'submit');
          }
      }

      $('#needs-validation').submit(function (event){
          event.preventDefault()
          waitingDialog.show('Processando...');
          let form = $('#needs-validation').serialize();
          var form_data = new FormData($('#needs-validation')[0]);
          $.ajax({
              type: 'post',
              url: "{{route('partners.store')}}",
              headers: {
                  'X-CSRF-TOKEN': $('body input[name="_token"]').val()
              },
              data: form_data,
              dataType: 'json',
              processData: false,
              contentType: false,
              success: function(response){
                  if(response.status == 'success'){
                      swal({
                          title: "Sucesso",
                          text: "Cadastro enviado para o SAP. Deseja visualizar os dados do parceiro?",
                          icon: "success",
                          //buttons: true,
                          buttons: ["Não", "Sim"]
                      })
                      .then((refresh) => {
                          if (refresh) {
                              window.location.href = "{{route('partners.edit')}}/"+response.code
                          }
                      });
                  }else if(response.status == 'error'){
                      swal({
                          title: "Opss...",
                          text: `${response.message}`,
                          icon: "error",
                          buttons: ["Fechar"],
                      })
                  }
                  waitingDialog.hide();
              },
              error: function(response){
                  waitingDialog.hide();
                  swal({
                      title: "Opss...",
                      text: `${response.message}`,
                      icon: "error",
                      buttons: ["Fechar"],
                  });
              }
          });
      });

      let bankIndex = {{$bank_cont}};
      function saveBankAccounts(){
          
          var tr = $("<tr id='bankaccount-" + bankIndex + "'>");
          var form = $('#bankAccountForm');
          var line = '';

          form.find('input, select, textarea').not('input[type="search"]').each(function (index, item) {
              
              if (item.name === 'line') {
                  line = item.value;
                  return true;
              }

              if($(item).is('select')){
                  tr.append($('<td><input type="hidden" data-name="' + item.name + '" name="bankaccount[' + bankIndex + '][' + item.name + ']" value="' + item.value.trim() + '">' +$(item).find(':selected').text()+ '</td>'));
              }else{
                  tr.append($('<td><input type="hidden" data-name="' + item.name + '" name="bankaccount[' + bankIndex + '][' + item.name + ']" value="' + item.value.trim() + '">' +item.value+ '</td>'));
              }
          });
          
          tr.find('td').first().append('<input type="hidden" value="' + line + '" data-name="line" name="bankaccount[' + bankIndex + '][line]">');
          
          tr.append($("<td><a href='#' class='text-center' onclick='editBankAccount(" + bankIndex + ")'><svg class='icon icon-xl'><use xlink:href='{{ asset('icons_assets/custom.svg#edit') }}'></use></svg></td>"));

          tr.append($("<td class='text-center'><input type='checkbox' class='form-check-input' style='width: 20px; height: 20px;' value='1' name='contacts[" + bankIndex + "][delete]' data-name='delete'></td>"));

          bankIndex++;

          $('#bankAccountsTable').find('tbody').append(tr);
          $('#bankAccountModal').modal('hide')
          form.find('input').val('');
          return false;
          
      }

      function editBankAccount(id){
          var form = $('#bankAccountForm');
          var tr = $('#bankaccount-' + id);
          tr.find('input').each(function (index, item) {
              form.find('[name=' + $(item).data('name') + ']').val('');
              form.find('[name=' + $(item).data('name') + ']').val(item.value);
          });

          $('#bankAccountModal').modal('show');
          
          form.attr('onsubmit', "updateBankAccount(" + id + "); return false;");
          
      }

      function updateBankAccount(id){
          $("#bankaccount-" + id).remove();
          bankIndex--;
          saveBankAccounts();
      }

      function openBankAccountModal(){
          $('#bankAccountModal').modal('show');
          $('#bankAccountForm').find('input, select').val('')
          $('#bankAccountForm').attr('onsubmit', "saveBankAccounts(); return false;");
      }

      function selectPaymentFormFull(event){
        let select = $(event.target).find('option:selected').text();
        let checkbox = $(`#tab-6 input[name*="paymentForms[${select}]`);

        if(checkbox){
          checkbox.attr('checked', true)
        }
      }

      let contractIndex = {{$contractIndex}};
      function addContract(event){

        let tr = $(`<tr value="${contractIndex}"></tr>`);

        tr.append(`<td>${contractIndex}</td>`);
        tr.append(`<td></td>`);
        tr.append(`<td><input type="text" class="form-control" name="contracts[${contractIndex}][contractNumber]" required></td>`);
        tr.append(`<td><input type="date" class="form-control" name="contracts[${contractIndex}][startDate]" required></td>`);
        tr.append(`<td><input type="date" class="form-control" name="contracts[${contractIndex}][endDate]" required></td>`);
        tr.append(`<td></td>`);
        tr.append(`<td><input type="text" class="form-control money" name="contracts[${contractIndex}][amount]" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)" required></td>`);
        tr.append(`<td><input type="hidden" class="form-control money locked" name="contracts[${contractIndex}][residualAmount]" onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)" readonly></td>`);

        $('#contractsTable tbody').append(tr);
        contractIndex++;
      }

      function excludeContract(index){
        $('#contractsTable').find(`tr[value="${index}"]`).remove();
      }

      function searchCNPJ(event){

        let cnpj = $('#cnpj').val().replace(/[./-]/gi, '');

        if(!validarCpfCnpj(cnpj)){
          swal({
              title: "Opss...",
              text: "Verifique o CNPJ informado!",
              icon: "error",
              buttons: ["Fechar"],
          });
          return;
        }

        swal({
          title: "Dados para preenchimento rápido",
          text: "O CNPJ informado foi encontrado na base de dados da Receita Federal, deseja preencher automaticamente os seguintes campos:\n ° Endereço\n° Contato\n° Contabilidade ?",
          icon: "info",
          buttons: ["Fechar", "Preencher automaticamente"],
        })
        .then((result) => {
          if (result) {
            $.ajax({
            url: `https://publica.cnpj.ws/cnpj/${cnpj}`,
            type: 'get',
          }).done(function(data, status) {

            $('input[name="name"]').val(data.razao_social);
            $('input[name="fantasy_name"]').val(data.estabelecimento.nome_fantasia);

            $(`#cnae option:contains("${data.estabelecimento.atividade_principal.subclasse}")`).prop('selected', true)
            //add contact
            $('#contactForm').find('input[name="name"]').val(data.razao_social);
            $('#contactForm').find('input[name="telephone"]').val(`${data.estabelecimento.ddd1}${data.estabelecimento.telefone1}`);
            $('#contactForm').find('input[name="email"]').val(data.estabelecimento.email);
            addContact();

            let indicador_ie = 1;
            if(data.simples){
              if(data.simples.simples == 'Sim' && data.simples.mei == 'Não'){
                indicador_ie = 2;
              }else if(data.simples.simples == 'Sim' && data.simples.mei == 'Sim'){
                indicador_ie = 9;
              }
            }
            $('#addressForm').find('input[name="name"]').val(data.razao_social);
            $('#addressForm').find('select[name="type"]').val(2);
            $('#addressForm').find(`select[name="U_SKILL_indIEDest"] option[value="${indicador_ie}"]`).prop('selected', true);
            $('#addressForm').find('input[name="postcode"]').val(data.estabelecimento.cep);
            $('#addressForm').find('input[name="typeofaddress"]').val(data.estabelecimento.tipo_logradouro);
            $('#addressForm').find('input[name="street"]').val(data.estabelecimento.logradouro);
            $('#addressForm').find('input[name="number"]').val(data.estabelecimento.numero);
            $('#addressForm').find('input[name="neighborhood"]').val(data.estabelecimento.bairro);
            $('#addressForm').find('input[name="complement"]').val(data.estabelecimento.complemento);
            $('#addressForm').find('input[name="city"]').val(data.estabelecimento.cidade.nome);
            $('#addressForm').find(`select[name="state"] option[value="${data.estabelecimento.estado.sigla}"]`).prop('selected', true);
            $('#addressForm').find(`select[name="country"] option[value="${data.estabelecimento.pais.iso2}"]`).prop('selected', true);
            $('.selectpicker').selectpicker('destroy');
            $('.selectpicker').selectpicker(selectpickerConfig).selectpicker('render');
            addAddress();

            $('.selectpicker').selectpicker('destroy');
            $('.selectpicker').selectpicker(selectpickerConfig).selectpicker('render');
          });
        }
        });
      }

      $('#contractUsageHistory-modal').on('show.coreui.modal', event => {

        let lineNum = $(event.relatedTarget).attr('data-lineNum');

        $('#contractUsageHistory-table').dataTable().fnDestroy();
        let table = $("#contractUsageHistory-table").DataTable({
          processing: true,
          serverSide: true,
          responsive: true,
          destroy: true,
          ajax: {
            url: "{{ route('partners.get.contracts.usage.history') }}",
            data: function(d) {
              d.cardCode = $('input[name="code"]').val()
              d.contract = $(event.relatedTarget).attr('value')
            }
          },
          columns: [{
              name: 'codSAP',
              data: 'codSAP'
            },
            {
              name: 'code',
              data: 'code',
              render: renderRedirectButton
            },
            {
              name: 'sequenceSerial',
              data: 'sequenceSerial',
            },
            {
              name: 'username',
              data: 'name'
            },
            {
              name: 'taxDate',
              data: 'taxDate',
              render: renderFormatedDate
            },
            // {name: 'OnHand', data: 'ItemCode', render: renderWHS, orderable: false},
            {
              name: 'docTotal',
              data: 'docTotal',
              render: renderFormatedValues,
              orderable: false
            }
          ],
          lengthMenu: [5, 30, 50],
          language: dataTablesPtBr,
          drawCallback: function( settings ) {
            
            let amountUsed = 0;

            $.each(settings.json.data, function(index, value){
              amountUsed +=  roundNumber(value.docTotal);
            });

            $('#amountUsed').val(renderFormatedValues(amountUsed));
            //console.log($(`input[name="contracts[${lineNum}][amount]"]`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
            $('#initialAmount').val($(`input[name="contracts[${lineNum}][amount]"]`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
            $('#residualAmount').val($(`input[name="contracts[${lineNum}][residualAmount]"]`).val().replace(/[.]/gi, '').replace(/[,]/gi, '.'));
            setMaskMoney();

          }
        });
      });

      function renderRedirectButton(code, type, data) {
        return `<a class='text-warning' href='{{ route("purchase.ap.invoice.read") }}/${data.id}' target="_blank">
                    <svg class="icon icon-xl">
                      <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                    </svg>
                    </a>
                  <span class='text-default'>${code}</span>`;
      }

    function renderFormatedValues(valor) { //2 decimal cases
      return new Intl.NumberFormat('pt-BR').format(valor);
    }

    function removeLine(input) {
      var tr = $(input).closest('tr');
      tr.remove();
    }

    function removeContract(element){
      swal({
        title: `Tem certeza que deseja remove o contrato?`,
        text: `Esta operação não pode ser desfeita!`,
        icon: "warning",
        //buttons: true,
        buttons: ["Não", "Sim"],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          waitingDialog.show('Processando...');
          $.ajax({
            url: `{{ route('partners.remove-contract') }}`,
            type: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('body input[name="_token"]').val()
            },
            dataType: "json",
            data: {contract_code: $(element).attr('data-contract_code')},
            success: function(response) {
              waitingDialog.hide();
              if(response.code == 200){
                swal({
                  title: "Sucesso!",
                  text: 'O contrato foi removido com sucesso!',
                  icon: "success"
                });
  
                removeLine(element);
              }
            },
            error: function(error) {
              swal({
                title: "Erro",
                text: error.responseJSON.message,
                icon: "error"
              });
              waitingDialog.hide();
            }
          });
        }
      });
    }

  </script>
    @endsection
