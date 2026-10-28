<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">
    @php
        // Áreas del sistema: nombre de ruta => texto e ícono
        $areas = [
            'admin' => ['label' => 'Administración', 'icon' => 'bi-gear'],
            'mesero' => ['label' => 'Mesero', 'icon' => 'bi-person-badge'],
            'cocina' => ['label' => 'Cocina', 'icon' => 'bi-fire'],
            'caja' => ['label' => 'Caja', 'icon' => 'bi-cash-coin'],
        ];
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="{{ url('/') }}">
                <i class="bi bi-egg-fried"></i> {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    @foreach ($areas as $area => $info)
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs($area.'.*')) active @endif"
                               href="{{ route($area.'.index') }}">
                                <i class="bi {{ $info['icon'] }}"></i> {{ $info['label'] }}
                            </a>
                        </li>
                    @endforeach
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('menu.index') }}" target="_blank">
                            <i class="bi bi-book"></i> Menú público
                        </a>
                    </li>
                </ul>
                @auth
                    <span class="navbar-text small">{{ auth()->user()->name }}</span>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Enlaces propios de cada área: @section('subnav') ... @endsection --}}
    @hasSection('subnav')
        <div class="bg-white border-bottom">
            <div class="container py-2">
                <ul class="nav nav-pills small">
                    @yield('subnav')
                </ul>
            </div>
        </div>
    @endif

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
