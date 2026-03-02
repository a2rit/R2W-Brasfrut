@if (checkAccess('b_partners'))
<li class="nav-item">
  <a class="nav-link" href="{{ route('partners.index') }}">
    <svg class="nav-icon text-turquoise">
      <use xlink:href="{{ asset('icons_assets/custom.svg#partners') }}"></use>
    </svg>Parceiros de Negócios
  </a>
</li>
@endif
