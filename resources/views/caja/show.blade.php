@extends('layouts.app')

@section('title', 'Mesa '.$pedido->mesa->numero.' · Caja')

@php
    $propinaActual = (float) $pedido->propina;
    $opcionPropina = match (true) {
        $propinaActual === 0.0 => 'sin',
        abs($propinaActual - $propinaSugerida) < 0.01 => 'sugerida',
        default => 'manual',
    };
    $saldo = $pedido->saldo();
    $iconosMetodo = ['efectivo' => 'bi-cash-stack', 'tarjeta' => 'bi-credit-card', 'transferencia' => 'bi-bank'];
@endphp

@section('content')
    <div id="caja-pedido" data-saldo-centavos="{{ (int) round($saldo * 100) }}">

    <x-page-header title="Mesa {{ $pedido->mesa->numero }}"
                   subtitle="{{ $pedido->mesero->name }} · abierto a las {{ $pedido->abierto_en->format('H:i') }}"
                   :back="route('caja.index')">
        <span class="estado-mesa estado-{{ $pedido->mesa->estado }} align-self-center">
            {{ \App\Models\Mesa::ESTADOS[$pedido->mesa->estado] ?? $pedido->mesa->estado }}
        </span>
    </x-page-header>

    @include('caja._vuelto')

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7 order-2 order-lg-1">
            <div class="card mb-3">
                <div class="card-header">Cuenta</div>
                @if ($productos->isEmpty())
                    <div class="card-body text-center text-muted small py-4">Este pedido no tiene productos.</div>
                @else
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
                @endif
            </div>

            <div class="card mb-3">
                <div class="card-header">Propina</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('caja.pedidos.propina', $pedido) }}" id="form-propina">
                        @csrf
                        @method('PATCH')
                        <div class="opciones-pago" role="group" aria-label="Propina">
                            <input type="radio" class="btn-check opcion-propina" name="opcion_propina" id="propina-sin"
                                   value="0.00" @checked($opcionPropina === 'sin')>
                            <label class="opcion-pago" for="propina-sin">Sin propina</label>

                            <input type="radio" class="btn-check opcion-propina" name="opcion_propina" id="propina-sugerida"
                                   value="{{ number_format($propinaSugerida, 2, '.', '') }}" @checked($opcionPropina === 'sugerida')>
                            <label class="opcion-pago" for="propina-sugerida">
                                10% <span class="small">(<x-money :amount="$propinaSugerida" />)</span>
                            </label>

                            <input type="radio" class="btn-check opcion-propina" name="opcion_propina" id="propina-manual"
                                   value="manual" @checked($opcionPropina === 'manual')>
                            <label class="opcion-pago" for="propina-manual">Otro monto</label>
                        </div>

                        <div id="propina-manual-grupo" class="mt-3" @if ($opcionPropina !== 'manual') hidden @endif>
                            <label for="propina-input" class="form-label">Monto de propina</label>
                            <div class="d-flex gap-2">
                                <div class="input-group" style="max-width: 200px">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" name="propina" id="propina-input" class="form-control"
                                           step="0.01" min="0" inputmode="decimal" required
                                           value="{{ number_format($propinaActual, 2, '.', '') }}">
                                </div>
                                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($saldo > 0)
                <div class="card">
                    <div class="card-header">Dividir el saldo en partes iguales</div>
                    <div class="card-body">
                        <label for="personas" class="form-label">Entre cuántas personas</label>
                        <div class="input-group mb-3" style="max-width: 180px">
                            <button type="button" class="btn btn-outline-primary" id="personas-menos" aria-label="Una persona menos">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" id="personas" class="form-control text-center" min="1" max="20" step="1"
                                   value="2" inputmode="numeric">
                            <button type="button" class="btn btn-outline-primary" id="personas-mas" aria-label="Una persona más">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <ul id="lista-division" class="list-group list-group-flush">
                            @foreach ($division as $i => $centavos)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Persona {{ $i + 1 }}</span>
                                    <x-money :amount="$centavos / 100" />
                                </li>
                            @endforeach
                        </ul>
                        <p class="small text-muted mt-2 mb-0">Toca «Cobrar» en una persona para cobrar solo su parte.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5 order-1 order-lg-2">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><x-money :amount="$subtotal" /></div>
                    <div class="d-flex justify-content-between"><span>Propina</span><x-money :amount="$pedido->propina" /></div>
                    <div class="d-flex justify-content-between fw-semibold"><span>Total</span><x-money :amount="$pedido->total()" /></div>
                    @if ($pedido->pagado() > 0)
                        <div class="d-flex justify-content-between text-muted">
                            <span>Ya pagado</span><span>− <x-money :amount="$pedido->pagado()" /></span>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="fw-semibold">Por cobrar</span>
                        <x-money :amount="$saldo" class="saldo-caja" />
                    </div>
                </div>
            </div>

            @if ($saldo > 0)
                <div class="card mb-3" id="cobro">
                    <div class="card-header">Cobrar</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('caja.pedidos.pagos.store', $pedido) }}" id="form-pago">
                            @csrf
                            <span class="form-label d-block">¿Cómo paga?</span>
                            <div class="opciones-pago mb-3" role="group" aria-label="Método de pago">
                                @foreach (\App\Models\Pago::METODOS as $valor => $texto)
                                    <input type="radio" class="btn-check" name="metodo" id="metodo-{{ $valor }}"
                                           value="{{ $valor }}" @checked(old('metodo', 'efectivo') === $valor) required>
                                    <label class="opcion-pago opcion-metodo" for="metodo-{{ $valor }}">
                                        <i class="bi {{ $iconosMetodo[$valor] ?? 'bi-wallet2' }}" aria-hidden="true"></i>{{ $texto }}
                                    </label>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label for="pago-monto" class="form-label">Monto a cobrar</label>
                                <div class="input-group">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" name="monto" id="pago-monto" class="form-control"
                                           step="0.01" min="0.01" max="{{ number_format($saldo, 2, '.', '') }}" inputmode="decimal"
                                           value="{{ old('monto', number_format($saldo, 2, '.', '')) }}" required>
                                </div>
                                <div class="form-text">Ya viene el saldo completo. Cámbialo solo si pagan por partes.</div>
                            </div>

                            {{-- Solo para efectivo --}}
                            <div id="pago-efectivo" class="mb-3">
                                <label for="pago-recibido" class="form-label fw-semibold">¿Con cuánto paga el cliente?</label>
                                <div class="input-group input-group-lg input-recibido">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" name="recibido" id="pago-recibido" class="form-control"
                                           step="0.01" min="0" inputmode="decimal" placeholder="0.00"
                                           value="{{ old('recibido') }}">
                                </div>
                                <div class="billetes" id="pago-billetes" aria-label="Montos rápidos"></div>

                                <div class="vuelto-caja" id="pago-vuelto" data-estado="vacio" aria-live="polite">
                                    <span class="vuelto-caja-titulo" id="pago-vuelto-titulo">Vuelto</span>
                                    <span class="vuelto-caja-monto" id="pago-vuelto-monto">—</span>
                                </div>
                            </div>

                            <button class="btn btn-primary btn-lg w-100" id="pago-boton">
                                <i class="bi bi-cash-coin me-1"></i>Cobrar <span id="pago-boton-monto"><x-money :amount="$saldo" /></span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card mb-3">
                    <div class="card-body text-center">
                        <p class="mb-3">No queda nada por cobrar.</p>
                        <form method="POST" action="{{ route('caja.pedidos.cerrar', $pedido) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-check2-circle me-1"></i>Cerrar cuenta y liberar mesa
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
                                        @if ($pago->metodo === 'efectivo' && $pago->recibido !== null)
                                            · recibió <x-money :amount="$pago->recibido" />, vuelto <x-money :amount="$pago->vuelto" />
                                        @endif
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
