@if(checkAccess('portioning'))
    <li class="nav-group @if(Route::current()->getPrefix() == "/porcionamento") active @endif">
        <a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon text-success">
                <use xlink:href="{{asset('icons_assets/custom.svg#notebook-2')}}"></use>
            </svg>Porcionamento
        </a>
        
        <ul class="nav-group-items">
            @if(checkAccess('portion_search'))
                <li class="nav-item">
                    <a class="nav-link" href="{{route('porcionamento.pesquisar')}}"><span class="nav-icon"></span> Pesquisar Nota</a>
                </li>
            @endif
            @if(checkAccess('portion_list'))
                <li class="nav-item">
                    <a class="nav-link" href="{{route('porcionamento.listar')}}"><span class="nav-icon"></span> Listar</a>
                </li>
            @endif
            @if(checkAccess('portion_loss'))
                <li class="nav-item">
                    <a class="nav-link" href="{{route('porcionamento.porcentagemPerdaListar')}}"><span class="nav-icon"></span> Porcentagens de Perda</a>
                </li>
            @endif
            @if(checkAccess('portion_justify'))
                <li class="nav-item">
                    <a class="nav-link" href="{{route('porcionamento.justificativas')}}"><span class="nav-icon"></span> Justificativas</a>
                </li>
            @endif
            @if(checkAccess('portion_loss_justify'))
                <li class="nav-item">
                    <a class="nav-link" href="{{route('porcionamento.listar-alta-perda')}}"><span class="nav-icon"></span> Porcionamentos com justificativa e/ou alta perda</a>
                </li>
            @endif
        </ul>
    </li>
    @endif


{{-- <li class="">
    <a href="javascript:" data-toggle="collapse" data-target="#demo2"><i
                class="glyphicon glyphicon-duplicate"></i> Porcionamento <i
                class="fa fa-fw fa-caret-down"></i></a>
    <ul id="demo2" class="collapse @if(Route::current()->getPrefix() == "/porcionamento") in @endif">
        <li>
            <a href="{{route('porcionamento.pesquisar')}}"><i class="glyphicon glyphicon-search"></i>
                Pesquisar Nota</a>
        </li>
        <li>
            <a href="{{route('porcionamento.listar')}}"><i class="glyphicon glyphicon-search"></i>
                Listar</a>
        </li>
        <li>
            <a href="{{route('porcionamento.porcentagemPerdaListar')}}"><i
                        class="glyphicon glyphicon-search"></i>
                Porcentagens de Perda</a>
        </li>
        <li>
            <a href="{{route('porcionamento.justificativas')}}"><i
                        class="glyphicon glyphicon-search"></i>
                Justificativas</a>
        </li>
        <li>
            <a href="{{route('porcionamento.listar-alta-perda')}}"><i
                        class="glyphicon glyphicon-search"></i>
                Porcionamentos com justificativa e/ou alta perda</a>
        </li>
    </ul>
</li> --}}