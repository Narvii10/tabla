{{--
    Salamín, la mascota del menú: una rodaja de salami dibujada en SVG.
    <x-mascota class="..." />
    Las animaciones (parpadeo, saludo, carita triste) están en public/css/menu.css
    y se activan con el atributo data-humor="feliz|triste|guino" que cambia el JavaScript.
--}}
<svg {{ $attributes->merge(['class' => 'mascota', 'data-humor' => 'feliz']) }} viewBox="0 0 120 132" role="img" aria-label="Salamín, la mascota de la casa">
    {{-- Cordón de amarre, como el de los embutidos colgados --}}
    <path class="mascota-cordon" d="M60 31 C52 19 54 9 60 9 C66 9 68 19 60 31" fill="none" stroke="#c8963e" stroke-width="3.5" stroke-linecap="round" />

    {{-- Brazo izquierdo (quieto) --}}
    <path d="M22 82 Q11 88 9 99" fill="none" stroke="#5a1f2a" stroke-width="5" stroke-linecap="round" />
    <circle cx="9" cy="101" r="5.5" fill="#a8384b" stroke="#5a1f2a" stroke-width="3" />

    {{-- Brazo derecho: saluda --}}
    <g class="mascota-brazo">
        <path d="M98 78 Q111 72 113 58" fill="none" stroke="#5a1f2a" stroke-width="5" stroke-linecap="round" />
        <circle cx="113" cy="55" r="5.5" fill="#a8384b" stroke="#5a1f2a" stroke-width="3" />
    </g>

    {{-- Pies --}}
    <ellipse cx="46" cy="122" rx="10" ry="5.5" fill="#2f221b" />
    <ellipse cx="74" cy="122" rx="10" ry="5.5" fill="#2f221b" />

    {{-- La rodaja: borde de tripa, carne y trocitos de grasa y pimienta --}}
    <g class="mascota-cuerpo">
        <circle cx="60" cy="74" r="42" fill="#a8384b" stroke="#5a1f2a" stroke-width="6" />
        <g fill="#f6dccb">
            <ellipse cx="38" cy="52" rx="5" ry="3.5" transform="rotate(-25 38 52)" />
            <ellipse cx="62" cy="44" rx="4" ry="3" />
            <ellipse cx="84" cy="54" rx="5" ry="3.2" transform="rotate(30 84 54)" />
            <ellipse cx="30" cy="74" rx="3.2" ry="4.5" />
            <ellipse cx="92" cy="80" rx="3.5" ry="4.8" transform="rotate(15 92 80)" />
            <ellipse cx="44" cy="100" rx="4.5" ry="3" transform="rotate(20 44 100)" />
            <ellipse cx="78" cy="102" rx="4.8" ry="3.2" transform="rotate(-20 78 102)" />
            <ellipse cx="62" cy="108" rx="3" ry="2.2" />
        </g>
        <g fill="#3a1a1f">
            <circle cx="50" cy="45" r="1.8" />
            <circle cx="74" cy="46" r="1.6" />
            <circle cx="28" cy="62" r="1.6" />
            <circle cx="95" cy="66" r="1.8" />
            <circle cx="34" cy="90" r="1.7" />
            <circle cx="88" cy="94" r="1.6" />
            <circle cx="56" cy="104" r="1.5" />
        </g>

        {{-- Cara --}}
        <g class="mascota-ojos">
            <ellipse cx="47" cy="70" rx="5.5" ry="7" fill="#2f221b" />
            <ellipse cx="73" cy="70" rx="5.5" ry="7" fill="#2f221b" />
            <circle cx="49" cy="67" r="2" fill="#fff" />
            <circle cx="75" cy="67" r="2" fill="#fff" />
        </g>
        <ellipse cx="38" cy="83" rx="6" ry="4" fill="#f19aa6" opacity=".85" />
        <ellipse cx="82" cy="83" rx="6" ry="4" fill="#f19aa6" opacity=".85" />
        <path class="mascota-boca-feliz" d="M51 84 Q60 94 69 84" fill="none" stroke="#2f221b" stroke-width="3.2" stroke-linecap="round" />
        <path class="mascota-boca-triste" d="M52 91 Q60 84 68 91" fill="none" stroke="#2f221b" stroke-width="3.2" stroke-linecap="round" />
    </g>
</svg>
