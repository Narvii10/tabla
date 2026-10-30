<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Http\Requests\Caja\CierreCajaRequest;
use App\Models\CierreCaja;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CierreCajaController extends Controller
{
    public function create(): View
    {
        return view('caja.cierre', [
            'cierre' => CierreCaja::deHoy(),
            'esperado' => $this->esperadoDeHoy(today()),
            'pendientes' => Pedido::where('estado', 'abierto')->count(),
        ]);
    }

    public function store(CierreCajaRequest $request): RedirectResponse
    {
        $hoy = today();

        if (CierreCaja::deHoy()) {
            return redirect()->route('caja.cierre.create')->with('error', 'Ya existe un cierre de caja para hoy.');
        }

        $esperado = $this->esperadoDeHoy($hoy);
        $efectivoContado = round((float) $request->validated('efectivo_contado'), 2);

        CierreCaja::create([
            'cajero_id' => auth()->id(),
            'fecha' => $hoy,
            'efectivo_esperado' => $esperado['efectivo'],
            'tarjeta_esperado' => $esperado['tarjeta'],
            'transferencia_esperado' => $esperado['transferencia'],
            'efectivo_contado' => $efectivoContado,
            'diferencia' => round($efectivoContado - $esperado['efectivo'], 2),
            'notas' => $request->validated('notas'),
        ]);

        return redirect()->route('caja.cierre.create')->with('success', 'Cierre de caja guardado.');
    }

    private function esperadoDeHoy($hoy): array
    {
        $pagos = Pago::whereDate('created_at', $hoy)
            ->selectRaw('metodo, sum(monto) as total')
            ->groupBy('metodo')
            ->pluck('total', 'metodo');

        return [
            'efectivo' => round((float) ($pagos['efectivo'] ?? 0), 2),
            'tarjeta' => round((float) ($pagos['tarjeta'] ?? 0), 2),
            'transferencia' => round((float) ($pagos['transferencia'] ?? 0), 2),
        ];
    }
}
