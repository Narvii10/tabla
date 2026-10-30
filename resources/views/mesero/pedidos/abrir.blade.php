@extends('layouts.app')

@section('title', 'Abrir pedido · Mesa '.$mesa->numero)

@section('content')
    <x-page-header title="Mesa {{ $mesa->numero }}" subtitle="Esta mesa está libre. ¿Llegaron clientes?" :back="route('mesero.index')" />

    <div class="card mx-auto" style="max-width: 420px">
        <div class="card-body text-center py-4">
            {{-- .mesa-tile pone los colores de la mesa; aquí solo se usa para dibujarla --}}
            <div class="mesa-tile mx-auto border-0 bg-transparent" data-estado="libre" style="max-width: 220px">
                <x-plano-mesa :numero="$mesa->numero" :capacidad="$mesa->capacidad" />
            </div>
            <p class="text-muted mt-2 mb-4">
                Para {{ $mesa->capacidad }} {{ $mesa->capacidad === 1 ? 'persona' : 'personas' }}@if ($mesa->zona) · {{ $mesa->zona }}@endif
            </p>

            <form method="POST" action="{{ route('mesero.mesas.pedido.abrir', $mesa) }}">
                @csrf
                <button class="btn btn-primary btn-lg w-100"><i class="bi bi-journal-plus me-1"></i>Abrir pedido</button>
            </form>
        </div>
    </div>
@endsection
