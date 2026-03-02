@extends('layouts.main')
@section('title', 'Administração')
@section('content')
  <div class="col-12">
    <h3 class="header-page">Configurações Gerais</h3>
  </div>
  <hr>
  <form action="" method="post" id="needs-validation" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <div class="accordion" id="accordionExample">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button fw-bold" type="button" data-coreui-target="#collapseOne" aria-expanded="true"
            aria-controls="collapseOne">
            Opções
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
          data-coreui-parent="#accordionExample">
          <div class="accordion-body">
            <div class="col-12 text-center">
              {{-- <a href="{{ route('settings.boot.index') }}" class="btn">
                <svg class="icon icon-xxl text-warning">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#tools') }}"></use>
                </svg>
                <div>
                  <strong>Inicilização</strong>
                </div>
              </a> --}}
              <a href="{{ route('settings.general.index') }}" class="btn">
                <svg class="icon icon-xxl text-primary">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#company-gear') }}"></use>
                </svg>
                <div>
                  <strong>Configurações Gerais</strong>
                </div>
              </a>
              <a href="{{ route('settings.logs.errors.index') }}" class="btn">
                <svg class="icon icon-xxl text-danger">
                  <use xlink:href="{{ asset('icons_assets/custom.svg#database-x') }}"></use>
                </svg>
                <div>
                  <strong>Logs de Erros</strong>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection
