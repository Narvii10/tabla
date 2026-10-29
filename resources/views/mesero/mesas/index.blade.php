@extends('layouts.app')

@section('title', 'Mapa de mesas')

@section('content')
    <x-page-header title="Mesas" subtitle="Toca una mesa para ver su pedido. Se actualiza sola cada 5 segundos." />

    @if ($zonas->isEmpty())
        <div class="card">
            <x-empty-state title="Todavía no hay mesas" text="El administrador las crea en Administración → Mesas." />
        </div>
    @else
        {{-- Resumen: cuántas mesas hay en cada estado y si hay comandas por recoger --}}
        <div class="leyenda-mesas mb-4" id="leyenda-mesas">
            @foreach ($resumen as $estado => $dato)
                <span class="estado-mesa estado-{{ $estado }}">
                    {{ $dato['texto'] }} <strong data-resumen="{{ $estado }}">{{ $dato['total'] }}</strong>
                </span>
            @endforeach

            <span class="aviso-listas" id="aviso-listas" @if ($listas === 0) hidden @endif>
                <i class="bi bi-bell-fill" aria-hidden="true"></i>
                <span id="aviso-listas-texto">{{ $listas }} {{ $listas === 1 ? 'comanda lista' : 'comandas listas' }} para llevar</span>
            </span>

            <span class="estado-conexion" id="estado-conexion" role="status" aria-live="polite">
                Actualizado {{ $actualizado }}
            </span>
        </div>

        <div id="mapa-mesas" data-datos="{{ route('mesero.mesas.datos') }}">
            @foreach ($zonas as $zona => $mesas)
                {{-- Cada zona es un "cuarto" del plano, con su propio piso --}}
                <section @class(['zona-mesas', 'zona-terraza' => str_contains(mb_strtolower($zona), 'terraza')])>
                    <div class="zona-mesas-encabezado">
                        <h2 class="zona-mesas-titulo">{{ $zona }}</h2>
                        <span class="zona-mesas-conteo" data-zona-conteo="{{ $zona }}">
                            {{ $mesas->count() }} {{ $mesas->count() === 1 ? 'mesa' : 'mesas' }} ·
                            {{ $mesas->where('estado', 'libre')->count() }} libres
                        </span>
                    </div>
                    <div class="cuadricula-mesas">
                        @foreach ($mesas as $mesa)
                            @include('mesero.mesas._mesa')
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/mapa-mesas.js') }}?v={{ filemtime(public_path('js/mapa-mesas.js')) }}"></script>
@endpush
