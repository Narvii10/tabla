<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD de categorías del menú (área de administración).
 */
class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = $request->string('buscar')->trim()->toString();

        $categorias = Categoria::withCount('productos')
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->ordenadas()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categorias.index', compact('categorias', 'buscar'));
    }

    public function create(): View
    {
        // Sugerimos el siguiente número de orden
        $categoria = new Categoria([
            'orden' => (Categoria::max('orden') ?? 0) + 1,
            'activo' => true,
        ]);

        return view('admin.categorias.create', compact('categoria'));
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $categoria = Categoria::create($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('success', "Categoría «{$categoria->nombre}» creada.");
    }

    public function edit(Categoria $categoria): View
    {
        return view('admin.categorias.edit', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('success', "Categoría «{$categoria->nombre}» actualizada.");
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', "No se puede eliminar «{$categoria->nombre}» porque tiene productos. Muévelos a otra categoría o desactívala.");
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', "Categoría «{$categoria->nombre}» eliminada.");
    }
}
