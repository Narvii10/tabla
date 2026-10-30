// Caja: cobro, propina, división y cierre. Los montos se manejan en centavos para no tener errores de redondeo.
(() => {
    const dividir = (centavos, personas) => {
        if (personas < 1) return [centavos];
        const base = Math.floor(centavos / personas);
        const resto = centavos % personas;
        return Array.from({ length: personas }, (_, i) => base + (i < resto ? 1 : 0));
    };

    const formatoQ = (centavos) => `Q ${(centavos / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const aCentavos = (texto) => Math.round(parseFloat(texto || '0') * 100) || 0;
    const aTexto = (centavos) => (centavos / 100).toFixed(2);

    // Propina
    const formPropina = document.getElementById('form-propina');
    if (formPropina) {
        const input = document.getElementById('propina-input');
        const grupoManual = document.getElementById('propina-manual-grupo');
        formPropina.querySelectorAll('.opcion-propina').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (radio.value === 'manual') {
                    grupoManual.hidden = false;
                    input.focus();
                    input.select();
                } else {
                    grupoManual.hidden = true;
                    input.value = radio.value;
                    formPropina.submit();
                }
            });
        });
    }

    const formPago = document.getElementById('form-pago');
    const montoInput = document.getElementById('pago-monto');

    // Dividir la cuenta
    const contenedor = document.getElementById('caja-pedido');
    const personasInput = document.getElementById('personas');
    const listaDivision = document.getElementById('lista-division');
    if (contenedor && personasInput && listaDivision) {
        const saldoCentavos = parseInt(contenedor.dataset.saldoCentavos, 10);

        const dibujarDivision = () => {
            const personas = Math.min(Math.max(parseInt(personasInput.value, 10) || 1, 1), 20);
            listaDivision.replaceChildren(...dividir(saldoCentavos, personas).map((centavos, i) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';
                const nombre = document.createElement('span');
                nombre.className = 'flex-grow-1';
                nombre.textContent = `Persona ${i + 1}`;
                const monto = document.createElement('strong');
                monto.textContent = formatoQ(centavos);
                li.append(nombre, monto);

                if (formPago) {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn btn-sm btn-outline-primary';
                    boton.textContent = 'Cobrar';
                    boton.addEventListener('click', () => {
                        montoInput.value = aTexto(centavos);
                        montoInput.dispatchEvent(new Event('input'));
                        document.getElementById('cobro').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                    li.append(boton);
                }
                return li;
            }));
        };

        const cambiarPersonas = (cuanto) => {
            personasInput.value = Math.min(Math.max((parseInt(personasInput.value, 10) || 1) + cuanto, 1), 20);
            dibujarDivision();
        };
        document.getElementById('personas-menos').addEventListener('click', () => cambiarPersonas(-1));
        document.getElementById('personas-mas').addEventListener('click', () => cambiarPersonas(1));
        personasInput.addEventListener('input', dibujarDivision);
        dibujarDivision();
    }

    // Cobro y vuelto
    if (formPago) {
        const efectivoGrupo = document.getElementById('pago-efectivo');
        const recibidoInput = document.getElementById('pago-recibido');
        const billetes = document.getElementById('pago-billetes');
        const vuelto = document.getElementById('pago-vuelto');
        const vueltoTitulo = document.getElementById('pago-vuelto-titulo');
        const vueltoMonto = document.getElementById('pago-vuelto-monto');
        const boton = document.getElementById('pago-boton');
        const botonMonto = document.getElementById('pago-boton-monto');

        const metodo = () => formPago.querySelector('input[name="metodo"]:checked')?.value;

        // Botones de monto rápido
        const dibujarBilletes = () => {
            const monto = aCentavos(montoInput.value);
            const opciones = [monto];
            [1000, 5000, 10000, 20000].forEach((billete) => {
                const redondo = Math.ceil(monto / billete) * billete;
                if (redondo > monto && !opciones.includes(redondo)) opciones.push(redondo);
            });

            billetes.replaceChildren(...opciones.slice(0, 4).map((centavos, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn btn-light billete';
                b.textContent = i === 0 ? 'Exacto' : formatoQ(centavos);
                b.addEventListener('click', () => {
                    recibidoInput.value = aTexto(centavos);
                    actualizarVuelto();
                });
                return b;
            }));
        };

        const actualizarVuelto = () => {
            const monto = aCentavos(montoInput.value);
            botonMonto.textContent = formatoQ(monto);

            if (metodo() !== 'efectivo') {
                boton.disabled = false;
                return;
            }

            if (recibidoInput.value === '') {
                vuelto.dataset.estado = 'vacio';
                vueltoTitulo.textContent = 'Vuelto';
                vueltoMonto.textContent = '—';
                boton.disabled = false;
                return;
            }

            const diferencia = aCentavos(recibidoInput.value) - monto;
            if (diferencia < 0) {
                vuelto.dataset.estado = 'falta';
                vueltoTitulo.textContent = 'Falta dinero';
                vueltoMonto.textContent = formatoQ(-diferencia);
                boton.disabled = true;
            } else {
                vuelto.dataset.estado = diferencia === 0 ? 'exacto' : 'ok';
                vueltoTitulo.textContent = diferencia === 0 ? 'Pago exacto, sin vuelto' : 'Vuelto a entregar';
                vueltoMonto.textContent = formatoQ(diferencia);
                boton.disabled = false;
            }
        };

        const actualizarMetodo = () => {
            const esEfectivo = metodo() === 'efectivo';
            efectivoGrupo.hidden = !esEfectivo;
            recibidoInput.required = esEfectivo;
            if (!esEfectivo) recibidoInput.value = '';
            actualizarVuelto();
        };

        formPago.querySelectorAll('input[name="metodo"]').forEach((radio) => radio.addEventListener('change', actualizarMetodo));
        recibidoInput.addEventListener('input', actualizarVuelto);
        montoInput.addEventListener('input', () => {
            dibujarBilletes();
            actualizarVuelto();
        });

        dibujarBilletes();
        actualizarMetodo();

        formPago.addEventListener('submit', () => {
            boton.disabled = true;
        });
    }

    // Cierre del día
    const formCierre = document.getElementById('form-cierre');
    if (formCierre) {
        const contadoInput = document.getElementById('cierre-efectivo-contado');
        const caja = document.getElementById('cierre-diferencia');
        const titulo = document.getElementById('cierre-diferencia-titulo');
        const monto = document.getElementById('cierre-diferencia-monto');
        const boton = document.getElementById('cierre-boton');

        const actualizarDiferencia = () => {
            if (contadoInput.value === '') {
                caja.dataset.estado = 'vacio';
                titulo.textContent = 'Diferencia';
                monto.textContent = '—';
                return;
            }

            const diferencia = aCentavos(contadoInput.value) - aCentavos(contadoInput.dataset.esperado);
            if (diferencia === 0) {
                caja.dataset.estado = 'exacto';
                titulo.textContent = 'Cuadra exacto';
            } else if (diferencia > 0) {
                caja.dataset.estado = 'ok';
                titulo.textContent = 'Sobra dinero';
            } else {
                caja.dataset.estado = 'falta';
                titulo.textContent = 'Falta dinero';
            }
            monto.textContent = formatoQ(Math.abs(diferencia));
        };

        contadoInput.addEventListener('input', actualizarDiferencia);
        actualizarDiferencia();

        formCierre.addEventListener('submit', () => {
            boton.disabled = true;
        });
    }
})();
