{{-- Campos compartidos por crear y editar. Recibe $categoria. --}}
@csrf

<div class="card" style="max-width: 40rem;">
    <div class="card-body p-4">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}"
                   class="form-control @error('nombre') is-invalid @enderror" required maxlength="100" autofocus
                   placeholder="Ej. Tablas">
            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="orden" class="form-label">Orden en el menú</label>
            <input type="number" id="orden" name="orden" value="{{ old('orden', $categoria->orden) }}"
                   class="form-control @error('orden') is-invalid @enderror" required min="0" max="1000" step="1" style="max-width: 10rem;">
            <div class="form-text">Las categorías con número menor aparecen primero.</div>
            @error('orden') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-check form-switch mb-4">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" id="activo" name="activo" value="1" class="form-check-input" role="switch"
                   @checked(old('activo', $categoria->activo))>
            <label for="activo" class="form-check-label">Visible en el menú público</label>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar</button>
            <a href="{{ route('admin.categorias.index') }}" class="btn btn-light">Cancelar</a>
        </div>
    </div>
</div>
