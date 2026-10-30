<?php

namespace App\Http\Controllers\Cocina;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cocina\CambiarEstadoComandaRequest;
use App\Models\Comanda;
use App\Models\DetallePedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Pantalla de cocina: comandas pendientes, en preparación y listas, de la más vieja a la más nueva.
 * La vista consulta datos() cada 5 segundos (public/js/cocina.js) y no se recarga.
 */
class CocinaController extends Controller
{
    public function index(): View
    {
        // Lista para el botón "Se acabó…": solo productos que todavía se venden
        $productos = Producto::disponibles()->orderBy('nombre')->get(['id', 'nombre']);

        return view('cocina.index', compact('productos'));
    }

    /**
     * Comandas activas en JSON (ruta cocina.comandas.datos).
     */
    public function datos(): JsonResponse
    {
        $comandas = Comanda::whereIn('estado', ['pendiente', 'preparando', 'listo'])
            // Solo de pedidos abiertos: si caja ya cobró, la comanda no se queda colgada
            ->whereHas('pedido', fn ($query) => $query->where('estado', 'abierto'))
            ->with(['pedido.mesa', 'detalles.producto'])
            ->orderBy('enviada_en')
            ->get();

        return response()->json([
            'comandas' => $comandas->map(fn (Comanda $comanda) => $this->comandaParaPantalla($comanda))->values(),
            'resumen' => $this->resumenPendiente($comandas),
            'actualizado' => now()->format('H:i:s'),
        ]);
    }

    public function cambiarEstado(CambiarEstadoComandaRequest $request, Comanda $comanda): JsonResponse
    {
        $comanda->update(['estado' => $request->validated('estado')]);

        return response()->json(['ok' => true, 'estado' => $comanda->estado]);
    }

    /**
     * Cocina avisa que se acabó un producto: queda agotado en el menú y en pedidos.
     */
    public function marcarAgotado(Producto $producto): JsonResponse
    {
        $producto->update(['disponible' => false]);

        return response()->json(['ok' => true, 'mensaje' => "«{$producto->nombre}» quedó agotado en el menú."]);
    }

    private function comandaParaPantalla(Comanda $comanda): array
    {
        $minutos = (int) $comanda->enviada_en->diffInMinutes(now());

        return [
            'id' => $comanda->id,
            'mesa' => $comanda->pedido->mesa->numero,
            'zona' => $comanda->pedido->mesa->zona,
            'estado' => $comanda->estado,
            'hora' => $comanda->enviada_en->format('H:i'),
            'minutos' => $minutos,
            // Verde menos de 10 minutos, amarillo de 10 a 20, rojo más de 20
            'espera' => match (true) {
                $minutos < 10 => 'verde',
                $minutos <= 20 => 'amarillo',
                default => 'rojo',
            },
            'productos' => $comanda->detalles->map(fn (DetallePedido $detalle) => [
                'cantidad' => $detalle->cantidad,
                'nombre' => $detalle->producto->nombre,
                'notas' => $detalle->notas,
            ])->values(),
        ];
    }

    /**
     * Cuánto hay que preparar de cada producto, sumando todas las comandas pendientes y en preparación.
     * Ej.: [{nombre: "Panini caprese", cantidad: 4}, ...]
     */
    private function resumenPendiente($comandas): array
    {
        return $comandas->whereIn('estado', ['pendiente', 'preparando'])
            ->flatMap->detalles
            ->groupBy(fn (DetallePedido $detalle) => $detalle->producto->nombre)
            ->map(fn ($detalles, $nombre) => ['nombre' => $nombre, 'cantidad' => $detalles->sum('cantidad')])
            ->sortByDesc('cantidad')
            ->values()
            ->all();
    }
}
