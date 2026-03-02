@extends('layouts.main')

@section('title', 'Dashboard de Compras')

@section('content')

  <!-- Filters -->
  <p class="fs-5">
    <strong>Centro de custo: </strong> {{ $costCenters->where('value', $selectedCostCenter)->first()->name }}
  </p>
  <p class="fs-5">
    @if ($selectedCostCenter === '1.0')
      <strong>Centro de custo 2: </strong>{{ $costCenters2->where('value', $selectedCostCenter2)->first()->name }}
    @endif
  </p>
  <p class="fs-5"><strong>Período:</strong> {{ date_format(date_create($fist_date), 'd/m/Y') }} até
    {{ date_format(date_create($last_date), 'd/m/Y') }}</p>
  <form action="{{ route('journal-entry.dashboard.filter') }}" method="GET" id="needs-validation"
    enctype="multipart/form-data">
    <div class="accordion" id="filterAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseOne"
            aria-expanded="true" aria-controls="collapseOne">
            Filtros
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#filterAccordion">
          <div class="accordion-body">
            <div class="row">
              <div class="col-md-3">
                <label>Centro de custo</Label>
                <select id="roleGlobal" name="costCenter" class="form-control selectpicker" onchange="setRoleFull()"
                  required>
                  <option value="">Selecione</option>
                  @foreach ($costCenters as $costCenter)
                    <option value="{{ $costCenter->value }}">{{ $costCenter->value }} - {{ $costCenter->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label>Centro de custo 2</Label>
                <select id="roleGlobal2" name="costCenter2" class="form-control selectpicker">
                  <option value="">Selecione</option>
                  @foreach ($costCenters2 as $costCenter2)
                    <option value="{{ $costCenter2->value }}">{{ $costCenter2->value }} - {{ $costCenter2->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Inicial</label>
                  <input type="date" name="data_fist" class="form-control datepicker" placeholder="Inicial"
                    autocomplete="off">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Data Final</label>
                  <input type="date" name="data_last" class="form-control datepicker" placeholder="Final"
                    autocomplete="off">
                </div>
              </div>
            </div>
            <div class="row mt-3">
              <div class="form-group d-grid justify-content-end">
                <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
  <div class="row mt-5">
    <div class="accordion" id="contasAPagarAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            Contas a pagar ( Em Aberto )
          </button>
        </h2>
        <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#contasAPagarAccordion">
          <div class="accordion-body">
            <div class="row">
              @foreach ($contas_a_pagar->groupBy('GroupCode') as $value)
                <div class="col-3">
                  <div class="card border-top-danger border-top-3 mb-3 text-center">
                    <div class="card-header bg-danger" style="--cui-bg-opacity: .5;">{{ $value->first()->GroupName }}</div>
                    <div class="card-body bg-danger" style="--cui-bg-opacity: .1;">
                      <h5 class="card-title">R$ {{ number_format($value->sum('VALOR EM ABERTO'), 2, ',', '.') }}</h5>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="accordion" id="contasAPagarAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree">
          <button class="accordion-button" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseThree"
            aria-expanded="true" aria-controls="collapseThree">
            Contas pagas ( Liquidado )
          </button>
        </h2>
        <div id="collapseThree" class="accordion-collapse collapse show" aria-labelledby="headingThree"
          data-coreui-parent="#contasAPagarAccordion">
          <div class="accordion-body">
            <div class="row">
              @foreach ($contas_pagas->groupBy('GroupCode') as $value)
                <div class="col-3">
                  <div class="card border-top-turquoise border-top-3 mb-3 text-center">
                    <div class="card-header bg-turquoise" style="--cui-bg-opacity: .5;">{{ $value->first()->GroupName }}</div>
                    <div class="card-body bg-turquoise" style="--cui-bg-opacity: .1;">
                      <h5 class="card-title " >R$ {{ number_format($value->sum('VALOR PAGO'), 2, ',', '.') }}</h5>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr>


@endsection

@section('scripts')
  <script>
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);

    function setRoleFull() {
      var val = $('#roleGlobal').val();

      setTimeout(() => {
        if (val == '1.0') {
          $(`#roleGlobal2`).removeClass('disabled');
        } else {
          $(`#roleGlobal2`).addClass('disabled');
        }

        $(`#roleGlobal2`).selectpicker('destroy');
        $(`#roleGlobal2`).selectpicker(selectpickerConfig).selectpicker('render');

      }, 200);
    }
  </script>
@endsection
