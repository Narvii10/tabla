{{-- Enlaces del área del mesero. Cada módulo agrega aquí su enlace. --}}
<li class="nav-item">
    <a class="nav-link text-nowrap @if (request()->routeIs('mesero.index')) active @endif"
       href="{{ route('mesero.index') }}"><i class="bi bi-grid-3x3-gap"></i> Mapa de mesas</a>
</li>
