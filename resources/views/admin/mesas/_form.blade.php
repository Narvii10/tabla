{{-- Campos compartidos por crear y editar. Recibe $mesa y $zonas. --}}
@csrf

<div class="card">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6">
                <label for="numero" class="form-label">Número de mesa</label>
                <input type="number" id="numero" name="numero" value="{{ old('numero', $mesa->numero) }}"
                       class="form-control @error('numero') is-invalid @enderror" required min="1" max="999" step="1"
                       inputmode="numeric" autofocus>
                <div class="form-text">Es el número que verá el cliente en su menú.</div>
                @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-sm-6">
                <label for="capacidad" class="form-label">Capacidad</label>
                <input type="number" id="capacidad" name="capacidad" value="{{ old('capacidad', $mesa->capacidad) }}"
                       class="form-control @error('capacidad') is-invalid @enderror" required min="1" max="30" step="1"
                       inputmode="numeric">
                <div class="form-text">Cuántas personas caben normalmente. Es de referencia: no impide poner sillas extra.</div>
                @error('capacidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="zona" class="form-label">Zona <span class="text-muted fw-normal">(opcional)</span></label>
                {{-- La lista sugiere las zonas que ya existen, pero se puede escribir una nueva --}}
                <input type="text" id="zona" name="zona" value="{{ old('zona', $mesa->zona) }}" list="zonas-existentes"
                       class="form-control @error('zona') is-invalid @enderror" maxlength="40" placeholder="Ej. Salón o Terraza">
                <datalist id="zonas-existentes">
                    @foreach ($zonas as $opcion)
                        <option value="{{ $opcion }}"></option>
                    @endforeach
                </datalist>
                @error('zona') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar mesa</button>
            <a href="{{ route('admin.mesas.index') }}" class="btn btn-light">Cancelar</a>
        </div>
    </div>
</div>
