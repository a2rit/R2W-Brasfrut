@if(checkAccess('intern_consumption'))
<li class="nav-group @if(Route::current()->getPrefix() == "/consumo-interno") active @endif">
    <a class="nav-link nav-group-toggle" href="#">
        <svg class="nav-icon">
            <use xlink:href="{{asset('icons_assets/custom.svg#notebook')}}"></use>
        </svg>Consumo Interno
    </a>
    <ul class="nav-group-items" id="consumo-interno">
        <li class="nav-item">
            <a class="nav-link" href="{{route('consumo-interno.index')}}"><span class="nav-icon"></span> Lançamento</a>
        </li>
        <li>
            <a href="{{route('consumo-interno.listar')}}"><span class="nav-icon"></span> Listar</a>
        </li>
    </ul>
</li>
@endif