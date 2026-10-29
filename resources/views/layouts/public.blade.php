<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    {{-- viewport-fit=cover: la página llega hasta el notch y el CSS separa el contenido con env(safe-area-inset-*) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    {{-- Color de la barra del celular: el mismo café de la madera que está arriba de la página --}}
    <meta name="theme-color" content="#2f221b">
    <title>@yield('title', 'Menú') · {{ config('negocio.nombre') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Besley: letra de cartel antiguo para títulos. Libre Franklin: texto. --}}
    <link href="https://fonts.googleapis.com/css2?family=Besley:wght@700;800&family=Libre+Franklin:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/menu.css') }}?v={{ filemtime(public_path('css/menu.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="menu-publico">
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
