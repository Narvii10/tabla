{{--
    Una mesa vista desde arriba, con tantas sillas como su capacidad: <x-plano-mesa :numero="5" :capacidad="4" />
    Hasta 3 personas es redonda; de 4 en adelante, rectangular y más larga mientras más sillas tiene.
    Los colores salen de variables CSS (--mesa-tabla, --mesa-silla, --mesa-numero) que pone .mesa-tile según su estado.
--}}
@props(['numero', 'capacidad'])
@php
    // Se dibujan hasta 10 sillas; una mesa más grande se ve igual de larga
    $sillas = max(1, min((int) $capacidad, 10));
    $redonda = $sillas <= 3;
    $posiciones = [];

    if ($redonda) {
        // Sillas repartidas alrededor del círculo, mirando al centro
        for ($i = 0; $i < $sillas; $i++) {
            $angulo = -90 + $i * 360 / $sillas;
            $posiciones[] = [
                'x' => 60 + 35 * cos(deg2rad($angulo)),
                'y' => 45 + 35 * sin(deg2rad($angulo)),
                'giro' => $angulo + 90,
            ];
        }
    } else {
        // Mitad de las sillas arriba y la otra mitad abajo, a lo largo de la mesa
        $arriba = (int) ceil($sillas / 2);
        $abajo = $sillas - $arriba;
        $ancho = min(96, 44 + max(0, $arriba - 2) * 17);
        $izquierda = 60 - $ancho / 2;
        foreach ([[$arriba, 45 - 18 - 9, 0], [$abajo, 45 + 18 + 9, 180]] as [$cuantas, $y, $giro]) {
            for ($i = 0; $i < $cuantas; $i++) {
                $posiciones[] = ['x' => $izquierda + $ancho * ($i + 0.5) / $cuantas, 'y' => $y, 'giro' => $giro];
            }
        }
    }
@endphp
<svg {{ $attributes->merge(['class' => 'plano-mesa']) }} viewBox="0 0 120 90" aria-hidden="true">
    @foreach ($posiciones as $silla)
        <rect class="plano-mesa-silla" x="-8" y="-5" width="16" height="10" rx="4"
              transform="translate({{ round($silla['x'], 1) }} {{ round($silla['y'], 1) }}) rotate({{ round($silla['giro']) }})" />
    @endforeach

    @if ($redonda)
        <circle class="plano-mesa-tabla" cx="60" cy="45" r="24" />
    @else
        <rect class="plano-mesa-tabla" x="{{ $izquierda }}" y="27" width="{{ $ancho }}" height="36" rx="7" />
    @endif

    <text class="plano-mesa-numero" x="60" y="45" text-anchor="middle" dominant-baseline="central">{{ $numero }}</text>
</svg>
