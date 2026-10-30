// Pantalla del pedido (mesero): filtra el catálogo y abre la ventana de cantidad y nota.
(() => {
    const catalogo = document.getElementById('catalogo');
    if (!catalogo) return;

    const buscador = document.getElementById('buscar-producto');
    const vacio = document.getElementById('catalogo-vacio');
    const modalElemento = document.getElementById('modal-agregar');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);
    let categoria = '';

    // Sin tildes ni mayúsculas, para que "jamon" encuentre "Jamón"
    const limpiar = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

    const filtrar = () => {
        const texto = limpiar(buscador.value.trim());
        let visibles = 0;
        catalogo.querySelectorAll('.catalogo-seccion').forEach((seccion) => {
            let enSeccion = 0;
            seccion.querySelectorAll('.item-catalogo').forEach((item) => {
                const coincide = (categoria === '' || item.dataset.categoria === categoria)
                    && limpiar(item.dataset.nombre).includes(texto);
                item.hidden = !coincide;
                if (coincide) enSeccion++;
            });
            seccion.hidden = enSeccion === 0;
            visibles += enSeccion;
        });
        vacio.hidden = visibles > 0;
    };

    buscador.addEventListener('input', filtrar);

    document.querySelectorAll('.btn-categoria').forEach((boton) => {
        boton.addEventListener('click', () => {
            categoria = boton.dataset.filtro;
            document.querySelectorAll('.btn-categoria').forEach((b) => b.classList.toggle('activo', b === boton));
            filtrar();
        });
    });

    catalogo.addEventListener('click', (evento) => {
        const item = evento.target.closest('.item-catalogo');
        if (!item || item.disabled) return;

        document.getElementById('modal-agregar-titulo').textContent = item.dataset.nombre;
        document.getElementById('agregar-producto').value = item.dataset.producto;
        document.getElementById('agregar-cantidad').value = 1;
        document.getElementById('agregar-notas').value = '';
        document.getElementById('agregar-vino').hidden = !item.dataset.vino;
        document.getElementById('agregar-vino-nombre').textContent = item.dataset.vino;
        modal.show();
    });

    modalElemento.addEventListener('shown.bs.modal', () => document.getElementById('agregar-cantidad').select());

    // Mantener la posición del scroll al recargar
    const claveScroll = `scroll-pedido-${catalogo.dataset.pedido}`;
    try {
        const guardado = sessionStorage.getItem(claveScroll);
        if (guardado !== null) {
            sessionStorage.removeItem(claveScroll);
            window.scrollTo(0, parseInt(guardado, 10));
        }
    } catch {}
    document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', () => {
        try { sessionStorage.setItem(claveScroll, String(window.scrollY)); } catch {}
    }));
})();
