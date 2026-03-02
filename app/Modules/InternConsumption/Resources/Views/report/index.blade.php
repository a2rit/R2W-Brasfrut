@extends('layouts.main')
@section('title', 'Relatório consumo interno')

@section('actions')
@endsection

@section('content')
  <div id="app">
    <div class="container-fluid w-50">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title text-center mt-2">Relatório</h5>
        </div>
        <div class="card-body">
          <b-form action="{{ route('intern-consumption.report.index') }}" @submit="submit" method="post">
            {!! csrf_field() !!}
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Tipo documento</label>
              <div class="col-sm-9">
                <b-select class="form-control selectpicker col-sm-9" v-model="form.document_type" name="document_type" onchange="changeDocumentType(this)" required>
                  <option value="0" selected>PADRÃO</option>
                  <option value="1">PERDAS</option>
                  <option value="2">EVENTOS</option>
                </b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label" id="pos_label">Ponto de venda</label>
              <div class="col-sm-9">
                <b-select :options="pos" class="form-control selectpicker col-sm-9" v-model="form.pos_id"
                  name="pos_id"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Status</label>
              <div class="col-sm-9">
                <b-select :options="statuses" class="form-control selectpicker" v-model="form.status"
                  name="status"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Solicitante</label>
              <div class="col-sm-9">
                <b-select :options="requesters" class="form-control selectpicker" v-model="form.requester_sap_id"
                  name="requester_sap_id"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Autorizador</label>
              <div class="col-sm-9">
                <b-select :options="users" class="form-control selectpicker" v-model="form.authorizer_user_id"
                  name="authorizer_user_id"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Projeto</label>
              <div class="col-md-9">
                <b-select :options="projects" class="form-control selectpicker" v-model="form.project"
                  name="project"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Regra de distribuição</label>
              <div class="col-md-9">
                <b-select :options="distribution_rules" class="form-control selectpicker" v-model="form.distribution_rule"
                  name="distribution_rule"></b-select>
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Data Inicial</label>
              <div class="col-md-9">
                <date-picker2 name="start_date" v-model="form.start_date" :input-attr="inputDate"></date-picker2>
                <input type="hidden" v-model="form.start_date" name="start_date">
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Data Final</label>
              <div class="col-md-9">
                <date-picker2 name="end_date" v-model="form.end_date" :input-attr="inputDate"></date-picker2>
                <input type="hidden" v-model="form.end_date" name="end_date">
              </div>
            </div>
            <div class="row mb-3">
              <label for="code" class="col-sm-3 col-form-label">Formatos</label>
              <div class="col-md-9">
                <b-select :options="types" class="form-control selectpicker" v-model="form.type" name="type"
                  required></b-select>
              </div>
            </div>
            <b-col md="12" class="text-center">
              <b-button class="mt-3" variant="success" type="submit">Gerar</b-button>
            </b-col>
          </b-form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script>
    let app = new Vue({
      el: '#app',
      data() {
        return {
          pos: @json($pos),
          statuses: @json($statuses),
          users: @json($users),
          requesters: @json($requesters),
          projects: @json($projects),
          distribution_rules: @json($distributionRules),
          form: {},
          inputDate: {
            required: true
          },
          types: [{
              text: 'Analítico',
              value: 'analytic'
            },
            {
              text: 'Analítico - Excel',
              value: 'analytic-excel'
            },
            {
              text: 'Sintético',
              value: 'synthetic'
            },
          ]
        }
      },
      methods: {
        submit(e) {
          if (moment(this.form.start_date).unix() > moment(this.form.end_date).unix()) {
            swal('A data final deve ser maior ou igual a data inicial!');
            e.preventDefault();
            return false;
          }
          return true;
        }
      },
      computed: {}
    });

    let selectpicker = $('.selectpicker').selectpicker(selectpickerConfig);

    function changeDocumentType(element){
      if(parseInt($(element).val()) !== 0){
        $('#pos_label').text('Depósito');
      }else{
        $('#pos_label').text('Ponto de venda');
      }
    }

  </script>
@endsection
