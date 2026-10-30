@extends('layouts.app')

@section('title', 'Mesa '.$pedido->mesa->numero.' · Caja')

@php
    $propinaActual = (float) $pedido->propina;
    $opcionPropina = match (true) {
        $propinaActual === 0.0 => 'sin',
        abs($propinaActual - $propinaSugerida) < 0.01 => 'sugerida',
        default => 'manual',
    };
@endphp

@section('content')
    <div id="caja-pedido" data-saldo-centavos="{{ (int) round($pedido->saldo() * 100) }}">

    <x-page-header title="Mesa {{ $pedido->mesa->numero }}"
                   subtitle="{{ $pedido->mesero->name }} · abierto a las {{ $pedido->abierto_en->format('H:i') }}"
                   :back="route('caja.index')">
        <span class="estado-mesa estado-{{ $pedido->mesa->estado }} align-self-center">
            {{ \App\Models\Mesa::ESTADOS[$pedido->mesa->estado] ?? $pedido->mesa->estado }}
        </span>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7 order-2 order-lg-1">
            <div class="card mb-3">
                <div class="card-header">Cuenta</div>
                <div class="table-responsive">
                    <table class="table table-tabla mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $linea)
                                <tr>
                                    <td>{{ $linea['nombre'] }}</td>
                                    <td class="text-center">{{ $linea['cantidad'] }}</td>
                                    <td class="text-end"><x-money :amount="$linea['precio_unitario']" /></td>
                                    <td class="text-end"><x-money :amount="$linea['total']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Propina</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('caja.pedidos.propina', $pedido) }}" id="form-propina">
                        @csrf
                        @method('PATCH')
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="form-check">
                                <input class="form-check-input opcion-propina" type="radio" name="opcion_propina"
                                       id="propina-sin" value="0" @checked($opcionPropina === 'sin')>
                                <label class="form-check-label" for="propina-sin">Sin propina</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input opcion-propina" type="radio" name="opcion_propina"
                                       id="propina-sugerida" value="{{ $propinaSugerida }}" @checked($opcionPropina === 'sugerida')>
                                <label class="form-check-label" for="propina-sugerida">
                                    10% sugerida (<x-money :amount="$propinaSugerida" />)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input opcion-propina" type="radio" name="opcion_propina"
                                       id="propina-manual" value="manual" @checked($opcionPropina === 'manual')>
                                <label class="form-check-label" for="propina-manual">Otro monto</label>
                            </div>
                        </div>
                        <label for="propina-input" class="form-label">Monto de propina</label>
                        <input type="number" name="propina" id="propina-input" class="form-control"
                               step="0.01" min="0" style="max-width: 160px"
                               value="{{ number_format($propinaActual, 2, '.', '') }}"
                               {{ $opcionPropina === 'manual' ? '' : 'readonly' }} required>
                        <button class="btn btn-primary mt-3"><i class="bi bi-check-lg me-1"></i>Guardar propina</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Dividir el saldo</div>
                <div class="card-body">
                    <label for="personas" class="form-label">Entre cuántas personas</label>
                    <input type="number" id="personas" class="form-control mb-3" min="1" max="20" step="1"
                           value="2" style="max-width: 120px">
                    <ul id="lista-division" class="list-group list-group-flush">
                        @foreach ($division as $i => $centavos)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Persona {{ $i + 1 }}</span>
                                <x-money :amount="$centavos / 100" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5 order-1 order-lg-2">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><x-money :amount="$subtotal" /></div>
                    <div class="d-flex justify-content-between"><span>Propina</span><x-money :amount="$pedido->propina" /></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-semibold fs-5">
                        <span>Total</span><x-money :amount="$pedido->total()" />
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <span>Pagado</span><x-money :amount="$pedido->pagado()" />
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Saldo</span><x-money :amount="$pedido->saldo()" />
                    </div>
                </div>
            </div>

            @if ($pedido->saldo() > 0)
                <div class="card mb-3">
                    <div class="card-header">Registrar pago</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('caja.pedidos.pagos.store', $pedido) }}" id="form-pago">
                            @csrf
                            <div class="mb-3">
                                <label for="pago-metodo" class="form-label">Método</label>
                                <select name="metodo" id="pago-metodo" class="form-select" required>
                                    @foreach (\App\Models\Pago::METODOS as $valor => $texto)
                                        <option value="{{ $valor }}">{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="pago-monto" class="form-label">Monto</label>
                                <input type="number" name="monto" id="pago-monto" class="form-control"
                                       step="0.01" min="0.01" max="{{ $pedido->saldo() }}"
                                       value="{{ $pedido->saldo() }}" required>
                            </div>
                            <div class="mb-3" id="pago-recibido-grupo">
                                <label for="pago-recibido" class="form-label">Recibido</label>
                                <input type="number" name="recibido" id="pago-recibido" class="form-control"
                                       step="0.01" min="0">
                                <div class="form-text" id="pago-vuelto"></div>
                            </div>
                            <button class="btn btn-primary w-100" id="pago-boton">
                                <i class="bi bi-cash-coin me-1"></i>Cobrar
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Pagos registrados</div>
                @if ($pedido->pagos->isEmpty())
                    <div class="card-body text-center text-muted small py-4">Todavía no hay pagos.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($pedido->pagos as $pago)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    {{ \App\Models\Pago::METODOS[$pago->metodo] ?? $pago->metodo }}
                                    <span class="small text-muted d-block">
                                        {{ $pago->cajero->name }} · {{ $pago->created_at->format('H:i') }}
                                    </span>
                                </span>
                                <x-money :amount="$pago->monto" class="fw-semibold" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/caja.js') }}?v={{ filemtime(public_path('js/caja.js')) }}"></script>
@endpush
