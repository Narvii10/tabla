(() => {
    const dividir = (centavos, personas) => {
        if (personas < 1) return [centavos];
        const base = Math.floor(centavos / personas);
        const resto = centavos % personas;
        return Array.from({ length: personas }, (_, i) => base + (i < resto ? 1 : 0));
    };

    const formatoQ = (centavos) => `Q ${(centavos / 100).toFixed(2)}`;

    document.querySelectorAll('.opcion-propina').forEach((radio) => {
        radio.addEventListener('change', () => {
            const input = document.getElementById('propina-input');
            if (radio.id === 'propina-manual') {
                input.readOnly = false;
                input.focus();
            } else {
                input.readOnly = true;
                input.value = radio.value;
            }
        });
    });

    const contenedor = document.getElementById('caja-pedido');
    const personasInput = document.getElementById('personas');
    const listaDivision = document.getElementById('lista-division');
    if (contenedor && personasInput && listaDivision) {
        const saldoCentavos = parseInt(contenedor.dataset.saldoCentavos, 10);
        personasInput.addEventListener('input', () => {
            const personas = parseInt(personasInput.value, 10) || 1;
            const partes = dividir(saldoCentavos, personas);
            listaDivision.replaceChildren(...partes.map((centavos, i) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between';
                const nombre = document.createElement('span');
                nombre.textContent = `Persona ${i + 1}`;
                const monto = document.createElement('span');
                monto.textContent = formatoQ(centavos);
                li.append(nombre, monto);
                return li;
            }));
        });
    }

    const formPago = document.getElementById('form-pago');
    if (formPago) {
        const metodoSelect = document.getElementById('pago-metodo');
        const recibidoGrupo = document.getElementById('pago-recibido-grupo');
        const recibidoInput = document.getElementById('pago-recibido');
        const montoInput = document.getElementById('pago-monto');
        const vueltoTexto = document.getElementById('pago-vuelto');
        const boton = document.getElementById('pago-boton');

        const actualizarVisibilidad = () => {
            const esEfectivo = metodoSelect.value === 'efectivo';
            recibidoGrupo.hidden = !esEfectivo;
            recibidoInput.required = esEfectivo;
            if (!esEfectivo) {
                recibidoInput.value = '';
                vueltoTexto.textContent = '';
            }
        };

        const actualizarVuelto = () => {
            if (metodoSelect.value !== 'efectivo') return;
            const montoCentavos = Math.round(parseFloat(montoInput.value || '0') * 100);
            const recibidoCentavos = Math.round(parseFloat(recibidoInput.value || '0') * 100);
            const vueltoCentavos = recibidoCentavos - montoCentavos;
            vueltoTexto.textContent = vueltoCentavos >= 0
                ? `Vuelto: ${formatoQ(vueltoCentavos)}`
                : `Faltan ${formatoQ(Math.abs(vueltoCentavos))}`;
        };

        metodoSelect.addEventListener('change', () => {
            actualizarVisibilidad();
            actualizarVuelto();
        });
        recibidoInput.addEventListener('input', actualizarVuelto);
        montoInput.addEventListener('input', actualizarVuelto);
        actualizarVisibilidad();

        formPago.addEventListener('submit', () => {
            boton.disabled = true;
        });
    }

    const formCierre = document.getElementById('form-cierre');
    if (formCierre) {
        const contadoInput = document.getElementById('cierre-efectivo-contado');
        const diferenciaTexto = document.getElementById('cierre-diferencia');
        const boton = document.getElementById('cierre-boton');

        const actualizarDiferencia = () => {
            const esperado = parseFloat(contadoInput.dataset.esperado || '0');
            const contado = parseFloat(contadoInput.value || '0');
            const diferencia = Math.round((contado - esperado) * 100) / 100;

            if (diferencia === 0) {
                diferenciaTexto.textContent = 'Cuadra exacto';
            } else if (diferencia > 0) {
                diferenciaTexto.textContent = `Sobran Q ${diferencia.toFixed(2)}`;
            } else {
                diferenciaTexto.textContent = `Faltan Q ${Math.abs(diferencia).toFixed(2)}`;
            }
        };

        contadoInput.addEventListener('input', actualizarDiferencia);
        actualizarDiferencia();

        formCierre.addEventListener('submit', () => {
            boton.disabled = true;
        });
    }
})();
