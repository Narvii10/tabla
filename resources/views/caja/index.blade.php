@extends('layouts.app')

@section('title', 'Caja')

@section('content')
    <x-page-header title="Caja" subtitle="Cuentas abiertas. Las que pidieron la cuenta van primero.">
        <a href="{{ route('caja.cierre.create') }}" class="btn btn-outline-primary">
            <i class="bi bi-calculator me-1"></i>Cierre del día
        </a>
    </x-page-header>

    @include('caja._vuelto')

    @if ($pedidos->isEmpty())
        <div class="card">
            <x-empty-state icon="bi-cash-coin" title="No hay cuentas abiertas"
                           text="Aquí aparecerán las mesas en cuanto tengan un pedido abierto." />
        </div>
    @else
        <div class="row g-3">
            @foreach ($pedidos as $pedido)
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('caja.pedidos.show', $pedido) }}"
                       @class(['card tarjeta-pedido h-100 text-decoration-none', 'tarjeta-pedido-cuenta' => $pedido->mesa->estado === 'cuenta'])>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="numero-mesa">{{ $pedido->mesa->numero }}</span>
                                <div class="flex-grow-1 min-w-0">
                                    <span class="estado-mesa estado-{{ $pedido->mesa->estado }}">
                                        {{ \App\Models\Mesa::ESTADOS[$pedido->mesa->estado] ?? $pedido->mesa->estado }}
                                    </span>
                                    <div class="small text-muted mt-1">desde las {{ $pedido->abierto_en->format('H:i') }}</div>
                                </div>
                            </div>

                            <div class="row text-center mt-3 small">
                                <div class="col">
                                    <div class="text-muted">Total</div>
                                    <x-money :amount="$pedido->total()" />
                                </div>
                                <div class="col">
                                    <div class="text-muted">Pagado</div>
                                    <x-money :amount="$pedido->pagado()" />
                                </div>
                                <div class="col">
                                    <div class="text-muted">Saldo</div>
                                    <x-money :amount="$pedido->saldo()" class="fw-semibold" />
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
