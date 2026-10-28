<?php

namespace Database\Factories;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mesa_id' => Mesa::factory()->state(['estado' => 'ocupada']),
            'mesero_id' => User::factory(),
            'estado' => 'abierto',
            'propina' => 0,
            'abierto_en' => now(),
            'cerrado_en' => null,
        ];
    }
}
