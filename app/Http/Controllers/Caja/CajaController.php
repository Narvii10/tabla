<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Http\Requests\Caja\PropinaRequest;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::where('estado', 'abierto')
            ->with(['mesa', 'detalles.producto', 'pagos'])
            ->get()
            ->sortBy(fn (Pedido $pedido) => [$pedido->mesa->estado === 'cuenta' ? 0 : 1, $pedido->abierto_en])
            ->values();

        return view('caja.index', compact('pedidos'));
    }

    public function show(Pedido $pedido): View|RedirectResponse
    {
        if (! $pedido->estaAbierto()) {
            return redirect()->route('caja.index')->with('error', 'Ese pedido ya está cerrado.');
        }

        $pedido->load(['mesa', 'mesero', 'detalles.producto', 'pagos.cajero']);

        $productos = $pedido->detalles
            ->groupBy(fn (DetallePedido $detalle) => $detalle->producto_id.'-'.$detalle->precio_unitario)
            ->map(function ($detalles) {
                $primero = $detalles->first();
                $cantidad = $detalles->sum('cantidad');

                return [
                    'nombre' => $primero->producto->nombre,
                    'precio_unitario' => (float) $primero->precio_unitario,
                    'cantidad' => $cantidad,
                    'total' => round($cantidad * (float) $primero->precio_unitario, 2),
                ];
            })
            ->values();

        $subtotal = $pedido->subtotal();
        $saldoCentavos = (int) round($pedido->saldo() * 100);

        return view('caja.show', [
            'pedido' => $pedido,
            'productos' => $productos,
            'subtotal' => $subtotal,
            'propinaSugerida' => round($subtotal * 0.10, 2),
            'division' => self::dividir($saldoCentavos, 2),
        ]);
    }

    public function actualizarPropina(PropinaRequest $request, Pedido $pedido): RedirectResponse
    {
        $pedido->update(['propina' => $request->validated('propina')]);

        return redirect()->route('caja.pedidos.show', $pedido)->with('success', 'Propina actualizada.');
    }

    public static function dividir(int $centavos, int $personas): array
    {
        if ($personas < 1) {
            return [$centavos];
        }

        $base = intdiv($centavos, $personas);
        $resto = $centavos % $personas;

        return array_map(
            fn (int $i) => $base + ($i < $resto ? 1 : 0),
            range(0, $personas - 1)
        );
    }
}
