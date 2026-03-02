@extends('layouts.main')
@section('title', 'Consumo Interno')

@section('content')

  <div class="col-12">
    <h3 class="header-page">{{ $attributes['pageTitle'] }}</h3>
  </div>
  <hr>
  <div class="accordion" id="accordionExample">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-coreui-target="#collapseOne" aria-expanded="true"
          aria-controls="collapseOne">
          Dados do pedido
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
        data-coreui-parent="#accordionExample">
        <div class="accordion-body">
          <div class="row">
            <div class="form-group col-md-2">
              <label class="fw-bold">Id</label>
              <p>{{ $ic->id }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">Data do consumo</label>
              <p>{{ $ic->date->format('d-m-Y') }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">Solicitante</label>
              <p>{{ $ic->requester_name }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">Regra de distribuição</label>
              <p>{{ $ic->distribution_rule_name }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">Regra de distribuição 2</label>
              <p>{{ $ic->distribution_rule2_name }}</p>
            </div>
            @if($attributes['show_dest_whs'])
              <div class="form-group col-md-3">
                <label>Depósito destino</label>
                <p>{{ $ic->dest_whs_code }}</p>
              </div>
            @endif
            <div class="form-group col-md-2">
              <label class="fw-bold">Projeto</label>
              <p>{{ $ic->project_name }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">@if($ic->document_type <> 0) Depósito @else Ponto de venda @endif </label>
              <p>{{ $ic->pos->nome }}</p>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-3">
              <label class="fw-bold">Status</label>
              <p>{{ $ic->status_label }}</p>
            </div>
            <div class="form-group col-md-2">
              <label class="fw-bold">Observação</label>
              <p>{{ $ic->observation }}</p>
            </div>
            @if (empty($ic->authorizer_user_id))
              @if (($ic->document_type === 0 && Auth::user()->hasRole('InternConsumption.authorize'))
                    || $ic->document_type === 1 && checkAccess('intern_consumption_perdas')
                    || $ic->document_type === 2 && checkAccess('intern_consumption_eventos'))
                <div class="form-group col-md-3">
                  <label class="fw-bold">Comentário</label>
                  <textarea maxlength="254" id="comment" name="comment" class="form-control"></textarea>
                </div>
                <div class="col-4 mt-4">
                  <button class="btn btn-danger" onclick="setApproval(false)">Reprovar</button>
                  <button class="btn btn-success" onclick="setApproval(true)">Aprovar</button>
                </div>
              @endif
            @endif
          </div>
          <div class="row mt-2">
            @if ($ic->sales_order_code)
              <div class="form-group col-md-2">
                <label class="fw-bold">Pedido de Venda</label>
                <p>{{ $ic->sales_order_code }}</p>
              </div>
            @endif
            @if ($ic->stock_transfer_code)
              <div class="form-group col-md-2">
                <label class="fw-bold">Transf. de estoque</label>
                <p>{{ $ic->stock_transfer_code }}</p>
              </div>
            @endif
            @if ($ic->manual_account_entry_id)
              <div class="form-group col-md-2">
                <label class="fw-bold">Lançamento Contábil</label>
                <p>{{ $ic->manual_account_entry_id }}</p>
              </div>
            @endif
            @if ($ic->stock_output_id)
              <div class="form-group col-md-2">
                <label class="fw-bold">Saída de mercadorias</label>
                <a href="{{ route('inventory.output.edit', $ic->stock_output_id) }}" class="btn btn-primary">{{ $ic->output->code }}</a>
              </div>
            @endif
          </div>
          <div class="row mt-2">
            @if ($ic->message)
              <div class="form-group col-md-8">
                <label class="fw-bold">Mensagem</label>
                <p>{{ $ic->message }}</p>
              </div>
            @endif
            <div class="col-12">
                <button onclick="if(confirm('Cancelar?')){window.location='{{ route('intern-consumption.cancel', $ic) }}'}"
                  class="btn btn-danger">Cancelar
                </button>
                @if($ic->canUpdate())<a href="{{ route('intern-consumption.edit', $ic) }}"><button class="btn btn-success">Editar</button></a>@endif
                <a href="{{ route('intern-consumption.print', $ic) }}">
                  <button class="btn btn-warning">Imprimir</button>
                </a>
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
          Itens solicitados
        </button>
      </h2>
      <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#accordionExample">
        <div class="accordion-body">
          <div class="row" id="itemsVue">
            <div class="table-responsive">
              <table class="table table-default table-striped" v-if="items.length > 0">
                <thead>
                  <tr>
                    <th>Linha</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Preço Unit.</th>
                    <th>Total</th>
                    <th>Venda / Produção</th>
                    <th>Nº OP</th>
                    <th>Status OP</th>
                    <th>Comentários</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in items">
                    <td>@{{ index }}</td>
                    <td>@{{ item.code }}</td>
                    <td>@{{ item.name }}</td>
                    <td>@{{ parseFloat(item.qty).format(3, ",", ".") }}</td>
                    <td>@{{ parseFloat(item.value).format(4, ",", ".") }}</td>
                    <td>@{{ parseFloat(item.value * item.qty).format(2, ",", ".") }}</td>
                    <td>@{{ item.type }}</td>
                    <td>@{{ item.production_order_code || '-' }}</td>
                    <td>@{{ item.po_status_label }}</td>
                    <td>@{{ item.comments }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="accordion mt-4" id="accordionExample">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-coreui-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
          Comentários
        </button>
      </h2>
      <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-coreui-parent="#accordionExample">
        <div class="accordion-body">
          <div class="feed-activity-list">
            @foreach ($ic->comments as $comment)
              <div class="feed-element">
                <div class="pull-left">
                  <i class="fa fa-2x fa-user"></i>
                </div>
                <div class="media-body ">
                  <small class="pull-right">{{ $comment->created_at->diffForHumans() }}</small>
                  <strong>{{ $comment->user->name }}:</strong> {{ $comment->comment }} <br>
                  <small class="text-muted">{{ $comment->created_at->format('d-m-Y H:i') }}</small>
                </div>
              </div>
            @endforeach
            <form id="addComment" name="addComment" action="{{ route('intern-consumption.storeComment', $ic->id) }}"
              method="post" onsubmit="waitingDialog.show('Salvando...')">

              <div class="form-group mt-3">
                <label class="fw-bold">Adicionar comentário</label>
                <textarea form="addComment" name="comment" required="" maxlength="250" class="form-control" placeholder="Comentário"
                  rows="3"></textarea>
              </div>
              <button form="addComment" class="btn btn-primary mt-3" type="submit">Adiconar comentário
              </button>
              {!! csrf_field() !!}
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@section('scripts')
  <script>
    let vue = new Vue({
      el: '#itemsVue',
      data: {
        items: @json($ic->items)
      }
    });

    function setApproval(approve) {
      let msg = approve ? 'Confirma a aprovação?' : 'Confirma a reprovação?';
      let icon = approve ? 'info' : 'warning';
      let dangerMode = !approve;
      swal({
        title: msg,
        icon: icon,
        buttons: true,
        dangerMode: dangerMode,
        closeOnClickOutside: false,
        closeOnEsc: false,
      }).then((willDelete) => {
        if (willDelete) {
          let body = {
            approve: approve,
            comment: $('#comment').val()
          };
          axios({
            method: 'post',
            url: '{{ route('intern-consumption.update', $ic->id) }}',
            data: body
          }).then(function(response) {
            if (response.data.success) {
              swal("Feito!", response.data.message, "success")
                .then((value) => {
                  window.location.reload();
                });
            } else {
              swal("ERRO!", response.data.message, "error")
                .then((value) => {
                  window.location.reload();
                });
            }
            waitingDialog.hide();
          });
          waitingDialog.show('Salvando...');
        }
      });
    }
  </script>
@endsection
