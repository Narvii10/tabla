<?php

namespace App\Http\Controllers\Mesero;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mesero\DetallePedidoRequest;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Productos dentro de un pedido: agregar y quitar (solo si aún no se enviaron a cocina).
 */
class DetallePedidoController extends Controller
{
    public function store(DetallePedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        $producto = Producto::with('vinoSugerido')->findOrFail($request->integer('producto_id'));
        $cantidad = $request->integer('cantidad');
        $notas = $request->filled('notas') ? trim($request->input('notas')) : null;

        $agregado = DB::transaction(function () use ($pedido, $producto, $cantidad, $notas) {
            // Se bloquea el pedido para que dos clics a la vez no dupliquen la línea
            $pedido = Pedido::lockForUpdate()->findOrFail($pedido->id);
            if (! $pedido->estaAbierto()) {
                return false;
            }

            // Sin nota se suma a la línea que aún no se envía; con nota va en otra línea para que cocina la vea aparte
            $linea = $notas === null
                ? $pedido->detalles()
                    ->where('producto_id', $producto->id)
                    ->whereNull('comanda_id')
                    ->whereNull('notas')
                    ->first()
                : null;

            if ($linea) {
                $linea->increment('cantidad', $cantidad);
            } else {
                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    // El precio se copia: si el menú cambia después, este pedido no cambia
                    'precio_unitario' => $producto->precio,
                    'notas' => $notas,
                ]);
            }

            return true;
        });

        if (! $agregado) {
            return $this->pedidoCerrado($pedido);
        }

        $respuesta = redirect()->route('mesero.pedidos.show', $pedido);

        // Si el plato tiene vino sugerido (y hay), la pantalla le recuerda al mesero ofrecerlo
        if ($producto->vinoSugerido?->disponible) {
            $respuesta->with('vino_sugerido', [
                'id' => $producto->vinoSugerido->id,
                'nombre' => $producto->vinoSugerido->nombre,
                'plato' => $producto->nombre,
            ]);
        }

        return $respuesta;
    }

    public function destroy(Pedido $pedido, DetallePedido $detalle): RedirectResponse
    {
        if (! $pedido->estaAbierto()) {
            return $this->pedidoCerrado($pedido);
        }

        // Lo que ya salió a cocina no se puede quitar
        if ($detalle->comanda_id !== null) {
            return redirect()->route('mesero.pedidos.show', $pedido)
                ->with('error', 'Ese producto ya se envió a cocina y no se puede quitar.');
        }

        $detalle->delete();

        return redirect()->route('mesero.pedidos.show', $pedido);
    }

    private function pedidoCerrado(Pedido $pedido): RedirectResponse
    {
        return redirect()->route('mesero.pedidos.show', $pedido)
            ->with('error', 'Este pedido ya está cerrado y no se puede modificar.');
    }
}
