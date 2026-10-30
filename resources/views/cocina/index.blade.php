@extends('layouts.app')

@section('title', 'Cocina')

@section('content')
    <div id="cocina" data-datos="{{ route('cocina.comandas.datos') }}"
         data-estado="{{ route('cocina.comandas.estado', ['comanda' => '__ID__']) }}"
         data-agotar="{{ route('cocina.productos.agotado', ['producto' => '__ID__']) }}">

        <x-page-header title="Cocina" subtitle="Las comandas más antiguas van primero. Se actualiza sola cada 5 segundos.">
            {{-- Botón "Se acabó…": marca un producto como agotado en el menú y en pedidos --}}
            <form class="d-flex gap-2" id="form-agotar">
                <select class="form-select" id="producto-agotar" aria-label="Producto que se acabó" required>
                    <option value="">¿Se acabó algo?</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-primary text-nowrap">Marcar agotado</button>
            </form>
        </x-page-header>

        {{-- Resumen de lo que hay que preparar, sumando todas las comandas --}}
        <div class="resumen-cocina mb-4">
            <span class="resumen-cocina-titulo">Por preparar</span>
            <div id="resumen-cocina" class="resumen-cocina-lista"><span class="text-muted small">Cargando…</span></div>
            <span class="estado-conexion" id="estado-conexion" role="status" aria-live="polite"></span>
        </div>

        <noscript><div class="alert alert-danger">La pantalla de cocina necesita JavaScript para actualizarse.</div></noscript>

        <div class="tablero-cocina">
            @foreach (['pendiente' => 'Pendientes', 'preparando' => 'En preparación', 'listo' => 'Listas para llevar'] as $estado => $titulo)
                <section class="columna-cocina" aria-labelledby="titulo-{{ $estado }}">
                    <h2 class="columna-cocina-titulo" id="titulo-{{ $estado }}">
                        {{ $titulo }} <span class="columna-cocina-cuenta" data-cuenta="{{ $estado }}">0</span>
                    </h2>
                    <div class="columna-cocina-lista" data-columna="{{ $estado }}"></div>
                </section>
            @endforeach
        </div>
    </div>

    {{-- Plantilla de una comanda: cocina.js la copia y la llena con los datos --}}
    <template id="plantilla-comanda">
        <article class="comanda-cocina">
            <header class="comanda-cocina-encabezado">
                <span class="comanda-cocina-mesa"></span>
                <span class="comanda-cocina-tiempo"></span>
            </header>
            <ul class="comanda-cocina-productos"></ul>
            <button type="button" class="btn btn-lg w-100 comanda-cocina-boton"></button>
        </article>
    </template>
@endsection

@push('scripts')
    <script src="{{ asset('js/cocina.js') }}?v={{ filemtime(public_path('js/cocina.js')) }}"></script>
@endpush
