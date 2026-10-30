<?php

namespace Tests\Feature;

use App\Models\Comanda;
use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidosTest extends TestCase
{
    use RefreshDatabase;

    private User $mesero;

    /**
     * Las rutas del mesero piden sesión: cada prueba entra como mesero.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mesero = User::factory()->rol('mesero')->create();
        $this->actingAs($this->mesero);
    }

    public function test_abrir_pedido_ocupa_la_mesa_y_no_se_duplica(): void
    {
        $mesa = Mesa::factory()->create(['estado' => 'libre']);

        $this->post(route('mesero.mesas.pedido.abrir', $mesa))->assertRedirect();
        // Un segundo "abrir" (otro mesero al mismo tiempo) reutiliza el pedido
        $this->post(route('mesero.mesas.pedido.abrir', $mesa))->assertRedirect();

        $this->assertSame(1, Pedido::where('mesa_id', $mesa->id)->count());
        $this->assertSame('ocupada', $mesa->fresh()->estado);
        $this->assertSame($this->mesero->id, Pedido::first()->mesero_id);
    }

    public function test_clic_en_una_mesa_libre_propone_abrir_y_en_una_ocupada_va_al_pedido(): void
    {
        $libre = Mesa::factory()->create(['estado' => 'libre']);
        $this->get(route('mesero.mesas.pedido', $libre))->assertOk()->assertSee('Abrir pedido');

        $pedido = Pedido::factory()->create();
        $this->get(route('mesero.mesas.pedido', $pedido->mesa))
            ->assertRedirect(route('mesero.pedidos.show', $pedido));
    }

    public function test_el_precio_se_copia_y_las_cantidades_se_suman(): void
    {
        $pedido = Pedido::factory()->create();
        $panini = Producto::factory()->create(['precio' => 45.00]);

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $panini->id, 'cantidad' => 1]);
        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $panini->id, 'cantidad' => 2]);

        // Aunque el menú cambie de precio después, el pedido conserva el de ese momento
        $panini->update(['precio' => 60.00]);

        $this->assertSame(1, $pedido->detalles()->count());
        $detalle = $pedido->detalles()->first();
        $this->assertSame(3, $detalle->cantidad);
        $this->assertSame('45.00', $detalle->precio_unitario);
        $this->assertSame(135.0, $pedido->fresh()->subtotal());
    }

    public function test_un_producto_con_nota_va_en_otra_linea(): void
    {
        $pedido = Pedido::factory()->create();
        $panini = Producto::factory()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $panini->id, 'cantidad' => 1]);
        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $panini->id, 'cantidad' => 1, 'notas' => 'sin cebolla']);

        $this->assertSame(2, $pedido->detalles()->count());
        $this->assertSame('sin cebolla', $pedido->detalles()->whereNotNull('notas')->first()->notas);
    }

    public function test_un_producto_agotado_no_se_puede_agregar(): void
    {
        $pedido = Pedido::factory()->create();
        $agotado = Producto::factory()->agotado()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $agotado->id, 'cantidad' => 1])
            ->assertSessionHasErrors('producto_id');

        $this->assertSame(0, $pedido->detalles()->count());
    }

    public function test_la_cantidad_se_valida(): void
    {
        $pedido = Pedido::factory()->create();
        $producto = Producto::factory()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $producto->id, 'cantidad' => 0])
            ->assertSessionHasErrors('cantidad');
        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $producto->id, 'cantidad' => 51])
            ->assertSessionHasErrors('cantidad');
    }

    public function test_al_agregar_un_plato_se_ofrece_su_vino_sugerido(): void
    {
        $vino = Producto::factory()->vino()->create(['nombre' => 'Malbec']);
        $tabla = Producto::factory()->create(['vino_sugerido_id' => $vino->id]);
        $pedido = Pedido::factory()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $tabla->id, 'cantidad' => 1])
            ->assertSessionHas('vino_sugerido', fn (array $sugerido) => $sugerido['id'] === $vino->id);

        $this->get(route('mesero.pedidos.show', $pedido))->assertOk();
    }

    public function test_enviar_a_cocina_crea_una_comanda_por_ronda_y_la_mesa_pasa_a_esperando(): void
    {
        $pedido = Pedido::factory()->create();
        $panini = Producto::factory()->create();
        $taco = Producto::factory()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $panini->id, 'cantidad' => 2]);
        $this->post(route('mesero.pedidos.enviar', $pedido))->assertSessionHas('success');

        $this->assertSame(1, $pedido->comandas()->count());
        $this->assertSame('pendiente', $pedido->comandas()->first()->estado);
        $this->assertSame('esperando', $pedido->mesa->fresh()->estado);

        // Segunda ronda: solo lleva lo nuevo
        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $taco->id, 'cantidad' => 1]);
        $this->post(route('mesero.pedidos.enviar', $pedido));

        $this->assertSame(2, $pedido->comandas()->count());
        $segunda = $pedido->comandas()->orderByDesc('id')->first();
        $this->assertSame(1, $segunda->detalles()->count());
        $this->assertSame($taco->id, $segunda->detalles()->first()->producto_id);
    }

    public function test_no_se_envia_a_cocina_sin_productos_nuevos(): void
    {
        $pedido = Pedido::factory()->create();

        $this->post(route('mesero.pedidos.enviar', $pedido))->assertSessionHas('error');

        $this->assertSame(0, $pedido->comandas()->count());
        $this->assertSame('ocupada', $pedido->mesa->fresh()->estado);
    }

    public function test_se_quita_lo_que_no_se_envio_pero_no_lo_enviado(): void
    {
        $pedido = Pedido::factory()->create();
        $comanda = Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'pendiente']);
        $producto = Producto::factory()->create();
        $enviado = DetallePedido::create([
            'pedido_id' => $pedido->id, 'comanda_id' => $comanda->id, 'producto_id' => $producto->id,
            'cantidad' => 1, 'precio_unitario' => 10,
        ]);
        $nuevo = DetallePedido::create([
            'pedido_id' => $pedido->id, 'producto_id' => $producto->id, 'cantidad' => 1, 'precio_unitario' => 10,
        ]);

        $this->delete(route('mesero.pedidos.detalles.destroy', [$pedido, $enviado]))->assertSessionHas('error');
        $this->assertModelExists($enviado);

        $this->delete(route('mesero.pedidos.detalles.destroy', [$pedido, $nuevo]))->assertSessionHas('success');
        $this->assertModelMissing($nuevo);
    }

    public function test_un_detalle_de_otro_pedido_responde_404(): void
    {
        $pedido = Pedido::factory()->create();
        $otro = Pedido::factory()->create();
        $detalle = DetallePedido::create([
            'pedido_id' => $otro->id, 'producto_id' => Producto::factory()->create()->id,
            'cantidad' => 1, 'precio_unitario' => 10,
        ]);

        $this->delete(route('mesero.pedidos.detalles.destroy', [$pedido, $detalle]))->assertNotFound();
    }

    public function test_entregar_todas_las_comandas_pone_la_mesa_en_comiendo(): void
    {
        $pedido = Pedido::factory()->create();
        $pedido->mesa->update(['estado' => 'esperando']);
        $primera = Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'listo']);
        $segunda = Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'listo']);

        $this->patch(route('mesero.comandas.entregada', $primera));
        $this->assertSame('entregado', $primera->fresh()->estado);
        // Todavía falta una por entregar
        $this->assertSame('esperando', $pedido->mesa->fresh()->estado);

        $this->patch(route('mesero.comandas.entregada', $segunda));
        $this->assertSame('comiendo', $pedido->mesa->fresh()->estado);
    }

    public function test_no_se_entrega_una_comanda_que_cocina_no_ha_terminado(): void
    {
        $pedido = Pedido::factory()->create();
        $comanda = Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'preparando']);

        $this->patch(route('mesero.comandas.entregada', $comanda))->assertSessionHas('error');

        $this->assertSame('preparando', $comanda->fresh()->estado);
    }

    public function test_pedir_la_cuenta_pasa_la_mesa_a_cuenta(): void
    {
        $pedido = Pedido::factory()->create();
        DetallePedido::create([
            'pedido_id' => $pedido->id, 'producto_id' => Producto::factory()->create()->id,
            'cantidad' => 1, 'precio_unitario' => 10,
        ]);

        $this->patch(route('mesero.pedidos.cuenta', $pedido))->assertSessionHas('success');

        $this->assertSame('cuenta', $pedido->mesa->fresh()->estado);
    }

    public function test_un_pedido_cerrado_no_se_modifica(): void
    {
        $pedido = Pedido::factory()->create(['estado' => 'cerrado', 'cerrado_en' => now()]);
        $producto = Producto::factory()->create();

        $this->post(route('mesero.pedidos.detalles.store', $pedido), ['producto_id' => $producto->id, 'cantidad' => 1])
            ->assertSessionHas('error');
        $this->post(route('mesero.pedidos.enviar', $pedido))->assertSessionHas('error');
        $this->patch(route('mesero.pedidos.cuenta', $pedido))->assertSessionHas('error');

        $this->assertSame(0, $pedido->detalles()->count());
        $this->assertSame(0, $pedido->comandas()->count());
    }

    public function test_la_lista_de_pedidos_abiertos_muestra_solo_los_abiertos(): void
    {
        $abierto = Pedido::factory()->create();
        $abierto->mesa->update(['numero' => 7]);
        Pedido::factory()->create(['estado' => 'cerrado'])->mesa->update(['numero' => 8]);

        $this->get(route('mesero.pedidos.index'))
            ->assertOk()
            ->assertSee($abierto->mesero->name)
            ->assertDontSee('No hay pedidos abiertos');
    }

    public function test_cocina_no_entra_a_pedidos(): void
    {
        $this->actingAs(User::factory()->rol('cocina')->create());
        $pedido = Pedido::factory()->create();

        $this->get(route('mesero.pedidos.index'))->assertForbidden();
        $this->get(route('mesero.pedidos.show', $pedido))->assertForbidden();
        $this->post(route('mesero.pedidos.enviar', $pedido))->assertForbidden();
    }
}
