<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3b2a20">
    <title>@yield('title', 'Menú') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #faf7f2; }
        .public-header { background: #3b2a20; color: #fff; }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Pensado para celular: encabezado fijo arriba y contenido en una columna --}}
    <header class="public-header sticky-top shadow-sm">
        <div class="container py-3">
            <a href="{{ route('menu.index') }}" class="text-white text-decoration-none fs-5 fw-semibold">
                {{ config('app.name') }}
            </a>
            @hasSection('subtitle')
                <div class="small opacity-75">@yield('subtitle')</div>
            @endif
        </div>
    </header>

    <main class="container py-3" style="max-width: 720px;">
        @yield('content')
    </main>

    <footer class="text-center text-muted small py-4">
        Precios en quetzales, incluyen IVA.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
