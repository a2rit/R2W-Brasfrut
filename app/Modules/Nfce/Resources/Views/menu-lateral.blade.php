@if (checkAccess('erros') || checkAccess('nfcs'))
  <li class="nav-group @if (Route::current()->getPrefix() == '/erros') active @endif">
    <a class="nav-link nav-group-toggle" href="#">
      <svg class="nav-icon text-danger">
        <use xlink:href="{{ asset('icons_assets/custom.svg#nfce-error') }}"></use>
      </svg>Integração NFC-e
    </a>
    <ul class="nav-group-items">
      @if (checkAccess('erros'))
      <li class="nav-item">
          <a class="nav-link" href="{{ route('erros.listar') }}">
            <span class="nav-icon"></span>
            Erros de Sincronização
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('erros.estoque-nfce') }}">
            <span class="nav-icon"></span>
            Estoque Insuficiente NFCe
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('erros.estoque-op') }}">
            <span class="nav-icon"></span>
            Estoque Insuficiente OP
          </a>
        </li>
      @endif
      @if (checkAccess('nfcs'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('erros.contingencia') }}">
              <span class="nav-icon"></span>
              NFCs em contingência
          </a>
        </li>
      @endif
    </ul>
  </li>
@endif
