/*
 * Mapa de mesas del mesero (resources/views/mesero/mesas/index.blade.php).
 * Cada 5 segundos pide a la ruta mesero.mesas.datos el estado de todas las mesas
 * y actualiza las tarjetas sin recargar la página. Si una mesa tiene una comanda
 * nueva lista para llevar, la resalta y hace vibrar el celular (si se puede).
 */
(() => {
    const mapa = document.getElementById('mapa-mesas');
    if (!mapa) return;

    const CADA = 5000;
    const estadoConexion = document.getElementById('estado-conexion');
    const avisoListas = document.getElementById('aviso-listas');
    const tituloOriginal = document.title;

    // Cuántas comandas listas tenía cada mesa en la consulta anterior, para saber cuáles son nuevas
    const listasAntes = new Map();
    mapa.querySelectorAll('[data-mesa]').forEach((tarjeta) => {
        listasAntes.set(tarjeta.dataset.mesa, Number(tarjeta.dataset.listas));
    });

    let temporizador;
    let consultando = false;

    const pintarMesa = (mesa) => {
        const tarjeta = mapa.querySelector(`[data-mesa="${mesa.id}"]`);
        const campo = (nombre) => tarjeta.querySelector(`[data-campo="${nombre}"]`);

        tarjeta.dataset.estado = mesa.estado;
        campo('estado').className = `estado-mesa estado-${mesa.estado}`;
        campo('estado').textContent = mesa.estado_texto;
        campo('tiempo').textContent = mesa.tiempo;

        const hayListas = mesa.comandas_listas > 0;
        tarjeta.classList.toggle('con-lista', hayListas);
        campo('aviso').hidden = !hayListas;
        campo('aviso-texto').textContent = mesa.comandas_listas > 1 ? `${mesa.comandas_listas} comandas listas` : '¡Comanda lista!';

        // Comanda nueva lista desde la última consulta: se resalta un momento
        const nueva = mesa.comandas_listas > (listasAntes.get(String(mesa.id)) ?? 0);
        listasAntes.set(String(mesa.id), mesa.comandas_listas);
        if (nueva) {
            tarjeta.classList.remove('recien-lista');
            void tarjeta.offsetWidth; // reinicia la animación
            tarjeta.classList.add('recien-lista');
        }
        return nueva;
    };

    const pintar = (datos) => {
        const mesas = Object.values(datos.zonas).flat();

        // Si el admin agregó o quitó mesas, se recarga la página para dibujar la cuadrícula de nuevo
        const idsPagina = [...mapa.querySelectorAll('[data-mesa]')].map((t) => t.dataset.mesa).sort().join();
        const idsDatos = mesas.map((m) => String(m.id)).sort().join();
        if (idsPagina !== idsDatos) {
            location.reload();
            return;
        }

        const hayNuevas = mesas.map(pintarMesa).some(Boolean);
        if (hayNuevas && navigator.vibrate) navigator.vibrate([120, 80, 120]);

        // Conteo de cada zona: "5 mesas · 3 libres"
        document.querySelectorAll('[data-zona-conteo]').forEach((conteo) => {
            const mesasZona = datos.zonas[conteo.dataset.zonaConteo] || [];
            const libres = mesasZona.filter((m) => m.estado === 'libre').length;
            conteo.textContent = `${mesasZona.length} ${mesasZona.length === 1 ? 'mesa' : 'mesas'} · ${libres} libres`;
        });

        Object.entries(datos.resumen).forEach(([estado, dato]) => {
            const contador = document.querySelector(`[data-resumen="${estado}"]`);
            if (contador) contador.textContent = dato.total;
        });

        avisoListas.hidden = datos.listas === 0;
        document.getElementById('aviso-listas-texto').textContent =
            `${datos.listas} ${datos.listas === 1 ? 'comanda lista' : 'comandas listas'} para llevar`;

        // En la pestaña del navegador se ve cuántas comandas hay por llevar: "(2) Mapa de mesas"
        document.title = datos.listas > 0 ? `(${datos.listas}) ${tituloOriginal}` : tituloOriginal;

        estadoConexion.textContent = `Actualizado ${datos.actualizado}`;
        estadoConexion.classList.remove('sin-conexion');
    };

    const consultar = async () => {
        if (consultando) return;
        consultando = true;
        try {
            const respuesta = await fetch(mapa.dataset.datos, { headers: { Accept: 'application/json' } });
            if (!respuesta.ok) throw new Error(respuesta.status);
            pintar(await respuesta.json());
        } catch {
            estadoConexion.textContent = 'Sin conexión. Reintentando…';
            estadoConexion.classList.add('sin-conexion');
        } finally {
            consultando = false;
        }
    };

    const iniciar = () => {
        clearInterval(temporizador);
        temporizador = setInterval(consultar, CADA);
    };

    // Con la pestaña o el celular en segundo plano no se consulta; al volver, se actualiza al instante
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(temporizador);
        } else {
            consultar();
            iniciar();
        }
    });

    iniciar();
})();
