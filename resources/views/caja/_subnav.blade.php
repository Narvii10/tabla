<li class="nav-item">
    <a class="nav-link text-nowrap @if (request()->routeIs('caja.index', 'caja.pedidos.*')) active @endif"
       href="{{ route('caja.index') }}"><i class="bi bi-cash-coin"></i> Cuentas</a>
</li>
<li class="nav-item">
    <a class="nav-link text-nowrap @if (request()->routeIs('caja.cierre.*')) active @endif"
       href="{{ route('caja.cierre.create') }}"><i class="bi bi-calculator"></i> Cierre del día</a>
</li>
