/*
 * JavaScript del menú público.
 * 1. Filtro por categoría y búsqueda al instante, sin recargar.
 * 2. Al bajar, la pestaña de la categoría que se está viendo se marca sola.
 * 3. Los platos aparecen con una pequeña animación al entrar en pantalla.
 * 4. El botón de vino sugerido lleva al vino y lo resalta.
 * 5. Salamín: aparece al bajar, comenta cada sección y, al tocarlo, recomienda un plato.
 * 6. Mi selección: el cliente marca platos con el corazón y ve su lista con el total estimado.
 *    No es un pedido: se guarda solo en su celular (localStorage).
 * Sin JavaScript la página sigue funcionando con enlaces y formulario normales.
 */
(() => {
    const { filtradoEnServidor } = window.menuTabla || {};
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const inputBuscar = document.getElementById('buscar');
    const pestanas = [...document.querySelectorAll('.pestana')];
    const contenedorPestanas = document.querySelector('.pestanas');
    const secciones = [...document.querySelectorAll('[data-seccion]')];
    const sinResultados = document.getElementById('sin-resultados');
    const portada = document.querySelector('.portada');
    const salamin = document.getElementById('salamin');
    const botonSalamin = document.getElementById('salamin-boton');
    const globo = document.getElementById('globo');
    const mascotaFlotante = botonSalamin.querySelector('.mascota');

    let categoriaElegida = '';

    // "Jamón" y "jamon" deben coincidir: se quitan tildes y mayúsculas
    const normalizar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

    // ---------- Salamín habla ----------

    let temporizadorGlobo;
    let globoEsComentario = false;

    const cerrarGlobo = () => {
        globo.classList.remove('abierto');
        mascotaFlotante.dataset.humor = 'feliz';
    };

    // comentario: true = se cierra apenas el cliente vuelve a bajar, para no tapar los platos
    const decir = (texto, { humor = 'feliz', duracion = 4200, comentario = false } = {}) => {
        globo.textContent = texto;
        globo.classList.add('abierto');
        globoEsComentario = comentario;
        mascotaFlotante.dataset.humor = humor;
        clearTimeout(temporizadorGlobo);
        temporizadorGlobo = setTimeout(cerrarGlobo, duracion);
    };

    // Los comentarios de sección esperan a que el cliente deje de bajar
    let comentarioPendiente = null;
    let temporizadorQuieto;
    window.addEventListener('scroll', () => {
        if (globoEsComentario && globo.classList.contains('abierto')) cerrarGlobo();
        clearTimeout(temporizadorQuieto);
        temporizadorQuieto = setTimeout(() => {
            if (comentarioPendiente && salamin.classList.contains('visible')) {
                decir(comentarioPendiente, { duracion: 3200, comentario: true });
            }
            comentarioPendiente = null;
        }, 700);
    }, { passive: true });

    // Un comentario por tipo de sección; se busca por palabras del nombre de la categoría
    const comentarios = [
        ['tabla', 'Las tablas son para compartir... o no, aquí no juzgamos.'],
        ['panini', 'Recién salidos de la plancha, bien doraditos.'],
        ['sandwich', 'Recién salidos de la plancha, bien doraditos.'],
        ['hamburguesa', '¡Mis primas favoritas! Pídelas con calma.'],
        ['taco', 'Con su guacamol, como debe ser.'],
        ['vino', 'Si no sabes cuál, el mesero te da a probar.'],
        ['bebida', '¿Algo fresco para acompañar?'],
        ['queso', 'Quesos de la casa, mis vecinos de vitrina.'],
        ['postre', 'Siempre hay espacio para el postre.'],
    ];

    const comentarioDe = (nombreCategoria) => {
        const nombre = normalizar(nombreCategoria);
        const encontrado = comentarios.find(([palabra]) => nombre.includes(palabra));
        return encontrado ? encontrado[1] : `¡Todo lo de ${nombreCategoria.toLowerCase()} está bueno!`;
    };

    // Lleva a un plato y lo resalta un momento
    const irAPlato = (plato) => {
        plato.classList.add('visto');
        plato.scrollIntoView({ behavior: reducirMovimiento ? 'auto' : 'smooth', block: 'center' });
        plato.classList.remove('resaltado');
        void plato.offsetWidth; // reinicia la animación si se toca dos veces
        plato.classList.add('resaltado');
    };

    // ---------- 1. Filtro y búsqueda ----------

    const aplicarFiltros = () => {
        const busqueda = normalizar(inputBuscar.value);
        let hayResultados = false;

        secciones.forEach((seccion) => {
            const mostrarCategoria = categoriaElegida === '' || seccion.dataset.seccion === categoriaElegida;
            let platosVisibles = 0;

            seccion.querySelectorAll('.plato').forEach((plato) => {
                const coincide = mostrarCategoria && normalizar(plato.dataset.texto).includes(busqueda);
                plato.classList.toggle('d-none', !coincide);
                if (coincide) {
                    platosVisibles++;
                    plato.classList.add('visto');
                }
            });

            seccion.classList.toggle('d-none', platosVisibles === 0);
            if (platosVisibles > 0) hayResultados = true;
        });

        sinResultados.classList.toggle('d-none', hayResultados);
        return hayResultados;
    };

    const marcarPestana = (pestana) => {
        pestanas.forEach((otra) => otra.classList.toggle('activo', otra === pestana));
        // Mueve solo la fila de pestañas, sin mover la página
        const izquierda = pestana.offsetLeft - (contenedorPestanas.clientWidth - pestana.offsetWidth) / 2;
        contenedorPestanas.scrollTo({ left: izquierda, behavior: reducirMovimiento ? 'auto' : 'smooth' });
    };

    const quitarFiltros = () => {
        inputBuscar.value = '';
        categoriaElegida = '';
        marcarPestana(pestanas[0]);
        aplicarFiltros();
    };

    if (!filtradoEnServidor) {
        let temporizadorBusqueda;
        inputBuscar.addEventListener('input', () => {
            const hayResultados = aplicarFiltros();
            clearTimeout(temporizadorBusqueda);
            temporizadorBusqueda = setTimeout(() => {
                if (!hayResultados && inputBuscar.value.trim() !== '') {
                    decir('Mmm, eso no lo tenemos. ¿Probamos con otra palabra?', { humor: 'triste' });
                }
            }, 500);
        });

        document.getElementById('form-buscar').addEventListener('submit', (evento) => {
            evento.preventDefault();
            inputBuscar.blur(); // cierra el teclado del celular
        });

        pestanas.forEach((pestana) => {
            pestana.addEventListener('click', (evento) => {
                evento.preventDefault();
                categoriaElegida = pestana.dataset.categoria;
                marcarPestana(pestana);
                aplicarFiltros();
                document.getElementById('carta').scrollIntoView({ behavior: reducirMovimiento ? 'auto' : 'smooth' });
            });
        });

        document.getElementById('ver-todo').addEventListener('click', (evento) => {
            evento.preventDefault();
            quitarFiltros();
        });
    }

    // ---------- 2. Pestaña de la sección que se está viendo ----------

    const comentadas = new Set();
    const observadorSecciones = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada) => {
            if (!entrada.isIntersecting || categoriaElegida !== '' || filtradoEnServidor) return;

            const seccion = entrada.target;
            const pestana = pestanas.find((p) => p.dataset.categoria === seccion.dataset.seccion);
            if (pestana) marcarPestana(pestana);

            // Salamín comenta cada sección una sola vez, y no interrumpe si ya está diciendo algo
            const ocupado = globo.classList.contains('abierto') && !globoEsComentario;
            if (!ocupado && !comentadas.has(seccion.dataset.seccion)) {
                comentadas.add(seccion.dataset.seccion);
                comentarioPendiente = comentarioDe(seccion.dataset.nombre);
            }
        });
    }, { rootMargin: '-35% 0px -60% 0px' });

    secciones.forEach((seccion) => observadorSecciones.observe(seccion));

    // ---------- 3. Entrada de los platos ----------

    document.documentElement.classList.add('animar');
    const observadorEntrada = new IntersectionObserver((entradas) => {
        // Los que entran juntos aparecen uno tras otro, con 60 ms entre cada uno
        entradas.filter((e) => e.isIntersecting).forEach((entrada, indice) => {
            entrada.target.style.transitionDelay = `${Math.min(indice * 60, 360)}ms`;
            entrada.target.classList.add('visto');
            observadorEntrada.unobserve(entrada.target);
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('.revelar').forEach((elemento) => observadorEntrada.observe(elemento));

    // ---------- 4. Vino sugerido ----------

    document.querySelectorAll('.maridaje').forEach((enlace) => {
        enlace.addEventListener('click', (evento) => {
            if (filtradoEnServidor) return; // el enlace lleva al menú completo
            const vino = document.getElementById(`producto-${enlace.dataset.vinoId}`);
            evento.preventDefault();
            if (!vino) return;
            if (vino.closest('.d-none')) quitarFiltros();
            irAPlato(vino);
            decir('¡Buena elección! Este vino le queda perfecto.', { duracion: 3000 });
        });
    });

    // ---------- 5. Salamín flotante ----------

    // Aparece cuando la portada sale de la pantalla
    new IntersectionObserver(([entrada]) => {
        salamin.classList.toggle('visible', !entrada.isIntersecting);
        if (entrada.isIntersecting) globo.classList.remove('abierto');
    }, { threshold: 0.15 }).observe(portada);

    // Al tocarlo recomienda un plato disponible que esté a la vista (que no sea vino), con su vino si tiene
    let ultimaRecomendacion = null;
    botonSalamin.addEventListener('click', () => {
        const candidatos = [...document.querySelectorAll('.plato[data-disponible]')]
            .filter((plato) => !plato.closest('.d-none') && !plato.closest('.hoja-vino') && plato !== ultimaRecomendacion);

        salamin.classList.remove('brinco');
        void salamin.offsetWidth;
        salamin.classList.add('brinco');

        if (candidatos.length === 0) {
            decir('Hoy no encuentro nada por aquí. ¡Pregúntale al mesero!', { humor: 'triste' });
            return;
        }

        const plato = candidatos[Math.floor(Math.random() * candidatos.length)];
        ultimaRecomendacion = plato;
        const vino = plato.dataset.vino ? ` Va de lujo con ${plato.dataset.vino}.` : '';
        decir(`Te recomiendo: ${plato.dataset.nombre}, ${plato.dataset.precio}.${vino}`, { duracion: 5200 });
        irAPlato(plato);
    });

    // ---------- 6. Mi selección ----------

    const CLAVE = 'tabla-seleccion';
    const barra = document.getElementById('barra-seleccion');
    const hojaSeleccion = document.getElementById('hoja-seleccion');
    const lista = document.getElementById('lista-seleccion');
    const plantilla = document.getElementById('plantilla-seleccion');
    const corazones = [...document.querySelectorAll('[data-seleccionar]')];

    const quetzales = (monto) => 'Q ' + monto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // { id: { nombre, precio, cantidad } }. Se guarda el nombre y el precio para poder mostrar
    // la lista aunque la página venga filtrada y ese plato no esté en pantalla.
    let seleccion = {};
    try {
        seleccion = JSON.parse(localStorage.getItem(CLAVE)) || {};
    } catch {
        seleccion = {};
    }

    // Si un plato marcado se agotó desde la última visita, se quita de la lista;
    // si cambió de nombre o precio, se toma el dato nuevo del menú
    Object.keys(seleccion).forEach((id) => {
        const plato = document.getElementById(`producto-${id}`);
        if (!plato) return;
        if (!plato.hasAttribute('data-disponible')) {
            delete seleccion[id];
            return;
        }
        seleccion[id].nombre = plato.dataset.nombre;
        seleccion[id].precio = Number(plato.dataset.precioNumero);
    });

    const guardar = () => {
        try {
            localStorage.setItem(CLAVE, JSON.stringify(seleccion));
        } catch {
            // Modo privado o almacenamiento lleno: la lista sigue funcionando mientras la página esté abierta
        }
    };

    const latir = (elemento) => {
        elemento.classList.remove('latido');
        void elemento.offsetWidth;
        elemento.classList.add('latido');
    };

    const pintarSeleccion = () => {
        const renglones = Object.entries(seleccion);
        const cantidad = renglones.reduce((suma, [, item]) => suma + item.cantidad, 0);
        const total = renglones.reduce((suma, [, item]) => suma + item.precio * item.cantidad, 0);

        // Corazones
        corazones.forEach((boton) => {
            const marcado = Boolean(seleccion[boton.dataset.seleccionar]);
            const nombre = boton.closest('.plato').dataset.nombre;
            boton.setAttribute('aria-pressed', String(marcado));
            boton.setAttribute('aria-label', marcado ? `Quitar ${nombre} de mi selección` : `Agregar ${nombre} a mi selección`);
        });

        // Barra
        barra.classList.toggle('visible', cantidad > 0);
        barra.setAttribute('aria-label', `Ver mi selección: ${cantidad} ${cantidad === 1 ? 'plato' : 'platos'}, ${quetzales(total)}`);
        document.getElementById('seleccion-cuenta').textContent = cantidad;
        document.getElementById('seleccion-total-barra').textContent = quetzales(total);

        // Lista de la hoja
        lista.replaceChildren(...renglones.map(([id, item]) => {
            const renglon = plantilla.content.firstElementChild.cloneNode(true);
            renglon.dataset.id = id;
            renglon.querySelector('.renglon-cantidad').textContent = `${item.cantidad}×`;
            renglon.querySelector('.renglon-nombre').textContent = item.nombre;
            renglon.querySelector('.renglon-precio').textContent =
                item.cantidad > 1 ? `${item.cantidad} × ${quetzales(item.precio)} = ${quetzales(item.precio * item.cantidad)}` : quetzales(item.precio);
            renglon.querySelector('.contador-valor').textContent = item.cantidad;
            renglon.querySelector('.contador').setAttribute('aria-label', `Cantidad de ${item.nombre}`);
            renglon.querySelector('[data-accion="menos"]').setAttribute('aria-label', item.cantidad === 1 ? `Quitar ${item.nombre}` : `Uno menos de ${item.nombre}`);
            renglon.querySelector('[data-accion="mas"]').setAttribute('aria-label', `Uno más de ${item.nombre}`);
            return renglon;
        }));
        document.getElementById('seleccion-total').textContent = quetzales(total);

        if (cantidad === 0 && hojaSeleccion.open) hojaSeleccion.close();
    };

    corazones.forEach((boton) => {
        boton.hidden = false; // sin JavaScript se quedan escondidos
        boton.addEventListener('click', () => {
            const plato = boton.closest('.plato');
            const id = boton.dataset.seleccionar;

            if (seleccion[id]) {
                delete seleccion[id];
            } else {
                seleccion[id] = { nombre: plato.dataset.nombre, precio: Number(plato.dataset.precioNumero), cantidad: 1 };
                latir(boton);
                latir(barra);
                const total = Object.values(seleccion).reduce((suma, item) => suma + item.cantidad, 0);
                decir(`¡Anotado! Llevas ${total} en tu selección.`, { duracion: 2400, comentario: true });
            }

            guardar();
            pintarSeleccion();
        });
    });

    // Modo mesero: la lista en grande y sin botones; el mismo botón regresa a editar
    const botonMesero = document.getElementById('mostrar-mesero');
    const modoMesero = (activar) => {
        hojaSeleccion.classList.toggle('modo-mesero', activar);
        botonMesero.setAttribute('aria-pressed', String(activar));
        botonMesero.lastElementChild.textContent = activar ? 'Volver a editar' : 'Mostrar al mesero';
        botonMesero.firstElementChild.className = activar ? 'bi bi-pencil' : 'bi bi-person-raised-hand';
        hojaSeleccion.classList.remove('cambio');
        void hojaSeleccion.offsetWidth;
        hojaSeleccion.classList.add('cambio');
        hojaSeleccion.scrollTop = 0;
    };
    botonMesero.addEventListener('click', () => modoMesero(!hojaSeleccion.classList.contains('modo-mesero')));

    // Al cerrar la hoja vuelve al modo normal, para que la próxima vez se pueda editar
    hojaSeleccion.addEventListener('close', () => {
        hojaSeleccion.classList.remove('modo-mesero', 'cambio');
        botonMesero.setAttribute('aria-pressed', 'false');
        botonMesero.lastElementChild.textContent = 'Mostrar al mesero';
        botonMesero.firstElementChild.className = 'bi bi-person-raised-hand';
    });

    barra.hidden = false;
    barra.addEventListener('click', () => hojaSeleccion.showModal());
    document.getElementById('cerrar-seleccion').addEventListener('click', () => hojaSeleccion.close());

    // Tocar fuera de la hoja (en el fondo oscuro) también la cierra
    hojaSeleccion.addEventListener('click', (evento) => {
        if (evento.target === hojaSeleccion) hojaSeleccion.close();
    });

    lista.addEventListener('click', (evento) => {
        const boton = evento.target.closest('[data-accion]');
        if (!boton) return;
        const id = boton.closest('.renglon-seleccion').dataset.id;

        seleccion[id].cantidad += boton.dataset.accion === 'mas' ? 1 : -1;
        if (seleccion[id].cantidad <= 0) delete seleccion[id];

        guardar();
        pintarSeleccion();

        // Después de redibujar, el foco vuelve al mismo botón para poder seguir sumando con el teclado
        lista.querySelector(`[data-id="${id}"] [data-accion="${boton.dataset.accion}"]`)?.focus();
    });

    document.getElementById('vaciar-seleccion').addEventListener('click', () => {
        seleccion = {};
        guardar();
        pintarSeleccion();
    });

    pintarSeleccion();
})();
