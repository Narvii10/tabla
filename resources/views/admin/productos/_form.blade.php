{{-- Campos compartidos por crear y editar. Recibe $producto, $categorias y $vinos. --}}
@csrf

<div class="row g-4">
    {{-- Columna izquierda: datos del producto --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Información</h2>

                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
                               class="form-control @error('nombre') is-invalid @enderror" required maxlength="150" autofocus
                               placeholder="Ej. Tabla de embutidos">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                            <option value="">Elegir…</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected((int) old('categoria_id', $producto->categoria_id) === $categoria->id)>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Ingredientes o detalles que verá el cliente">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-6">
                        <label for="precio" class="form-label">Precio de venta</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">Q</span>
                            <input type="number" id="precio" name="precio" value="{{ old('precio', $producto->precio) }}"
                                   class="form-control @error('precio') is-invalid @enderror" required min="0" step="0.01" placeholder="0.00">
                            @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <label for="costo" class="form-label">Costo <span class="text-muted fw-normal">(opcional, no se muestra al cliente)</span></label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">Q</span>
                            <input type="number" id="costo" name="costo" value="{{ old('costo', $producto->costo) }}"
                                   class="form-control @error('costo') is-invalid @enderror" min="0" step="0.01" placeholder="0.00">
                            @error('costo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Vino y maridaje</h2>

                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="es_vino" value="0">
                    <input type="checkbox" id="es_vino" name="es_vino" value="1" class="form-check-input" role="switch"
                           @checked(old('es_vino', $producto->es_vino))>
                    <label for="es_vino" class="form-check-label">Este producto es un vino</label>
                    <div class="form-text">Los vinos se pueden sugerir como acompañamiento de otros platos.</div>
                </div>

                <div id="campo-vino-sugerido">
                    <label for="vino_sugerido_id" class="form-label">Vino sugerido <span class="text-muted fw-normal">(opcional)</span></label>
                    <select id="vino_sugerido_id" name="vino_sugerido_id" class="form-select @error('vino_sugerido_id') is-invalid @enderror">
                        <option value="">Sin vino sugerido</option>
                        @foreach ($vinos as $vino)
                            <option value="{{ $vino->id }}" @selected((int) old('vino_sugerido_id', $producto->vino_sugerido_id) === $vino->id)
                                    data-nombre="{{ $vino->nombre }}" data-precio="{{ $vino->precio }}" @if ($vino->disponible) data-disponible @endif>
                                {{ $vino->nombre }} — Q {{ number_format((float) $vino->precio, 2) }}{{ $vino->disponible ? '' : ' (agotado)' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">El mesero lo verá al tomar el pedido y el cliente en el menú.</div>
                    @error('vino_sugerido_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Columna derecha: vista previa, foto y disponibilidad --}}
    <div class="col-lg-4">
        {{-- Vista previa: así aparece el plato en la hoja del menú público. Se actualiza mientras se escribe. --}}
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-1">Así se verá en el menú</h2>
                <p class="small text-muted mb-4">Se actualiza mientras escribes.</p>

                <div id="vista-previa" class="vista-previa" aria-live="polite">
                    <img id="foto-preview" alt="Vista previa de la foto"
                         @if ($producto->foto) src="{{ Storage::url($producto->foto) }}" @endif
                         class="vista-previa-foto {{ $producto->foto ? '' : 'd-none' }}">
                    <div class="vista-previa-plato">
                        <div class="vista-previa-nombre" id="previa-nombre"></div>
                        <div class="vista-previa-precio" id="previa-precio"></div>
                        <p class="vista-previa-descripcion" id="previa-descripcion"></p>
                        <div class="vista-previa-extra">
                            <span class="vista-previa-sello" id="previa-sello">Agotado</span>
                            <span class="vista-previa-maridaje" id="previa-maridaje"><i class="bi bi-cup-straw" aria-hidden="true"></i><span></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Foto</h2>

                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                       class="form-control @error('foto') is-invalid @enderror" aria-label="Foto">
                <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
                @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Disponibilidad</h2>
                <div class="form-check form-switch">
                    <input type="hidden" name="disponible" value="0">
                    <input type="checkbox" id="disponible" name="disponible" value="1" class="form-check-input" role="switch"
                           @checked(old('disponible', $producto->disponible))>
                    <label for="disponible" class="form-check-label">Disponible para vender</label>
                </div>
                <div class="form-text">Si lo apagas queda <strong>agotado</strong>: no se puede pedir y el menú lo muestra como "Agotado".</div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Guardar producto</button>
            <a href="{{ route('admin.productos.index') }}" class="btn btn-light">Cancelar</a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Un vino no lleva vino sugerido: se oculta ese campo cuando "es un vino" está marcado
        const esVino = document.getElementById('es_vino');
        const campoVino = document.getElementById('campo-vino-sugerido');
        const actualizarCampoVino = () => campoVino.classList.toggle('d-none', esVino.checked);
        esVino.addEventListener('change', actualizarCampoVino);
        actualizarCampoVino();

        // Vista previa de la foto elegida, antes de guardar
        const inputFoto = document.getElementById('foto');
        const previewFoto = document.getElementById('foto-preview');
        inputFoto.addEventListener('change', () => {
            const archivo = inputFoto.files[0];
            if (!archivo) return;
            previewFoto.src = URL.createObjectURL(archivo);
            previewFoto.classList.remove('d-none');
        });

        // Vista previa del plato en el menú: copia lo que se escribe en el formulario
        (() => {
            const campo = (id) => document.getElementById(id);
            const quetzales = (valor) => 'Q ' + Number(valor || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const actualizar = () => {
                const nombre = campo('nombre').value.trim();
                const previaNombre = campo('previa-nombre');
                previaNombre.textContent = nombre || 'Nombre del plato';
                previaNombre.classList.toggle('vacio', !nombre);

                campo('previa-precio').textContent = quetzales(campo('precio').value);

                const descripcion = campo('descripcion').value.trim();
                campo('previa-descripcion').textContent = descripcion;
                campo('previa-descripcion').classList.toggle('d-none', !descripcion);

                const agotado = !campo('disponible').checked;
                campo('vista-previa').classList.toggle('agotado', agotado);
                campo('previa-sello').classList.toggle('d-none', !agotado);

                // El menú solo muestra el maridaje si el vino está disponible y el producto no es vino
                const vino = campo('vino_sugerido_id').selectedOptions[0];
                const mostrarVino = !esVino.checked && vino && vino.value && vino.hasAttribute('data-disponible');
                campo('previa-maridaje').classList.toggle('d-none', !mostrarVino);
                if (mostrarVino) {
                    campo('previa-maridaje').lastElementChild.textContent =
                        `Vino sugerido: ${vino.dataset.nombre} · ${quetzales(vino.dataset.precio)}`;
                }

                campo('vista-previa').classList.toggle('es-vino', esVino.checked);
            };

            ['nombre', 'precio', 'descripcion'].forEach((id) => campo(id).addEventListener('input', actualizar));
            ['disponible', 'es_vino', 'vino_sugerido_id'].forEach((id) => campo(id).addEventListener('change', actualizar));
            actualizar();
        })();
    </script>
@endpush
