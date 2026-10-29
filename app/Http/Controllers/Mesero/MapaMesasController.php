<?php

namespace App\Http\Controllers\Mesero;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Mapa de mesas del mesero: cuadrícula con el estado de cada mesa y aviso
 * cuando cocina tiene una comanda lista. El JavaScript de la vista consulta
 * datos() cada 5 segundos para actualizarlo sin recargar la página.
 */
class MapaMesasController extends Controller
{
    public function index(): View
    {
        return view('mesero.mesas.index', $this->datosMapa());
    }

    /**
     * Lo mismo que muestra la página, en JSON (ruta mesero.mesas.datos).
     */
    public function datos(): JsonResponse
    {
        return response()->json($this->datosMapa());
    }

    /**
     * Mesas agrupadas por zona, con su pedido abierto y cuántas comandas están listas para llevar.
     */
    private function datosMapa(): array
    {
        $mesas = Mesa::query()
            // Una sola consulta para los pedidos abiertos y otra para contar sus comandas listas (sin N+1)
            ->with(['pedidoAbierto' => fn ($query) => $query->withCount([
                'comandas as comandas_listas' => fn ($query) => $query->where('estado', 'listo'),
            ])])
            ->orderBy('numero')
            ->get()
            ->map(fn (Mesa $mesa) => [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'zona' => $mesa->zona ?? 'Sin zona',
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'estado_texto' => Mesa::ESTADOS[$mesa->estado] ?? $mesa->estado,
                'comandas_listas' => (int) ($mesa->pedidoAbierto?->comandas_listas ?? 0),
                // Hace cuánto se abrió el pedido ("Hace 25 min"); vacío si la mesa está libre
                'tiempo' => $this->tiempoAbierto($mesa),
                'url' => $this->urlPedido($mesa),
            ]);

        return [
            'zonas' => $mesas->groupBy('zona')->map->values(),
            'resumen' => collect(Mesa::ESTADOS)
                ->map(fn ($texto, $estado) => ['texto' => $texto, 'total' => $mesas->where('estado', $estado)->count()]),
            'listas' => $mesas->sum('comandas_listas'),
            'actualizado' => now()->format('H:i:s'),
        ];
    }

    private function tiempoAbierto(Mesa $mesa): string
    {
        $abierto = $mesa->pedidoAbierto?->abierto_en;
        if (! $abierto) {
            return '';
        }

        $minutos = (int) $abierto->diffInMinutes(now());

        return match (true) {
            $minutos < 1 => 'Recién abierta',
            $minutos < 60 => "Hace {$minutos} min",
            default => 'Hace '.intdiv($minutos, 60).' h '.($minutos % 60).' min',
        };
    }

    /**
     * A dónde lleva el clic en una mesa. La pantalla del pedido es del módulo de Pedidos:
     * cuando exista la ruta mesero.mesas.pedido, el mapa la usa sola; mientras tanto, no lleva a ningún lado.
     */
    private function urlPedido(Mesa $mesa): ?string
    {
        return Route::has('mesero.mesas.pedido') ? route('mesero.mesas.pedido', $mesa) : null;
    }
}
