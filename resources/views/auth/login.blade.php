<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2f221b">
    <title>Iniciar sesión · {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Besley:wght@700;800&family=Libre+Franklin:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/tabla.css') }}?v={{ filemtime(public_path('css/tabla.css')) }}" rel="stylesheet">
</head>
{{-- Pantalla de login: una hoja del menú de papel sobre la madera del mostrador (estilos en tabla.css, sección "Login y usuarios") --}}
<body class="pantalla-login">
    <main class="login-contenedor">
        <div class="login-hoja">
            {{-- Salamín asomado detrás del borde de arriba de la hoja --}}
            <x-mascota class="login-mascota" aria-hidden="true" />

            <div class="text-center mb-4">
                <img src="{{ asset(config('negocio.logo')) }}" alt="{{ config('negocio.nombre') }}" class="login-logo" width="96" height="96">
                <h1 class="login-titulo">{{ config('app.name') }}</h1>
                <p class="login-subtitulo mb-0">Sistema de {{ config('negocio.nombre') }}</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2 small" role="status">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           required autocomplete="username" autofocus placeholder="tu@correo.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                           required autocomplete="current-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                </button>
            </form>

            {{-- Solo en desarrollo (APP_ENV=local): un clic llena el formulario con un usuario de prueba --}}
            @if ($usuariosDePrueba->isNotEmpty())
                <div class="login-prueba">
                    <p class="small fw-semibold mb-2"><i class="bi bi-tools me-1"></i>Usuarios de prueba <span class="fw-normal text-muted">· contraseña: <code>password</code></span></p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($usuariosDePrueba as $usuario)
                            <button type="button" class="btn btn-sm btn-light" data-correo="{{ $usuario->email }}">
                                {{ \App\Models\User::ROLES[$usuario->rol] ?? $usuario->rol }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <a href="{{ route('menu.index') }}" class="login-menu-publico">
            <i class="bi bi-book me-1"></i>Ver el menú público
        </a>
    </main>

    @if ($usuariosDePrueba->isNotEmpty())
        <script>
            document.querySelectorAll('[data-correo]').forEach((boton) => {
                boton.addEventListener('click', () => {
                    document.getElementById('email').value = boton.dataset.correo;
                    document.getElementById('password').value = 'password';
                    document.getElementById('password').focus();
                });
            });
        </script>
    @endif
</body>
</html>
