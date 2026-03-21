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
      <h3 class="header-page">Cadastro de item</h3>
    </div>
  </div>
  <hr>
  <form id="needs-validation">
    {!! csrf_field() !!}
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>Série de numeração</label>
            <select class="form-control selectpicker" id="numberSeries" name="numberSeries">
              @foreach ($series as $serie)
                <option value="{{ $serie->value }}">{{ $serie->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group"><label>Nome</label>
            <input type="text" maxlength="100" placeholder="Nome" name="name" class="form-control" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group"><label>Nome estrangeiro</label>
            <input type="text" maxlength="100" placeholder="Nome estrangeiro" name="foreign_name" class="form-control">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Tipo</label>
            <select class="form-control selectpicker" name="type">
              <option value="">Selecione um tipo</option>
              @foreach ($itemTypes as $item)
                <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Grupo</label>
            <select class="form-control selectpicker" required name="group">
              @foreach ($itemGroups as $itemGroup)
                <option value="{{ $itemGroup->value }}">{{ $itemGroup->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Subgrupo</label>
            <select class="form-control selectpicker" required name="subGroup">
              @foreach ($subGroups as $subGroup)
                <option value="{{ $subGroup->value }}">{{ $subGroup->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>Item de estoque</label>
            <select class="form-control selectpicker" name="is_inventory_item">
              <option value="1">Sim</option>
              <option selected value="0">Não</option>
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Item de venda</label>
            <select class="form-control selectpicker" name="is_sales_item">
              <option selected value="1">Sim</option>
              <option value="0">Não</option>
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Item de compra</label>
            <select class="form-control selectpicker" name="is_purchase_item">
              <option selected value="1">Sim</option>
              <option value="0">Não</option>
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="need-approval">Necessita de aprovação</label>
            <select class="form-control selectpicker" name="needApproval">
              <option selected value="Y">Sim</option>
              <option value="N">Não</option>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Lista de preço</label>
            <select class="form-control selectpicker" id="priceList" name="priceList">
              @foreach ($priceList as $item)
                <option value="{{ $item->value }}">{{ $item->name }}</option>
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

        {{-- <div class="col-md-6">
                                    <div class="form-group"><label>Ativo</label>
                                        <div class="switch">
                                            <div class="onoffswitch">
                                                <input type="checkbox" checked="" class="onoffswitch-checkbox" id="active"
                                                       name="active" required>
                                                <label class="onoffswitch-label" for="active">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
      </div>
      <div class="col-md-12 mt-4">
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
                          required> Serviço
                      </label>
                      <label>
                        <input type="radio" checked name="classification" onclick="changeClassification()"
                          value="2" required> Material
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
                      @foreach ($productSources as $item)
                        <option value="{{ $item->value }}">{{ $item->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código NCM</label>
                      <select class="form-control selectpicker with-ajax-ncm" name="ncm">
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código CEST</label>
                      <select class="form-control selectpicker" name="cest">
                        <option></option>
                        @foreach ($cests as $cest)
                          <option value="{{ $cest['U_SKILL_COD_CEST'] }}">
                            {{ substr($cest['U_SKILL_DESC_CEST'], 0, 80) }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Grupo de materiais</label>
                      <select class="form-control selectpicker" name="materials_group">
                        <option value="-1">Selecione</option>
                        @foreach ($materialGroups as $item)
                          <option value="{{ $item->value }}">{{ $item->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Tipo de material</label>
                      <select class="form-control selectpicker" name="material_type" data-size='5' required>
                        <option value="">Selecione o tipo de material</option>
                        @foreach ($materialTypes as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Código produto DNF</label>
                                                <select class="form-control selectpicker" name="dnf_code">
                                                    <option value="-1">Selecione</option>
                                                    @foreach ($dnfCodes as $item)
                                                        <option value="{{$item->value}}">{{$item->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> --}}
                </div>

                <div id="info-service">
                  <h5>Informações para classificação: Serviço</h5>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código do serviço contratado</label>
                      <select class="form-control selectpicker" name="contracted_service_code">
                        <option value="-1">Selecione</option>
                        @foreach ($serviceCodesContrated as $item)
                          <option value="{{ $item->value }}">{{ $item->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Código do serviço prestado</label>
                      <select class="form-control selectpicker" name="service_code">
                        <option value="-1">Selecione</option>
                        @foreach ($serviceCodes as $item)
                          <option value="{{ $item->value }}">{{ $item->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  {{-- <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Grupo de serviço</label>
                                                <select class="form-control selectpicker" name="service_group">
                                                    <option value="-1">Selecione</option>
                                                    @foreach ($serviceGroups as $item)
                                                        <option value="{{$item->value}}">{{$item->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> --}}
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
                        @foreach ($unitsMeasurement as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Itens por unidade de compra</label>
                      <input type="text" class="form-control qtd" name="numInBuy" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de compras (embalagem)</label>
                      <select class="form-control selectpicker" name="purchase_package_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Quantidade por U.M. embalagem</label>
                      <input type="text" class="form-control qtd" name="purPackUn" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Fornecedor preferencial</label>
                      <select class="form-control selectpicker with-ajax-suppliers"
                        name="preferred_supplier">
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
                        @foreach ($unitsMeasurement as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Itens por unidade de venda</label>
                      <input type="text" class="form-control qtd" name="numInSale" onclick='destroyMask(event)'
                        onblur="setMaskMoney();focusBlur(event)">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de vendas (embalagem)</label>
                      <select class="form-control selectpicker" name="sales_package_um">
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Quantidade por U.M. embalagem</label>
                      <input type="text" class="form-control qtd" name="salPackUn" onclick='destroyMask(event)'
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
                      <select class="form-control selectpicker" data-live-search="true" name="gl_method" required>
                        <option value="">Selecione</option>
                        @foreach ($glMethods as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Depósito padrão</label>
                      <select class="form-control selectpicker" data-live-search="true" name="default_warehouse"
                        required>
                        <option value="">Selecione</option>
                        @foreach ($warehouses as $item)
                          <option value="{{ $item->value }}">{{ $item->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>U.M. de estoque</label>
                      <select class="form-control selectpicker" name="inventory_um" required>
                        <option value="">Selecione</option>
                        @foreach ($unitsMeasurement as $item)
                          <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                {{-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Estoque Minimo</label>
                                            <input type="number" name="min_whs" class="form-control">
                                        </div>
                                    </div> --}}
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
                        <tr>
                          <td>{{ $property->name }}</td>
                          <td class="text-center">
                            <input type="checkbox" name="itemProperties[{{ $property->value }}]"
                              style="width: 20px; height: 20px;">
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
                  <textarea class="form-control" name="comments"></textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-7">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-2 form-group">
                    <label for="">Genero Item</label>
                    <input type="text" name="item_genre" id="" class="form-control" maxlength="2">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">Tipo</label>
                    <input type="text" name="item_type" id="" class="form-control" maxlength="2">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CFOP</label>
                    <input type="text" name="cfop" id="" class="form-control" maxlength="6">
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
                      maxlength="6">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CFOP fora do estado</label>
                    <input type="text" name="cfop_outside_state" id="" class="form-control"
                      maxlength="6">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST ICMS</label>
                    <input type="text" name="cst_icms" id="" class="form-control" maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST PIS</label>
                    <input type="text" name="cst_pis" id="" class="form-control" maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">CST COFINS</label>
                    <input type="text" name="cst_cofins" id="" class="form-control" maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST ICMS Saída</label>
                    <input type="text" name="cst_icms_output" id="" class="form-control" maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST PIS Saída</label>
                    <input type="text" name="cst_pis_output" id="" class="form-control" maxlength="10">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="">CST COFINS Saída</label>
                    <input type="text" name="cst_cofins_output" id="" class="form-control"
                      maxlength="10">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">Monofásico</label>
                    <input type="text" name="monofasico" id="" class="form-control" maxlength="6">
                  </div>
                  <div class="col-md-2 form-group">
                    <label for="">AS INTEGRADO</label>
                    <select name="as_integrado" id="" class="form-control selectpicker">
                      <option value=""></option>
                      <option value="S" selected>SIM</option>
                      <option value="N">NÂO</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-8">
              <div class="panel-body mt-4">
                <div class="row">
                  <div class="col-md-3">
                    <label for="">Método de planejamento</label>
                    <select name="planingSys" id="planingSys" class="form-control selectpicker">
                      <option value="">Selecione</option>
                      <option value="0">MRP</option>
                      <option value="1">Nenhum</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="">Método de suprimento</label>
                    <select name="prcrmntMtd" id="prcrmntMtd" class="form-control selectpicker"
                      onchange="changeCompoWH(event)">
                      <option value="">Selecione</option>
                      <option value="0">Comprar</option>
                      <option value="1">Produzir</option>
                    </select>
                  </div>
                  <div class="col-md-3" style="display: none;">
                    <label for="">Depósito de componente</label>
                    <select name="compoWH" id="compoWH" class="form-control selectpicker">
                      <option value="">Selecione</option>
                      <option value="0">Da linha da estrutura de produtos</option>
                      <option value="1">Da linha do documento do item pai</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-primary float-end mt-5" type="submit" onclick="validateTabs()">Salvar</button>
    </div>
  </form>
@endsection

@section('scripts')
  <script src="{!! asset('js/jquery.maskMoney.js') !!}" type="text/javascript"></script>
  <script type="application/javascript">

        let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

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
                            title: "Item cadastrado com sucesso!",
                            text: `Novo código SAP gerado: ${response.key}\n
                                Deseja visualizar o novo item cadastrado?`,
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
