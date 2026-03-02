
@if(checkAccess('inventoryx'))
<li class="nav-group @if(Route::current()->getPrefix() == "/inventory") active @endif">
    <a class="nav-link nav-group-toggle" href="#">
        <svg class="nav-icon text-yellow">
            <use xlink:href="{{asset('icons_assets/custom.svg#box')}}"></use>
        </svg>Estoque
    </a>
    <ul class="nav-group-items" id="inventory">
        @if(checkAccess('inventory_items'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.items.index') }}"><span class="nav-icon"></span>Cadastro de itens</a></li>
        @endif
        @if(checkAccess('inventory_stock_loan'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.stockloan.index') }}"><span class="nav-icon"></span>Empréstimo de ferramentas</a></li>
        @endif
        @if(checkAccess('inventory_input'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.input.index') }}"><span class="nav-icon"></span>Entrada de Mercadorias</a></li>
        @endif
        @if(checkAccess('inventory_transfer_taking'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.transferTaking.index') }}"><span class="nav-icon"></span>Pedido de transferência</a></li>
        @endif
        @if(checkAccess('inventory_request'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.request.index') }}"><span class="nav-icon"></span>Requisição Interna</a></li>
        @endif
        @if(checkAccess('inventory_output'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.output.index') }}"><span class="nav-icon"></span>Saida de Mercadorias</a></li>
        @endif
        @if(checkAccess('inventory_transfer'))
            <li class="nav-item"><a class="nav-link" href="{{ route('inventory.transfer.index') }}"><span class="nav-icon"></span>Transferências</a></li>
        @endif
        <li class="nav-item"><a class="nav-link" href="{{ route('inventory.report.index') }}"><span class="nav-icon"></span>Relatórios</a></li>
        
      </ul>
</li>
@endif