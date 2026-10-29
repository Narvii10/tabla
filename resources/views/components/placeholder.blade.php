{{-- Aviso para páginas provisionales: <x-placeholder module="Cocina" branch="feature/cocina" /> --}}
@props(['module', 'branch'])
<div class="card">
    <div class="card-body text-center py-5">
        <span class="icono-circulo mb-3"><i class="bi bi-cone-striped"></i></span>
        <h2 class="h4">Módulo de {{ $module }} en construcción</h2>
        <p class="text-muted mb-0">
            Esta página es provisional. Se reemplaza en la rama <code>{{ $branch }}</code>.
        </p>
        {{ $slot }}
    </div>
</div>
