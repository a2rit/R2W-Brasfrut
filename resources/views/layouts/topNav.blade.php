<header class="header header-sticky mb-4">
  <div class="container-fluid d-flex flex-row">
    <button class="header-toggler px-md-0 me-md-3" type="button"
      onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
      <svg class="icon icon-lg">
        <use xlink:href="{{ asset('icons_assets/custom.svg#list') }}"></use>
      </svg>
    </button>
    
    @include('layouts.top-navigation-bar')
    
    <ul class="header-nav ms-auto">
      <li class="nav-item dropdown d-md-down-none">
        <a class="nav-link" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true"
          aria-expanded="false">
          <svg class="icon icon-lg">
            <use xlink:href="{{ asset('icons_assets/free.svg#cil-bell') }}"></use>
          </svg>
        </a>
      </li>
    </ul>
    <ul class="header-nav ms-3">
      <li class="nav-item dropdown">
        <a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true"
          aria-expanded="false">
          <svg class="icon icon-lg">
            <use xlink:href="{{ asset('icons_assets/free.svg#cil-user') }}"></use>
          </svg>
          <span class="text-muted">{{substr(strtoupper(Auth::user()->name), 0, 15)}}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end pt-0">
          <div class="dropdown-header bg-light py-2">
            <div class="fw-semibold">Conta</div>
          </div>
          <a class="dropdown-item" href="{{ route('mudarSenha') }}">
            Alterar senha
          </a>
          
          @if (checkAccess('tomticket'))
            <a class="dropdown-item" href="{{ route('tomticket.geraracessorapido') }}" target="_blank">
              Suporte - TomTicket
            </a>
          @endif
          <a class="dropdown-item" href="{{ url('/changelogs') }}" >
            Mural de avisos  
          </a>
          <a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault();
            document.getElementById('logout-form').submit();">
             Sair  
          </a>
        </div>
      </li>
    </ul>
  </div>
  <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>
</header>
