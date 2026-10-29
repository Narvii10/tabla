<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del formulario de inicio de sesión: que vengan correo y contraseña.
 * Si son correctos o no lo revisa LoginController@store.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cualquiera puede intentar entrar
        return true;
    }

    /**
     * El correo se compara sin espacios y en minúsculas.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido, por ejemplo mesero@tabla.test.',
            'password.required' => 'Escribe tu contraseña.',
        ];
    }
}
