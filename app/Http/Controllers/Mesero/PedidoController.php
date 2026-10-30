<?php

namespace App\Http\Controllers\Mesero;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pedidos del mesero: abrir pedido, enviar rondas a cocina, entregar comandas y pedir la cuenta.
 * Cada acción actualiza también el estado de la mesa.
 */
class PedidoController extends Controller
{
    /**
     * Pedidos abiertos: todas las mesas que se están atendiendo.
     */
    public function index(): View
    {
        $pedidos = Pedido::where('estado', 'abierto')
            ->with(['mesa', 'mesero', 'detalles'])
            ->withCount(['comandas as comandas_listas' => fn ($query) => $query->where('estado', 'listo')])
            ->orderBy('abierto_en')
            ->get();

        return view('mesero.pedidos.index', compact('pedidos'));
    }

    /**
     * Clic en una mesa del mapa: va a su pedido abierto o propone abrir uno.
     */
    public function mesa(Mesa $mesa): View|RedirectResponse
    {
        $pedido = $mesa->pedidoAbierto;
        if ($pedido) {
            return redirect()->route('mesero.pedidos.show', $pedido);
        }

        // Una mesa que no está libre pero no tiene pedido abierto es un dato raro: no se deja abrir
        if ($mesa->estado !== 'libre') {
            return redirect()->route('mesero.index')
                ->with('error', "La mesa {$mesa->numero} no está libre. Pídele al administrador que la revise.");
        }

        return view('mesero.pedidos.abrir', compact('mesa'));
    }

    /**
     * Abre el pedido de una mesa libre y la pasa a ocupada.
     */
    public function abrir(Mesa $mesa): RedirectResponse
    {
        $pedido = DB::transaction(function () use ($mesa) {
            // Se bloquea la mesa un instante: si dos meseros tocan "abrir" a la vez, el segundo espera y ya encuentra el pedido
            $mesa = Mesa::lockForUpdate()->findOrFail($mesa->id);

            $existente = $mesa->pedidos()->where('estado', 'abierto')->first();
            if ($existente) {
                return $existente;
            }
            if ($mesa->estado !== 'libre') {
                return null;
            }

            $pedido = $mesa->pedidos()->create([
                'mesero_id' => auth()->id(),
                'estado' => 'abierto',
                'abierto_en' => now(),
            ]);
            $mesa->update(['estado' => 'ocupada']);

            return $pedido;
        });

        if (! $pedido) {
            return redirect()->route('mesero.index')->with('error', "La mesa {$mesa->numero} no está libre.");
        }

        return redirect()->route('mesero.pedidos.show', $pedido);
    }

    /**
     * Pantalla del pedido: catálogo para agregar, lo que falta enviar, las rondas y el total.
     */
    public function show(Pedido $pedido): View
    {
        $pedido->load(['mesa', 'mesero', 'detalles.producto', 'comandas.detalles.producto']);

        $categorias = Categoria::activas()->ordenadas()
            ->with(['productos' => fn ($query) => $query->with('vinoSugerido')->orderBy('nombre')])
            ->get()
            ->filter(fn (Categoria $categoria) => $categoria->productos->isNotEmpty());

        return view('mesero.pedidos.show', [
            'pedido' => $pedido,
            'categorias' => $categorias,
            // Productos agregados que todavía no salieron a cocina
            'porEnviar' => $pedido->detalles->whereNull('comanda_id'),
            'comandas' => $pedido->comandas->sortBy('id')->values(),
        ]);
    }

    /**
     * Crea una comanda (una ronda) con lo que aún no se había enviado. La mesa pasa a esperando.
     */
    public function enviarACocina(Pedido $pedido): RedirectResponse
    {
        $error = DB::transaction(function () use ($pedido) {
            $pedido = Pedido::with('mesa')->lockForUpdate()->findOrFail($pedido->id);
            if (! $pedido->estaAbierto()) {
                return 'Este pedido ya está cerrado.';
            }

            $pendientes = $pedido->detalles()->whereNull('comanda_id')->pluck('id');
            if ($pendientes->isEmpty()) {
                return 'No hay productos nuevos para enviar a cocina.';
            }

            $comanda = $pedido->comandas()->create(['enviada_en' => now(), 'estado' => 'pendiente']);
            $pedido->detalles()->whereIn('id', $pendientes)->update(['comanda_id' => $comanda->id]);

            // Si ya pidió la cuenta, la mesa se queda en "cuenta"
            if (in_array($pedido->mesa->estado, ['ocupada', 'comiendo'], true)) {
                $pedido->mesa->update(['estado' => 'esperando']);
            }

            return null;
        });

        if ($error) {
            return redirect()->route('mesero.pedidos.show', $pedido)->with('error', $error);
        }

        return redirect()->route('mesero.pedidos.show', $pedido)->with('success', 'Ronda enviada a cocina.');
    }

    /**
     * El mesero llevó a la mesa una comanda que cocina dejó lista.
     * Cuando ya no queda ninguna sin entregar, la mesa pasa a comiendo.
     */
    public function marcarEntregada(Comanda $comanda): RedirectResponse
    {
        $pedido = $comanda->pedido;

        $error = DB::transaction(function () use ($comanda, $pedido) {
            $comanda = Comanda::lockForUpdate()->findOrFail($comanda->id);
            $pedido = Pedido::with('mesa')->findOrFail($pedido->id);

            if (! $pedido->estaAbierto()) {
                return 'Este pedido ya está cerrado.';
            }
            if ($comanda->estado !== 'listo') {
                return 'Cocina todavía no marca esa comanda como lista.';
            }

            $comanda->update(['estado' => 'entregado']);

            $faltan = $pedido->comandas()->where('estado', '!=', 'entregado')->exists();
            if (! $faltan && $pedido->mesa->estado === 'esperando') {
                $pedido->mesa->update(['estado' => 'comiendo']);
            }

            return null;
        });

        if ($error) {
            return redirect()->route('mesero.pedidos.show', $pedido)->with('error', $error);
        }

        return redirect()->route('mesero.pedidos.show', $pedido)->with('success', 'Comanda entregada en la mesa.');
    }

    /**
     * La mesa pidió la cuenta: pasa a "cuenta" y caja la ve resaltada.
     */
    public function pedirCuenta(Pedido $pedido): RedirectResponse
    {
        if (! $pedido->estaAbierto()) {
            return redirect()->route('mesero.pedidos.show', $pedido)->with('error', 'Este pedido ya está cerrado.');
        }

        if ($pedido->detalles()->doesntExist()) {
            return redirect()->route('mesero.pedidos.show', $pedido)
                ->with('error', 'El pedido no tiene productos todavía.');
        }

        // No se pide la cuenta con productos que cocina no ha recibido
        if ($pedido->detalles()->whereNull('comanda_id')->exists()) {
            return redirect()->route('mesero.pedidos.show', $pedido)
                ->with('error', 'Hay productos sin enviar a cocina. Envíalos o quítalos antes de pedir la cuenta.');
        }

        $pedido->mesa->update(['estado' => 'cuenta']);

        return redirect()->route('mesero.pedidos.show', $pedido)
            ->with('success', "Listo: caja ya ve la cuenta de la mesa {$pedido->mesa->numero}.");
    }

    /**
     * Cancela un pedido vacío (mesa abierta por error) y libera la mesa.
     */
    public function cancelar(Pedido $pedido): RedirectResponse
    {
        if (! $pedido->estaAbierto()) {
            return redirect()->route('mesero.pedidos.show', $pedido)->with('error', 'Este pedido ya está cerrado.');
        }

        if ($pedido->detalles()->exists()) {
            return redirect()->route('mesero.pedidos.show', $pedido)
                ->with('error', 'Solo se puede cancelar un pedido sin productos.');
        }

        $pedido->update(['estado' => 'cancelado', 'cerrado_en' => now()]);
        $pedido->mesa->update(['estado' => 'libre']);

        return redirect()->route('mesero.index')
            ->with('success', "Pedido cancelado. La mesa {$pedido->mesa->numero} está libre.");
    }
}
