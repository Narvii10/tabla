{{--
    Una mesa del mapa. Recibe $mesa (arreglo de MapaMesasController::datosMapa).
    public/js/mapa-mesas.js actualiza data-estado y los elementos con data-campo cada 5 segundos.
--}}
<a href="{{ $mesa['url'] }}"
    @class(['mesa-tile', 'con-lista' => $mesa['comandas_listas'] > 0])
    data-mesa="{{ $mesa['id'] }}" data-estado="{{ $mesa['estado'] }}" data-listas="{{ $mesa['comandas_listas'] }}"
    aria-label="Mesa {{ $mesa['numero'] }}, para {{ $mesa['capacidad'] }} {{ $mesa['capacidad'] === 1 ? 'persona' : 'personas' }}">
    {{-- La mesa vista desde arriba: las sillas son su capacidad --}}
    <x-plano-mesa :numero="$mesa['numero']" :capacidad="$mesa['capacidad']" />

    <div class="mesa-tile-info">
        <span class="estado-mesa estado-{{ $mesa['estado'] }}" data-campo="estado">{{ $mesa['estado_texto'] }}</span>
        <span class="mesa-tile-tiempo" data-campo="tiempo">{{ $mesa['tiempo'] }}</span>
    </div>

    <span class="mesa-tile-aviso" data-campo="aviso" @if ($mesa['comandas_listas'] === 0) hidden @endif>
        <i class="bi bi-bell-fill" aria-hidden="true"></i>
        <span data-campo="aviso-texto">{{ $mesa['comandas_listas'] > 1 ? $mesa['comandas_listas'].' comandas listas' : '¡Comanda lista!' }}</span>
    </span>
</a>
