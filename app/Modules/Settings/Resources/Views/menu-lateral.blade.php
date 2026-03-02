@if(checkAccess('configuration'))
                {{-- <li class="">
                    <a href="javascript:" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-wrench"></i>
                        Configurações <i class="fa fa-fw fa-caret-down"></i></a>
                    <ul id="demo" class="collapse @if(Route::current()->getPrefix() == "/ponto-venda" || Route::current()->getPrefix() == "/settings" ) in @endif">
                        {{-- @if(checkAccess('config_boot'))
                        <li>
                            <a href="{{route('settings.boot.index')}}">Inicialização</a>
                        </li>
                        @endif 
                        @if(checkAccess('config_approvers'))
                        <li>
                            <a href="{{route('administration.user.registration.approver.index')}}"> Aprovadores</a>
                        </li>
                        @endif
                        @if(checkAccess('config_whs_group'))
                        <li>
                            <a href="{{route('grupo.deposito.index')}}">Grupos de Depósito</a>
                        </li>
                        @endif
                        @if(checkAccess('config_users_group'))
                        <li>
                            <a href="{{route('user.groups.index')}}">Grupos de Usuário</a>
                        </li>
                        @endif
                        @if(checkAccess('config_sale_point'))
                        <li>
                            <a href="{{route('pv.listar')}}">Pontos de Venda</a>
                        </li>
                        @endif
                        @if(checkAccess('config_users'))
                        <li>
                            <a href="{{route('usuarios.listar')}}">Usuários</a>
                        </li>
                        @endif
                        
                    </ul>
                </li> --}}
@endif
