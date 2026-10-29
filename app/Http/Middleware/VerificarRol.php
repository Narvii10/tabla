<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware "rol". En las rutas se usa así: ->middleware(['auth', 'rol:mesero,admin']).
 *
 * - Si el usuario fue desactivado mientras tenía la sesión abierta, se la cierra
 *   y lo manda al login (así lo saca en su siguiente clic).
 * - Si su rol no está en la lista, responde 403 (No autorizado).
 *
 * Va siempre después de "auth": aquí ya sabemos que hay un usuario con sesión.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu usuario fue desactivado. Habla con el administrador.']);
        }

        if (! in_array($usuario->rol, $roles, true)) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
