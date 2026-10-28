@extends('layouts.public')

@section('title', isset($mesa) ? 'Mesa '.$mesa->numero : 'Menú')

@section('content')
    {{-- Portada: el cartel de la casa sobre una pintura de quesos y pan --}}
    <header class="portada">
        <img class="portada-fondo" src="{{ asset('img/menu/'.$portada['archivo']) }}" alt="" width="1200" height="843" fetchpriority="high">

        <div class="cartel-envoltura">
            {{-- Salamín se asoma por encima del cartel y saluda --}}
            <div class="portada-mascota" aria-hidden="true">
                <p class="globo globo-portada">¡Hola! Soy Salamín. ¿Te ayudo a escoger?</p>
                <x-mascota class="saludando" />
            </div>

            <div class="cartel">
                {{-- El logo es el título de la página; su texto alternativo es el nombre del negocio --}}
                <h1 class="cartel-nombre">
                    <img src="{{ asset(config('negocio.logo')) }}" alt="{{ config('negocio.nombre') }}" width="640" height="640" fetchpriority="high">
                </h1>
                <p class="cartel-lema">Embutidos, quesos y vinos de la casa, con platos para compartir.</p>

                @isset($mesa)
                    {{-- Boleto de la mesa: el cliente sabe que el QR es de su mesa --}}
                    <div class="boleto">
                        <span class="boleto-mesa">Mesa {{ $mesa->numero }}</span>
                        <span class="boleto-texto">Cuando estés listo, tu mesero toma el pedido.</span>
                    </div>
                @endisset

                <div class="cartel-pie">
                    <a href="#carta" class="boton-cartel">Ver el menú <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                    <span class="cartel-nota">Precios en quetzales</span>
                </div>

                <p class="cartel-oficios"><span>Delicatessen</span><span>Quesos</span><span>Vinos</span></p>
            </div>
        </div>
    </header>

    {{-- Barra fija: buscador y categorías. Sin JavaScript funcionan como formulario y enlaces normales. --}}
    <div class="barra" id="carta">
        <div class="barra-interior">
            <form method="GET" action="{{ url()->current() }}" class="buscador" role="search" id="form-buscar">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="{{ $buscar }}" id="buscar"
                       placeholder="¿Qué se te antoja?" aria-label="Buscar en el menú" autocomplete="off" enterkeyhint="search">
            </form>

            <nav class="pestanas" aria-label="Categorías">
                <a href="{{ url()->current() }}" data-categoria="" @class(['pestana', 'activo' => ! $categoriaId])>Todo</a>
                @foreach ($categorias as $categoria)
                    <a href="{{ url()->current().'?'.http_build_query(['categoria' => $categoria->id]) }}"
                       data-categoria="{{ $categoria->id }}" title="{{ $categoria->nombre }}"
                       @class(['pestana', 'activo' => $categoriaId === $categoria->id])>
                        {{ $categoria->nombre }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <main class="carta">
        @if ($buscar !== '' || $categoriaId)
            <p class="aviso-filtro">
                Mostrando resultados filtrados. <a href="{{ url()->current() }}">Ver todo el menú</a>
            </p>
        @endif

        @foreach ($secciones as $categoria)
            {{-- Cada categoría es una hoja de papel con su lámina --}}
            <section @class(['hoja', 'hoja-vino' => in_array($categoria->id, $seccionesDeVino)])
                     id="categoria-{{ $categoria->id }}" data-seccion="{{ $categoria->id }}" data-nombre="{{ $categoria->nombre }}">
                <h2 @class(['hoja-titulo', 'revelar', 'largo' => mb_strlen($categoria->nombre) > 24])>{{ $categoria->nombre }}</h2>
                <p class="hoja-nota revelar">
                    {{ $categoria->productos->count() }} {{ $categoria->productos->count() === 1 ? 'opción' : 'opciones' }}
                </p>

                <figure class="lamina revelar">
                    <img src="{{ asset('img/menu/'.$laminas[$categoria->id]['archivo']) }}" alt="{{ $laminas[$categoria->id]['alt'] }}"
                         width="1200" height="800" loading="lazy" decoding="async">
                    <figcaption>Bodegón de {{ $laminas[$categoria->id]['autor'] }}, {{ $laminas[$categoria->id]['anio'] }}</figcaption>
                </figure>

                <ul class="platos">
                    @foreach ($categoria->productos as $producto)
                        <li @class(['plato', 'revelar', 'agotado' => ! $producto->disponible, 'con-foto' => $producto->foto])
                            id="producto-{{ $producto->id }}"
                            data-texto="{{ $producto->nombre }} {{ $producto->descripcion }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-precio="Q {{ number_format((float) $producto->precio, 2, '.', ',') }}"
                            data-precio-numero="{{ $producto->precio }}"
                            @if ($producto->disponible) data-disponible @endif
                            @if ($producto->vinoSugerido?->disponible) data-vino="{{ $producto->vinoSugerido->nombre }}" @endif>
                            @if ($producto->foto)
                                <img src="{{ Storage::url($producto->foto) }}" alt="{{ $producto->nombre }}" class="plato-foto"
                                     width="96" height="96" loading="lazy" decoding="async">
                            @endif

                            <h3 class="plato-nombre">{{ $producto->nombre }}</h3>
                            <x-money :amount="$producto->precio" class="plato-precio" />

                            @if ($producto->descripcion)
                                <p class="plato-descripcion">{{ $producto->descripcion }}</p>
                            @endif

                            <div class="plato-extra">
                                @if (! $producto->disponible)
                                    <span class="sello">Agotado</span>
                                @endif

                                {{-- Maridaje: solo si el vino está disponible. El enlace lleva al vino en la hoja de vinos. --}}
                                @if ($producto->vinoSugerido?->disponible)
                                    <a href="{{ $buscar !== '' || $categoriaId ? url()->current() : '' }}#producto-{{ $producto->vinoSugerido->id }}"
                                       class="maridaje" data-vino-id="{{ $producto->vinoSugerido->id }}">
                                        <i class="bi bi-cup-straw" aria-hidden="true"></i>
                                        <span>Vino sugerido: {{ $producto->vinoSugerido->nombre }} · <x-money :amount="$producto->vinoSugerido->precio" /></span>
                                    </a>
                                @endif

                                {{-- Corazón de "Mi selección": solo en platos disponibles. Sin JavaScript queda escondido. --}}
                                @if ($producto->disponible)
                                    <button type="button" class="marcar" data-seleccionar="{{ $producto->id }}" aria-pressed="false"
                                            aria-label="Agregar {{ $producto->nombre }} a mi selección" hidden>
                                        <i class="bi bi-heart" aria-hidden="true"></i>
                                        <i class="bi bi-heart-fill" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        {{-- Sin resultados: el servidor lo muestra si no hay nada, y el JavaScript cuando el filtro deja todo oculto --}}
        <section id="sin-resultados" @class(['hoja', 'hoja-vacia', 'd-none' => $secciones->isNotEmpty()])>
            <x-mascota class="mascota-vacia" data-humor="triste" />
            <h2 class="hoja-titulo">No encontramos ese plato</h2>
            <p class="hoja-nota">Prueba con otra palabra o revisa todo el menú.</p>
            <a href="{{ url()->current() }}" class="boton-cartel" id="ver-todo">Ver todo el menú</a>
        </section>
    </main>

    {{-- Pie: el reverso de la postal --}}
    <footer class="reverso">
        <div class="postal">
            <div class="postal-mensaje">
                <h2 class="postal-titulo">¡Buen provecho!</h2>
                <p>Gracias por sentarte a nuestra mesa. Todo se prepara al momento, así que si tienes alguna alergia, cuéntale a tu mesero.</p>
                <p>Precios en quetzales, incluyen IVA.</p>
            </div>

            <div class="postal-direccion">
                <div class="estampilla" aria-hidden="true"><x-mascota /></div>
                <p class="postal-firma">{{ config('negocio.nombre') }}</p>
                <p>Charcutería, quesos y vinos</p>
                <p>Guatemala</p>
            </div>

            <p class="postal-imprenta">
                Láminas: bodegones de dominio público de
                @foreach ($creditos->unique('autor') as $credito)
                    <a href="{{ $credito['fuente'] }}" target="_blank" rel="noopener">{{ $credito['autor'] }}</a>{{ $loop->last ? '.' : ',' }}
                @endforeach
            </p>
        </div>
    </footer>

    {{-- Mi selección: barra que aparece al marcar platos y hoja con la lista para enseñársela al mesero.
         No es un pedido: se guarda solo en el celular del cliente (localStorage). --}}
    <button type="button" class="barra-seleccion" id="barra-seleccion" hidden>
        <i class="bi bi-heart-fill" aria-hidden="true"></i>
        <span>Mi selección</span>
        <span class="barra-seleccion-cuenta" id="seleccion-cuenta">0</span>
        <span class="barra-seleccion-total" id="seleccion-total-barra">Q 0.00</span>
    </button>

    <dialog class="hoja-seleccion" id="hoja-seleccion" aria-labelledby="titulo-seleccion">
        <div class="hoja-seleccion-interior">
            <div class="hoja-seleccion-encabezado">
                <h2 id="titulo-seleccion">Mi selección</h2>
                <button type="button" class="cerrar-seleccion" id="cerrar-seleccion" aria-label="Cerrar mi selección">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <p class="hoja-seleccion-nota solo-editar">Cuando llegue tu mesero, toca "Mostrar al mesero" y dale el teléfono.</p>

            {{-- En modo mesero se ve la mesa en grande para que la lea de un vistazo --}}
            @isset($mesa)
                <p class="seleccion-mesa solo-mesero">Mesa {{ $mesa->numero }}</p>
            @endisset

            <ul class="lista-seleccion" id="lista-seleccion"></ul>

            <div class="hoja-seleccion-total">
                <span>Total estimado</span>
                <strong id="seleccion-total">Q 0.00</strong>
            </div>
            <p class="hoja-seleccion-nota">Sin propina. El total final lo da caja.</p>

            <div class="hoja-seleccion-acciones">
                <button type="button" class="boton-cartel" id="mostrar-mesero" aria-pressed="false">
                    <i class="bi bi-person-raised-hand" aria-hidden="true"></i> <span>Mostrar al mesero</span>
                </button>
                <button type="button" class="vaciar-seleccion solo-editar" id="vaciar-seleccion">Vaciar la lista</button>
            </div>
        </div>
    </dialog>

    {{-- Plantilla de cada renglón de la lista (el JavaScript la copia y llena) --}}
    <template id="plantilla-seleccion">
        <li class="renglon-seleccion">
            <span class="renglon-cantidad solo-mesero"></span>
            <div class="min-w-0">
                <p class="renglon-nombre"></p>
                <p class="renglon-precio"></p>
            </div>
            <div class="contador solo-editar" role="group">
                <button type="button" class="contador-boton" data-accion="menos"><i class="bi bi-dash-lg" aria-hidden="true"></i></button>
                <span class="contador-valor" aria-live="polite"></span>
                <button type="button" class="contador-boton" data-accion="mas"><i class="bi bi-plus-lg" aria-hidden="true"></i></button>
            </div>
        </li>
    </template>

    {{-- Salamín flotante: aparece al bajar. Al tocarlo recomienda un plato disponible con su vino. --}}
    <div class="salamin" id="salamin">
        <p class="globo" id="globo" role="status" aria-live="polite"></p>
        <button type="button" class="salamin-boton" id="salamin-boton" aria-label="Pedirle una recomendación a Salamín">
            <x-mascota />
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        // Datos para el JavaScript del menú: si la página ya viene filtrada desde el servidor, no se filtra de nuevo
        window.menuTabla = { filtradoEnServidor: @js($buscar !== '' || (bool) $categoriaId) };
    </script>
    <script src="{{ asset('js/menu.js') }}?v={{ filemtime(public_path('js/menu.js')) }}"></script>
@endpush
