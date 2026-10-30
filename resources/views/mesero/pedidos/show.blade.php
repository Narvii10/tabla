@extends('layouts.app')

@section('title', 'Pedido · Mesa '.$pedido->mesa->numero)

@php
    $abierto = $pedido->estaAbierto();
    $vino = session('vino_sugerido');
@endphp

@section('content')
    <x-page-header title="Mesa {{ $pedido->mesa->numero }}"
                   subtitle="Pedido de {{ $pedido->mesero->name }} · abierto a las {{ $pedido->abierto_en->format('H:i') }}"
                   :back="route('mesero.index')">
        <span class="estado-mesa estado-{{ $pedido->mesa->estado }} align-self-center">
            {{ \App\Models\Mesa::ESTADOS[$pedido->mesa->estado] ?? $pedido->mesa->estado }}
        </span>
    </x-page-header>

    {{-- Errores de validación (por ejemplo, un producto agotado) --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Maridaje: se ofrece el vino sugerido del plato que se acaba de agregar --}}
    @if ($vino && $abierto)
        <div class="alert alert-vino shadow-sm d-flex flex-wrap align-items-center gap-2" role="status">
            <i class="bi bi-droplet-half fs-5"></i>
            <div class="flex-grow-1">
                <strong>Ofrécele el vino sugerido</strong>
                <div class="small">Con «{{ $vino['plato'] }}» va muy bien {{ $vino['nombre'] }}.</div>
            </div>
            <form method="POST" action="{{ route('mesero.pedidos.detalles.store', $pedido) }}">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $vino['id'] }}">
                <input type="hidden" name="cantidad" value="1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Agregar {{ $vino['nombre'] }}</button>
            </form>
        </div>
    @endif

    <div class="row g-4">
        {{-- Catálogo: buscador, categorías y productos --}}
        @if ($abierto)
            <div class="col-lg-7 order-2 order-lg-1">
                <div class="card">
                    <div class="card-body border-bottom">
                        <div class="position-relative mb-3">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="search" id="buscar-producto" class="form-control ps-5" placeholder="Buscar por nombre"
                                   aria-label="Buscar producto" autocomplete="off">
                        </div>
                        <div class="categorias-pedido" role="group" aria-label="Categorías">
                            <button type="button" class="btn btn-sm btn-categoria activo" data-filtro="">Todo</button>
                            @foreach ($categorias as $categoria)
                                <button type="button" class="btn btn-sm btn-categoria" data-filtro="{{ $categoria->id }}">{{ $categoria->nombre }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div id="catalogo" class="card-body pt-2">
                        @foreach ($categorias as $categoria)
                            <section class="catalogo-seccion" data-seccion="{{ $categoria->id }}">
                                <h2 class="catalogo-titulo">{{ $categoria->nombre }}</h2>
                                @foreach ($categoria->productos as $producto)
                                    <button type="button" @class(['item-catalogo', 'agotado' => ! $producto->disponible])
                                            data-producto="{{ $producto->id }}"
                                            data-nombre="{{ $producto->nombre }}"
                                            data-categoria="{{ $categoria->id }}"
                                            data-vino="{{ $producto->vinoSugerido?->nombre }}"
                                            @disabled(! $producto->disponible)>
                                        <span class="flex-grow-1 text-start">
                                            <span class="d-block fw-semibold">{{ $producto->nombre }}</span>
                                            @if ($producto->descripcion)
                                                <span class="d-block small text-muted item-descripcion">{{ $producto->descripcion }}</span>
                                            @endif
                                        </span>
                                        @if ($producto->disponible)
                                            <span class="fw-semibold"><x-money :amount="$producto->precio" /></span>
                                            <i class="bi bi-plus-circle-fill text-primary fs-5" aria-hidden="true"></i>
                                        @else
                                            <span class="badge badge-suave peligro">Agotado</span>
                                        @endif
                                    </button>
                                @endforeach
                            </section>
                        @endforeach

                        <p id="catalogo-vacio" class="text-center text-muted py-4 mb-0" hidden>No hay productos con ese nombre.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Pedido: por enviar, rondas enviadas y total --}}
        <div @class(['order-1 order-lg-2', 'col-lg-5' => $abierto, 'col-lg-8 mx-auto' => ! $abierto])>
            @unless ($abierto)
                <div class="alert alert-aviso"><i class="bi bi-lock-fill me-1"></i>Este pedido ya está cerrado.</div>
            @endunless

            <div class="card mb-3">
                <div class="card-header">Por enviar a cocina</div>
                @if ($porEnviar->isEmpty())
                    <div class="card-body text-center text-muted small py-4">
                        Agrega productos del catálogo. Aquí aparecen hasta que los envíes.
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($porEnviar as $detalle)
                            <li class="linea-pedido list-group-item">
                                <span class="linea-cantidad">{{ $detalle->cantidad }}×</span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold">{{ $detalle->producto->nombre }}</div>
                                    @if ($detalle->notas)
                                        <div class="linea-nota"><i class="bi bi-pencil-fill" aria-hidden="true"></i> {{ $detalle->notas }}</div>
                                    @endif
                                </div>
                                <x-money :amount="$detalle->totalLinea()" />
                                @if ($abierto)
                                    <form method="POST" action="{{ route('mesero.pedidos.detalles.destroy', [$pedido, $detalle]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger" title="Quitar" aria-label="Quitar {{ $detalle->producto->nombre }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($abierto)
                        <div class="card-body border-top">
                            <form method="POST" action="{{ route('mesero.pedidos.enviar', $pedido) }}">
                                @csrf
                                <button class="btn btn-primary w-100"><i class="bi bi-send-fill me-1"></i>Enviar a cocina</button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Rondas ya enviadas, con el estado que les pone cocina --}}
            @foreach ($comandas as $comanda)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Ronda {{ $loop->iteration }} <span class="text-muted fw-normal small">· {{ $comanda->enviada_en->format('H:i') }}</span></span>
                        <span class="estado-comanda estado-comanda-{{ $comanda->estado }}">{{ \App\Models\Comanda::ESTADOS[$comanda->estado] ?? $comanda->estado }}</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($comanda->detalles as $detalle)
                            <li class="linea-pedido list-group-item">
                                <span class="linea-cantidad">{{ $detalle->cantidad }}×</span>
                                <div class="flex-grow-1 min-w-0">
                                    <div>{{ $detalle->producto->nombre }}</div>
                                    @if ($detalle->notas)
                                        <div class="linea-nota"><i class="bi bi-pencil-fill" aria-hidden="true"></i> {{ $detalle->notas }}</div>
                                    @endif
                                </div>
                                <x-money :amount="$detalle->totalLinea()" />
                            </li>
                        @endforeach
                    </ul>
                    @if ($abierto && $comanda->estado === 'listo')
                        <div class="card-body border-top">
                            <form method="POST" action="{{ route('mesero.comandas.entregada', $comanda) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i>Ya la llevé a la mesa</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Total y cuenta --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center fs-5 fw-semibold">
                        <span>Total</span>
                        <x-money :amount="$pedido->subtotal()" />
                    </div>
                    <p class="small text-muted mb-0">La propina y el cobro se hacen en caja.</p>

                    @if ($abierto)
                        @if ($pedido->mesa->estado === 'cuenta')
                            <div class="alert alert-aviso mt-3 mb-0 py-2 small">
                                <i class="bi bi-receipt me-1"></i>La cuenta ya está en caja.
                            </div>
                        @else
                            <form method="POST" action="{{ route('mesero.pedidos.cuenta', $pedido) }}" class="mt-3"
                                  onsubmit="return confirm('¿La mesa pidió la cuenta?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-primary w-100"><i class="bi bi-receipt me-1"></i>Pidió la cuenta</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Ventana para elegir cantidad y nota del producto tocado --}}
    @if ($abierto)
        <div class="modal fade" id="modal-agregar" tabindex="-1" aria-labelledby="modal-agregar-titulo" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('mesero.pedidos.detalles.store', $pedido) }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="producto_id" id="agregar-producto">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="modal-agregar-titulo">Agregar producto</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted" id="agregar-vino" hidden>
                            <i class="bi bi-droplet-half text-primary"></i> Vino sugerido: <strong id="agregar-vino-nombre"></strong>
                        </p>
                        <div class="mb-3">
                            <label for="agregar-cantidad" class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" id="agregar-cantidad" class="form-control" value="1" min="1" max="50" required inputmode="numeric">
                        </div>
                        <div>
                            <label for="agregar-notas" class="form-label">Nota para cocina <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="notas" id="agregar-notas" class="form-control" maxlength="200" placeholder="Ej. sin cebolla">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@if ($abierto)
    @push('scripts')
        <script>
            // Catálogo: filtra por texto y categoría sin recargar, y abre la ventana de cantidad y nota
            (() => {
                const catalogo = document.getElementById('catalogo');
                const buscador = document.getElementById('buscar-producto');
                const vacio = document.getElementById('catalogo-vacio');
                const modalElemento = document.getElementById('modal-agregar');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);
                let categoria = '';

                // Sin tildes ni mayúsculas, para que "jamon" encuentre "Jamón"
                const limpiar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

                const filtrar = () => {
                    const texto = limpiar(buscador.value.trim());
                    let visibles = 0;
                    catalogo.querySelectorAll('.catalogo-seccion').forEach((seccion) => {
                        let enSeccion = 0;
                        seccion.querySelectorAll('.item-catalogo').forEach((item) => {
                            const coincide = (categoria === '' || item.dataset.categoria === categoria)
                                && limpiar(item.dataset.nombre).includes(texto);
                            item.hidden = !coincide;
                            if (coincide) enSeccion++;
                        });
                        seccion.hidden = enSeccion === 0;
                        visibles += enSeccion;
                    });
                    vacio.hidden = visibles > 0;
                };

                buscador.addEventListener('input', filtrar);

                document.querySelectorAll('.btn-categoria').forEach((boton) => {
                    boton.addEventListener('click', () => {
                        categoria = boton.dataset.filtro;
                        document.querySelectorAll('.btn-categoria').forEach((b) => b.classList.toggle('activo', b === boton));
                        filtrar();
                    });
                });

                catalogo.addEventListener('click', (evento) => {
                    const item = evento.target.closest('.item-catalogo');
                    if (!item || item.disabled) return;

                    document.getElementById('modal-agregar-titulo').textContent = item.dataset.nombre;
                    document.getElementById('agregar-producto').value = item.dataset.producto;
                    document.getElementById('agregar-cantidad').value = 1;
                    document.getElementById('agregar-notas').value = '';
                    document.getElementById('agregar-vino').hidden = !item.dataset.vino;
                    document.getElementById('agregar-vino-nombre').textContent = item.dataset.vino;
                    modal.show();
                });

                modalElemento.addEventListener('shown.bs.modal', () => document.getElementById('agregar-cantidad').select());
            })();
        </script>
    @endpush
@endif
