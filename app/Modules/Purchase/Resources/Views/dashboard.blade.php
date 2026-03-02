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
  <form action="{{ route('purchase.dashboard.filter') }}" method="GET" id="needs-validation" enctype="multipart/form-data">
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
                <select id="roleGlobal" name="costCenter" class="form-control selectpicker" onchange="setRoleFull()" required>
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

  
  <!-- Purchase Requests -->

  <div class="card mt-5">
    <div class="card-header">
      <h5 class="card-title text-start mt-2">Solicitação de compras</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-8">
          <canvas id="lineChartPurchaseRequest" width="100" height="50"></canvas>
          <p class="fst-italic"><small>*Por quantidade</small></p>
        </div>
        <div class="col-4">
          <div class="card h-75">
            <div class="card-body">
              <?php $purchase_requests_total = $purchase_requests->count(); ?>
              <div class="row">
                <p>
                  <svg class="icon text-primary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Abertos:
                  @if($purchase_requests_total > 0)
                    {{ number_format(($purchase_requests->where('codStatus', '1')->count() * 100) / $purchase_requests_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </p>
              </div>
              <div class="row">
                <p>
                  <svg class="icon text-danger">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Cancelados:
                  @if($purchase_requests_total > 0)
                    {{ number_format(($purchase_requests->where('codStatus', '2')->count() * 100) / $purchase_requests_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </p>
              </div>
              <div class="row">
                <p>
                  <svg class="icon text-success">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Fechados:
                  @if($purchase_requests_total > 0)
                    {{ number_format(($purchase_requests->where('codStatus', '5')->count() * 100) / $purchase_requests_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </p>
              </div>
              <div class="row">
                <p>
                  <svg class="icon text-secondary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  PC. Parcial:
                  @if($purchase_requests_total > 0)
                    {{ number_format(($purchase_requests->where('codStatus', '3')->count() * 100) / $purchase_requests_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </p>
              </div>
              <div class="row">
                <p>
                  <svg class="icon text-warning">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Cot. Geradas:
                  @if($purchase_requests_total > 0)
                    {{ number_format(($purchase_requests->where('codStatus', '6')->count() * 100) / $purchase_requests_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </p>
              </div>
              <hr>
              <h6>Total: 100%</h6>
            </div>
          </div>
          <p class="fst-italic"><small>*Por percentual ( % )</small></p>
        </div>
      </div>
    </div>
  </div>


  <!-- Purchase Quotations -->

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title text-start mt-2">Cotação de compras</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-8">
          <canvas id="lineChartPurchaseQuotation" width="100" height="50"></canvas>
          <p class="fst-italic"><small>*Por quantidade</small></p>
        </div>
        <div class="col-4">
          <canvas id="pieChartPurchaseQuotation" width="100" height="100"></canvas>
          <p class="fst-italic"><small>*Por valores ( $ )</small></p>
        </div>
        <div class="col-4">
          <div class="card h-100">
            <div class="card-body">
              <?php $purchase_quotations_total = $purchase_quotations->count(); ?>
              <div class="row">
                <span>
                  <svg class="icon text-primary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Abertos:
                  @if($purchase_quotations_total > 0)
                    {{ number_format(($purchase_quotations->where('status', '1')->count() * 100) / $purchase_quotations_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-danger">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Cancelados:
                  @if($purchase_quotations_total > 0)
                    {{ number_format(($purchase_quotations->where('status', '2')->count() * 100) / $purchase_quotations_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              
              <div class="row">
                <span>
                  <svg class="icon text-success">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Fechados:
                  @if($purchase_quotations_total > 0)
                    {{ number_format(($purchase_quotations->where('status', '4')->count() * 100) / $purchase_quotations_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-secondary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Pendentes:
                  @if($purchase_quotations_total > 0)
                    {{ number_format(($purchase_quotations->where('status', '3')->count() * 100) / $purchase_quotations_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              
              <div class="row">
                <span>
                  <svg class="icon text-warning">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  PC. Gerados:
                  @if($purchase_quotations_total > 0)
                    {{ number_format(($purchase_quotations->where('status', '5')->count() * 100) / $purchase_quotations_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <hr>
              <p>Total: 100%</p>
            </div>
          </div>
        </div>
        <p class="fst-italic"><small>*Por percentual ( % )</small></p>
      </div>
    </div>
  </div>

  <!-- Purchase Order -->

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title text-start mt-2">Pedido de compras</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-8">
          <canvas id="lineChartPurchaseOrder" width="100" height="50"></canvas>
          <p class="fst-italic"><small>*Por quantidade</small></p>
        </div>
        <div class="col-4">
          <canvas id="pieChartPurchaseOrder" width="100" height="100"></canvas>
          <p class="fst-italic"><small>*Por valores ( $ )</small></p>
        </div>
        <div class="col-4">
          <div class="card h-100">
            <div class="card-body">
              <?php $purchase_orders_total = $purchase_orders->count(); ?>
              <div class="row">
                <span>
                  <svg class="icon text-primary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Abertos:
                  @if($purchase_orders_total > 0)
                    {{ number_format(($purchase_orders->where('status', '1')->count() * 100) / $purchase_orders_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-danger">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Cancelados:
                  @if($purchase_orders_total > 0)
                    {{ number_format(($purchase_orders->where('status', '2')->count() * 100) / $purchase_orders_total, 2, '.', ',') }}  
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-success">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Fechados:
                  @if($purchase_orders_total > 0)
                    {{ number_format(($purchase_orders->where('status', '0')->count() * 100) / $purchase_orders_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-secondary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Pendentes:
                  @if($purchase_orders_total > 0)
                    {{ number_format(($purchase_orders->where('status', '3')->count() * 100) / $purchase_orders_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-warning">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Reprovados:
                  {{ number_format(($purchase_orders->where('status', '4')->count() * 100) / $purchase_orders_total, 2, '.', ',') }}
                  %
                </span>
              </div>
              <hr>
              <p>Total: 100%</p>
            </div>
          </div>
        </div>
        <p class="fst-italic"><small>*Por percentual ( % )</small></p>
      </div>
    </div>
  </div>

  <!-- Incoing Invoices -->

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title text-start mt-2">Nota fiscal de Entrada ( Serviços )</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-8">
          <canvas id="lineChartIncoingInvoice" width="100" height="50"></canvas>
          <p class="fst-italic"><small>*Por quantidade</small></p>
        </div>
        <div class="col-4">
          <canvas id="pieChartIncoingInvoice" width="100" height="100"></canvas>
          <p class="fst-italic"><small>*Por valores  ( $ )</small></p>
        </div>
        <div class="col-4">
          <div class="card h-100">
            <div class="card-body">
              <?php $incoing_invoices_total = $incoing_invoices->count(); ?>
              <div class="row">
                <span>
                  <svg class="icon text-primary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Abertos:
                  @if($incoing_invoices_total > 0)
                    {{ number_format(($incoing_invoices->where('status', '1')->count() * 100) / $incoing_invoices_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-danger">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Cancelados:
                  @if($incoing_invoices_total > 0)
                  {{ number_format(($incoing_invoices->where('status', '2')->count() * 100) / $incoing_invoices_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-success">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Fechados:
                  @if($incoing_invoices_total > 0)
                    {{ number_format(($incoing_invoices->where('status', '0')->count() * 100) / $incoing_invoices_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <hr>
              <p>Total: 100%</p>
            </div>
          </div>
        </div>
        <p class="fst-italic"><small>*Por percentual ( % )</small></p>
      </div>
    </div>
  </div>


  <!-- Advance Providers -->

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="card-title text-start mt-2">Adiantamento para fornecedores</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-8">
          <canvas id="lineChartAdvanceProvider" width="100" height="50"></canvas>
          <p class="fst-italic"><small>*Por quantidade</small></p>
        </div>
        <div class="col-4">
          <canvas id="pieChartAdvanceProvider" width="100" height="100"></canvas>
          <p class="fst-italic"><small>*Por valores  ( $ )</small></p>
        </div>
        <div class="col-4">
          <div class="card h-100">
            <div class="card-body">
              <?php $advance_providers_total = $advance_providers->count(); ?>
              <div class="row">
                <span>
                  <svg class="icon text-primary">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Abertos:
                  @if($advance_providers_total > 0)
                    {{ number_format(($advance_providers->where('status', '1')->count() * 100) / $advance_providers_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <div class="row">
                <span>
                  <svg class="icon text-success">
                    <use xlink:href="{{ asset('icons_assets/custom.svg#dash-circle-fill') }}"></use>
                  </svg>
                  Fechados:
                  @if($advance_providers_total > 0)
                  {{ number_format(($advance_providers->where('status', '0')->count() * 100) / $advance_providers_total, 2, '.', ',') }}
                  @else
                    0
                  @endif
                  %
                </span>
              </div>
              <hr>
              <p>Total: 100%</p>
            </div>
          </div>
        </div>
        <p class="fst-italic"><small>*Por percentual ( % )</small></p>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script>
    let selectpicker = $(".selectpicker").selectpicker(selectpickerConfig);
    var monthName = new Array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
    var d = new Date();
    let months = [];
    d.setDate(1);

    for (i = 0; i <= 11; i++) {
      months.push(monthName[d.getMonth()] + ' / ' + d.getFullYear())
      d.setMonth(d.getMonth() - 1);
    }
    months.reverse();

    //purchase requests
    let purchase_request_dataset = [{
        label: 'Abertos',
        data: [],
        borderColor: '#257DBC',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#257DBC",
      },
      {
        label: 'Cancelados',
        data: [],
        borderColor: '#FF0000',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#FF0000",
      },
      {
        label: 'Fechados',
        data: [],
        borderColor: '#2EB85C',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#2EB85C",
      },
      {
        label: 'PC. Parcial',
        data: [],
        borderColor: '#4F5D73',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#4F5D73",
      },
      {
        label: 'Cot. Geradas',
        data: [],
        borderColor: '#F9B115',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#F9B115",
      }
    ];

    <?php $purchase_requests = $purchase_requests->groupBy('month'); ?>
    @foreach (getLastTwelveMonths() as $key => $date)
      purchase_request_dataset[0].data[{{ $key }}] = 0;
      purchase_request_dataset[1].data[{{ $key }}] = 0;
      purchase_request_dataset[2].data[{{ $key }}] = 0;
      purchase_request_dataset[3].data[{{ $key }}] = 0;
      purchase_request_dataset[4].data[{{ $key }}] = 0;
      @if (!empty($purchase_requests[$date['month']]))
        @foreach ($purchase_requests[$date['month']] as $ind => $dataMonth)
          @if ($dataMonth->codStatus == 1)
            purchase_request_dataset[0].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->codStatus == 2)
            purchase_request_dataset[1].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->codStatus == 5)
            purchase_request_dataset[2].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->codStatus == 3)
            purchase_request_dataset[3].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->codStatus == 6)
            purchase_request_dataset[4].data[{{ $key }}] += 1;
          @endif
        @endForeach
      @endif
    @endForeach

    const lineChartPurchaseRequest = document.getElementById('lineChartPurchaseRequest');
    new Chart(lineChartPurchaseRequest, {
      type: 'line',
      data: {
        labels: months,
        datasets: purchase_request_dataset,
      },
      options: {
        legend: {
          position: 'bottom',
        },
        scales: {
          y: {
            beginAtZero: true
          }
        },
        responsive: true
      }
    });
    //end purchase requests

    
    //purchase quotations
    let purchase_quotation_dataset1 = [{
        label: 'Abertos',
        data: [],
        borderColor: '#257DBC',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#257DBC",
      },
      {
        label: 'Cancelados',
        data: [],
        borderColor: '#FF0000',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#FF0000",
      },
      {
        label: 'Fechados',
        data: [],
        borderColor: '#2EB85C',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#2EB85C",
      },
      {
        label: 'Pendentes',
        data: [],
        borderColor: '#4F5D73',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#4F5D73",
      },
      {
        label: 'PC. Gerados',
        data: [],
        borderColor: '#F9B115',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#F9B115",
      }
    ];

    //purchase quotation dataset 2
    let purchase_quotation_dataset2 = [{
      data: [],
      backgroundColor: [
        '#257DBC',
        '#FF0000',
        '#2EB85C',
        '#4F5D73',
        '#F9B115',
      ],
      pointBackgroundColor: ['#257DBC',
        '#FF0000',
        '#2EB85C',
        '#4F5D73',
        '#F9B115',],
      hoverOffset: 4,
    }];


    <?php $purchase_orders_group_months = $purchase_quotations->groupBy('month'); ?>

    //dataset to Chart 1
    @foreach (getLastTwelveMonths() as $key => $date)
      purchase_quotation_dataset1[0].data[{{ $key }}] = 0;
      purchase_quotation_dataset1[1].data[{{ $key }}] = 0;
      purchase_quotation_dataset1[2].data[{{ $key }}] = 0;
      purchase_quotation_dataset1[3].data[{{ $key }}] = 0;
      purchase_quotation_dataset1[4].data[{{ $key }}] = 0;
      @if (!empty($purchase_orders_group_months[$date['month']]))
        @foreach ($purchase_orders_group_months[$date['month']] as $ind => $dataMonth)
          @if ($dataMonth->status == 1)
            purchase_quotation_dataset1[0].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 2)
            purchase_quotation_dataset1[1].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 4)
            purchase_quotation_dataset1[2].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 3)
            purchase_quotation_dataset1[3].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 5)
            purchase_quotation_dataset1[4].data[{{ $key }}] += 1;
          @endif
        @endForeach
      @endif
    @endForeach


    //chart 2
    purchase_quotation_dataset2[0].data[0] = 0;
    purchase_quotation_dataset2[0].data[1] = 0;
    purchase_quotation_dataset2[0].data[2] = 0;
    purchase_quotation_dataset2[0].data[3] = 0;
    purchase_quotation_dataset2[0].data[4] = 0;
    @foreach ($purchase_quotations as $ind => $data)
      @if ($data->status == 1)
        purchase_quotation_dataset2[0].data[0] += {{ number_format((float) $dataMonth->getDocTotal(), 2, '.', '') }};
      @endif
      @if ($data->status == 2)
        purchase_quotation_dataset2[0].data[1] += {{ number_format((float) $dataMonth->getDocTotal(), 2, '.', '') }};
      @endif
      @if ($data->status == 4)
        purchase_quotation_dataset2[0].data[2] += {{ number_format((float) $dataMonth->getDocTotal(), 2, '.', '') }};
      @endif
      @if ($data->status == 3)
        purchase_quotation_dataset2[0].data[3] += {{ number_format((float) $dataMonth->getDocTotal(), 2, '.', '') }};
      @endif
      @if ($data->status == 5)
        purchase_quotation_dataset2[0].data[4] += {{ number_format((float) $dataMonth->getDocTotal(), 2, '.', '') }};
      @endif
    @endForeach

    const lineChartPurchaseQuotation = document.getElementById('lineChartPurchaseQuotation');
    new Chart(lineChartPurchaseQuotation, {
      type: 'line',
      data: {
        labels: months,
        datasets: purchase_quotation_dataset1,
      },
      options: {
        legend: {
          position: 'bottom',
        },
        scales: {
          y: {
            beginAtZero: true
          }
        },
        responsive: true
      }
    });

    const pieChartPurchaseQuotation = document.getElementById('pieChartPurchaseQuotation');
    new Chart(pieChartPurchaseQuotation, {
      type: 'doughnut',
      data: {
        labels: ['Aberto', 'Cancelado', 'Fechado', 'Pendente', 'PC. Gerado'],
        datasets: purchase_quotation_dataset2,
      },
      options: {
        tooltips: {
          callbacks: {
            label: function(t, d) {
              var xLabel = d.labels[t.index];
              var value = d.datasets[t.datasetIndex].data[t.index];
              var yLabel = value >= 1000 ? value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              }) : value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              });
              return xLabel + ': ' + yLabel;
            }
          }
        },
        legend: {
          position: 'bottom',
        },
        responsive: true
      }
    });

    //end purchase quotations


    //purchase order dataset 1
    let purchase_order_dataset1 = [{
        label: 'Abertos',
        data: [],
        borderColor: '#257DBC',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#257DBC",
      },
      {
        label: 'Cancelados',
        data: [],
        borderColor: '#FF0000',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#FF0000",
      },
      {
        label: 'Fechados',
        data: [],
        borderColor: '#2EB85C',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#2EB85C",
      },
      {
        label: 'Pendentes',
        data: [],
        borderColor: '#4F5D73',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#4F5D73",
      },
      {
        label: 'Reprovados',
        data: [],
        borderColor: '#F9B115',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#F9B115",
      }
    ];

    //purchase order dataset 2
    let purchase_order_dataset2 = [{
      data: [],
      backgroundColor: [
        '#257DBC',
        '#FF0000',
        '#2EB85C',
        '#4F5D73',
        '#F9B115',
      ],
      backgroundColor: [
        '#257DBC',
        '#FF0000',
        '#2EB85C',
        '#4F5D73',
        '#F9B115',
      ],
      hoverOffset: 4,
    }];


    <?php $purchase_orders_group_months = $purchase_orders->groupBy('month'); ?>

    @foreach (getLastTwelveMonths() as $key => $date)
      purchase_order_dataset1[0].data[{{ $key }}] = 0;
      purchase_order_dataset1[1].data[{{ $key }}] = 0;
      purchase_order_dataset1[2].data[{{ $key }}] = 0;
      purchase_order_dataset1[3].data[{{ $key }}] = 0;
      purchase_order_dataset1[4].data[{{ $key }}] = 0;
      @if (!empty($purchase_orders_group_months[$date['month']]))
        @foreach ($purchase_orders_group_months[$date['month']] as $ind => $dataMonth)
          @if ($dataMonth->status == 1)
            purchase_order_dataset1[0].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 2)
            purchase_order_dataset1[1].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 0)
            purchase_order_dataset1[2].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 3)
            purchase_order_dataset1[3].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 4)
            purchase_order_dataset1[4].data[{{ $key }}] += 1;
          @endif
        @endForeach
      @endif
    @endForeach

    //chart 2
    purchase_order_dataset2[0].data[0] = 0;
    purchase_order_dataset2[0].data[1] = 0;
    purchase_order_dataset2[0].data[2] = 0;
    purchase_order_dataset2[0].data[3] = 0;
    purchase_order_dataset2[0].data[4] = 0;
    @foreach ($purchase_orders as $ind => $data)
      @if ($data->status == 1)
        purchase_order_dataset2[0].data[0] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 2)
        purchase_order_dataset2[0].data[1] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 0)
        purchase_order_dataset2[0].data[2] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 3)
        purchase_order_dataset2[0].data[3] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 4)
        purchase_order_dataset2[0].data[4] += {{ $data->docTotal }};
      @endif
    @endForeach

    const lineChartPurchaseOrder = document.getElementById('lineChartPurchaseOrder');
    new Chart(lineChartPurchaseOrder, {
      type: 'line',
      data: {
        labels: months,
        datasets: purchase_order_dataset1,
      },
      options: {
        legend: {
          position: 'bottom'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        },
        responsive: true
      }
    });

    const pieChartPurchaseOrder = document.getElementById('pieChartPurchaseOrder');
    new Chart(pieChartPurchaseOrder, {
      type: 'doughnut',
      data: {
        labels: ['Aberto', 'Cancelado', 'Fechado', 'Pendente', 'Reprovado'],
        datasets: purchase_order_dataset2,
      },
      options: {
        tooltips: {
          callbacks: {
            label: function(t, d) {
              var xLabel = d.labels[t.index];
              var value = d.datasets[t.datasetIndex].data[t.index];
              var yLabel = value >= 1000 ? value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              }) : value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              });
              return xLabel + ': ' + yLabel;
            }
          }
        },
        legend: {
          position: 'bottom'
        },
        responsive: true
      }
    });

    //end purchase orders

    //incoing invoices
    let incoing_invoice_dataset1 = [{
        label: 'Abertos',
        data: [],
        borderColor: '#257DBC',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#257DBC",
      },
      {
        label: 'Cancelados',
        data: [],
        borderColor: '#FF0000',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#FF0000",
      },
      {
        label: 'Fechados',
        data: [],
        borderColor: '#2EB85C',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#2EB85C",
      }
    ];

    //incoing invoices dataset 2
    let incoing_invoice_dataset2 = [{
      data: [],
      backgroundColor: [
        '#257DBC',
        '#FF0000',
        '#2EB85C',
      ],
      backgroundColor: [
        '#257DBC',
        '#FF0000',
        '#2EB85C',
      ],
      hoverOffset: 4,
    }];

    <?php $incoing_invoices_group_months = $incoing_invoices->groupBy('month'); ?>

    //chart 1
    @foreach (getLastTwelveMonths() as $key => $date)
      incoing_invoice_dataset1[0].data[{{ $key }}] = 0;
      incoing_invoice_dataset1[1].data[{{ $key }}] = 0;
      incoing_invoice_dataset1[2].data[{{ $key }}] = 0;
      @if (!empty($incoing_invoices_group_months[$date['month']]))
        @foreach ($incoing_invoices_group_months[$date['month']] as $ind => $dataMonth)
          @if ($dataMonth->status == 1)
            incoing_invoice_dataset1[0].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 2)
            incoing_invoice_dataset1[1].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 0)
            incoing_invoice_dataset1[2].data[{{ $key }}] += 1;
          @endif
        @endForeach
      @endif
    @endForeach

    //chart 2
    incoing_invoice_dataset2[0].data[0] = 0;
    incoing_invoice_dataset2[0].data[1] = 0;
    incoing_invoice_dataset2[0].data[2] = 0;
    incoing_invoice_dataset2[0].data[3] = 0;
    incoing_invoice_dataset2[0].data[4] = 0;
    @foreach ($incoing_invoices as $ind => $data)
      @if ($data->status == 1)
        incoing_invoice_dataset2[0].data[0] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 2)
        incoing_invoice_dataset2[0].data[1] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 0)
        incoing_invoice_dataset2[0].data[2] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 3)
        incoing_invoice_dataset2[0].data[3] += {{ $data->docTotal }};
      @endif
      @if ($data->status == 4)
        incoing_invoice_dataset2[0].data[4] += {{ $data->docTotal }};
      @endif
    @endForeach

    const lineChartIncoingInvoice = document.getElementById('lineChartIncoingInvoice');
    new Chart(lineChartIncoingInvoice, {
      type: 'line',
      data: {
        labels: months,
        datasets: incoing_invoice_dataset1,
      },
      options: {
        legend: {
          position: 'bottom'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        },
        responsive: true
      }
    });

    const pieChartIncoingInvoice = document.getElementById('pieChartIncoingInvoice');
    new Chart(pieChartIncoingInvoice, {
      type: 'doughnut',
      data: {
        labels: ['Aberto', 'Cancelado', 'Fechado'],
        datasets: incoing_invoice_dataset2,
      },
      options: {
        tooltips: {
          callbacks: {
            label: function(t, d) {
              var xLabel = d.labels[t.index];
              var value = d.datasets[t.datasetIndex].data[t.index];
              var yLabel = value >= 1000 ? value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              }) : value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              });
              return xLabel + ': ' + yLabel;
            }
          }
        },
        legend: {
          position: 'bottom'
        },
        responsive: true
      }
    });

    //end incoing invoices


    //advance providers

    let advance_provider_dataset1 = [{
        label: 'Abertos',
        data: [],
        borderColor: '#257DBC',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#257DBC",
      },
      {
        label: 'Fechados',
        data: [],
        borderColor: '#2EB85C',
        fill: false,
        tension: 0.1,
        pointRadius: 5,
        pointBackgroundColor: "#2EB85C",
      },
    ];

    //incoing invoices dataset 2
    let advance_provider_dataset2 = [{
      data: [],
      backgroundColor: [
        '#257DBC',
        '#2EB85C',
      ],
      backgroundColor: [
        '#257DBC',
        '#2EB85C',
      ],
      hoverOffset: 4,
    }];

    <?php $advance_providers_group_month = $advance_providers->groupBy('month'); ?>


    @foreach (getLastTwelveMonths() as $key => $date)
      advance_provider_dataset1[0].data[{{ $key }}] = 0;
      advance_provider_dataset1[1].data[{{ $key }}] = 0;
      @if (!empty($advance_providers_group_month[$date['month']]))
        @foreach ($advance_providers_group_month[$date['month']] as $ind => $dataMonth)
          @if ($dataMonth->status == 1)
            advance_provider_dataset1[0].data[{{ $key }}] += 1;
          @endif
          @if ($dataMonth->status == 0)
            advance_provider_dataset1[1].data[{{ $key }}] += 1;
          @endif
        @endForeach
      @endif
    @endForeach

    //chart 2
    advance_provider_dataset2[0].data[0] = 0;
    advance_provider_dataset2[0].data[1] = 0;
    @foreach ($advance_providers as $ind => $data)
      @if ($data->status == 1)
        advance_provider_dataset2[0].data[0] += {{ $data->getDocTotal() }};
      @endif
      @if ($data->status == 0)
        advance_provider_dataset2[0].data[1] += {{ $data->getDocTotal() }};
      @endif
    @endForeach

    const lineChartAdvanceProvider = document.getElementById('lineChartAdvanceProvider');
    new Chart(lineChartAdvanceProvider, {
      type: 'line',
      data: {
        labels: months,
        datasets: advance_provider_dataset1,
      },
      options: {
        legend: {
          position: 'bottom'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        },
        responsive: true
      }
    });

    const pieChartAdvanceProvider = document.getElementById('pieChartAdvanceProvider');
    new Chart(pieChartAdvanceProvider, {
      type: 'doughnut',
      data: {
        labels: ['Aberto', 'Fechado'],
        datasets: advance_provider_dataset2,
      },
      options: {
        tooltips: {
          callbacks: {
            label: function(t, d) {
              var xLabel = d.labels[t.index];
              var value = d.datasets[t.datasetIndex].data[t.index];
              var yLabel = value >= 1000 ? value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              }) : value.toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
              });
              return xLabel + ': ' + yLabel;
            }
          }
        },
        legend: {
          position: 'bottom'
        },
        responsive: true
      }
    });


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
