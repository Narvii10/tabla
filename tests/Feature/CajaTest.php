<?php

namespace Tests\Feature;

use App\Http\Controllers\Caja\CajaController;
use App\Models\CierreCaja;
use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->rol('caja')->create());
    }

    private function detalle(Pedido $pedido, Producto $producto, int $cantidad, ?float $precio = null): DetallePedido
    {
        return DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio ?? $producto->precio,
        ]);
    }

    public function test_pagar_el_total_cierra_el_pedido_y_deja_la_mesa_libre(): void
    {
        $mesa = Mesa::factory()->create(['estado' => 'cuenta']);
        $pedido = Pedido::factory()->create(['mesa_id' => $mesa->id]);
        $this->detalle($pedido, Producto::factory()->create(['precio' => 50]), 2);

        $this->post(route('caja.pedidos.pagos.store', $pedido), [
            'metodo' => 'tarjeta',
            'monto' => 100,
        ])->assertRedirect(route('caja.index'));

        $this->assertSame('cerrado', $pedido->fresh()->estado);
        $this->assertSame('libre', $mesa->fresh()->estado);
    }

    public function test_pago_parcial_en_efectivo_guarda_recibido_y_vuelto_y_el_pedido_sigue_abierto(): void
    {
        $pedido = Pedido::factory()->create();
        $this->detalle($pedido, Producto::factory()->create(['precio' => 100]), 1);

        $this->post(route('caja.pedidos.pagos.store', $pedido), [
            'metodo' => 'efectivo',
            'monto' => 40,
            'recibido' => 50,
        ])->assertRedirect(route('caja.pedidos.show', $pedido));

        $pago = Pago::first();
        $this->assertSame('50.00', $pago->recibido);
        $this->assertSame('10.00', $pago->vuelto);
        $this->assertSame('abierto', $pedido->fresh()->estado);
    }

    public function test_no_se_puede_cobrar_mas_del_saldo_ni_con_efectivo_insuficiente(): void
    {
        $pedido = Pedido::factory()->create();
        $this->detalle($pedido, Producto::factory()->create(['precio' => 100]), 1);

        $this->post(route('caja.pedidos.pagos.store', $pedido), [
            'metodo' => 'tarjeta',
            'monto' => 150,
        ])->assertSessionHas('error');
        $this->assertSame(0, $pedido->pagos()->count());

        $this->post(route('caja.pedidos.pagos.store', $pedido), [
            'metodo' => 'efectivo',
            'monto' => 50,
            'recibido' => 20,
        ])->assertSessionHasErrors('recibido');
        $this->assertSame(0, $pedido->pagos()->count());
    }

    public function test_propina_10_por_ciento_se_guarda_y_cambia_el_total(): void
    {
        $pedido = Pedido::factory()->create();
        $this->detalle($pedido, Producto::factory()->create(['precio' => 100]), 1);

        $this->patch(route('caja.pedidos.propina', $pedido), ['propina' => 10])
            ->assertRedirect(route('caja.pedidos.show', $pedido));

        $pedido->refresh();
        $this->assertSame('10.00', $pedido->propina);
        $this->assertSame(110.0, $pedido->total());
    }

    public function test_dividir_100_entre_3_da_33_34_33_33_33_33_y_suma_exacto(): void
    {
        $partes = CajaController::dividir(10000, 3);

        $this->assertSame([3334, 3333, 3333], $partes);
        $this->assertSame(10000, array_sum($partes));
    }

    public function test_cierre_calcula_esperado_por_metodo_ignora_el_formulario_y_el_segundo_del_dia_se_rechaza(): void
    {
        $uno = Pedido::factory()->create(['estado' => 'cerrado', 'cerrado_en' => now()]);
        $dos = Pedido::factory()->create(['estado' => 'cerrado', 'cerrado_en' => now()]);
        Pago::create([
            'pedido_id' => $uno->id, 'cajero_id' => auth()->id(),
            'metodo' => 'efectivo', 'monto' => 100, 'recibido' => 100, 'vuelto' => 0,
        ]);
        Pago::create([
            'pedido_id' => $dos->id, 'cajero_id' => auth()->id(),
            'metodo' => 'tarjeta', 'monto' => 50,
        ]);

        $this->post(route('caja.cierre.store'), [
            'efectivo_contado' => 90,
            'efectivo_esperado' => 999,
        ])->assertRedirect(route('caja.cierre.create'));

        $cierre = CierreCaja::first();
        $this->assertSame('100.00', $cierre->efectivo_esperado);
        $this->assertSame('50.00', $cierre->tarjeta_esperado);
        $this->assertSame('-10.00', $cierre->diferencia);

        $this->post(route('caja.cierre.store'), ['efectivo_contado' => 100])
            ->assertSessionHas('error');
        $this->assertSame(1, CierreCaja::count());
    }

    public function test_un_mesero_recibe_403_en_caja(): void
    {
        $this->actingAs(User::factory()->rol('mesero')->create());
        $pedido = Pedido::factory()->create();

        $this->get(route('caja.index'))->assertForbidden();
        $this->post(route('caja.pedidos.pagos.store', $pedido), ['metodo' => 'tarjeta', 'monto' => 10])
            ->assertForbidden();
    }
}
