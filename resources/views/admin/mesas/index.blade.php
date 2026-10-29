@extends('layouts.app')

@section('title', 'Mesas')

@section('content')
    <x-page-header title="Mesas" subtitle="Las mesas del local y el QR que abre el menú en cada una.">
        <a href="{{ route('admin.mesas.imprimir') }}" class="btn btn-light">
            <i class="bi bi-printer me-1"></i>Imprimir QR
        </a>
        <a href="{{ route('admin.mesas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nueva mesa
        </a>
    </x-page-header>

    <div class="resumen mb-4">
        <div class="dato-resumen">
            <div class="valor">{{ $resumen['total'] }}</div>
            <div class="etiqueta">Mesas</div>
        </div>
        <div class="dato-resumen">
            <div class="valor">{{ $resumen['libres'] }}</div>
            <div class="etiqueta">Libres ahora</div>
        </div>
        <div class="dato-resumen">
            <div class="valor">{{ $resumen['puestos'] }}</div>
            <div class="etiqueta">Capacidad total del local</div>
        </div>
    </div>

    {{-- En celular cada mesa se muestra como tarjeta (ver .card-listado y .tabla-tarjetas en tabla.css) --}}
    <div class="card card-listado">
        {{-- Filtros --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.mesas.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control ps-5"
                               placeholder="Número de mesa o zona" aria-label="Buscar por número de mesa o zona">
                    </div>
                </div>
                <div class="col-8 col-md-4">
                    <select name="zona" class="form-select" aria-label="Filtrar por zona" onchange="this.form.submit()">
                        <option value="">Todas las zonas</option>
                        @foreach ($zonas as $opcion)
                            <option value="{{ $opcion }}" @selected($zona === $opcion)>{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-fill">Buscar</button>
                    @if ($buscar !== '' || $zona !== '')
                        <a href="{{ route('admin.mesas.index') }}" class="btn btn-link px-2" title="Quitar filtros" aria-label="Quitar filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @if ($mesas->isEmpty())
            <x-empty-state title="No se encontraron mesas" text="Prueba con otro número o zona, o crea una mesa nueva.">
                <a href="{{ route('admin.mesas.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nueva mesa</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-tabla table-hover align-middle tabla-tarjetas">
                    <thead>
                        <tr>
                            <th class="ps-3">Mesa</th>
                            <th class="d-none d-md-table-cell">Zona</th>
                            <th class="text-end">Capacidad</th>
                            <th>Estado</th>
                            <th class="text-end pe-3"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mesas as $mesa)
                            <tr>
                                <td class="ps-3 celda-principal">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="numero-mesa">{{ $mesa->numero }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold">Mesa {{ $mesa->numero }}</div>
                                            <div class="small text-muted d-md-none">{{ $mesa->zona ?? 'Sin zona' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @if ($mesa->zona)
                                        {{ $mesa->zona }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end celda-precio">
                                    <span class="text-muted fw-normal">Hasta</span> {{ $mesa->capacidad }}
                                    <span class="text-muted fw-normal">{{ $mesa->capacidad === 1 ? 'persona' : 'personas' }}</span>
                                </td>
                                <td class="celda-estado">
                                    <span class="estado-mesa estado-{{ $mesa->estado }}">{{ \App\Models\Mesa::ESTADOS[$mesa->estado] ?? $mesa->estado }}</span>
                                </td>
                                <td class="text-end pe-3 text-nowrap celda-acciones">
                                    <a href="{{ route('admin.mesas.imprimir', ['mesa' => $mesa->id]) }}" class="btn btn-sm btn-light"
                                       title="Imprimir el QR de esta mesa" aria-label="Imprimir el QR de la mesa {{ $mesa->numero }}">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    <a href="{{ route('admin.mesas.edit', $mesa) }}" class="btn btn-sm btn-light"
                                       title="Editar" aria-label="Editar la mesa {{ $mesa->numero }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}" class="d-inline"
                                          onsubmit="return confirm(@js('¿Eliminar la mesa '.$mesa->numero.'? Su QR dejará de funcionar.'))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger" title="Eliminar" aria-label="Eliminar la mesa {{ $mesa->numero }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">{{ $mesas->links() }}</div>
@endsection
