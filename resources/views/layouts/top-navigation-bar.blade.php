
<div class="justify-content-start mt-2">
  <a id="back_page_url" class="btn btn-sm text-primary float-start d-none p-1 text-tooltip" data-coreui-placement="bottom" title="Voltar à página inicial">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/free.svg#cil-arrow-thick-to-left') }}"></use>
    </svg>
  </a>
  <a id="refresh_page_url" href="javascript:window.location.reload(true)" class="btn btn-sm text-primary p-1 float-start ms-2 text-tooltip d-none" data-coreui-placement="bottom" title="Atualizar registro">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/free.svg#cil-reload') }}"></use>
    </svg>
  </a>
  <div id="dropdown_print_div" class="dropdown float-end d-none text-tooltip" title="Imprimir espelho">
    <button class="btn btn-sm text-primary dropdown-toggle text-tooltip" type="button" data-coreui-toggle="dropdown">
      <svg class="icon icon-xl">
        <use xlink:href="{{ asset('icons_assets/custom.svg#printer') }}"></use>
      </svg>
    </button>
    <ul class="dropdown-menu">
    </ul>
  </div>
  <button id="search-document-button" class="btn btn-sm text-primary text-tooltip d-none" data-coreui-placement="bottom" title="Procurar" type="button" data-coreui-toggle="modal" data-coreui-target="#searchDocumentTopNavModal">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/free.svg#cil-zoom') }}"></use>
    </svg>
  </button>
  <button id="send-to-email" class="btn btn-sm text-primary text-tooltip d-none" data-coreui-placement="bottom" title="Enviar por e-mail" type="button">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/custom.svg#send-email') }}"></use>
    </svg>
  </button>
</div>
<div class="ms-auto mt-2">
  <a id="previous_record_url" class="btn btn-sm text-primary me-2 d-none text-tooltip" data-coreui-placement="bottom" title="Registro anterior">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/free.svg#cil-arrow-left') }}"></use>
    </svg>
  </a>
  <a id="create_record_url" class="btn btn-sm text-primary me-2 d-none text-tooltip" data-coreui-placement="bottom" title="Adicionar">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/custom.svg#new-document') }}"></use>
    </svg>
  </a>
  <a id="next_record_url" class="btn btn-sm text-primary d-none text-tooltip" data-coreui-placement="bottom" title="Próximo registro">
    <svg class="icon icon-xl">
      <use xlink:href="{{ asset('icons_assets/free.svg#cil-arrow-right') }}"></use>
    </svg>
  </a>
</div>