<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Inicio y cierre de sesión.
 */
class LoginController extends Controller
{
    /**
     * Muestra el formulario. Si ya tiene sesión, lo manda directo a su área.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($usuario = $request->user()) {
            return redirect($this->inicioSegunRol($usuario));
        }

        // En desarrollo se muestran los usuarios de prueba para entrar rápido
        $usuariosDePrueba = app()->isLocal()
            ? User::where('activo', true)->where('email', 'like', '%@tabla.test')->orderBy('id')->get(['name', 'email', 'rol'])
            : collect();

        return view('auth.login', compact('usuariosDePrueba'));
    }

    /**
     * Revisa correo, contraseña y que el usuario esté activo.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $usuario = User::where('email', $request->validated('email'))->first();

        // Mismo mensaje si no existe el correo o si la contraseña está mal,
        // para no darle pistas a quien esté adivinando
        if (! $usuario || ! Hash::check($request->validated('password'), $usuario->password)) {
            return back()->onlyInput('email')->withErrors(['email' => __('auth.failed')]);
        }

        if (! $usuario->activo) {
            return back()->onlyInput('email')
                ->withErrors(['email' => 'Tu usuario está desactivado. Habla con el administrador.']);
        }

        Auth::login($usuario);

        // Sesión nueva al entrar, para que nadie pueda reutilizar una sesión anterior
        $request->session()->regenerate();

        return redirect($this->inicioSegunRol($usuario));
    }

    /**
     * Cierra la sesión (botón "Salir" de la barra, siempre por POST con @csrf).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Cerraste sesión.');
    }

    /**
     * A dónde va cada rol después de entrar.
     */
    public function inicioSegunRol(User $usuario): string
    {
        return match ($usuario->rol) {
            'admin' => route('admin.index'),
            'mesero' => route('mesero.index'),
            'cocina' => route('cocina.index'),
            'caja' => route('caja.index'),
            default => route('menu.index'),
        };
    }
}
