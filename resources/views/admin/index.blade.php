@extends('layouts.app')

@section('title', 'Administración')

@section('content')
    <x-page-header title="Administración" subtitle="Configura el menú, las mesas y los usuarios." />

    <div class="row g-3">
        @foreach ([
            ['ruta' => 'admin.productos.index', 'icono' => 'bi-basket', 'nombre' => 'Productos', 'texto' => 'Platos, vinos y bebidas. Precios, fotos y agotados.'],
            ['ruta' => 'admin.categorias.index', 'icono' => 'bi-tags', 'nombre' => 'Categorías', 'texto' => 'Secciones del menú y su orden.'],
            ['ruta' => 'admin.mesas.index', 'icono' => 'bi-grid-3x3-gap', 'nombre' => 'Mesas', 'texto' => 'Números, zonas y el QR de cada mesa.'],
        ] as $acceso)
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route($acceso['ruta']) }}" class="card card-acceso h-100 text-decoration-none">
                    <div class="card-body p-4">
                        <span class="icono-circulo mb-3"><i class="bi {{ $acceso['icono'] }}"></i></span>
                        <h2 class="h5 text-body mb-1">{{ $acceso['nombre'] }}</h2>
                        <p class="small text-muted mb-0">{{ $acceso['texto'] }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
