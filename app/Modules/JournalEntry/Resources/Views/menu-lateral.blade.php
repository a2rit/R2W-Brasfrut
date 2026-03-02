@if (checkAccess('accounting'))
  <li class="nav-item">
    <a class="nav-link" href="{{ route('journal-entry.index') }}">
      <svg class="nav-icon text-info">
        <use xlink:href="{{ asset('icons_assets/custom.svg#graph-pie') }}"></use>
      </svg>Lançamento Contábil Manual
    </a>
  </li>
@endif
