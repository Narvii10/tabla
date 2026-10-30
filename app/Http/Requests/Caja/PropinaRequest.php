<?php

namespace App\Http\Requests\Caja;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PropinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'propina' => ['required', 'numeric', 'min:0', 'max:100000', 'decimal:0,2'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $pedido = $this->route('pedido');
                $nuevoTotal = $pedido->subtotal() + (float) $this->input('propina');

                if ($nuevoTotal < $pedido->pagado()) {
                    $validator->errors()->add('propina', 'Esa propina deja el total por debajo de lo que ya se pagó.');
                }
            },
        ];
    }
}
