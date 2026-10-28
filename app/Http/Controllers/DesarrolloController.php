<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\View\View;

/**
 * Página de desarrollo en "/": enlaces a cada área mientras el login no esté listo.
 */
class DesarrolloController extends Controller
{
    public function __invoke(): View
    {
        return view('desarrollo', [
            'usuarios' => User::orderBy('id')->get(['name', 'email', 'rol', 'activo']),
            'mesas' => Mesa::orderBy('numero')->get(),
            'totales' => [
                'Categorías' => Categoria::count(),
                'Productos' => Producto::count(),
                'Mesas' => Mesa::count(),
                'Usuarios' => User::count(),
            ],
        ]);
    }
}
