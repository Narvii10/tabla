<?php

namespace App\Http\Requests\Cocina;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Cocina mueve una comanda: pendiente → preparando → listo.
 * "Entregado" no lo pone cocina, lo marca el mesero al llevarla a la mesa.
 */
class CambiarEstadoComandaRequest extends FormRequest
{
    /**
     * Estado actual => único estado al que puede pasar.
     */
    public const SIGUIENTE = [
        'pendiente' => 'preparando',
        'preparando' => 'listo',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(array_values(self::SIGUIENTE))],
        ];
    }

    /**
     * Además de que el estado exista, tiene que ser el siguiente paso (no se salta ni se regresa).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $actual = $this->route('comanda')->estado;
                if ((self::SIGUIENTE[$actual] ?? null) !== $this->input('estado')) {
                    $validator->errors()->add('estado', 'Esa comanda ya cambió de estado. La pantalla se actualizará.');
                }
            },
        ];
    }
}
