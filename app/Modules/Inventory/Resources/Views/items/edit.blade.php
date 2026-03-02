@extends('layouts.main')
@section('title', 'Cadastro de item')
@section('content')

  @if (isset($item->is_locked) && $item->is_locked == 1)
    <div class="alert alert-danger">
      {{ $item->message }}
    </div>
  @endif
  <div class="row mb-1">
    <div class="col-12">
      <h3 class="header-page">Detalhes do item</h3>
    </div>
  </div>
  <hr>

  <div id="alert"></div>
  <form id="needs-validation">
    {!! csrf_field() !!}
    <input type="hidden" name="itemCode" value="{{ $item->ItemCode }}">
    <div class="row">
      <div class="col-lg-12">
        <div class="row">
          <div class="col-md-2">
            <div class="form-group">
              <label>Cod. SAP</label>
              <input type="text" maxlength="100" required placeholder="Nome" name="name" class="form-control"
                value="{{ $item->ItemCode }}" readonly>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Série de numeração</label>
              <select class="form-control selectpicker" id="numberSeries" name="numberSeries" disabled>
                @foreach ($series as $serie)
                  <option value="{{ $serie->value }}" @if ($item->Series == $serie->value) selected @endif>
                    {{ $serie->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <label for="">Status</label>
            <input type="text" class="form-control"
              value="@if ($item->validFor == 'Y') ATIVO @elseif($item->validFor == 'N') INATIVO @endif"
              disabled>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Nome</label>
              <input type="text" maxlength="100" required placeholder="Nome" name="name" class="form-control"
                value="{{ $item->ItemName }}">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Tipo</label>
              <select class="form-control selectpicker" required name="type">
                <option value="">Selecione um tipo</option>
                @foreach ($itemTypes as $type)
                  <option value="{{ $type['value'] }}" @if ($item->ItemType == $type['value']) selected @endif>
                    {{ $type['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Grupo</label>
              <select class="form-control selectpicker" required name="group">
                @foreach ($itemGroups as $itemGroup)
                  <option value="{{ $itemGroup->value }}" @if ($item->ItmsGrpCod == $itemGroup->value) selected @endif>
                    {{ $itemGroup->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Subgrupo</label>
              <select class="form-control selectpicker" required name="subGroup">
                @foreach ($subGroups as $subGroup)
                  <option value="{{ $subGroup->value }}" @if($item->FirmCode == $subGroup->value) selected @endif>{{ $subGroup->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Item de estoque</label>
              <select class="form-control selectpicker" name="is_inventory_item">
                <option value="1" @if ($item->InvntItem == 'Y') selected @endif>Sim</option>
                <option value="0" @if ($item->InvntItem == 'N') selected @endif>Não</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Item de venda</label>
              <select class="form-control selectpicker" name="is_sales_item">
                <option value="1" @if ($item->SellItem == 'Y') selected @endif>Sim</option>
                <option value="0" @if ($item->SellItem == 'N') selected @endif>Não</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Item de compra</label>
              <select class="form-control selectpicker" name="is_purchase_item">
                <option value="1" @if ($item->PrchseItem == 'Y') selected @endif>Sim</option>
                <option value="0" @if ($item->PrchseItem == 'N') selected @endif>Não</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label for="need-approval">Necessita de aprovação</label>
              <select class="form-control selectpicker" name="needApproval">
                <option @if (!empty($item->U_R2W_APROVAITEM) && $item->U_R2W_APROVAITEM == 'Y') selected @endif value="Y">Sim</option>
                <option @if (!empty($item->U_R2W_APROVAITEM) && $item->U_R2W_APROVAITEM == 'N') selected @endif value="N">Não</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Lista de preço</label>
              <select class="form-control selectpicker" id="priceList" name="priceList" onchange="changePriceList()">
                @foreach ($prices as $price)
                  <option value="{{ $price->PriceList }}" data-price="{{ number_format($price->Price, 2) }}"
                    @if ($price->Price > 0) selected @endif>{{ $price->ListName }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Preço</label>
              <input type="text" name="price" id="price" class="form-control moneyPlus price"
                onclick='destroyMask(event)' onblur="setMaskMoney();focusBlur(event)">
            </div>
          </div>
        </div>
        <div class="col-12 mt-2">
          <a class="btn btn-primary" onclick="printMovement()">Movimentação de estoque</a>
          <a class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#lastProviders">Últimos fornecedores</a>
        </div>
        <hr>
      </div>
      <div class="col-md-12">
        <div class="tabs-container">
          <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-1">
              <a class="nav-link active" data-toggle="tab" href="#tab-1">Geral</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-2">
              <a class="nav-link" data-toggle="tab" href="#tab-2">Dados de compra</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-3">
              <a class="nav-link" data-toggle="tab" href="#tab-3">Dados de venda</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-4">
              <a class="nav-link" data-toggle="tab" href="#tab-4">Dados de estoque</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-5">
              <a class="nav-link" data-toggle="tab" href="#tab-5">Características</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-8">
              <a class="nav-link" data-toggle="tab" href="#tab-8">Dados de planejamento</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-7">
              <a class="nav-link" data-toggle="tab" href="#tab-7">Adicionais</a>
            </li>
            <li class="nav-item" role="presentation" data-coreui-toggle="tab" data-coreui-target="#tab-6">
              <a class="nav-link" data-toggle="tab" href="#tab-6">Observações</a>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-1">
              <div class="panel-body mt-4">
                <div class="row mb-3">
                  <div class="form-group">
                    <label class="fw-bold">Classificação do item</label>
                    <div class="col-md-6">
                      <label class="me-3">
                        <input type="radio" name="classification" onclick="changeClassification()" value="1"
                          @if ($item->ItemClass == 1) checked @endif required> Serviço
                      </label>
                      <label>
                        <input type="radio" checked name="classification" onclick="changeClassification()"
                          value="2" @if ($item->ItemClass == 2) checked @endif required> Material
                      </label>
                    </div>
                  </div>
                </div>
                <div id="info-material" style="display: none" class="row">
                  <h5>Informações para classificação: Material</h5>
                  <div class="col-md-6">
                    <label>Fonte do produto</label>
                    <select class="form-control selectpicker" name="source">
                      <option value="">Selecione</option>
                      @foreach ($productSources as $src)
                        <option value="{{ $src->value }}" @if ($item->ProductSrc == $src->value) selected @endif>
                          {{ $src->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código NCM</label>
                      <select class="form-control selectpicker with-ajax-ncm" name="ncm">
                        @if (!empty($ncm))
                          <option value="{{ $ncm->value }}">{{ $ncm->name }}</option>
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código CEST</label>
                      <select class="form-control selectpicker" name="cest">
                        <option></option>
                        @foreach ($cests as $cest)
                          
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Grupo de materiais</label>
                      <select class="form-control selectpicker" name="materials_group">
                        <option value="">Selecione</option>
                        @foreach ($materialGroups as $matGrp)
                          <option value="{{ $matGrp->value }}" @if ($item->MatGrp == $matGrp->value) selected @endif>
                            {{ $matGrp->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Tipo de material</label>
                      <select class="form-control selectpicker" name="material_type" required>
                        <option value="">Selecione o tipo de material</option>
                        @foreach ($materialTypes as $matType)
                          <option value="{{ $matType['value'] }}" @if ($item->MatType == $matType['value']) selected @endif>
                            {{ $matType['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>

                <div id="info-service" class="row">
                  <h5>Informações para classificação: Serviço</h5>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código do serviço prestado</label>
                      <select class="form-control selectpicker" name="service_code">
                        <option value="-1">Selecione</option>
                        @foreach ($serviceCodes as $code)
                          <option value="{{ $code->value }}" @if ($item->OSvcCode == $code->value) selected @endif>
                            {{ $code->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código do serviço contratado</label>
                      <select class="form-control selectpicker" name="contracted_service_code">
                        <option value="-1">Selecione</option>
                        @foreach ($serviceCodesContrated as $code)
                          <option value="{{ $code->value }}" @if ($item->ISvcCode == $code->value) selected @endif>
                            {{ $code->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-2">
              <div class="panel-body mt-4">
                <div class="row">

                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de compras</label>
                      <select class="form-control selectpicker" name="purchase_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $um)
                          <option value="{{ $um['value'] }}" @if ($item->BuyUnitMsr == $um['value']) selected @endif>
                            {{ $um['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Itens por unidade de compra</label>
                      <input type="text" class="form-control qtd" name="numInBuy"
                        value="{{ number_format($item->NumInBuy, 3) }}" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de compras (embalagem)</label>
                      <select class="form-control selectpicker" name="purchase_package_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $um)
                          <option value="{{ $um['value'] }}" @if ($item->PurPackMsr == $um['value']) selected @endif>
                            {{ $um['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Quantidade por U.M. embalagem</label>
                      <input type="text" class="form-control qtd" name="purPackUn"
                        value="{{ number_format($item->PurPackUn, 3) }}" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Fornecedor preferencial</label>
                      <select class="form-control selectpicker with-ajax-suppliers" name="preferred_supplier">
                        @if (isset($preferenceSuplier) && !is_null($preferenceSuplier))
                          <option value="">{{ $preferenceSuplier }}</option>
                        @endif
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-3">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de vendas</label>
                      <select class="form-control selectpicker" name="sales_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $um)
                          <option value="{{ $um['value'] }}" @if ($item->SalUnitMsr == $um['value']) selected @endif>
                            {{ $um['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Itens por unidade de venda</label>
                      <input type="text" class="form-control qtd" name="numInSale"
                        value="{{ number_format($item->NumInSale, 3) }}" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de vendas (embalagem)</label>
                      <select class="form-control selectpicker" name="sales_package_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $um)
                          <option value="{{ $um['value'] }}" @if ($item->SalPackMsr == $um['value']) selected @endif>
                            {{ $um['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Quantidade por U.M. embalagem</label>
                      <input type="text" class="form-control qtd" name="salPackUn"
                        value="{{ number_format($item->SalPackUn, 3) }}" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-4">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Definir contas contábeis por</label>
                      <select class="form-control selectpicker" name="gl_method">
                        <option value="">Selecione</option>
                        @foreach ($glMethods as $method)
                          <option value="{{ $method['value'] }}" @if ($item->GLMethod == $method['value']) selected @endif>
                            {{ $method['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Depósito padrão</label>
                      <select class="form-control selectpicker" name="default_warehouse">
                        <option value="">Selecione</option>
                        @foreach ($warehouses as $whs)
                          <option value="{{ $whs->value }}" @if ($item->DfltWH == $whs->value) selected @endif>
                            {{ $whs->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de estoque</label>
                      <select class="form-control selectpicker" name="inventory_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $um)
                          <option value="{{ $um['value'] }}" @if ($item->InvntryUom == $um['value']) selected @endif>
                            {{ $um['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <hr>
                <div class="row pre-scrollable">
                  <div class="table-responsive">
                    <table id="requiredTable" class="table table-default table-striped table-bordered table-hover dataTables-example"
                      style="width: 100%;">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Cod. SAP</th>
                          <th>Nome</th>
                          <th>Custo do item</th>
                          <th>Bloqueado</th>
                          <th>Em estoque</th>
                          <th>Confirmado</th>
                          <th>Pedido de compra</th>
                          <th>Estoque mínimo</th>
                          <th>Estoque máximo</th>
                          <th>Conta de despesas</th>
                          <th>Conta de devoluções</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $cont = 1; ?>
                        @foreach ($warehouse as $key => $value)
                          <tr id="rowTable-{{ $cont }}" value="{{ $value->WhsCode }}"
                            data-row="{{ $cont }}">
                            <td>
                              {{ $cont }}
                            </td>
                            <td>
                              {{ $value->WhsCode }}
                            </td>
                            <td>
                              <p @if ($item->DfltWH == $value->WhsCode) style="font-weight: bold" @endif>
                                {{ $value->WhsName }}</p>
                            </td>
                            <td>
                              {{ number_format($value->AvgPrice, 2, ',', '.') }}
                            </td>
                            <td>
                              {{ $value->Locked }}
                            </td>
                            <td>
                              {{ number_format($value->OnHand, 3, ',', '.') }}
                            </td>
                            <td>
                              {{ number_format($value->IsCommited, 3, ',', '.') }}
                            </td>
                            <td>
                              {{ number_format($value->OnOrder, 3, ',', '.') }}
                            </td>
                            <td>
                              {{ number_format($value->MinStock, 3, ',', '.') }}
                            </td>
                            <td>
                              {{ number_format($value->MaxStock, 3, ',', '.') }}
                            </td>
                            <td>
                              {{ $value->ExpensesAc }}
                            </td>
                            <td>
                              {{ $value->ReturnAc }}
                            </td>
                            <?php $cont++; ?>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-5">
              <div class="panel-body pre-scrollable">
                <div class="table-responsive mt-4">
                  <table id="table" class="table table-default table-striped table-bordered table-hover">
                    <thead>
                      <th style="width: 90%">Nome</th>
                      <th style="width: 10%">Selecionar</th>
                    </thead>
                    <tbody>
                      @foreach ($itemProperties as $index => $property)
                      <?php $qryGroup = 'QryGroup' . $property->value; ?>
                        <tr>
                          <td>{{ $property->name }}</td>
                          <td class="text-center">
                            <input type="checkbox" name="itemProperties[{{ $property->value }}]"
                              @if ($item->$qryGroup == 'Y') checked @endif style="width: 20px; height: 20px;">
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                
              </div>
            </div>
            <div class="tab-pane" id="tab-6">
              <div class="panel-body mt-4">
                <div class="form-group">
                  <label for="">Observações</label>
                  <textarea class="form-control" name="comments">{{ $item->UserText }}</textarea>
                </div>
              </div>
            </div>
            <!--
            <div class="tab-pane" id="tab-7">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-2 form-group">
                    <label for="">Genero Item</label>
                    <input type="text" name="item_genre" id="" class="form-control"
                      value='' maxlength="2">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">Tipo</label>
                    <input type="text" name="item_type" id="" class="form-control"
                      value='' maxlength="2">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CFOP</label>
                    <input type="text" name="cfop" id="" class="form-control"
                      value='' maxlength="6">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">Revisado</label>
                    <select name="revised" id="" class="form-control selectpicker">
                      <option value="S">SIM</option>
                      <option value="N">NÂO</option>
                    </select>
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CFOP dentro do estado</label>
                    <input type="text" name="cfop_inside_state" id="" class="form-control"
                      value='' maxlength="6">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CFOP fora do estado</label>
                    <input type="text" name="cfop_outside_state" id="" class="form-control"
                      value='' maxlength="6">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST ICMS</label>
                    <input type="text" name="cst_icms" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST PIS</label>
                    <input type="text" name="cst_pis" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST COFINS</label>
                    <input type="text" name="cst_cofins" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST ICMS Saída</label>
                    <input type="text" name="cst_icms_output" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST PIS Saída</label>
                    <input type="text" name="cst_pis_output" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST COFINS Saída</label>
                    <input type="text" name="cst_cofins_output" id="" class="form-control"
                      value='' maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">Monofásico</label>
                    <input type="text" name="monofasico" id="" class="form-control"
                      value='' maxlength="6">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">AS INTEGRADO</label>
                    <select name="as_integrado" id="" class="form-control selectpicker">
                      <option value="S">SIM</option>
                      <option value="N">NÂO</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            -->
            <div class="tab-pane" id="tab-8">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-3">
                    <label for="">Método de planejamento</label>
                    <select name="planingSys" id="planingSys" class="form-control selectpicker">
                      <option value="">Selecione</option>
                      <option value="0" @if ($item->PlaningSys === 'M') selected @endif>MRP</option>
                      <option value="1" @if ($item->PlaningSys === 'N') selected @endif>Nenhum</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="">Método de suprimento</label>
                    <select name="prcrmntMtd" id="prcrmntMtd" class="form-control selectpicker"
                      onchange="changeCompoWH(event)">
                      <option value="">Selecione</option>
                      <option value="0" @if ($item->PrcrmntMtd === 'B') selected @endif>Comprar</option>
                      <option value="1" @if ($item->PrcrmntMtd === 'M') selected @endif>Produzir</option>
                    </select>
                  </div>
                  <div class="col-md-3" @if ($item->PrcrmntMtd === 'B') style="display: none;" @endif>
                    <label for="">Depósito de componente</label>
                    <select name="compoWH" id="compoWH" class="form-control selectpicker">
                      <option value="">Selecione</option>
                      <option value="0" @if ($item->CompoWH === 'B') selected @endif>Da linha da estrutura de
                        produtos</option>
                      <option value="1" @if ($item->CompoWH === 'P') selected @endif>Da linha do documento do
                        item pai</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @if (checkAccess('inventory_items_edit'))
      <div class="col-md-12 mt-4">
        <button class="btn btn-primary float-end" type="submit" onclick="validateTabs()">Salvar</button>
        <button id="duplicateBtn" class="btn btn-success float-end me-1" type="button"
          onclick="duplicate(event)">Duplicar</button>
      </div>
    @endif
  </form>

  <div class="modal fade" id="printMovement" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Gerar documento de movimentação do item</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>    
        </div>
        <div class="modal-body">
          <form action="{{ route('inventory.items.movement') }}" method="GET">
            <input type="hidden" name="itemCode" value="{{ $item->ItemCode }}">
            <div class="row">
              <div class="col-md-6">
                <label for="">Data inicial</label>
                <input type="date" class="form-control" name="initialDate" required>
              </div>
              <div class="col-md-6">
                <label for="">Data final</label>
                <input type="date" class="form-control" name="lastDate" required>
              </div>
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-primary">Gerar</button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="lastProviders" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Últimos fornecedores</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>    
        </div>
        <div class="table-responsive">
                <table id="requiredTable"
                  class="table table-default table-striped table-bordered table-hover dataTables-example"
                  style="width: 100%;">
                  <thead>
                    <tr>
                      <th style="width: 3%">#</th>
                      <th style="width: 10%">Cod. SAP NFE</th>
                      <th>Ult. Fornecedor</th>
                      <th style="width: 10%">Data</th>
                      <th style="width: 10%">Preço Unit.</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if (isset($lastProviders))
                      <?php $cont = 1;
                      $docTotal = 0; ?>
                      @foreach ($lastProviders as $key => $value)
                        <tr id="rowTable-{{ $cont }}"
                          data-row="{{ $cont }}">
                          <td>
                            {{ $cont }}
                          </td>
                          <td>
                            {{$value['DocEntry']}}
                          </td>
                          <td style="max-width: 16em;">
                            <div class="d-flex flex-row" style="max-width: 100%;">
                              <a class="text-warning" href="{{ route('partners.edit', $value['CardCode']) }}" target="_blank">
                                <svg class="icon icon-lg">
                                  <use xlink:href="{{ asset('icons_assets/custom.svg#right-arrow') }}"></use>
                                </svg>
                              </a>
                              <span class="text-truncate text-tooltip" data-coreui-toggle="tooltip"
                                title="{{ $value['CardCode'] }} - {{ $value['CardName'] }}">{{ $value['CardCode'] }} -
                                {{ $value['CardName'] }}</span>
                            </div>
                          </td> 
                          <td>
                            {{formatDate($value['TaxDate'])}}
                          </td>
                          <td>
                          <input type='text' class='form-control money min-100 locked'
                            value="{{ number_format($value['Price'], 2, ',', '.') }}"
                            name='requiredProducts[{{ $cont }}][Price]' id="Price-{{ $cont }}"
                            readonly>
                          </td>
                          <?php $cont++; ?>
                        </tr>
                      @endforeach
                      <input type="hidden" value="{{ $cont - 1 }}" id="cont">
                    @endif
                  </tbody>
                </table>
              </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-white" data-coreui-dismiss="modal">Fechar</button>
        </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="application/javascript">

        let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    
        function changePriceList() {
            var price = parseFloat($('#priceList').find(":selected").attr("data-price"));
            $("#price").val(price);
            $("#price").maskMoney('destroy');
            $("#price").focus();
        }

        function changeClassification() {
            // Service
            if ($("[name='classification']:checked").val() === '1') {
                $("#info-service").show();
                $("#info-material").hide();
                $("[name='source']").prop('required', false);
                $("[name='material_type']").prop('required', false);
            } else {
                $("#info-service").hide();
                $("#info-material").show();
                $("[name='source']").prop('required', true);
                $("[name='material_type']").prop('required', true);
            }
        }

        function setMaskMoney() {
            $('.qtd').maskMoney({precision: 3, decimal: ',', thousands:'.', selectAllOnFocus: true});
            $('.money').maskMoney({precision: 2, decimal: ',', thousands:'.', selectAllOnFocus: true});
            $('.moneyPlus').maskMoney({precision: 4, decimal: ',', thousands:'.', selectAllOnFocus: true});
            $.each($('input[class *="money"], input[class *="qtd"], input[class *="moneyPlus"]'), function(index, value){
                $(value).trigger('mask.maskMoney')
            })
        }

        function destroyMask(event){
            $(event.target).maskMoney('destroy')
            $(event.target).select()
        }
        function focusBlur(event){
            $(event.target).trigger('mask.maskMoney')
        }

        selectpicker.filter('.with-ajax-ncm')
            .ajaxSelectPicker(getAjaxSelectPickerOptions("{{route('inventory.items.ncmSearch')}}"));
        selectpicker.filter('.with-ajax-cest')
            .ajaxSelectPicker(getAjaxSelectPickerOptions("{{route('inventory.items.cestSearch')}}"));
        selectpicker.filter('.with-ajax-suppliers')
            .ajaxSelectPicker(getAjaxSelectPickerOptions("{{route('inventory.items.suppliersSearch')}}"));

        changePriceList();
        changeClassification();

        /**
         * Change tab if input is invalid
         */
        function validateTabs() {
            var input = $("#needs-validation").find("*:invalid").first();
            var tabPane = input.closest('.tab-pane').first();

            if(tabPane.length === 1) {
                $('#myTabs').find('a[href="#' + tabPane.attr('id') + '"]').tab('show');
            }
        }

        function printMovement(){

            $("#printMovement").modal("show");
        }

        $('#needs-validation').submit(function(event){
            event.preventDefault();
            waitingDialog.show('Processando...');
            
            var form_data = new FormData($('#needs-validation')[0]);
        
            $.ajax({
                type: 'post',
                url: "{{route('inventory.items.store')}}",
                headers: {
                'X-CSRF-TOKEN': $('body input[name="_token"]').val()
                },
                data: form_data,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response){
                    waitingDialog.hide();
                    if(response.status == 'success' && response.key){
                        swal({
                            title: `Item ${response.key} atualizado com sucesso!`,
                            text: "Deseja atualizar a pagina?",
                            icon: "success",
                            buttons: ["Não", "Sim"]
                        })
                        .then((redirect) => {
                            if (redirect) {
                                window.location.href = "{{route('inventory.items.edit')}}/"+response.key
                            }
                        });
                    }else{
                        waitingDialog.hide();
                        swal({
                            title: "Opss...",
                            text: response.message,
                            icon: "error",
                            buttons: ["Fechar"],
                        });
                    }
                },
                error: function(response){
                    waitingDialog.hide();
                    swal({
                        title: "Opss...",
                        text: "Ocorreu um erro, tente novamente.",
                        icon: "error",
                        buttons: ["Fechar"],
                    })
                }
            })
        });

        function duplicate(event){
            event.preventDefault();
            swal({
                title: "Tem certeza que deseja duplicar o item?",
                text: "Após confirmar não há como desfazer a ação!",
                icon: "warning",
                //buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
            })
            .then((confirm) => {
                if (confirm) {
                    $('input[name="itemCode"]').remove();
                    $('select').removeAttr('disabled');
                    $('.selectpicker').selectpicker('refresh');
                    $('#duplicateBtn').remove();
                    $('#alert').append($(`
                        <div class="alert alert-danger" role="alert">
                            <p>Modo de duplicação de item está ativo!</p>
                        </div>`))
                }
            });
        }

        function removeItem(item) {
              swal({
                  title: "Tem certeza que deseja remover?",
                  text: "Esta operação não pode ser desfeita!",
                  icon: "warning",
                  //buttons: true,
                  buttons: ["Não", "Sim"],
                  dangerMode: true,
              })
              .then((willDelete) => {
                if (willDelete) {
                     waitingDialog.show('Removendo...');
                     window.location.href = "{{route('inventory.items.remove')}}" +'/'+item;
                 }
               });
          }

          function changeCompoWH(event){
            let selectedOption = $(event.target).find(':selected').val();
            if(selectedOption == '0'){
                $('#compoWH').parent().parent().hide();
            }else{
                $('#compoWH').parent().parent().show();
            }
        }
    </script>
@endsection
