<?php

namespace Tests\Feature;

use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapaMesasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Las rutas de administración y mesero piden sesión: cada prueba entra como admin.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->rol('admin')->create());
    }

    public function test_el_mapa_muestra_las_mesas_por_zona_con_su_estado(): void
    {
        Mesa::factory()->create(['numero' => 1, 'zona' => 'Salón', 'estado' => 'libre']);
        Mesa::factory()->create(['numero' => 6, 'zona' => 'Terraza', 'estado' => 'cuenta']);

        $this->get(route('mesero.index'))
            ->assertOk()
            ->assertSeeInOrder(['Salón', 'Mesa 1', 'Terraza', 'Mesa 6'])
            ->assertSee('Pidió la cuenta');
    }

    public function test_los_datos_avisan_cuando_una_comanda_esta_lista(): void
    {
        $pedido = Pedido::factory()->create(['abierto_en' => now()->subMinutes(25)]);
        $pedido->mesa->update(['numero' => 3, 'estado' => 'esperando']);
        Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'listo']);
        Comanda::create(['pedido_id' => $pedido->id, 'enviada_en' => now(), 'estado' => 'preparando']);
        // Una comanda lista de un pedido ya cerrado no debe contar
        $viejo = Pedido::factory()->create(['mesa_id' => $pedido->mesa_id, 'estado' => 'cerrado']);
        Comanda::create(['pedido_id' => $viejo->id, 'enviada_en' => now(), 'estado' => 'listo']);

        Mesa::factory()->create(['numero' => 4, 'estado' => 'libre']);

        $respuesta = $this->getJson(route('mesero.mesas.datos'))->assertOk();
        $mesas = collect($respuesta->json('zonas'))->flatten(1)->keyBy('numero');

        $this->assertSame('esperando', $mesas[3]['estado']);
        $this->assertSame(1, $mesas[3]['comandas_listas']);
        $this->assertSame('Hace 25 min', $mesas[3]['tiempo']);

        $this->assertSame(0, $mesas[4]['comandas_listas']);
        $this->assertSame('', $mesas[4]['tiempo']);

        $this->assertSame(1, $respuesta->json('listas'));
        $this->assertSame(1, $respuesta->json('resumen.esperando.total'));
    }
}
