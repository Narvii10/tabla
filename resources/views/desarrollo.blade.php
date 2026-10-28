@extends('layouts.app')

@section('title', 'Desarrollo')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Página de desarrollo</h1>
            <p class="text-muted mb-0">Acceso rápido a cada área mientras el login no está listo.</p>
        </div>
        <span class="badge text-bg-warning">Temporal</span>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['ruta' => 'admin.index', 'nombre' => 'Administración', 'icono' => 'bi-gear', 'texto' => 'Menú, usuarios y mesas'],
            ['ruta' => 'mesero.index', 'nombre' => 'Mesero', 'icono' => 'bi-person-badge', 'texto' => 'Mapa de mesas y pedidos'],
            ['ruta' => 'cocina.index', 'nombre' => 'Cocina', 'icono' => 'bi-fire', 'texto' => 'Comandas en tiempo real'],
            ['ruta' => 'caja.index', 'nombre' => 'Caja', 'icono' => 'bi-cash-coin', 'texto' => 'Cobro y cierre de caja'],
            ['ruta' => 'menu.index', 'nombre' => 'Menú público', 'icono' => 'bi-book', 'texto' => 'Lo que ve el cliente'],
        ] as $area)
            <div class="col-6 col-md-4 col-lg">
                <a href="{{ route($area['ruta']) }}" class="card h-100 text-decoration-none shadow-sm">
                    <div class="card-body">
                        <i class="bi {{ $area['icono'] }} fs-3 text-primary"></i>
                        <h2 class="h6 mt-2 mb-1 text-body">{{ $area['nombre'] }}</h2>
                        <p class="small text-muted mb-0">{{ $area['texto'] }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Usuarios de prueba</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead>
                            <tr><th>Correo</th><th>Rol</th><th>Activo</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td><code>{{ $usuario->email }}</code></td>
                                    <td>{{ \App\Models\User::ROLES[$usuario->rol] ?? $usuario->rol }}</td>
                                    <td>{{ $usuario->activo ? 'Sí' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Sin usuarios. Corre los seeders.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white small text-muted">Contraseña de todos: <code>password</code></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Menú por mesa (QR)</div>
                <div class="list-group list-group-flush">
                    @forelse ($mesas as $mesa)
                        <a href="{{ route('menu.mesa', $mesa->token_qr) }}" target="_blank"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>Mesa {{ $mesa->numero }} <small class="text-muted">· {{ $mesa->zona }}</small></span>
                            <span class="badge text-bg-secondary">{{ \App\Models\Mesa::ESTADOS[$mesa->estado] ?? $mesa->estado }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">Sin mesas. Corre los seeders.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body d-flex flex-wrap gap-4 small">
            @foreach ($totales as $nombre => $total)
                <div><span class="text-muted">{{ $nombre }}:</span> <strong>{{ $total }}</strong></div>
            @endforeach
            <div><span class="text-muted">Ejemplo de &lt;x-money&gt;:</span> <strong><x-money :amount="1234.5" /></strong></div>
            <div><span class="text-muted">Hora local:</span> <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
        </div>
    </div>
@endsection
