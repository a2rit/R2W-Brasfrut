@if (checkAccess('configuration'))
  <li class="nav-group @if (Route::current()->getPrefix() == '/ponto-venda' || Route::current()->getPrefix() == '/settings') active @endif">
    <a class="nav-link nav-group-toggle" href="#">
      <svg class="nav-icon">
        <use xlink:href="{{ asset('icons_assets/custom.svg#gear') }}"></use>
      </svg>Configurações
    </a>
    <ul class="nav-group-items" id="settings">
      @if (checkAccess('config_approvers'))
        <li class="nav-item">
          <a class="nav-link" href="{{route('settings.lofted.index')}}"><span
              class="nav-icon"></span> Alçadas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('administration.user.registration.approver')}}"><span
              class="nav-icon"></span> Aprovadores</a>
        </li>
      @endif
      @if (checkAccess('config_whs_group'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('grupo.deposito.index') }}"><span class="nav-icon"></span>Grupos de
            Depósito</a>
        </li>
      @endif
      @if (checkAccess('config_users_group'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('user.groups.index') }}"><span class="nav-icon"></span>Grupos de
            Usuário</a>
        </li>
      @endif
      @if (checkAccess('config_sale_point'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('pv.listar') }}"><span class="nav-icon"></span>Pontos de Venda</a>
        </li>
      @endif
      @if (checkAccess('config_users'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('usuarios.listar') }}"><span class="nav-icon"></span>Usuários</a>
        </li>
      @endif
      @if (Auth::user()->hasRole('admin'))
        <li class="nav-item">
          <a class="nav-link" href="{{ route('settings.index') }}"><span class="nav-icon"></span>Administração</a>
        </li>
      @endif
    </ul>
  </li>
@endif
