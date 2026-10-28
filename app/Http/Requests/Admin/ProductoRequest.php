<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para crear y editar productos.
 */
class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware del grupo admin
        return true;
    }

    /**
     * Los checkboxes sin marcar no se envían; los convertimos en false.
     * Un vino no lleva vino sugerido.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'disponible' => $this->boolean('disponible'),
            'es_vino' => $this->boolean('es_vino'),
        ]);

        if ($this->boolean('es_vino')) {
            $this->merge(['vino_sugerido_id' => null]);
        }
    }

    public function rules(): array
    {
        $vinoSugerido = [
            'nullable', 'integer',
            // Solo se puede sugerir un producto marcado como vino
            Rule::exists('productos', 'id')->where('es_vino', true),
        ];

        // Al editar, un producto no se puede sugerir a sí mismo
        if ($producto = $this->route('producto')) {
            $vinoSugerido[] = Rule::notIn([$producto->id]);
        }

        return [
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'costo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'disponible' => ['boolean'],
            'es_vino' => ['boolean'],
            'vino_sugerido_id' => $vinoSugerido,
        ];
    }

    public function messages(): array
    {
        return [
            'vino_sugerido_id.exists' => 'El vino sugerido debe ser un producto marcado como vino.',
            'vino_sugerido_id.not_in' => 'Un producto no puede sugerirse a sí mismo.',
        ];
    }
}
