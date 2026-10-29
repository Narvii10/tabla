{{-- Campos compartidos por crear y editar. Recibe $usuario. --}}
@csrf

@php
    $editando = $usuario->exists;
    // Si el admin se edita a sí mismo, no puede quitarse el rol de administrador
    $esYo = $editando && $usuario->is(auth()->user());

    // Qué ve cada rol al entrar
    $areasPorRol = [
        'admin' => ['icono' => 'bi-sliders', 'texto' => 'Entra a todo: menú, mesas, usuarios, mesero, cocina y caja.'],
        'mesero' => ['icono' => 'bi-grid-3x3-gap', 'texto' => 'Entra al mapa de mesas y toma los pedidos.'],
        'cocina' => ['icono' => 'bi-fire', 'texto' => 'Entra a la pantalla de cocina con las comandas.'],
        'caja' => ['icono' => 'bi-cash-coin', 'texto' => 'Entra a las cuentas, cobros y cierre de caja.'],
    ];
@endphp

<div class="card">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}"
                       class="form-control @error('name') is-invalid @enderror" required maxlength="100"
                       autocomplete="off" autofocus placeholder="Ej. María López">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
                <label for="email" class="form-label">Correo</label>
                <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required maxlength="150"
                       autocomplete="off" placeholder="maria@tabla.test">
                <div class="form-text">Con este correo inicia sesión.</div>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Rol: cuatro opciones que explican a qué área entra cada uno --}}
            <div class="col-12">
                <span class="form-label d-block">Rol</span>
                @if ($esYo)
                    <div class="form-text mt-0 mb-2">Es tu propio usuario: no puedes quitarte el rol de administrador.</div>
                @endif
                <div class="row g-2">
                    @foreach (\App\Models\User::ROLES as $valor => $nombre)
                        <div class="col-sm-6">
                            <input type="radio" class="btn-check" name="rol" id="rol-{{ $valor }}" value="{{ $valor }}"
                                   @checked(old('rol', $usuario->rol) === $valor)
                                   @disabled($esYo && $valor !== 'admin') required>
                            <label class="opcion-rol" for="rol-{{ $valor }}">
                                <i class="bi {{ $areasPorRol[$valor]['icono'] }}"></i>
                                <span>
                                    <span class="d-block fw-semibold">{{ $nombre }}</span>
                                    <span class="d-block small text-muted">{{ $areasPorRol[$valor]['texto'] }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('rol') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
                <label for="password" class="form-label">
                    {{ $editando ? 'Contraseña nueva' : 'Contraseña' }}
                    @if ($editando) <span class="text-muted fw-normal">(opcional)</span> @endif
                </label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       @unless ($editando) required @endunless minlength="8" autocomplete="new-password">
                <div class="form-text">
                    {{ $editando ? 'Déjala vacía para que siga la misma.' : 'Mínimo 8 caracteres.' }}
                </div>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
                <label for="password_confirmation" class="form-label">Repite la contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                       @unless ($editando) required @endunless minlength="8" autocomplete="new-password">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar usuario</button>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-light">Cancelar</a>
        </div>
    </div>
</div>
