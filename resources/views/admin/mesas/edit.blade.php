@extends('layouts.app')

@section('title', 'Editar mesa')

@section('content')
    <x-page-header :title="'Mesa '.$mesa->numero" subtitle="Editar mesa" :back="route('admin.mesas.index')" />

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.mesas.update', $mesa) }}">
                @method('PUT')
                @include('admin.mesas._form')
            </form>
        </div>

        {{-- Columna del QR. Cambiar el QR es un formulario aparte, fuera del de editar. --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-2">Código QR</h2>
                    <p class="small text-muted mb-3">
                        Abre el menú de esta mesa. Estado actual:
                        <span class="estado-mesa estado-{{ $mesa->estado }}">{{ \App\Models\Mesa::ESTADOS[$mesa->estado] ?? $mesa->estado }}</span>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.mesas.imprimir', ['mesa' => $mesa->id]) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-printer me-1"></i>Imprimir su QR
                        </a>
                        <a href="{{ route('menu.mesa', $mesa->token_qr) }}" target="_blank" rel="noopener" class="btn btn-light btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Abrir el menú de la mesa
                        </a>
                    </div>

                    <hr class="my-4">

                    <h3 class="h6 mb-1">¿Se perdió o se dañó el QR?</h3>
                    <p class="small text-muted mb-3">Genera uno nuevo. El QR impreso anterior dejará de funcionar y tendrás que imprimir el nuevo.</p>
                    <form method="POST" action="{{ route('admin.mesas.qr', $mesa) }}"
                          onsubmit="return confirm(@js('¿Cambiar el QR de la mesa '.$mesa->numero.'? El impreso dejará de funcionar.'))">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Generar QR nuevo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
