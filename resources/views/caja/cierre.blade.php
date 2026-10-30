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
                            <span>Diferencia</span><x-money :amount="$cierre->diferencia" />
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
                                <label for="cierre-efectivo-contado" class="form-label">Efectivo contado</label>
                                <input type="number" name="efectivo_contado" id="cierre-efectivo-contado"
                                       class="form-control" step="0.01" min="0" required
                                       data-esperado="{{ $esperado['efectivo'] }}">
                            </div>
                            <p class="mb-3" id="cierre-diferencia"></p>
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
