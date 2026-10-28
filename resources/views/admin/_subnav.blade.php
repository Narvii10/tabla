{{-- Enlaces del área de administración. Cada módulo agrega aquí su enlace. --}}
<li class="nav-item">
    <a class="nav-link text-nowrap @if (request()->routeIs('admin.productos.*')) active @endif"
       href="{{ route('admin.productos.index') }}"><i class="bi bi-basket"></i> Productos</a>
</li>
<li class="nav-item">
    <a class="nav-link text-nowrap @if (request()->routeIs('admin.categorias.*')) active @endif"
       href="{{ route('admin.categorias.index') }}"><i class="bi bi-tags"></i> Categorías</a>
</li>
