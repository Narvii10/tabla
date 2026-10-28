{{--
    Encabezado de página con título, subtítulo opcional y botones a la derecha.
    <x-page-header title="Productos" subtitle="Platos y vinos del menú">
        <a href="..." class="btn btn-primary">Nuevo</a>
    </x-page-header>
--}}
@props(['title', 'subtitle' => null, 'back' => null])
<div class="encabezado-pagina d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="small text-decoration-none d-inline-block mb-1">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p class="subtitulo">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
