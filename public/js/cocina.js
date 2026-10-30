/*
 * Pantalla de cocina (resources/views/cocina/index.blade.php).
 * Cada 5 segundos pide las comandas a la ruta cocina.comandas.datos y redibuja las tres columnas.
 * Los botones "Empezar" y "Listo" cambian el estado con fetch, sin recargar la página.
 */
(() => {
    const cocina = document.getElementById('cocina');
    if (!cocina) return;

    const CADA = 5000;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const plantilla = document.getElementById('plantilla-comanda');
    const estadoConexion = document.getElementById('estado-conexion');
    const conocidas = new Set(); // comandas ya vistas, para resaltar las nuevas
    let primeraVez = true;
    let temporizador;

    // Qué botón lleva cada comanda según su estado
    const BOTONES = {
        pendiente: { texto: 'Empezar a preparar', siguiente: 'preparando', clase: 'btn-outline-primary' },
        preparando: { texto: 'Listo', siguiente: 'listo', clase: 'btn-primary' },
        listo: null, // el mesero la marca entregada desde su pantalla
    };

    const peticion = (url, opciones = {}) => fetch(url, {
        ...opciones,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    });

    const dibujarComanda = (comanda) => {
        const tarjeta = plantilla.content.firstElementChild.cloneNode(true);
        tarjeta.dataset.espera = comanda.estado === 'listo' ? 'lista' : comanda.espera;
        tarjeta.querySelector('.comanda-cocina-mesa').textContent = `Mesa ${comanda.mesa}`;
        tarjeta.querySelector('.comanda-cocina-tiempo').textContent = `${comanda.hora} · hace ${comanda.minutos} min`;

        const lista = tarjeta.querySelector('.comanda-cocina-productos');
        comanda.productos.forEach((producto) => {
            const item = document.createElement('li');
            const cantidad = document.createElement('strong');
            cantidad.textContent = `${producto.cantidad}×`;
            item.append(cantidad, ` ${producto.nombre}`);
            if (producto.notas) {
                // Las notas van resaltadas: es lo que más se le olvida a una cocina con prisa
                const nota = document.createElement('span');
                nota.className = 'nota-cocina';
                nota.textContent = producto.notas;
                item.append(nota);
            }
            lista.append(item);
        });

        const boton = tarjeta.querySelector('.comanda-cocina-boton');
        const accion = BOTONES[comanda.estado];
        if (accion) {
            boton.textContent = accion.texto;
            boton.classList.add(accion.clase);
            boton.addEventListener('click', () => cambiarEstado(comanda.id, accion.siguiente, boton));
        } else {
            boton.remove();
        }

        if (!primeraVez && !conocidas.has(comanda.id)) tarjeta.classList.add('nueva');
        conocidas.add(comanda.id);
        return tarjeta;
    };

    const dibujar = (datos) => {
        ['pendiente', 'preparando', 'listo'].forEach((estado) => {
            const deEstado = datos.comandas.filter((c) => c.estado === estado);
            const columna = cocina.querySelector(`[data-columna="${estado}"]`);
            columna.replaceChildren(...deEstado.map(dibujarComanda));
            if (deEstado.length === 0) {
                const vacio = document.createElement('p');
                vacio.className = 'columna-cocina-vacia';
                vacio.textContent = estado === 'pendiente' ? 'Sin comandas nuevas' : 'Nada por aquí';
                columna.append(vacio);
            }
            cocina.querySelector(`[data-cuenta="${estado}"]`).textContent = deEstado.length;
        });

        // Resumen: "4× Panini caprese · 2× Tabla de quesos"
        const resumen = document.getElementById('resumen-cocina');
        if (datos.resumen.length === 0) {
            resumen.innerHTML = '<span class="text-muted small">Nada pendiente</span>';
        } else {
            resumen.replaceChildren(...datos.resumen.map((item) => {
                const chip = document.createElement('span');
                chip.className = 'chip-resumen';
                const cantidad = document.createElement('strong');
                cantidad.textContent = item.cantidad;
                chip.append(cantidad, ` ${item.nombre}`);
                return chip;
            }));
        }

        estadoConexion.textContent = `Actualizado ${datos.actualizado}`;
        estadoConexion.classList.remove('sin-conexion');
        primeraVez = false;
    };

    const consultar = async () => {
        try {
            const respuesta = await peticion(cocina.dataset.datos);
            if (!respuesta.ok) throw new Error(respuesta.status);
            dibujar(await respuesta.json());
        } catch {
            estadoConexion.textContent = 'Sin conexión. Reintentando…';
            estadoConexion.classList.add('sin-conexion');
        }
    };

    const cambiarEstado = async (id, estado, boton) => {
        boton.disabled = true;
        const respuesta = await peticion(cocina.dataset.estado.replace('__ID__', id), {
            method: 'PATCH',
            body: JSON.stringify({ estado }),
        }).catch(() => null);
        if (!respuesta || !respuesta.ok) boton.disabled = false;
        consultar(); // se redibuja al instante, sin esperar los 5 segundos
    };

    // Marcar un producto como agotado
    document.getElementById('form-agotar').addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const select = document.getElementById('producto-agotar');
        const opcion = select.selectedOptions[0];
        if (!select.value || !confirm(`¿Se acabó «${opcion.textContent}»? Ya no se podrá pedir.`)) return;

        const respuesta = await peticion(cocina.dataset.agotar.replace('__ID__', select.value), { method: 'PATCH' }).catch(() => null);
        if (respuesta && respuesta.ok) {
            opcion.remove();
            select.value = '';
            alert((await respuesta.json()).mensaje);
        } else {
            alert('No se pudo marcar como agotado. Intenta de nuevo.');
        }
    });

    const iniciar = () => {
        clearInterval(temporizador);
        temporizador = setInterval(consultar, CADA);
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(temporizador);
        } else {
            consultar();
            iniciar();
        }
    });

    consultar();
    iniciar();
})();
