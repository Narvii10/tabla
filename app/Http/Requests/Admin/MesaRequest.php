<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para crear y editar mesas.
 * El token del QR y el estado no se editan aquí: el token lo genera el modelo
 * y el estado lo cambian Pedidos y Caja.
 */
class MesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware del grupo admin
        return true;
    }

    /**
     * Una zona escrita solo con espacios se guarda como "sin zona".
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['zona' => trim((string) $this->input('zona')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'numero' => [
                'required', 'integer', 'min:1', 'max:999',
                // Al editar, ignora la misma mesa
                Rule::unique('mesas', 'numero')->ignore($this->route('mesa')),
            ],
            'capacidad' => ['required', 'integer', 'min:1', 'max:30'],
            'zona' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.unique' => 'Ya existe una mesa con ese número.',
        ];
    }
}
