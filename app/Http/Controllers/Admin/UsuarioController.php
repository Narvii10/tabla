<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Usuarios del sistema (área de administración): listado, crear, editar y activar/desactivar.
 *
 * Los usuarios no se borran: se desactivan. Así no se pierde quién abrió cada
 * pedido o cobró cada pago. Un usuario desactivado no puede iniciar sesión.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = $request->string('buscar')->trim()->toString();
        $rol = $request->string('rol')->toString();

        if (! array_key_exists($rol, User::ROLES)) {
            $rol = '';
        }

        $usuarios = User::query()
            ->when($buscar !== '', fn ($query) => $query->where(function ($query) use ($buscar) {
                $query->where('name', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%");
            }))
            ->when($rol !== '', fn ($query) => $query->where('rol', $rol))
            // Primero los activos, luego por nombre
            ->orderByDesc('activo')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Cuántos usuarios activos hay de cada rol, para el resumen de arriba
        $porRol = User::where('activo', true)->selectRaw('rol, count(*) as total')->groupBy('rol')->pluck('total', 'rol');

        return view('admin.usuarios.index', compact('usuarios', 'buscar', 'rol', 'porRol'));
    }

    public function create(): View
    {
        return view('admin.usuarios.create', ['usuario' => new User(['rol' => 'mesero'])]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        // La contraseña se cifra sola (cast "hashed" del modelo User)
        $usuario = User::create($request->validated() + ['activo' => true]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} creado. Ya puede entrar con su correo.");
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.edit', compact('usuario'));
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();

        // Contraseña vacía = se queda la que tenía
        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado.");
    }

    /**
     * Botón Activo / Desactivado del listado.
     */
    public function alternarActivo(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('success', $usuario->activo
            ? "{$usuario->name} está activo de nuevo y ya puede entrar."
            : "{$usuario->name} quedó desactivado: ya no puede entrar al sistema.");
    }
}
