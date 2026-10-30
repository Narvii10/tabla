{{-- Vuelto del último cobro en efectivo --}}
@php $vuelto = session('vuelto'); @endphp
@if ($vuelto)
    <div class="aviso-vuelto alert alert-dismissible fade show" role="alert">
        <i class="bi bi-cash-coin aviso-vuelto-icono" aria-hidden="true"></i>
        <div>
            <div class="aviso-vuelto-titulo">Entrega de vuelto · Mesa {{ $vuelto['mesa'] }}</div>
            <div class="aviso-vuelto-monto"><x-money :amount="$vuelto['vuelto']" /></div>
            <div class="small">
                Recibiste <x-money :amount="$vuelto['recibido']" /> por un cobro de <x-money :amount="$vuelto['monto']" />.
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Ya lo entregué"></button>
    </div>
@endif
