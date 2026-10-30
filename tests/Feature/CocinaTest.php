<?php

namespace Tests\Feature;

use App\Models\Comanda;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CocinaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->rol('cocina')->create());
    }

    /**
     * Crea una comanda con productos: [[producto, cantidad, notas], ...]
     */
    private function comanda(Pedido $pedido, string $estado, int $minutos, array $lineas): Comanda
    {
        $comanda = Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now()->subMinutes($minutos), 'estado' => $estado]);
        foreach ($lineas as [$producto, $cantidad, $notas]) {
            DetallePedido::create([
                'pedido_id' => $pedido->id, 'comanda_id' => $comanda->id, 'producto_id' => $producto->id,
                'cantidad' => $cantidad, 'precio_unitario' => $producto->precio, 'notas' => $notas,
            ]);
        }

        return $comanda;
    }

    public function test_datos_de_la_pantalla_ordenados_por_antiguedad_y_con_color(): void
    {
        $pedido = Pedido::factory()->create();
        $panini = Producto::factory()->create(['nombre' => 'Panini caprese']);
        $tabla = Producto::factory()->create(['nombre' => 'Tabla de quesos']);

        $this->comanda($pedido, 'pendiente', 5, [[$panini, 2, 'sin albahaca']]);
        $this->comanda($pedido, 'preparando', 25, [[$panini, 2, null], [$tabla, 1, null]]);
        $this->comanda($pedido, 'listo', 15, [[$tabla, 1, null]]);
        $this->comanda($pedido, 'entregado', 40, [[$tabla, 3, null]]); // ya entregada: no sale

        $datos = $this->getJson(route('cocina.comandas.datos'))->assertOk()->json();

        $this->assertCount(3, $datos['comandas']);
        // De la más vieja a la más nueva
        $this->assertSame([25, 15, 5], array_column($datos['comandas'], 'minutos'));
        $this->assertSame(['rojo', 'amarillo', 'verde'], array_column($datos['comandas'], 'espera'));
        $this->assertSame('sin albahaca', $datos['comandas'][2]['productos'][0]['notas']);

        // Resumen: solo pendientes y en preparación (4 paninis, 1 tabla)
        $this->assertSame([
            ['nombre' => 'Panini caprese', 'cantidad' => 4],
            ['nombre' => 'Tabla de quesos', 'cantidad' => 1],
        ], $datos['resumen']);
    }

    public function test_la_comanda_avanza_paso_a_paso(): void
    {
        $pedido = Pedido::factory()->create();
        $comanda = $this->comanda($pedido, 'pendiente', 1, [[Producto::factory()->create(), 1, null]]);

        // No se puede saltar de pendiente a listo
        $this->patchJson(route('cocina.comandas.estado', $comanda), ['estado' => 'listo'])->assertUnprocessable();

        $this->patchJson(route('cocina.comandas.estado', $comanda), ['estado' => 'preparando'])->assertOk();
        $this->patchJson(route('cocina.comandas.estado', $comanda), ['estado' => 'listo'])->assertOk();
        $this->assertSame('listo', $comanda->fresh()->estado);

        // "Entregado" lo pone el mesero, no cocina
        $this->patchJson(route('cocina.comandas.estado', $comanda), ['estado' => 'entregado'])->assertUnprocessable();
    }

    public function test_comandas_de_un_pedido_ya_cobrado_no_salen(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'cerrado', 'cerrado_en' => now()]);
        $this->comanda($pedido, 'listo', 3, [[Producto::factory()->create(), 1, null]]);

        $this->getJson(route('cocina.comandas.datos'))->assertJsonCount(0, 'comandas');
    }

    public function test_cocina_marca_un_producto_agotado(): void
    {
        $producto = Producto::factory()->create();

        $this->patchJson(route('cocina.productos.agotado', $producto))->assertOk();
        $this->assertFalse($producto->fresh()->disponible);
    }
}
