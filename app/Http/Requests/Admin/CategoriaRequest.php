<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para crear y editar categorías.
 */
class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware del grupo admin
        return true;
    }

    /**
     * Un checkbox sin marcar no se envía; lo convertimos en false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo')]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                // Al editar, ignora la misma categoría
                Rule::unique('categorias', 'nombre')->ignore($this->route('categoria')),
            ],
            'orden' => ['required', 'integer', 'min:0', 'max:1000'],
            'activo' => ['boolean'],
        ];
    }
}
