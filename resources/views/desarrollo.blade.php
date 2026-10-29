@extends('layouts.app')

@section('title', 'Desarrollo')

@section('content')
    <x-page-header title="Página de desarrollo" subtitle="Acceso rápido a cada área mientras el login no está listo.">
        <span class="badge badge-suave vino align-self-center px-3 py-2"><i class="bi bi-tools"></i> Temporal</span>
    </x-page-header>

    <div class="row g-3 mb-4">
        @foreach ([
            ['ruta' => 'admin.index', 'nombre' => 'Administración', 'icono' => 'bi-sliders', 'texto' => 'Menú, usuarios y mesas'],
            ['ruta' => 'mesero.index', 'nombre' => 'Mesero', 'icono' => 'bi-grid-3x3-gap', 'texto' => 'Mapa de mesas y pedidos'],
            ['ruta' => 'cocina.index', 'nombre' => 'Cocina', 'icono' => 'bi-fire', 'texto' => 'Comandas en tiempo real'],
            ['ruta' => 'caja.index', 'nombre' => 'Caja', 'icono' => 'bi-cash-coin', 'texto' => 'Cobro y cierre de caja'],
            ['ruta' => 'menu.index', 'nombre' => 'Menú público', 'icono' => 'bi-book', 'texto' => 'Lo que ve el cliente'],
        ] as $area)
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route($area['ruta']) }}" class="card card-acceso h-100 text-decoration-none">
                    <div class="card-body p-4">
                        <span class="icono-circulo mb-3"><i class="bi {{ $area['icono'] }}"></i></span>
                        <h2 class="h6 text-body mb-1" style="font-family: inherit;">{{ $area['nombre'] }}</h2>
                        <p class="small text-muted mb-0">{{ $area['texto'] }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3"><h2 class="h6 mb-0" style="font-family: inherit;">Usuarios de prueba</h2></div>
                <div class="table-responsive">
                    <table class="table table-tabla align-middle">
                        <thead>
                            <tr><th class="ps-3">Correo</th><th>Rol</th><th>Activo</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td class="ps-3"><code>{{ $usuario->email }}</code></td>
                                    <td>{{ \App\Models\User::ROLES[$usuario->rol] ?? $usuario->rol }}</td>
                                    <td>{{ $usuario->activo ? 'Sí' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted ps-3">Sin usuarios. Corre los seeders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white small text-muted">Contraseña de todos: <code>password</code></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3"><h2 class="h6 mb-0" style="font-family: inherit;">Menú por mesa (QR)</h2></div>
                <div class="list-group list-group-flush">
                    @forelse ($mesas as $mesa)
                        <a href="{{ route('menu.mesa', $mesa->token_qr) }}" target="_blank"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Mesa {{ $mesa->numero }} <small class="text-muted">· {{ $mesa->zona }}</small></span>
                            <span class="badge badge-suave neutro">{{ \App\Models\Mesa::ESTADOS[$mesa->estado] ?? $mesa->estado }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Sin mesas. Corre los seeders.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-4 small text-muted mt-4">
        @foreach ($totales as $nombre => $total)
            <div>{{ $nombre }}: <strong class="text-body">{{ $total }}</strong></div>
        @endforeach
        <div>Ejemplo de &lt;x-money&gt;: <strong class="text-body"><x-money :amount="1234.5" /></strong></div>
        <div>Hora local: <strong class="text-body">{{ now()->format('d/m/Y H:i') }}</strong></div>
    </div>
@endsection
