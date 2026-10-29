<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::pluck('id', 'nombre');

        // Primero los vinos, para poder sugerirlos en los platos
        $vinos = [];
        foreach ([
            ['nombre' => 'Malbec, copa', 'descripcion' => 'Tinto argentino, frutos rojos y final suave.', 'precio' => 45, 'costo' => 18],
            ['nombre' => 'Cabernet Sauvignon, copa', 'descripcion' => 'Tinto chileno con cuerpo, ideal con carnes.', 'precio' => 50, 'costo' => 20],
            ['nombre' => 'Sauvignon Blanc, copa', 'descripcion' => 'Blanco fresco y cítrico.', 'precio' => 42, 'costo' => 16],
            ['nombre' => 'Rioja Crianza, botella', 'descripcion' => 'Tinto español de Tempranillo con notas de roble.', 'precio' => 285, 'costo' => 140],
        ] as $vino) {
            $vinos[$vino['nombre']] = Producto::create($vino + [
                'categoria_id' => $categorias['Vinos'],
                'es_vino' => true,
            ]);
        }

        $platos = [
            ['Tablas', 'Tabla de embutidos', 'Jamón serrano, salami, chorizo español y pan de la casa.', 165, 70, 'Rioja Crianza, botella'],
            ['Tablas', 'Tabla de quesos', 'Manchego, brie, gouda añejo, frutos secos y mermelada.', 175, 75, 'Sauvignon Blanc, copa'],
            ['Tablas', 'Tabla mixta para compartir', 'Selección de embutidos y quesos para 3 a 4 personas.', 295, 130, 'Malbec, copa'],
            ['Paninis y sándwiches', 'Panini de jamón serrano', 'Jamón serrano, queso manchego, tomate y aceite de oliva.', 78, 32, 'Malbec, copa'],
            ['Paninis y sándwiches', 'Panini caprese', 'Mozzarella, tomate, albahaca y pesto.', 68, 26, 'Sauvignon Blanc, copa'],
            ['Paninis y sándwiches', 'Sándwich de pastrami', 'Pastrami de la casa, mostaza antigua y pepinillos.', 85, 35, 'Cabernet Sauvignon, copa'],
            ['Hamburguesas', 'Hamburguesa de la casa', 'Carne de res, queso cheddar añejo, tocino y cebolla caramelizada.', 95, 40, 'Cabernet Sauvignon, copa'],
            ['Hamburguesas', 'Hamburguesa de chorizo', 'Torta de chorizo artesanal, queso de capa y chirmol.', 89, 37, 'Malbec, copa'],
            ['Tacos', 'Tacos de longaniza', 'Tres tacos de longaniza con guacamol y cebolla.', 55, 20, null],
            ['Tacos', 'Tacos de lomo', 'Tres tacos de lomito con queso fundido.', 65, 27, 'Malbec, copa'],
            ['Bebidas', 'Limonada con hierbabuena', 'Natural, hecha al momento.', 22, 6, null],
            ['Bebidas', 'Agua pura', 'Botella de 600 ml.', 12, 4, null],
        ];

        foreach ($platos as [$categoria, $nombre, $descripcion, $precio, $costo, $vino]) {
            Producto::create([
                'categoria_id' => $categorias[$categoria],
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'costo' => $costo,
                'vino_sugerido_id' => $vino ? $vinos[$vino]->id : null,
            ]);
        }

        // Un producto agotado para probar el menú y los pedidos
        Producto::where('nombre', 'Tacos de longaniza')->update(['disponible' => false]);
    }
}
