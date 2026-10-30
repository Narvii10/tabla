@extends('layouts.app')

@section('title', 'Pedidos abiertos')

@section('content')
    <x-page-header title="Pedidos abiertos" subtitle="Las mesas que se están atendiendo ahora.">
        <a href="{{ route('mesero.index') }}" class="btn btn-primary"><i class="bi bi-grid-3x3-gap me-1"></i>Mapa de mesas</a>
    </x-page-header>

    @if ($pedidos->isEmpty())
        <div class="card">
            <x-empty-state icon="bi-journal-text" title="No hay pedidos abiertos"
                           text="Abre uno tocando una mesa libre en el mapa." />
        </div>
    @else
        <div class="row g-3">
            @foreach ($pedidos as $pedido)
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('mesero.pedidos.show', $pedido) }}" class="card tarjeta-pedido h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="numero-mesa">{{ $pedido->mesa->numero }}</span>
                                <div class="flex-grow-1 min-w-0">
                                    <span class="estado-mesa estado-{{ $pedido->mesa->estado }}">
                                        {{ \App\Models\Mesa::ESTADOS[$pedido->mesa->estado] ?? $pedido->mesa->estado }}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        {{ $pedido->mesero->name }} · desde las {{ $pedido->abierto_en->format('H:i') }}
                                    </div>
                                </div>
                                <div class="text-end fw-semibold text-body">
                                    <x-money :amount="$pedido->subtotal()" />
                                </div>
                            </div>

                            @if ($pedido->comandas_listas > 0)
                                <div class="mesa-tile-aviso mt-3">
                                    <i class="bi bi-bell-fill" aria-hidden="true"></i>
                                    {{ $pedido->comandas_listas > 1 ? $pedido->comandas_listas.' comandas listas' : '¡Comanda lista!' }}
                                </div>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
