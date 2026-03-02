@if(checkAccess('purchasex'))
<li class="nav-group @if(Route::current()->getPrefix() == "/purchase") active @endif">
    <a class="nav-link nav-group-toggle" href="#">
        <svg class="nav-icon text-primary">
            <use xlink:href="{{asset('icons_assets/free.svg#cil-cart')}}"></use>
        </svg>Compras
    </a>
    <ul class="nav-group-items">
        @if(checkAccess('purchase_suggestion_order') || checkAccess('purchase_suggestion_request'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.suggestion.index') }}"><span class="nav-icon"></span> Sugestão de Compras</a>
            </li>
        @endif
        
        @if(checkAccess('purchase_request'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.request.index') }}"><span class="nav-icon"></span> Solicitação de Compras</a>
            </li>
        @endif
        
        @if(checkAccess('purchase_quotation'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.quotation.index') }}"><span class="nav-icon"></span> Cotação de Compras</a>
            </li>
        @endif

        @if(checkAccess('purchase_order'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.order.index') }}"><span class="nav-icon"></span> Pedido de Compras</a>
            </li>
        @endif
        @if(checkAccess('purchase_nfc'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.ap.invoice.index') }}"><span class="nav-icon"></span> NFE Serviços</a>
            </li>
        @endif
        @if(checkAccess('purchase_advance_provider'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('purchase.advance.provider.index') }}"><span class="nav-icon"></span> Adiantamento para Fornecedores</a>
            </li>
        @endif
        
        {{--<li><a href="{{ route('purchase.receipts.goods.index') }}">Recebimento de Mercadoria</a></li>--}}
        
    </ul>
</li>
@endif