@extends('layouts.app')

@section('title', 'Productos')

@section('content')
    <x-page-header title="Productos" subtitle="Platos, vinos y bebidas que aparecen en el menú.">
        <a href="{{ route('admin.productos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nuevo producto
        </a>
    </x-page-header>

    {{-- Resumen: cada tarjeta también sirve de filtro rápido --}}
    <div class="resumen mb-4">
        <a href="{{ route('admin.productos.index') }}" @class(['dato-resumen', 'activo' => $estado === ''])>
            <div class="valor">{{ $resumen['total'] }}</div>
            <div class="etiqueta">Productos</div>
        </a>
        <a href="{{ route('admin.productos.index', ['estado' => 'agotados']) }}" @class(['dato-resumen', 'activo' => $estado === 'agotados'])>
            <div class="valor {{ $resumen['agotados'] ? 'text-danger' : '' }}">{{ $resumen['agotados'] }}</div>
            <div class="etiqueta">Agotados ahora</div>
        </a>
        <div class="dato-resumen">
            <div class="valor">{{ $resumen['vinos'] }}</div>
            <div class="etiqueta">Vinos para maridar</div>
        </div>
    </div>

    {{-- En celular la tarjeta de afuera desaparece y cada producto se muestra como su propia tarjeta (ver .card-listado en tabla.css) --}}
    <div class="card card-listado">
        {{-- Filtros --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.productos.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control ps-5" placeholder="Buscar por nombre" aria-label="Buscar por nombre">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="categoria_id" class="form-select" aria-label="Filtrar por categoría" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected($categoriaId === $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="estado" class="form-select" aria-label="Filtrar por estado" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="disponibles" @selected($estado === 'disponibles')>Disponibles</option>
                        <option value="agotados" @selected($estado === 'agotados')>Agotados</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-fill">Buscar</button>
                    @if ($buscar !== '' || $categoriaId || $estado !== '')
                        <a href="{{ route('admin.productos.index') }}" class="btn btn-link px-2" title="Quitar filtros"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @if ($productos->isEmpty())
            <x-empty-state icon="bi-basket" title="No se encontraron productos"
                           text="Prueba con otra búsqueda o crea un producto nuevo.">
                <a href="{{ route('admin.productos.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table table-tabla table-hover align-middle tabla-tarjetas">
                    <thead>
                        <tr>
                            <th class="ps-3">Producto</th>
                            <th class="d-none d-md-table-cell">Categoría</th>
                            <th class="text-end">Precio</th>
                            <th class="d-none d-lg-table-cell">Vino sugerido</th>
                            <th>Estado</th>
                            <th class="text-end pe-3"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr @class(['fila-agotada' => ! $producto->disponible])>
                                <td class="ps-3 celda-principal">
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($producto->foto)
                                            <img src="{{ Storage::url($producto->foto) }}" alt="" class="miniatura">
                                        @else
                                            <span class="miniatura miniatura-vacia" title="Sin foto"><i class="bi bi-image" aria-hidden="true"></i></span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-semibold nombre-producto">{{ $producto->nombre }}</div>
                                            <div class="small text-muted">
                                                <span class="d-md-none">{{ $producto->categoria->nombre }}</span>
                                                @if ($producto->es_vino)
                                                    <span class="badge badge-suave vino"><i class="bi bi-droplet-half"></i> Vino</span>
                                                @endif
                                            </div>
                                            {{-- En pantallas chicas la columna de vino sugerido no cabe: se muestra aquí --}}
                                            @if ($producto->vinoSugerido)
                                                <div class="small text-muted d-lg-none">
                                                    <i class="bi bi-droplet-half text-primary"></i> {{ $producto->vinoSugerido->nombre }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">{{ $producto->categoria->nombre }}</td>
                                <td class="text-end fw-semibold celda-precio"><x-money :amount="$producto->precio" /></td>
                                <td class="d-none d-lg-table-cell small">
                                    @if ($producto->vinoSugerido)
                                        <i class="bi bi-droplet-half text-primary"></i> {{ $producto->vinoSugerido->nombre }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="celda-estado">
                                    {{-- Botón agotado: un clic cambia el estado --}}
                                    <form method="POST" action="{{ route('admin.productos.disponible', $producto) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($producto->disponible)
                                            <button class="btn btn-estado disponible" title="Clic para marcar como agotado">
                                                <i class="bi bi-check-circle-fill"></i> Disponible
                                            </button>
                                        @else
                                            <button class="btn btn-estado agotado" title="Clic para marcar como disponible">
                                                <i class="bi bi-x-circle-fill"></i> Agotado
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-end pe-3 text-nowrap celda-acciones">
                                    <a href="{{ route('admin.productos.edit', $producto) }}" class="btn btn-sm btn-light" title="Editar" aria-label="Editar {{ $producto->nombre }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.productos.destroy', $producto) }}" class="d-inline"
                                          onsubmit="return confirm(@js('¿Eliminar el producto '.$producto->nombre.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger" title="Eliminar" aria-label="Eliminar {{ $producto->nombre }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">{{ $productos->links() }}</div>
@endsection
