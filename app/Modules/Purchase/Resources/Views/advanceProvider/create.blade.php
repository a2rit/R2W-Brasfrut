@extends('layouts.main')
@section('title', 'Adiantamento ao fornecedor')
@section('content')

  @if (isset($head) && !empty($head->message))
    <div class="alert alert-danger">
      {{ $head->message }}
    </div>
  @endif

  <form action="{{ route('purchase.advance.provider.save') }}" method="post" id="needs-validation"
    enctype="multipart/form-data" onsubmit="waitingDialog.show('Salvando...')">
    {!! csrf_field() !!}
    <input type="hidden" @if (isset($head) && isset($head->id)) value="{{ $head->id }}" @endif name="id">
    <div class="card">
      <h5 class="card-header fw-bolder">
        @if (isset($head))
          Adiantamento de fornecedores - detalhes
        @else
          Adiantamento de fornecedores - cadastro
        @endif
      </h5>
      <div class="card-body">
        @if (isset($head))
          <div class="row">
            <div class="col-md-2">
              <label>Cod. SAP</label>
              <input type="text" readonly value="{{ $head->codSAP }}" class="form-control">
            </div>
            <div class="col-md-2">
              <label>Cod. WEB</label>
              <input type="text" readonly value="{{ $head->code }}" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Usuário</label>
              <input type="text" readonly value="{{ $head->name }}" class="form-control">
            </div>
            <div class="col-md-2">
              <label>Status</label>
              @if (isset($head))
                <input type="text" readonly value="{{ $head::TEXT_STATUS[$head->status] }}" class="form-control">
              @endif
            </div>
          </div>
        @endif
        <div class="row mt-2">
          <div class="col-md-6">
            <label for="cardCode">Parceiro de negócios</label>
            <select id="cardCode" class="form-control selectpicker with-ajax-suppliers" data-size="10" data-width="100%"
              name="cardCode" @if (isset($head)) disabled @endif required>
              @if (isset($head))
                <option value="{{ $head->cardCode }}" selected>{{ $head->CardName }}</option>
              @endif
            </select>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-2">
            <div class="form-group">
              <label>Data do Documento</label>
              <input name="dataDocumento" id="dataDocumento" class="form-control"
                @if (isset($head['taxDate'])) type="text" value="{{ formatDate($head['taxDate']) }}"
                                                @else type="date" value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Data de Lançamento</label>
              <input name="dataLancamento" class="form-control"
                @if (isset($head['docDate'])) type="text" value="{{ formatDate($head['docDate']) }}"
                                                @else type="date" value="{{ DATE('Y-m-d') }}" @endif
                required>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Data de vencimento</label>
              <input name="dataVencimento" id="dataVencimento" class="form-control"
                @if (isset($head['docDueDate'])) @if ($head->status == $head::STATUS_OPEN) type="date" value="{{ $head['docDueDate'] }}" @else type="text" value="{{ formatDate($head['docDueDate']) }}" @endif
              @else type="date" value="{{ DATE('Y-m-d') }}" @endif
              required>
            </div>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-3">
            <div class="form-group">
              <label>Condição de Pagamento</label>
              <select class="form-control selectpicker" data-live-search="true" required data-size="10"
                name="condPagamentos" id="conditionPagamentos" onChange="getDueDate()"
                @if (isset($head)) readonly @endif>
                <option value=''>Selecione</option>
                @foreach ($paymentConditions as $key => $value)
                  <option value="{{ $value['GroupNum'] }}" @if (isset($head['paymentCondition']) && $head['paymentCondition'] == $value['GroupNum']) selected @endif>
                    {{ $value['PymntGroup'] }}</option>
                @endForeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="tabs-container container-fluid">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
            <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
            <a class="nav-link" data-toggle="tab" href="#tab-3">Pagamento</a>
          </li>
          <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
            <a class="nav-link" data-toggle="tab" href="#tab-2">Anexos</a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="tab-1">
            <div class="panel-body mt-4">
              <a href='#' class='text-dark text-tooltip' data-coreui-placement="right" title="Adicionar linha"
                onclick="loadingItems()" @if (!empty($head)) style="display: none;" @endif>
                <svg class="icon icon-xxl">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                </svg>
              </a>
              <div class="table-responsive mt-1">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <tr>
                      <th style="width: 5%;">#</th>
                      <th>Descrições</th>
                      <th>Quantidade</th>
                      <th>Preço Unitário</th>
                      <th>Total</th>
                      <th>Projeto</th>
                      <th>C. de Custo</th>
                      <th>C. de Custo2</th>
                      <th>Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if (isset($body))
                      <?php $cont = 1; ?>
                      @foreach ($body as $key => $value)
                        <tr id="rowTable-{{ $cont }}">
                          <td>
                            {{ $cont }}
                          </td>
                          <td style="max-width: 16em;">
                            <div class="d-flex flex-row" style="max-width: 100%;">
                              <a class="text-warning" href="{{ route('inventory.items.edit', $value->itemCode) }}"
                                target="_blank">
                                <svg class="icon icon-lg">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                </svg>
                              </a>
                              <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                title="{{ $value->itemCode }} - {{ $value->itemName }}">{{ $value->itemCode }} -
                                {{ $value->itemName }}</span>
                            </div>
                            <input type='hidden' value="{{ $value->itemName }}"
                              name="requiredProducts[{{ $cont }}][itemName]">
                            <input type='hidden' value="{{ $value->itemCode }}"
                              name="requiredProducts[{{ $cont }}][itemCode]">
                          </td>
                          <td>
                            <div class="input-group min-200 mt-1">
                              <div class="input-group-text">{{ $value['itemUnd'] ?? ' ' }}</div>
                              <input value="{{ number_format($value->quantity, 3, ',', '.') }}"
                                onclick='destroyMask(event)'
                                onblur="gerarTotal({{ $cont }})"
                                id="qtd-{{ $cont }}" type="text" class="form-control qtd min-100"
                                name="requiredProducts[{{ $cont }}][qtd]" min="1">
                              <input type='hidden' value='{{ $value->itemUnd }}'
                                name='requiredProducts[{{ $cont }}][itemUnd]'>
                            </div>
                          </td>
                          <td>
                            <input value="{{ number_format($value->price, 3, ',', '.') }}" onclick='destroyMask(event)'
                              onblur="gerarTotal({{ $cont }})"
                              id="price-{{ $cont }}" type="text" class="form-control moneyPlus min-100"
                              name="requiredProducts[{{ $cont }}][preco]" min="1">
                          </td>
                          <td>
                            <input value="{{ number_format($value->quantity * $value->price, 2, ',', '.') }}"
                              onclick="setMaskMoney()" onblur="gerarTotal({{ $cont }})"
                              id="totalLinha-{{ $cont }}" type="text"
                              class="form-control money min-100 locked"
                              name="requiredProducts[{{ $cont }}][totalLine]">
                          </td>
                          <td>
                            <select style="width: 150px;" class='form-control selectpicker'
                              id='project-{{ $cont }}' name='requiredProducts[{{ $cont }}][projeto]'
                              data-container="body" required>
                              <option value=''>Selecione</option>
                              @foreach ($projeto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value->project) selected @endif>{{ $values['value'] }} -
                                  {{ substr($values['name'], 0, 15) }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select style="width: 150px;" class='form-control selectpicker'
                              id='role-{{ $cont }}' name='requiredProducts[{{ $cont }}][costCenter]'
                              data-container="body" required onchange='validLine({{ $cont }})'>
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value->distrRule) selected @endif>{{ $values['value'] }}
                                  - {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          <td>
                            <select style="width: 150px;" class='form-control selectpicker'
                              id='role2-{{ $cont }}' name='requiredProducts[{{ $cont }}][costCenter2]'
                              data-container="body">
                              <option value=''>Selecione</option>
                              @foreach ($centroCusto2 as $keys => $values)
                                <option value="{{ $values['value'] }}"
                                  @if ($values['value'] == $value->distrRule2) selected @endif>{{ $values['value'] }}
                                  - {{ $values['name'] }}</option>
                              @endForeach
                            </select>
                          </td>
                          @if (isset($head) && empty($head->DocNum))
                            <td class="text-center">
                              <a class="text-danger text-tooltip" title="Remover linha" onclick="removeLinha(this);"
                                type="button">
                                <svg class="icon icon-xl">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                </svg>
                              </a>
                            </td>
                          @else
                            <td></td>
                          @endif
                          <?php $cont++; ?>
                        </tr>
                      @endforeach
                      <input type="hidden" value="{{ $cont - 1 }}" id="cont">
                    @endif
                  </tbody>
                </table>
              </div>
              <div class="card mt-4">
                <h6 class="card-header fw-bolder">Atalhos</h6>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <label>Projeto Principal</label>
                      <select class='form-control selectpicker' name='project' id='projectGlobal'
                        onchange="setProjecFull()">
                        <option value=''>Selecione</option>
                        @foreach ($projeto as $keys => $values)
                          <option value="{{ $values['value'] }}">{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Centro de Custo Principal</label>
                      <select class='form-control selectpicker' @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif
                        name='role' id='roleGlobal' onchange="setRoleFull()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Centro de Custo 2 Principal</label>
                      <select class='form-control selectpicker' @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif
                        name='role2' id='roleGlobal2' onchange="setRoleFull2()">
                        <option value=''>Selecione</option>
                        @foreach ($centroCusto2 as $keys => $values)
                          <option value='{{ $values['value'] }}'>{{ $values['value'] }}
                            - {{ $values['name'] }}</option>
                        @endForeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-3">
            <div class="row">
              <div class="tabs-container container-fluid">
                <ul class="nav nav-tabs" id="modalPayment" role="tablist">
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#Mtab-2">
                    <a class="nav-link" data-toggle="tab" href="#Mtab-2">Transferência</a>
                  </li>
                  <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#Mtab-4">
                    <a class="nav-link active" data-toggle="tab" href="#Mtab-4">Dinheiro</a>
                  </li>
                </ul>

                <div class="tab-content mt-3" id="myTabContent">
                  <div class="tab-pane" id="Mtab-2">
                    <div class="panel-body">
                      <div class="card mt-4">
                        <h6 class="card-header fw-bolder">Transferência</h6>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Conta Contábil</label>
                                <select class="form-control selectpicker"
                                  @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif name="conta_transferencia"
                                  id="conta_transferencia">
                                  <option readonly value=''>Selecione</option>

                                  @foreach ($accounts as $item)
                                    <option value="{{ $item['value'] }}"
                                      @if (!empty($payment->transferAccount) && $payment->transferAccount == $item['value']) selected @endif>{{ $item['name'] }}
                                    </option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Data</label>
                                <input
                                  @if (!empty($payment->transferDate)) value="{{ formatDate($payment->transferDate) }}" 
                                                                                          type="text"
                                                                                      @else
                                                                                          type="date" @endif
                                  autocomplete="off" class="form-control" name="dt_transferencia"
                                  id="dt_transferencia">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Valor</label>
                                <input type="text"
                                  @if (!empty($payment->transferSum)) value="{{ number_format($payment->transferSum, 2, ',', '.') }}" @endif
                                  class="form-control money" @if (isset($head->status))  @endif
                                  id="total_transferencia" onclick='destroyMask(event)'
                                  onblur="setMaskMoney();setTotal();focusBlur(event)" name="total_transferencia">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label>Referência</label>
                                <input type="text"
                                  @if (!empty($payment->transferReference)) value="{{ $payment->transferReference }}" @endif
                                  class="form-control" @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif
                                  name="referencia_transferencia" id="referencia_transferencia">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane show active" id="Mtab-4">
                    <div class="panel-body">
                      <div class="card mt-4">
                        <h6 class="card-header fw-bolder">Dinheiro</h6>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-4">
                              <div class="form-group">
                                <label>Conta Contábil</label>
                                <select class="form-control selectpicker"
                                  @if (isset($head) && $head->status != $head::STATUS_OPEN) readonly @endif name="conta_dinheiro"
                                  id="conta_dinheiro">
                                  <option value="">Selecione</option>
                                  @foreach ($accounts as $item)
                                    <option value="{{ $item['value'] }}"
                                      @if (isset($payment->cashAccount) && $payment->cashAccount == $item['value']) selected @endif>{{ $item['name'] }}
                                    </option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group">
                                <label>Valor</label>
                                <input type="text" class="form-control money"
                                  @if (!empty($payment->cashAccount)) value='{{ number_format($payment->cashSum, 2, ',', '.') }}' @endif
                                  id='total_dinheiro' onclick='destroyMask(event)'
                                  onblur="setMaskMoney();setTotal();focusBlur(event)" name="total_dinheiro">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-2">
            <div class="panel-body">
              <div class="card mt-4">
                <div class="card-body">
                  <div class="col-md-12">
                    <label>Observação</label>
                    <textarea class="form-control" rows="5" name="observacoes" maxlength="200">
@if (isset($head->comments)){{ $head->comments }}@endif
</textarea>
                  </div>
                </div>
              </div>
              <div class="row mt-4">
                <div class="col-md-6 mt-2">
                  <div class="table-responsive">
                    <table class="table table-default table-striped table-bordered table-hover dataTables-example">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Anexo</th>
                          <th>Visualizar</th>
                          <th>Remover</th>
                        </tr>
                      </thead>
                      <tbody>

                        @if (isset($upload))
                          <?php $upCont = 1; ?>
                          @foreach ($upload as $key => $item)
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
                                <a class="btn btn-danger" type="button"
                                  onclick='removeUpload({{ $item->id }},{{ $item->idReference }})'>
                                  <svg class="icon">
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
                    @if (isset($head))
                      <a class="btn btn-primary w-25 me-2" onclick="updateUploads(event)">Atualizar anexos</a>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mt-4">
            <h6 class="card-header fw-bolder">Totais</h6>
            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Total sem descontos</label>
                    <input type="text" class="form-control money locked" value="" readonly
                      id="totalSemDesconto" name="totalSemDesconto">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Total a adiantar</label>
                    <input type="text" name="totalAdiantado" id="advancePercent" class="form-control money" onclick='destroyMask(event)'
                      onblur="sumAllValues();"
                      value="@if (!empty($head)) {{ number_format($head['DocTotal'] * $head['DpmPrcnt'], 2, ',', '.') }} @endif">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Valor aplicado</label>
                    <input type="text" class="form-control money locked"
                      value="@if (!empty($payment)) {{ $payment['CashSum'] + $payment['TrsfrSum'] }} @endif"
                      readonly id="totalPago">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Total a pagar</label>
                    <input type="text" class="form-control locked" name="docTotal"
                      @if (isset($head)) value="{{ number_format($head['DocTotal'] * $head['DpmPrcnt'], 2, ',', '.') }}" @endif
                      readonly id="totalNota">
                    <input type="hidden" class="form-control"
                      @if (isset($head)) value="{{ number_format($head['DocTotal'] * $head['DpmPrcnt'], 2, ',', '.') }}" @endif
                      id="docTotal">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Saldo</label>
                    <input type="text" class="form-control money locked" readonly id="saldo">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12 mt-5">
      @if (isset($head))
        @if ($head->status == $head::STATUS_OPEN)
          <button class="btn btn-primary float-end" onclick="valide(this)">Atualizar</button>
        @endif
        @if (
            $head->status != $head::STATUS_REFUND &&
                !empty($payment) &&
                !empty($payment->codSAP) &&
                $payment->status == $payment::STATUS_OPEN)
          <button class="btn btn-warning float-end" type="button" onclick="refund()">Estornar pagamento</button>
        @endif
      @else
        <button class="btn btn-primary float-end" type="button" id="btn-save" onclick="valide(this)">Salvar</button>
      @endif
    </div>
  </form>
  <div class="modal inmodal" id="itensModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lista de itens</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive mt-3 ">
            <table id="table" class="table table-bordered table-hover" style="width: 100%;">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Cod. SAP</th>
                  <th style="width: 55%">Descrições</th>
                  <th style="width: 15%">Qtd. Estoque</th>
                  <th style="width: 10%">Opções</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
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

  <div class="modal inmodal" id="modalWHS" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title">Informações de depósitos</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <div class="table-responsive mt-1 table-default">
            <table id="tableWHS" class="table table-hover table-bordered">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%">Codigo</th>
                  <th style="width: 75%">Depósito</th>
                  <th style="width: 20%">quantidade</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
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
  <script type="text/javascript">
    var selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    selectpicker.filter('.with-ajax-suppliers')
      .ajaxSelectPicker(getAjaxSelectPickerOptions("{{ route('inventory.items.suppliersSearch') }}"));

    $(document).ready(function() {
      fillTopNav(
        @if (isset($head))
          @json($head->getTopNavData())
        @else
          @json($advance_provider_model->getTopNavData())
        @endif
      );
      setMaskMoney();

      @if (isset($head))
        @if ($head->status != $head::STATUS_OPEN)
          $.each($('#needs-validation').find('input, select'), function(index, element) {
            $(element).attr('disabled', true).selectpicker('refresh');
            $(element).addClass('locked');
          });
        @endif

        @if ($head->status == $head::STATUS_OPEN)
          var form = $('#needs-validation');
          form.find('input').not('#dataVencimento').not('input[type="file"]').each(function(i, item) {
            $(item).prop('readonly', true);
            $(item).addClass('locked');
          });
          form.find('select').each(function(i, item) {
            $(item).prop('disabled', true);
            $(item).selectpicker('destroy');
            $(item).selectpicker(selectpickerConfig).selectpicker('render');
          });
        @endif

        @if (!empty($head->codSAP))
          setTimeout(function() {
            var table = $('#requiredTable');
            table.find('input').each(function(i, item) {
              $(item).prop('readonly', true);
            });
            table.find('select').each(function(i, item) {
              $(item).prop('disabled', true).selectpicker('refresh');
            });
          }, 0100);
        @endif
      @endif

      setTimeout(function() {
        sumAllValues();
      }, 1000);
    });

    @if (isset($body))
      var index = $('#cont').val();
    @else
      var index = 0;
    @endif ;


    $('table').DataTable({
      language: dataTablesPtBr,
      paging: false,
      "lengthChange": false,
      "ordering": false,
      "bFilter": false,
      "bInfo": false,
      "searching": false
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

    function loadingItems() {
      $('#table').DataTable().clear()
      $("#itensModal").modal('show');
      let table = $("#table").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        ajax: {
          url: "{{ route('inventory.request.list.whs') }}",
          data: function(d) {
            d.purchase = true;
          }
        },
        columns: [{
            name: 'ItemCode',
            data: 'ItemCode'
          },
          {
            name: 'ItemName',
            data: 'ItemName'
          },
          {
            name: 'OnHand',
            data: 'ItemCode',
            render: renderWHSButton,
            orderable: false
          },
          {
            name: 'edit',
            data: 'ItemCode',
            render: renderEditButton,
            orderable: false
          }
        ],
        lengthMenu: [5, 15, 30],
        language: dataTablesPtBr
      });
    }

    function destroyMask(event) {
      $(event.target).maskMoney('destroy')
    }

    function focusBlur(event) {
      $(event.target).trigger('mask.maskMoney')
    }

    function openModalWHS(itemCode) {
      $('#tableWHS').dataTable().fnDestroy();

      $("#tableWHS").DataTable({
        searching: false,
        ajax: {
          url: "{{ route('inventory.get.whs') }}" + '/' + itemCode
        },
        columns: [{
            name: 'WhsCode',
            data: 'WhsCode'
          },
          {
            name: 'WhsName',
            data: 'WhsName'
          },
          {
            name: 'edit',
            data: 'OnHand',
            render: renderFormatedQuantity,
            orderable: false
          }
        ],
        lengthMenu: [5, 15, 30],
        language: dataTablesPtBr
      });
    }

    function renderEditButton(code) {
      return `<center>
                    <a class='text-primary' href='#' id='addItem-${code}' onclick='loadTable("${code}");' @if (isset($head->codSAP)) style="display: none;" @endif>
                      <svg class="icon icon-xl">
                        <use xlink:href="{{ asset('icons_assets/custom.svg#plus-circle') }}"></use>
                      </svg>
                    </a>
                  </center>`
    }

    function renderWHSButton(valor) {
      return `<center>
                      <a class='text-dark' href='#' onclick='openModalWHS("${valor}")' data-coreui-toggle="modal" data-coreui-target="#modalWHS" @if (isset($head->codSAP)) style="display: none;" @endif>
                        <svg class="icon icon-xl">
                          <use xlink:href="{{ asset('icons_assets/custom.svg#external-link') }}"></use>
                        </svg>
                      </a>
                    </center>`
    }

    function renderFormatedQuantity(valor) {
      return new Intl.NumberFormat('pt-BR').format(valor);
    }

    function getDueDate() {
      var codition = $('#conditionPagamentos').val();
      var date = $('#dataDocumento').val();
      $.get("{{ route('home/get/dueDate') }}" + '/' + date + '/' + codition, function(items) {
        document.getElementById('dataVencimento').value = items;
      });
    }

    function valideCode(code, mark = true) {
      if (used.indexOf(code) == '-1') {
        if (mark) {
          used.push(code);
        }
        return true;
      } else {
        return false;
      }
    }

    function updateUploads(event) {
      event.preventDefault();
      waitingDialog.show("Processando...");
      let formData = new FormData();
      $.each($('input[name="input-file-preview[]"]')[0].files, function(index, value) {
        formData.append('input-file-preview[]', value)
      })
      formData.append('table', 'advance_provider')
      formData.append('id', $('input[name="id"]').val())

      $.ajax({
          type: 'POST',
          url: "{{ route('purchase.advance.provider.updateUploads') }}",
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

    var used = new Array();

    function loadTable(code) {
      var table = $('#requiredTable');
      $('.dataTables_empty').remove();


      $.get('/getProductsSAP/' + code, function(items) {

        index++;
        if (!valideCode(index, true)) {
          index++;
        }


        var tr = $("<tr id='rowTable-" + index + "'>");
        tr.append($('<td>' + index + '</td>'));

        for (var i = 0; i < items.length; i++) {

          //4 casas decimais é definida para a classe moneyPlus
          tr.append($(`<td style="max-width: 16em;">
                          <div class="d-flex flex-row" style="max-width: 100%;">
                              <a class="text-warning" href="{{ route('inventory.items.edit') }}/${items[i].ItemCode}" target="_blank">
                                  <svg class="icon icon-lg">
                                      <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                  </svg>
                                  </a>
                              <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip" title="${items[i].ItemCode} - ${items[i].ItemName}">${items[i].ItemCode} - ${items[i].ItemName}</span>
                          </div>
                          <input type='hidden' value='${items[i].ItemName}' name='requiredProducts[${index}][itemName]' >
                          <input type='hidden' value='${items[i].ItemCode}' name='requiredProducts[${index}][itemCode]' >
                      </td>`));

          tr.append(
            $(`
              <td >
                <div class="input-group min-200 mt-1">
                  <div class="input-group-text">${items[i].BuyUnitMsr || " "}: ${renderFormatedQuantity(items[i].NumInBuy)}</div>
                  <input required onclick='destroyMask(event)' onblur='gerarTotal(${index})' id='qtd-${index}'
                    type='text' class='form-control qtd' name='requiredProducts[${index}][qtd]'>
                  <input type='hidden' value='${items[i].BuyUnitMsr || ""}' name='requiredProducts[${index}][itemUnd]'>
                </div>
              </td>`)
          );

          tr.append($("<td ><input required onclick='destroyMask(event)' onblur='gerarTotal(" +
            index + ")' id='price-" + index +
            "' value='' type='text' class='form-control moneyPlus min-100' name='requiredProducts[" + index +
            "][preco]' min='1'></td>"));

          tr.append($("<td ><input value='0'  class='form-control money min-100 locked' readonly id='totalLinha-" +
            index +
            "' onclick='destroyMask(event)' onblur='gerarTotal(" + index +
            ")' type='text'></td>"));

          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='project-" + index + "' name='requiredProducts[" +
            index +
            "][projeto]' data-container='body'  required > <option value=''>Selecione</option> @foreach ($projeto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='role-" + index + "' name='requiredProducts[" +
            index + "][costCenter]' data-container='body'  required onchange='validLine(" + index +
            ");' > <option value=''>Selecione</option> @foreach ($centroCusto as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));
          tr.append($("<td >" +
            "<select class='form-control selectpicker' id='role2-" + index + "' name='requiredProducts[" +
            index +
            "][costCenter2]' data-container='body'   > <option value=''>Selecione</option> @foreach ($centroCusto2 as $keys => $values) <option value='{{ $values['value'] }}'>{{ $values['value'] }} - {{ $values['name'] }}</option> @endForeach</select>" +
            "</td>"));

          tr.append($(`<td class='text-center'>
                                        <a class="text-danger text-tooltip" title="Remover linha" onclick='removeLinha(this);' type="button">
                                          <svg class="icon icon-xl">
                                            <use xlink:href="{{ asset('icons_assets/custom.svg#delete') }}"></use>
                                          </svg>
                                        </a>
                                      </td>`));
        }

        table.find('tbody').append(tr);
        $('.selectpicker').selectpicker(selectpickerConfig);
        gerarTotal(index);
        $('.text-truncate').tooltip();
        setTimeout(function() {
          setMaskMoney();
        }, 0500);
      });
    }

    function validLine(code) {
      var select = document.getElementById('role-' + code).value;
      let constCentersThatAllowCenterCost2 = [
        '1.0',
        '2.1',
        '2.2',
        '2.3',
        '2.4'
      ];

      if (constCentersThatAllowCenterCost2.includes(select)) {
        $('#role2-' + code).removeClass('disabled');
        $('#role2-' + code).attr('required', true);
      } else {
        $('#role2-' + code).addClass('disabled');
        $('#role2-' + code).attr('required', false);
      }
      $('#role2-' + code).selectpicker('destroy');
      $('#role2-' + code).selectpicker(selectpickerConfig).selectpicker('render');
    }

    function gerarTotal(code) {
      // clearNumber(code);

      var qtd = replaceToFloat($('#qtd-' + code).val());
      var qtd_required = parseFloat($('#qtd-' + code).attr('data-required-quantity'))
      
      if (parseFloat(qtd) > qtd_required) {
        $('#qtd-' + code).val($('#qtd-' + code).attr('data-required-quantity').replace('.', ','));
        alert('A quantidade não deve ser maior que a quantidade do item contido na solicitação de compras');
        return
      }
      
      var preco = replaceToFloat($(`#price-${code}`).val());
      var total = (qtd * preco) || 0;
      
      $(`#totalLinha-${code}`).val(total.format(2, ",", "."));

      sumAllValues();
    }

    function sumAllValues() {
      var total = 0;

      var total_dinheiro = replaceToFloat($('#total_dinheiro').val())
      var total_transferencia = replaceToFloat($('#total_transferencia').val())
      var total_adiantamento = roundNumber(parseFloat(total_dinheiro + total_transferencia)) || 0;

      for (i = 1; i <= index; i++) {
        if (document.getElementById('totalLinha-' + i)) {
          total += replaceToFloat($(`#totalLinha-${i}`).val());
        }
      }

      document.getElementById('totalSemDesconto').value = total.format(2, ",", ".");
      let totalAdiantado = replaceToFloat($('input[name="totalAdiantado"]').val())

      totalAdiantado = totalAdiantado <= 0 || totalAdiantado > total ? total : totalAdiantado;
      
      document.getElementById('advancePercent').value = totalAdiantado.format(2, ",", ".");
      document.getElementById('totalNota').value = totalAdiantado.format(2, ",", ".");
      document.getElementById('totalPago').value = (total_adiantamento).format(2, ",", ".");
      document.getElementById('saldo').value = (totalAdiantado - total_adiantamento).format(2, ",", ".");

      setMaskMoney();
    }

    function clearNumber(code) {
      var qtd = document.getElementById('qtd-' + code).value;
      var preco = document.getElementById('price-' + code).value;
      var total = document.getElementById('totalLinha-' + code).value;
      document.getElementById('qtd-' + code).value = qtd.replace(/[-]/g, '');
      document.getElementById('price-' + code).value = preco.replace(/[-]/g, '');
      document.getElementById('totalLinha-' + code).value = total.replace(/[-]/g, '');
    }

    function replaceToFloat(number_string) {
      if (number_string !== undefined) {
        let replaced_value = number_string
          .replace(/[.]/gi, "")
          .replace(/[,]/gi, ".");
        return parseFloat(replaced_value) || 0;
      }
      return 0;
    }

    function removeLinha(elemento) {
      var tr = $(elemento).closest('tr');
      tr.fadeOut(400, function() {

        tr.remove();

        setTimeout(function() {
          sumAllValues();
        }, 0600);

      });
    }

    function setUseFull() {
      var val = document.getElementById('useGlobal').value;
      var i;
      for (i = 1; i <= index; i++) {
        if (document.getElementById('use-' + i)) {
          document.getElementById('use-' + i).value = val;
        }
      }
    }

    function setProjecFull() {
      setTimeout(() => {

        let val = $('#projectGlobal').val();
        for (var i = 1; i <= index; i++) {
          if (document.getElementById('project-' + i)) {
            document.getElementById('project-' + i).value = val;
          }
        }

        $(`select[id*='project-']`).selectpicker('destroy');
        $(`select[id*='project-']`).selectpicker(selectpickerConfig).selectpicker('render');
      }, 600);
    }

    function setRoleFull() {
      var val = $('#roleGlobal').val();
      let constCentersThatAllowCenterCost2 = [
        '1.0',
        '2.1',
        '2.2',
        '2.3',
        '2.4'
      ];

      if (constCentersThatAllowCenterCost2.includes(val)) {
        $(`select[id*='role2-']`).removeClass('disabled');
        $(`select[id*='role2-']`).attr('required', true)
        $(`#roleGlobal2`).removeClass('disabled');
      } else {
        $(`select[id*='role2-']`).addClass('disabled');
        $(`select[id*='role2-']`).attr('required', false)
        $(`#roleGlobal2`).addClass('disabled');
      }

      $.each($(`select[id*='role-']`), function(index, element) {
        if ($(element)) {
          $(element).val(val);
        }
      });

      $(`select[id*='role-'], select[id*='role2-'], #roleGlobal2`).selectpicker('destroy');
      $(`select[id*='role-'], select[id*='role2-'], #roleGlobal2`).selectpicker(selectpickerConfig)
        .selectpicker('render');

    }

    function setRoleFull2() {
      var val = $('#roleGlobal2').val();

      setTimeout(() => {
        $(`select[id*='role2-']`).removeClass('disabled');
        $(`select[id*='role2-']`).attr('required', true)
        $(`#roleGlobal2`).removeClass('disabled');

        $.each($(`select[id*='role2-']`), function(index, element) {
          if ($(element)) {
            $(element).val(val);
          }
        });

        $(`select[id*='role2-'], #roleGlobal2`).selectpicker('destroy');
        $(`select[id*='role2-'], #roleGlobal2`).selectpicker(selectpickerConfig).selectpicker('render');

      }, 600);
    }

    function setTotal() {

      let totalMoney = replaceToFloat($(`#total_dinheiro`).val()) || 0;
      let totalTransfer = replaceToFloat($(`#total_transferencia`).val()) || 0;
      let totalNota = replaceToFloat($(`#totalNota`).val()) || 0;

      var total = (totalMoney + totalTransfer) || 0;

      if (total > totalNota) {
        alert("O valor pago não deve ser maior que o valor do adiantamento!");
        document.getElementById('total_dinheiro').value = 0;
        document.getElementById('total_transferencia').value = 0;
        return false;
      }

      document.getElementById('totalPago').value = total.format(2, ",", ".");
      sumAllValues();
    }

    function valide(button) {
      var erros = new Array();

      var codPN = $('#cardCode').val();
      if ($('#conditionPagamentos').val() == "") {
        erros.push('Informe a condição de pagamento! \n');
      }

      if ($('#qtd-1').val() == "") {
        erros.push('Informe a quantidade! \n');
      }
      if (codPN == '') {
        erros.push('Adicione um parceiro! \n');
      }

      if (index == '0') {
        erros.push('Adicione um item! \n');
      }

      if (erros.length > 0) {
        swal("Os seguintes erros foram encontrados: ", erros.toString(), "error");
        return;
      } else {
        $(button).attr('type', 'submit');
      }
    }

    @if (isset($head) &&
            $head->status == $head::STATUS_CLOSE &&
            !empty($payment) &&
            $payment->status == $payment::STATUS_OPEN)
      function refund() {
        swal({
            title: "Tem certeza que deseja estornar o pagamento?",
            text: "Será realizado o cancelamento do Contas a Pagar atrelado ao adiantamento e atualizado o status do mesmo na plataforma R2W!",
            icon: "warning",
            //buttons: true,
            buttons: ["Não", "Sim"],
            dangerMode: true,
          })
          .then((willRefund) => {
            if (willRefund) {
              waitingDialog.show('Processando...');
              window.location.href = "{{ route('purchase.advance.provider.refund') }}/{{ $head->payment->id }}";
            }
          });
      }
    @endif

    function addPayment() {
      var modal = $('#paymentModal');
      var form = $('#form');
      var totalMoney = document.getElementById('total_dinheiro').value;
      var conta_dinheiro = $("#conta_dinheiro option:selected").val();
      var dt_transferencia = document.getElementById('dt_transferencia').value;
      var totalTransfer = document.getElementById('total_transferencia').value;
      var referencia_transferencia = document.getElementById('referencia_transferencia').value;
      var conta_transferencia = $("#conta_transferencia option:selected").val();

      form.append($('<td><input type="hidden" name="payment[total_dinheiro]" value="' + totalMoney.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[conta_dinheiro]" value="' + conta_dinheiro.trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[dt_transferencia]" value="' + dt_transferencia.trim() +
        '">'));
      form.append($('<td><input type="hidden" name="payment[total_transferencia]" value="' + totalTransfer.trim() +
        '">'));
      form.append($('<td><input type="hidden" name="payment[referencia_transferencia]" value="' + referencia_transferencia
        .trim() + '">'));
      form.append($('<td><input type="hidden" name="payment[conta_transferencia]" value="' + conta_transferencia.trim() +
        '">'));
      modal.modal('hide');
      return false;
    }
  </script>
@endsection
