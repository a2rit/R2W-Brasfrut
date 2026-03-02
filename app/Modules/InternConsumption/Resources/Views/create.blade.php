@extends('layouts.main')
@section('title', 'Consumo Interno')

@section('content')

  <div id="app">
    <div class="col-md-10">
      <h3 class="header-page">{{ $attributes['pageTitle'] }}</h3>
    </div>
    <hr>

    <form action="#" method="post" id="createForm" @submit.prevent="validate">
      {!! csrf_field() !!}
      <input type="hidden" name="id" :value="ic.id">
      <div class="accordion" id="accordionExample">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-coreui-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Dados do pedido
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#accordionExample">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group col-md-3">
                  <label id="label-date">{{ $attributes['label-date'] }}</label>
                  <br>
                  <date-picker2 v-model="ic.date" name="date"></date-picker2>
                </div>
                <div class="form-group col-md-3">
                  <label>Solicitante</label>
                  <v-select :options="requesters" v-model="ic.requester_sap_id" name="requester_sap_id"
                    :reduce="requester => requester.id" label="name">
                    <template #search="{attributes, events}">
                      <input class="vs__search" :required="!ic.requester_sap_id" v-bind="attributes" v-on="events" />
                    </template>
                  </v-select>
                </div>
                {{-- <div class="form-group col-md-3">
                  <label>Ramal</label>
                  <input class="form-control" v-model="ic.requester_branch" name="requester_branch" />
                </div> --}}
                <div class="form-group col-md-3">
                  <label>Regra de distribuição</label>
                  <v-select :options="distributionRules" v-model="ic.distribution_rule"
                    :reduce="distributionRule => distributionRule.value" name="distribution_rule" label="name">
                  </v-select>
                </div>
                @if($attributes['show_dest_whs'])
                  <div class="form-group col-md-3">
                    <label>Depósito destino</label>
                    <v-select :options="whs" v-model="ic.dest_whs_code" name="dest_whs_code" :reduce="whs => whs.value" label="name" :required="true">
                    </v-select>
                  </div>
                @endif
              </div>
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Regra de distribuição 2</label>
                  <v-select :options="distributionRules2" v-model="ic.distribution_rule2"
                    :reduce="distributionRule => distributionRule.value" label="name" name="distribution_rule2">
                  </v-select>
                </div>
                <div class="form-group col-md-3">
                  <label>Projeto</label>
                  <v-select :options="projects" v-model="ic.project" name="project" :reduce="project => project.value"
                    label="name"></v-select>
                </div>
                <div class="form-group col-md-3" id="sellingPointDiv">
                  <label>{{ $attributes['label_ponto_venda'] }}</label>
                  <v-select :options="sellingPoints" v-model="ic.pos_id" :reduce="pos => pos.id" label="nome"
                    name="pos_id">
                    <template #search="{attributes, events}">
                      <input class="vs__search" :required="!ic.pos_id" v-bind="attributes" v-on="events" />
                    </template>
                  </v-select>
                </div>
                @if($attributes['show_local_entrega'])
                  <div class="form-group col-md-3">
                    <label>Local de entrega</label>
                    <input v-model="ic.delivery_location" name="delivery_location" class="form-control" />
                  </div>
                @endif
                
                <div class="form-group col-md-4">
                  <label>Comentário</label>
                  <textarea maxlength="254" v-model="ic.comment" name="comment" class="form-control"></textarea>
                </div>
                <div class="form-group col-md-4">
                  <label>Observação Geral</label>
                  <textarea maxlength="254" v-model="ic.observation" name="observation" class="form-control"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="accordion mt-4" id="accordionExample">
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-coreui-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
              Dados do pedido
            </button>
          </h2>
          <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#accordionExample">
            <div class="accordion-body">
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Selecione um item</label>
                  <v-select v-model="tempItem" label="name" :options="itemOptions" @search="fetchItems"
                    :filterable="false"></v-select>
    
                </div>
                <div class="form-group col-md-3">
                  <label>Quantidade</label>
                  <vue-numeric class="form-control" separator="." v-model="tempItem.qty" :empty-value="0"
                    :precision="3"></vue-numeric>
                </div>
                <div class="form-group col-md-3">
                  <label>Adicionar</label>
                  <button type="button" class="btn btn-success form-control" @click="addItem()">Adicionar
                  </button>
                </div>
              </div>
              <div class="row" id="itemsVue">
                <div class="table-responsive">
                  <table class="table table-striped" v-if="ic.items.length > 0">
                    <thead>
                      <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th>Venda / Produção</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                        @if($attributes['show_item_comments'])<th>Comentários</th>@endif
                        <th>Remover</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in ic.items">
                        <td>
                          <input type="hidden" v-model="item.code"
                            :name="'items[' + index + '][code]'">
                            @{{ item.code }}
                        </td>
                        <td>
                          <input type="hidden" v-model="item.name"
                            :name="'items[' + index + '][name]'">
                            @{{ item.name }}
                        </td>
                        <td>
                          <vue-numeric :name="'items[' + index + '][qty]'" separator="." :value="item.qty" read-only
                            :precision="3"></vue-numeric>
                        </td>
                        <td>
                          <input type="hidden" v-model="item.type"
                            :name="'items[' + index + '][type]'">
                            @{{ item.type }}
                        </td>
                        <td>
                          <vue-numeric :name="'items[' + index + '][value]'" currency="R$" separator="."
                            :value="item.value" read-only :precision="2"></vue-numeric>
                        </td>
                        <td>
                          <vue-numeric currency="R$" separator="." :value="(item.value * item.qty)" read-only
                            :precision="2"></vue-numeric>
                        </td>
                        @if($attributes['show_item_comments'])
                          <td>
                            <textarea v-model="item.comments" class="form-control" :name="'items[' + index + '][comments]'" cols="30" rows="1" maxlength="100" required></textarea>
                          </td>
                        @endif
                        <td>
                          <button type="button" @click="removeItem(index)" class="btn btn-danger">Remover
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-12 mt-3"><strong>Total: </strong>
                  <vue-numeric class="form-control" currency="R$" separator="." :value="total" read-only
                    :precision="2"></vue-numeric>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-primary float-end mt-5" type="submit">Salvar</button>
    </form>
  </div>
@endsection

@section('scripts')
  <script>
    $('.selectpicker').selectpicker(selectpickerConfig);
    let documentType = @if(!empty($internConsumption)) "{{ $internConsumption->document_type }}" @else {{ $documentType }} @endif

    $(document).on("keypress", "form", function(event) {
      return event.keyCode !== 13;
    }).on('keypress', '#qty', function(event) {
      if (event.keyCode === 13) {
        addItem();
      }
    });

    let vue = new Vue({
      el: '#app',
      data: {
        tempItem: {},
        itemOptions: [],
        ic: @json($internConsumption ?? (object) ['items' => []]),
        requesters: @json($requesters),
        distributionRules: @json($distributionRules),
        sellingPoints: @json($sellingPoints),
        projects: @json($projects),
        distributionRules2: @json($distributionRules2),
        whs: @json($whs)
      },
      methods: {
        fetchItems(search, loading) {
          loading(true);
          if(parseInt(documentType) !== 0 && !this.ic.pos_id){
            swal('Opss!', 'Selecione um depósito!', 'error');
            return false;
          }
          axios.get('{{ route('intern-consumption.searchItem') }}', {
            params: {
              q: search,
              pos_id: this.ic.pos_id,
              documentType: documentType,
              warehouse: this.ic.warehouse
            }
          }).then((response) => {
            _.each(response.data, (item) => {
              if (item) {
                item.qty = 0;
              }
            });
            this.itemOptions = response.data;
          }).catch((e) => {
            console.log(e)
          }).finally(() => loading(false))
        },
        removeItem: function(index) {
          swal({
            title: `Remover ${this.ic.items[index].name}?`,
            icon: "warning",
            buttons: true,
            dangerMode: true,
          }).then((willDelete) => {
            if (willDelete) {
              this.ic.items.splice(index, 1);
            }
          });
        },
        addItem() {
          if (!this.tempItem.code) {
            swal("Selecione um item!");
            return;
          }
          if (this.tempItem.qty <= 0) {
            swal("A quantidade deve ser maior que zero!");
            return;
          }
          this.ic.items.push(this.tempItem);
          this.tempItem = {
            qty: 0
          };
        },
        validate() {
          if (this.ic.items.length === 0) {
            swal('Adicione ao menos um item!');
            return false;
          }
          if (!this.ic.requester_sap_id) {
            swal("Informe o nome do solicitante!");
            return false;
          }

          @if($attributes['show_dest_whs'])
            if(!this.ic.dest_whs_code) {
              swal('Selecione o depósito de destino!');
              return false;
            }
          @endif
          
          this.ic['document_type'] = documentType;

          swal({
            title: "Confirma solicitação?",
            icon: "warning",
            buttons: true
          }).then((confirm) => {
            if (confirm) {
              waitingDialog.show("Salvando...");
              axios.post('{{ route('intern-consumption.store') }}', this.ic).then((response) => {
                swal("Salvo com sucesso!").then(() => {
                  window.location.href = response.data.ic.show_url;
                });
              }).catch((e) => {
                swal("Erro ao salvar!");
              }).finally(() => {
                waitingDialog.hide();
              });
            }
          });
          return false;
        }
      },
      computed: {
        total: function() {
          let total = 0;
          this.ic.items.forEach(function(item) {
            total = total + (item.value * item.qty)
          });
          return total;
        }
      }
    });
  </script>
@endsection
