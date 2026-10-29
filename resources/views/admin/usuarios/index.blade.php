@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <x-page-header title="Usuarios" subtitle="Quién puede entrar al sistema y a qué área.">
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nuevo usuario
        </a>
    </x-page-header>

    {{-- Resumen: usuarios activos por rol. Cada número filtra el listado por ese rol. --}}
    <div class="resumen resumen-usuarios mb-4">
        @foreach (\App\Models\User::ROLES as $valor => $nombre)
            <a href="{{ route('admin.usuarios.index', ['rol' => $rol === $valor ? null : $valor]) }}"
               class="dato-resumen @if ($rol === $valor) activo @endif">
                <div class="valor">{{ $porRol[$valor] ?? 0 }}</div>
                <div class="etiqueta">{{ $nombre }}</div>
            </a>
        @endforeach
    </div>

    {{-- En celular cada usuario se muestra como tarjeta (ver .card-listado y .tabla-tarjetas en tabla.css) --}}
    <div class="card card-listado">
        {{-- Filtros --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control ps-5"
                               placeholder="Nombre o correo" aria-label="Buscar por nombre o correo">
                    </div>
                </div>
                <div class="col-8 col-md-4">
                    <select name="rol" class="form-select" aria-label="Filtrar por rol" onchange="this.form.submit()">
                        <option value="">Todos los roles</option>
                        @foreach (\App\Models\User::ROLES as $valor => $nombre)
                            <option value="{{ $valor }}" @selected($rol === $valor)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-fill">Buscar</button>
                    @if ($buscar !== '' || $rol !== '')
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-link px-2" title="Quitar filtros" aria-label="Quitar filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @if ($usuarios->isEmpty())
            <x-empty-state title="No se encontraron usuarios" text="Prueba con otro nombre o correo, o crea un usuario nuevo.">
                <a href="{{ route('admin.usuarios.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo usuario</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-tabla table-hover align-middle tabla-tarjetas">
                    <thead>
                        <tr>
                            <th class="ps-3">Usuario</th>
                            <th class="d-none d-md-table-cell">Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-end pe-3"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            @php($esYo = $usuario->is(auth()->user()))
                            <tr class="@unless ($usuario->activo) fila-desactivada @endunless">
                                <td class="ps-3 celda-principal">
                                    <div class="d-flex align-items-center gap-3">
                                        {{-- Inicial del nombre, como avatar --}}
                                        <span class="avatar-usuario rol-{{ $usuario->rol }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold nombre-usuario text-truncate">
                                                {{ $usuario->name }}
                                                @if ($esYo) <span class="badge badge-suave neutro ms-1">Tú</span> @endif
                                            </div>
                                            <div class="small text-muted text-truncate d-md-none">{{ $usuario->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell text-muted">{{ $usuario->email }}</td>
                                <td class="celda-precio">
                                    <span class="badge badge-suave {{ $usuario->rol === 'admin' ? 'vino' : 'neutro' }}">
                                        {{ \App\Models\User::ROLES[$usuario->rol] ?? $usuario->rol }}
                                    </span>
                                </td>
                                <td class="celda-estado">
                                    {{-- Un clic activa o desactiva. Uno mismo no se puede desactivar. --}}
                                    @if ($esYo)
                                        <span class="btn-estado activo-si" title="No puedes desactivar tu propio usuario">
                                            <i class="bi bi-check-circle-fill"></i> Activo
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.usuarios.activo', $usuario) }}"
                                              @if ($usuario->activo) onsubmit="return confirm(@js('¿Desactivar a '.$usuario->name.'? Ya no podrá entrar al sistema.'))" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-estado {{ $usuario->activo ? 'activo-si' : 'activo-no' }}"
                                                    title="{{ $usuario->activo ? 'Clic para desactivar' : 'Clic para activar' }}">
                                                @if ($usuario->activo)
                                                    <i class="bi bi-check-circle-fill"></i> Activo
                                                @else
                                                    <i class="bi bi-slash-circle"></i> Desactivado
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap celda-acciones">
                                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-light"
                                       title="Editar" aria-label="Editar a {{ $usuario->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">{{ $usuarios->links() }}</div>
@endsection
