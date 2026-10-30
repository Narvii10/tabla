<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Http\Requests\Caja\PagoRequest;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function store(PagoRequest $request, Pedido $pedido): RedirectResponse
    {
        $error = DB::transaction(function () use ($request, $pedido) {
            $bloqueado = Pedido::with(['mesa', 'pagos'])->lockForUpdate()->findOrFail($pedido->id);

            if (! $bloqueado->estaAbierto()) {
                return 'Este pedido ya está cerrado.';
            }

            $monto = round((float) $request->validated('monto'), 2);
            if ($monto > $bloqueado->saldo()) {
                return 'No puedes cobrar más de lo que falta.';
            }

            $metodo = $request->validated('metodo');
            $recibido = null;
            $vuelto = null;
            if ($metodo === 'efectivo') {
                $recibido = round((float) $request->validated('recibido'), 2);
                $vuelto = round($recibido - $monto, 2);
            }

            $bloqueado->pagos()->create([
                'cajero_id' => auth()->id(),
                'metodo' => $metodo,
                'monto' => $monto,
                'recibido' => $recibido,
                'vuelto' => $vuelto,
            ]);

            $bloqueado->load('pagos');
            if ($bloqueado->saldo() <= 0) {
                $bloqueado->update(['estado' => 'cerrado', 'cerrado_en' => now()]);
                $bloqueado->mesa->update(['estado' => 'libre']);
            }

            return null;
        });

        if ($error) {
            return redirect()->route('caja.pedidos.show', $pedido)->with('error', $error);
        }

        if ($pedido->fresh()->estaAbierto()) {
            return redirect()->route('caja.pedidos.show', $pedido)->with('success', 'Pago registrado.');
        }

        return redirect()->route('caja.index')
            ->with('success', 'Pago registrado. El pedido quedó cerrado y la mesa está libre.');
    }
}
