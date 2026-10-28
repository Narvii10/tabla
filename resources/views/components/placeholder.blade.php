{{-- Aviso para páginas provisionales: <x-placeholder module="Cocina" branch="feature/cocina" /> --}}
@props(['module', 'branch'])
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-cone-striped display-4 text-warning"></i>
        <h2 class="h4 mt-3">Módulo de {{ $module }} en construcción</h2>
        <p class="text-muted mb-0">
            Esta página es provisional. Se reemplaza en la rama <code>{{ $branch }}</code>.
        </p>
        {{ $slot }}
    </div>
</div>
