<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = ['Tablas', 'Paninis y sándwiches', 'Hamburguesas', 'Tacos', 'Vinos', 'Bebidas'];

        foreach ($nombres as $indice => $nombre) {
            Categoria::create([
                'nombre' => $nombre,
                'orden' => $indice + 1,
                'activo' => true,
            ]);
        }
    }
}
