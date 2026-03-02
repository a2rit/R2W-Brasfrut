<div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
  <div class="sidebar-brand pt-3 pb-3">
    <img id="sidebar-logo" style="cursor: pointer;" src="{{ url('img/logo-nova.png') }}" class="img-fluid" onclick="window.location.replace('{{route('initial')}}');">
  </div>
  
  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
    @if (checkAccess('dashboard_menu'))
      <li class="nav-group">
        <a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon text-orange">
                <use xlink:href="{{asset('icons_assets/free.svg#cil-graph')}}"></use>
            </svg>Dashboard
        </a>
        <ul class="nav-group-items">
          @if (checkAccess('dashboard_purchase'))
            <li class="nav-item">
              <a class="nav-link" href="{{ route('purchase.dashboard.index') }}"><span class="nav-icon"></span> Compras</a>
            </li>
          @endif
          @if (checkAccess('dashboard_finances'))
            <li class="nav-item">
              <a class="nav-link" href="{{ route('journal-entry.dashboard.index') }}"><span class="nav-icon"></span> Financeiro</a>
            </li>
          @endif
        </ul>
      </li>
    @endif
    @foreach (Module::enabled()->sortBy('name') as $module)
      @if (view()->exists($module['slug'] . '::menu-lateral'))
        @include($module['slug'] . '::menu-lateral')
      @endif
    @endforeach
  </ul>
  <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
</div>
