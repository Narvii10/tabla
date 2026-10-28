<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <meta name="theme-color" content="#2f221b">
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    {{-- Las mismas letras del menú público: Besley para títulos de página, Libre Franklin para todo lo demás --}}
    <link href="https://fonts.googleapis.com/css2?family=Besley:wght@700;800&family=Libre+Franklin:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- ?v= cambia cuando se edita el archivo, para que el navegador no use una versión vieja --}}
    <link href="{{ asset('css/tabla.css') }}?v={{ filemtime(public_path('css/tabla.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    @php
        // Áreas del sistema: nombre de ruta => texto e ícono
        $areas = [
            'admin' => ['label' => 'Administración', 'icon' => 'bi-sliders'],
            'mesero' => ['label' => 'Mesero', 'icon' => 'bi-grid-3x3-gap'],
            'cocina' => ['label' => 'Cocina', 'icon' => 'bi-fire'],
            'caja' => ['label' => 'Caja', 'icon' => 'bi-cash-coin'],
        ];

        // Área en la que estamos (según el nombre de la ruta), para mostrar sus enlaces
        $areaActual = collect(array_keys($areas))->first(fn ($area) => request()->routeIs($area.'.*'));
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark navbar-tabla">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{-- Logo de la charcutería en crema, junto al nombre del sistema --}}
                <img src="{{ asset(config('negocio.logo_crema')) }}" alt="" class="marca-logo" width="44" height="44">
                <span>{{ config('app.name') }} <span class="marca-negocio">{{ config('negocio.nombre') }}</span></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto gap-lg-1 mt-2 mt-lg-0">
                    @foreach ($areas as $area => $info)
                        <li class="nav-item">
                            <a class="nav-link @if ($areaActual === $area) active @endif" href="{{ route($area.'.index') }}">
                                <i class="bi {{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('menu.index') }}" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Ver menú público
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <span class="navbar-text small"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</span>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- Enlaces propios de cada área: se ponen en resources/views/<area>/_subnav.blade.php --}}
    @if ($areaActual && view()->exists($areaActual.'._subnav'))
        <div class="subnav-tabla">
            <div class="container">
                <ul class="nav flex-nowrap overflow-auto">
                    @include($areaActual.'._subnav')
                </ul>
            </div>
        </div>
    @endif

    <main class="container py-4 py-lg-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Los mensajes de éxito se cierran solos después de 5 segundos
        document.querySelectorAll('.alert-success').forEach((alerta) => {
            setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 5000);
        });
    </script>
    @stack('scripts')
</body>
</html>
