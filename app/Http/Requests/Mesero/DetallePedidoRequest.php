<?php

namespace App\Http\Requests\Mesero;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para agregar un producto a un pedido.
 */
class DetallePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware del grupo mesero
        return true;
    }

    public function rules(): array
    {
        return [
            // El producto debe existir y no estar agotado: un agotado falla aquí, no solo en pantalla
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('disponible', true)],
            'cantidad' => ['required', 'integer', 'min:1', 'max:50'],
            'notas' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.exists' => 'Ese producto está agotado o ya no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'producto_id' => 'producto',
            'notas' => 'nota',
        ];
    }
}
