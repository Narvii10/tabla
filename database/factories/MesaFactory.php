<?php

namespace Database\Factories;

use App\Models\Mesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mesa>
 */
class MesaFactory extends Factory
{
    public function definition(): array
    {
        // token_qr lo genera el modelo al crear la mesa
        return [
            'numero' => fake()->unique()->numberBetween(1, 999),
            'capacidad' => fake()->randomElement([2, 4, 6]),
            'zona' => null,
            'estado' => 'libre',
        ];
    }
}
