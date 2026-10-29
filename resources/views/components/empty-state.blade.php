{{--
    Mensaje cuando una lista no tiene resultados. Muestra a Salamín triste.
    <x-empty-state icon="bi-basket" title="No hay productos" text="Crea el primero." />
    Con :mascota="false" se muestra el ícono en lugar de la mascota.
--}}
@props(['icon' => 'bi-inbox', 'title', 'text' => null, 'mascota' => true])
<div class="estado-vacio">
    @if ($mascota)
        <x-mascota data-humor="triste" aria-hidden="true" />
    @else
        <span class="icono-circulo"><i class="bi {{ $icon }}"></i></span>
    @endif
    <h2 class="h6 text-body mb-1">{{ $title }}</h2>
    @if ($text)
        <p class="small mb-0">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
