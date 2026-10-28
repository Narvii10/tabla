<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Se siembra aquí (dentro de la transacción de la prueba) y no con
     * $seed = true, para que los datos no queden en la base de las demás pruebas.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_pagina_de_desarrollo_y_areas_cargan(): void
    {
        $this->get('/')->assertOk()->assertSee('Página de desarrollo')->assertSee('admin@tabla.test');

        foreach (['admin.index', 'mesero.index', 'cocina.index', 'caja.index', 'menu.index'] as $ruta) {
            $this->get(route($ruta))->assertOk();
        }
    }

    public function test_menu_de_mesa_usa_token_qr_y_da_404_si_no_existe(): void
    {
        $mesa = Mesa::first();

        $this->get(route('menu.mesa', $mesa->token_qr))->assertOk()->assertSee('Mesa '.$mesa->numero);
        $this->get(route('menu.mesa', 'token-que-no-existe'))->assertNotFound();
    }

    public function test_seeders_crean_datos_de_ejemplo(): void
    {
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('categorias', 6);
        $this->assertDatabaseCount('mesas', 8);
        $this->assertGreaterThanOrEqual(15, Producto::count());
        $this->assertTrue(Producto::whereNotNull('vino_sugerido_id')->first()->vinoSugerido->es_vino);
    }

    public function test_componente_money_muestra_quetzales(): void
    {
        $this->blade('<x-money :amount="1234.5" />')->assertSee('Q 1,234.50');
    }

    public function test_total_del_pedido_es_detalles_mas_propina(): void
    {
        $pedido = Pedido::factory()->create(['propina' => 10]);
        $producto = Producto::first();

        DetallePedido::create(['pedido_id' => $pedido->id, 'producto_id' => $producto->id, 'cantidad' => 2, 'precio_unitario' => 50]);
        DetallePedido::create(['pedido_id' => $pedido->id, 'producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 25.5]);

        $this->assertSame(125.5, $pedido->subtotal());
        $this->assertSame(135.5, $pedido->total());
    }
}
