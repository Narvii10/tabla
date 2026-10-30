<?php

namespace App\Http\Requests\Caja;

use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo' => ['required', Rule::in(array_keys(Pago::METODOS))],
            'monto' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'recibido' => ['nullable', 'numeric', 'gte:monto', Rule::requiredIf($this->input('metodo') === 'efectivo')],
        ];
    }

    public function messages(): array
    {
        return [
            'recibido.gte' => 'Lo recibido no puede ser menor que el monto a cobrar.',
            'recibido.required' => 'En efectivo hay que indicar cuánto entregó el cliente.',
        ];
    }

    public function attributes(): array
    {
        return [
            'metodo' => 'método',
            'monto' => 'monto',
            'recibido' => 'recibido',
        ];
    }
}
