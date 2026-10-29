<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MesaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * CRUD de mesas (área de administración), más el botón para cambiar el QR de una mesa.
 */
class MesaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = $request->string('buscar')->trim()->toString();
        $zona = $request->string('zona')->toString();

        $mesas = Mesa::query()
            // Si escriben un número se busca esa mesa; si escriben texto, la zona
            ->when(ctype_digit($buscar), fn ($query) => $query->where('numero', (int) $buscar))
            ->when($buscar !== '' && ! ctype_digit($buscar), fn ($query) => $query->where('zona', 'like', "%{$buscar}%"))
            ->when($zona !== '', fn ($query) => $query->where('zona', $zona))
            ->orderBy('numero')
            ->paginate(15)
            ->withQueryString();

        // Zonas que ya existen, para el filtro y para sugerirlas en el formulario
        $zonas = $this->zonas();

        $resumen = [
            'total' => Mesa::count(),
            'libres' => Mesa::where('estado', 'libre')->count(),
            'puestos' => (int) Mesa::sum('capacidad'),
        ];

        return view('admin.mesas.index', compact('mesas', 'buscar', 'zona', 'zonas', 'resumen'));
    }

    public function create(): View
    {
        // Sugerimos el siguiente número libre
        $mesa = new Mesa([
            'numero' => (Mesa::max('numero') ?? 0) + 1,
            'capacidad' => 4,
        ]);

        return view('admin.mesas.create', ['mesa' => $mesa, 'zonas' => $this->zonas()]);
    }

    public function store(MesaRequest $request): RedirectResponse
    {
        // El token del QR lo genera el modelo Mesa al crearla
        $mesa = Mesa::create($request->validated());

        return redirect()->route('admin.mesas.index')
            ->with('success', "Mesa {$mesa->numero} creada. Ya puedes imprimir su QR.");
    }

    public function edit(Mesa $mesa): View
    {
        return view('admin.mesas.edit', ['mesa' => $mesa, 'zonas' => $this->zonas()]);
    }

    public function update(MesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $mesa->update($request->validated());

        return redirect()->route('admin.mesas.index')
            ->with('success', "Mesa {$mesa->numero} actualizada.");
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        // Si ya tuvo pedidos se conserva, para no romper el historial de ventas
        if ($mesa->pedidos()->exists()) {
            return back()->with('error', "No se puede eliminar la mesa {$mesa->numero} porque ya tiene pedidos registrados.");
        }

        $mesa->delete();

        return redirect()->route('admin.mesas.index')
            ->with('success', "Mesa {$mesa->numero} eliminada.");
    }

    /**
     * Página imprimible con el QR de cada mesa. Se puede filtrar por zona
     * o imprimir una sola mesa (?mesa=ID). El QR lo dibuja el navegador.
     */
    public function imprimir(Request $request): View
    {
        $zona = $request->string('zona')->toString();
        $mesaId = $request->integer('mesa') ?: null;

        $mesas = Mesa::query()
            ->when($mesaId, fn ($query) => $query->whereKey($mesaId))
            ->when(! $mesaId && $zona !== '', fn ($query) => $query->where('zona', $zona))
            ->orderBy('numero')
            ->get();

        return view('admin.mesas.imprimir', [
            'mesas' => $mesas,
            'zona' => $zona,
            'mesaId' => $mesaId,
            'zonas' => $this->zonas(),
        ]);
    }

    /**
     * Cambia el token del QR. El QR impreso anterior deja de funcionar (da 404),
     * útil si alguien se llevó una foto del QR o se imprimió mal.
     */
    public function regenerarQr(Mesa $mesa): RedirectResponse
    {
        $mesa->update(['token_qr' => Mesa::generarTokenQr()]);

        return back()->with('success', "La mesa {$mesa->numero} tiene un QR nuevo. Imprímelo de nuevo; el anterior ya no funciona.");
    }

    /**
     * Zonas distintas ya usadas en las mesas (Salón, Terraza…).
     */
    private function zonas(): Collection
    {
        return Mesa::whereNotNull('zona')->distinct()->orderBy('zona')->pluck('zona');
    }
}
