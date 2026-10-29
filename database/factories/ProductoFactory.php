<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nombre' => ucfirst(fake()->words(2, true)),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomFloat(2, 25, 200),
            'costo' => null,
            'foto' => null,
            'disponible' => true,
            'es_vino' => false,
            'vino_sugerido_id' => null,
        ];
    }

    /**
     * Producto agotado.
     */
    public function agotado(): static
    {
        return $this->state(fn (array $attributes) => [
            'disponible' => false,
        ]);
    }

    public function vino(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_vino' => true,
        ]);
    }
}
