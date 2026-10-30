@extends('layouts.app')

@section('title', 'Cierre de caja')

@section('content')
    <x-page-header title="Cierre del día" subtitle="Totales esperados por método, según los pagos de hoy."
                   :back="route('caja.index')" />

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    @if ($pendientes > 0)
        <div class="alert alert-aviso">
            <i class="bi bi-receipt me-1"></i>
            Quedan {{ $pendientes }} {{ $pendientes === 1 ? 'pedido abierto' : 'pedidos abiertos' }} sin cobrar.
            @unless ($cierre)
                Después del cierre ya no se puede cobrar hoy.
            @endunless
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Esperado hoy (según los pagos registrados)</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Efectivo</span><x-money :amount="$esperado['efectivo']" />
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Tarjeta</span><x-money :amount="$esperado['tarjeta']" />
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Transferencia</span><x-money :amount="$esperado['transferencia']" />
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            @if ($cierre)
                <div class="card">
                    <div class="card-header">Cierre de hoy</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Efectivo contado</span><x-money :amount="$cierre->efectivo_contado" />
                        </div>
                        <div class="d-flex justify-content-between fw-semibold">
                            <span>{{ $cierre->diferencia > 0 ? 'Sobró' : ($cierre->diferencia < 0 ? 'Faltó' : 'Diferencia') }}</span>
                            <x-money :amount="abs((float) $cierre->diferencia)" />
                        </div>
                        @if ($cierre->notas)
                            <p class="small text-muted mt-3 mb-0">{{ $cierre->notas }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">Contar efectivo</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('caja.cierre.store') }}" id="form-cierre">
                            @csrf
                            <div class="mb-3">
                                <label for="cierre-efectivo-contado" class="form-label fw-semibold">¿Cuánto efectivo hay en la caja?</label>
                                <div class="input-group input-group-lg input-recibido">
                                    <span class="input-group-text">Q</span>
                                    <input type="number" name="efectivo_contado" id="cierre-efectivo-contado"
                                           class="form-control" step="0.01" min="0" max="1000000" inputmode="decimal"
                                           placeholder="0.00" required value="{{ old('efectivo_contado') }}"
                                           data-esperado="{{ $esperado['efectivo'] }}">
                                </div>
                                <div class="form-text">Debería haber <x-money :amount="$esperado['efectivo']" /> en efectivo.</div>
                            </div>
                            <div class="vuelto-caja mb-3" id="cierre-diferencia" data-estado="vacio" aria-live="polite">
                                <span class="vuelto-caja-titulo" id="cierre-diferencia-titulo">Diferencia</span>
                                <span class="vuelto-caja-monto" id="cierre-diferencia-monto">—</span>
                            </div>
                            <div class="mb-3">
                                <label for="cierre-notas" class="form-label">Notas <span class="text-muted">(opcional)</span></label>
                                <textarea name="notas" id="cierre-notas" class="form-control" maxlength="500" rows="3"></textarea>
                            </div>
                            <button class="btn btn-primary w-100" id="cierre-boton">
                                <i class="bi bi-check-lg me-1"></i>Guardar cierre
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/caja.js') }}?v={{ filemtime(public_path('js/caja.js')) }}"></script>
@endpush
