<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crea_una_mesa_y_se_genera_su_qr(): void
    {
        $this->post(route('admin.mesas.store'), ['numero' => 12, 'capacidad' => 4, 'zona' => 'Terraza'])
            ->assertRedirect(route('admin.mesas.index'))
            ->assertSessionHas('success');

        $mesa = Mesa::where('numero', 12)->firstOrFail();
        $this->assertSame('libre', $mesa->estado);
        $this->assertSame(32, strlen($mesa->token_qr));

        // El QR abre el menú de esa mesa
        $this->get(route('menu.mesa', $mesa->token_qr))->assertOk()->assertSee('Mesa 12');
    }

    public function test_el_numero_de_mesa_no_se_repite(): void
    {
        Mesa::factory()->create(['numero' => 3]);

        $this->post(route('admin.mesas.store'), ['numero' => 3, 'capacidad' => 2])
            ->assertSessionHasErrors('numero');

        $this->assertDatabaseCount('mesas', 1);
    }

    public function test_editar_no_cambia_el_qr_ni_el_estado(): void
    {
        $mesa = Mesa::factory()->create(['numero' => 5, 'estado' => 'comiendo']);
        $token = $mesa->token_qr;

        // Aunque intenten mandar estado o token, el Form Request no los deja pasar
        $this->put(route('admin.mesas.update', $mesa), [
            'numero' => 6, 'capacidad' => 2, 'zona' => '  ', 'estado' => 'libre', 'token_qr' => 'otro',
        ])->assertRedirect(route('admin.mesas.index'));

        $mesa->refresh();
        $this->assertSame(6, $mesa->numero);
        $this->assertNull($mesa->zona);
        $this->assertSame('comiendo', $mesa->estado);
        $this->assertSame($token, $mesa->token_qr);
    }

    public function test_generar_qr_nuevo_invalida_el_anterior(): void
    {
        $mesa = Mesa::factory()->create();
        $tokenViejo = $mesa->token_qr;

        $this->patch(route('admin.mesas.qr', $mesa))->assertSessionHas('success');

        $this->assertNotSame($tokenViejo, $mesa->fresh()->token_qr);
        $this->get(route('menu.mesa', $tokenViejo))->assertNotFound();
    }

    public function test_no_se_elimina_una_mesa_con_pedidos(): void
    {
        $pedido = Pedido::factory()->create();

        $this->delete(route('admin.mesas.destroy', $pedido->mesa))->assertSessionHas('error');
        $this->assertModelExists($pedido->mesa);

        $sinPedidos = Mesa::factory()->create();
        $this->delete(route('admin.mesas.destroy', $sinPedidos))->assertSessionHas('success');
        $this->assertModelMissing($sinPedidos);
    }

    public function test_pagina_de_impresion_trae_el_qr_de_cada_mesa(): void
    {
        $salon = Mesa::factory()->create(['numero' => 1, 'zona' => 'Salón']);
        $terraza = Mesa::factory()->create(['numero' => 2, 'zona' => 'Terraza']);

        // Todas: cada tarjeta lleva la dirección del menú de su mesa para dibujar el QR
        $this->get(route('admin.mesas.imprimir'))
            ->assertOk()
            ->assertSee(route('menu.mesa', $salon->token_qr))
            ->assertSee(route('menu.mesa', $terraza->token_qr));

        // Solo una zona
        $this->get(route('admin.mesas.imprimir', ['zona' => 'Terraza']))
            ->assertOk()
            ->assertSee('Mesa 2')
            ->assertDontSee(route('menu.mesa', $salon->token_qr));

        // Solo una mesa
        $this->get(route('admin.mesas.imprimir', ['mesa' => $salon->id]))
            ->assertOk()
            ->assertSee(route('menu.mesa', $salon->token_qr))
            ->assertDontSee(route('menu.mesa', $terraza->token_qr));
    }

    public function test_listado_busca_por_numero_y_por_zona(): void
    {
        Mesa::factory()->create(['numero' => 7, 'zona' => 'Salón']);
        Mesa::factory()->create(['numero' => 21, 'zona' => 'Terraza']);

        $this->get(route('admin.mesas.index', ['buscar' => '7']))
            ->assertOk()->assertSee('Mesa 7')->assertDontSee('Mesa 21');

        $this->get(route('admin.mesas.index', ['buscar' => 'terr']))
            ->assertOk()->assertSee('Mesa 21')->assertDontSee('Mesa 7');
    }
}
