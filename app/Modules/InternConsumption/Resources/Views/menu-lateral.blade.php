{{--
<li class="">
    <a href="javascript:;" data-toggle="collapse" data-target="#consumo-interno"><i class="glyphicon glyphicon-duplicate">
        </i> Consumo Interno <i class="fa fa-fw fa-caret-down"></i></a>
    <ul id="consumo-interno" class="collapse @if(Route::current()->getPrefix() == "/intern-consumption") in @endif" >
        <li>
            <a href="{{route('intern-consumption.create')}}"><i class="glyphicon glyphicon-search"></i> Lançamento</a>
        </li>
        <li>
            <a href="{{route('intern-consumption.index')}}"><i class="glyphicon glyphicon-search"></i> Listar</a>
        </li>
    </ul>
</li>--}}
@if(checkAccess('intern_consumption'))
    <li class="nav-item">
        <a class="nav-link" href="{{route('intern-consumption.index')}}">
            <svg class="nav-icon text-purple">
                <use xlink:href="{{asset('icons_assets/custom.svg#notebook')}}"></use>
            </svg>Consumo Interno
        </a>
    </li>
@endif
