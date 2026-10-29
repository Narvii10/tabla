<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * CRUD de productos del menú (área de administración),
 * más el botón para marcar o desmarcar agotado.
 */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = $request->string('buscar')->trim()->toString();
        $categoriaId = $request->integer('categoria_id') ?: null;
        // '' = todos, 'disponibles' o 'agotados'
        $estado = $request->string('estado')->toString();

        $productos = Producto::with(['categoria', 'vinoSugerido'])
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->when($categoriaId, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->when($estado === 'disponibles', fn ($query) => $query->where('disponible', true))
            ->when($estado === 'agotados', fn ($query) => $query->where('disponible', false))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $categorias = Categoria::ordenadas()->get();

        // Números para las tarjetas de arriba (de todo el menú, sin filtros)
        $resumen = [
            'total' => Producto::count(),
            'agotados' => Producto::where('disponible', false)->count(),
            'vinos' => Producto::vinos()->count(),
        ];

        return view('admin.productos.index', compact('productos', 'categorias', 'buscar', 'categoriaId', 'estado', 'resumen'));
    }

    public function create(): View
    {
        return view('admin.productos.create', $this->datosFormulario(new Producto(['disponible' => true])));
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['foto'] = $request->hasFile('foto')
            ? $request->file('foto')->store('productos', 'public')
            : null;

        $producto = Producto::create($datos);

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto «{$producto->nombre}» creado.");
    }

    public function edit(Producto $producto): View
    {
        return view('admin.productos.edit', $this->datosFormulario($producto));
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $datos = $request->validated();
        unset($datos['foto']);

        // Si suben una foto nueva, se borra la anterior
        if ($request->hasFile('foto')) {
            $this->borrarFoto($producto);
            $datos['foto'] = $request->file('foto')->store('productos', 'public');
        }

        $producto->update($datos);

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto «{$producto->nombre}» actualizado.");
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        // Si ya se vendió, se conserva para no romper los pedidos anteriores
        if ($producto->detallesPedido()->exists()) {
            return back()->with('error', "No se puede eliminar «{$producto->nombre}» porque ya está en pedidos. Márcalo como agotado.");
        }

        $this->borrarFoto($producto);
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto «{$producto->nombre}» eliminado.");
    }

    /**
     * Botón agotado: cambia disponible de true a false o al revés.
     */
    public function alternarDisponible(Producto $producto): RedirectResponse
    {
        $producto->update(['disponible' => ! $producto->disponible]);

        $mensaje = $producto->disponible
            ? "«{$producto->nombre}» está disponible de nuevo."
            : "«{$producto->nombre}» marcado como agotado.";

        return back()->with('success', $mensaje);
    }

    /**
     * Lo que necesitan los formularios de crear y editar.
     */
    private function datosFormulario(Producto $producto): array
    {
        return [
            'producto' => $producto,
            'categorias' => Categoria::ordenadas()->get(),
            // Vinos para el maridaje (sin el propio producto)
            'vinos' => Producto::vinos()
                ->when($producto->exists, fn ($query) => $query->whereKeyNot($producto->id))
                ->orderBy('nombre')
                ->get(),
        ];
    }

    private function borrarFoto(Producto $producto): void
    {
        if ($producto->foto) {
            Storage::disk('public')->delete($producto->foto);
        }
    }
}
