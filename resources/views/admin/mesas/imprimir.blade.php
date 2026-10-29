@extends('layouts.app')

@section('title', 'Imprimir QR de mesas')

@section('content')
    {{-- Todo lo que tiene .no-imprimir se ve en pantalla pero no sale en el papel --}}
    <div class="no-imprimir">
        <x-page-header title="Imprimir QR" subtitle="Una tarjeta por mesa. Recórtalas por la línea punteada y ponlas en cada mesa."
                       :back="route('admin.mesas.index')">
            <button type="button" class="btn btn-primary" onclick="window.print()" @disabled($mesas->isEmpty())>
                <i class="bi bi-printer me-1"></i>Imprimir
            </button>
        </x-page-header>

        <form method="GET" action="{{ route('admin.mesas.imprimir') }}" class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <select name="zona" class="form-select w-auto" aria-label="Imprimir solo una zona" onchange="this.form.submit()">
                <option value="">Todas las mesas</option>
                @foreach ($zonas as $opcion)
                    <option value="{{ $opcion }}" @selected(! $mesaId && $zona === $opcion)>Solo {{ $opcion }}</option>
                @endforeach
            </select>
            <span class="small text-muted">
                {{ $mesas->count() }} {{ $mesas->count() === 1 ? 'tarjeta' : 'tarjetas' }} · caben 4 por hoja carta
            </span>
            @if ($mesaId)
                <a href="{{ route('admin.mesas.imprimir') }}" class="small">Ver todas las mesas</a>
            @endif
        </form>
    </div>

    @if ($mesas->isEmpty())
        <div class="card">
            <x-empty-state title="No hay mesas para imprimir" text="Crea las mesas primero; cada una tendrá su QR.">
                <a href="{{ route('admin.mesas.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nueva mesa</a>
            </x-empty-state>
        </div>
    @else
        <div class="hoja-qr">
            @foreach ($mesas as $mesa)
                <article class="tarjeta-qr">
                    <img src="{{ asset(config('negocio.logo')) }}" alt="{{ config('negocio.nombre') }}" class="tarjeta-qr-logo" width="640" height="640">
                    <h2 class="tarjeta-qr-mesa">Mesa {{ $mesa->numero }}</h2>

                    {{-- El JavaScript dibuja aquí el QR con la dirección del menú de esta mesa --}}
                    <div class="tarjeta-qr-codigo" data-qr="{{ route('menu.mesa', $mesa->token_qr) }}" role="img"
                         aria-label="Código QR del menú de la mesa {{ $mesa->numero }}">
                        <span class="tarjeta-qr-sin-js">{{ route('menu.mesa', $mesa->token_qr) }}</span>
                    </div>

                    <p class="tarjeta-qr-instruccion">Escanea con la cámara para ver el menú</p>
                    @if ($mesa->zona)
                        <p class="tarjeta-qr-zona">{{ $mesa->zona }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    {{-- qrcode-generator: dibuja códigos QR en el navegador, sin depender de nada más --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script>
        // Dibuja un QR en SVG (nítido a cualquier tamaño al imprimir) dentro de cada tarjeta.
        // Nivel de corrección "M": se sigue leyendo aunque el papel se manche o se doble un poco.
        document.querySelectorAll('[data-qr]').forEach((contenedor) => {
            if (typeof qrcode !== 'function') return; // si no cargó la librería, queda la dirección escrita

            const qr = qrcode(0, 'M');
            qr.addData(contenedor.dataset.qr);
            qr.make();
            contenedor.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true });
        });
    </script>
@endpush
