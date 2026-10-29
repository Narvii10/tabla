<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para crear y editar usuarios.
 * Al editar, la contraseña es opcional: si se deja vacía, no cambia.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware del grupo admin
        return true;
    }

    /**
     * El correo se guarda sin espacios y en minúsculas.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $editando = $usuario !== null;

        // El admin no puede quitarse su propio rol (se quedaría sin acceso)
        $roles = $editando && $usuario->is($this->user())
            ? ['admin']
            : array_keys(User::ROLES);

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:150',
                // Al editar, ignora al mismo usuario
                Rule::unique('users', 'email')->ignore($usuario),
            ],
            'rol' => ['required', Rule::in($roles)],
            'password' => [$editando ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ya hay un usuario con ese correo.',
            'rol.in' => $this->route('usuario')?->is($this->user())
                ? 'No puedes quitarte tu propio rol de administrador.'
                : 'Elige uno de los cuatro roles.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'password' => 'contraseña',
        ];
    }
}
