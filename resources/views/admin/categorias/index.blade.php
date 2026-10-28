@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <x-page-header title="Categorías" subtitle="Secciones del menú y el orden en que aparecen.">
        <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nueva categoría
        </a>
    </x-page-header>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.categorias.index') }}" class="row g-2">
                <div class="col">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control ps-5" placeholder="Buscar por nombre" aria-label="Buscar por nombre">
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-outline-primary">Buscar</button>
                    @if ($buscar !== '')
                        <a href="{{ route('admin.categorias.index') }}" class="btn btn-link px-2" title="Quitar búsqueda"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @if ($categorias->isEmpty())
            <x-empty-state icon="bi-tags" title="No se encontraron categorías" text="Prueba con otra búsqueda o crea una nueva." />
        @else
            <div class="table-responsive">
                <table class="table table-tabla table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 5rem;">Orden</th>
                            <th>Nombre</th>
                            <th class="text-center">Productos</th>
                            <th>Menú público</th>
                            <th class="text-end pe-3"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge badge-suave neutro fs-6">{{ $categoria->orden }}</span>
                                </td>
                                <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.productos.index', ['categoria_id' => $categoria->id]) }}" class="text-decoration-none">
                                        {{ $categoria->productos_count }}
                                    </a>
                                </td>
                                <td>
                                    @if ($categoria->activo)
                                        <span class="badge badge-suave exito"><i class="bi bi-eye"></i> Visible</span>
                                    @else
                                        <span class="badge badge-suave neutro"><i class="bi bi-eye-slash"></i> Oculta</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-sm btn-light" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.categorias.destroy', $categoria) }}" class="d-inline"
                                          onsubmit="return confirm(@js('¿Eliminar la categoría '.$categoria->nombre.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-3">{{ $categorias->links() }}</div>
@endsection
