<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuTest extends TestCase
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

    // ---------- Administración ----------

    public function test_listados_de_admin_cargan_y_buscan(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Tablas']);
        Producto::factory()->for($categoria)->create(['nombre' => 'Tabla de quesos']);
        Producto::factory()->for($categoria)->create(['nombre' => 'Panini caprese']);

        $this->get(route('admin.categorias.index'))->assertOk()->assertSee('Tablas');
        $this->get(route('admin.productos.index', ['buscar' => 'quesos']))
            ->assertOk()
            ->assertSee('Tabla de quesos')
            ->assertDontSee('Panini caprese');
    }

    public function test_listado_de_productos_filtra_agotados(): void
    {
        Producto::factory()->create(['nombre' => 'Panini caprese']);
        Producto::factory()->agotado()->create(['nombre' => 'Tacos de longaniza']);

        $this->get(route('admin.productos.index', ['estado' => 'agotados']))
            ->assertOk()
            ->assertSee('Tacos de longaniza')
            ->assertDontSee('Panini caprese');
    }

    public function test_admin_crea_producto_con_vino_sugerido_y_foto(): void
    {
        Storage::fake('public');
        $categoria = Categoria::factory()->create();
        $vino = Producto::factory()->vino()->create();

        $this->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Tabla ibérica',
            'precio' => '185.50',
            'disponible' => '1',
            'es_vino' => '0',
            'vino_sugerido_id' => $vino->id,
            'foto' => $this->imagenDePrueba(),
        ])->assertRedirect(route('admin.productos.index'))->assertSessionHas('success');

        $producto = Producto::where('nombre', 'Tabla ibérica')->first();
        $this->assertSame('185.50', $producto->precio);
        $this->assertTrue($producto->disponible);
        $this->assertSame($vino->id, $producto->vino_sugerido_id);
        Storage::disk('public')->assertExists($producto->foto);
    }

    public function test_vino_sugerido_debe_ser_un_vino(): void
    {
        $categoria = Categoria::factory()->create();
        $noEsVino = Producto::factory()->create(['es_vino' => false]);

        $this->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Hamburguesa',
            'precio' => '90',
            'vino_sugerido_id' => $noEsVino->id,
        ])->assertSessionHasErrors('vino_sugerido_id');
    }

    public function test_precio_es_obligatorio_y_no_negativo(): void
    {
        $categoria = Categoria::factory()->create();

        $this->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Sin precio',
            'precio' => '-5',
        ])->assertSessionHasErrors('precio');

        $this->assertDatabaseMissing('productos', ['nombre' => 'Sin precio']);
    }

    public function test_boton_agotado_cambia_disponible(): void
    {
        $producto = Producto::factory()->create(['disponible' => true]);

        $this->patch(route('admin.productos.disponible', $producto))->assertSessionHas('success');
        $this->assertFalse($producto->fresh()->disponible);

        $this->patch(route('admin.productos.disponible', $producto));
        $this->assertTrue($producto->fresh()->disponible);
    }

    public function test_no_se_elimina_un_producto_que_ya_se_vendio(): void
    {
        $producto = Producto::factory()->create();
        $pedido = Pedido::factory()->create();
        DetallePedido::create([
            'pedido_id' => $pedido->id, 'producto_id' => $producto->id,
            'cantidad' => 1, 'precio_unitario' => $producto->precio,
        ]);

        $this->delete(route('admin.productos.destroy', $producto))->assertSessionHas('error');
        $this->assertModelExists($producto);
    }

    public function test_no_se_elimina_una_categoria_con_productos(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->for($categoria)->create();

        $this->delete(route('admin.categorias.destroy', $categoria))->assertSessionHas('error');
        $this->assertModelExists($categoria);
    }

    public function test_admin_crea_y_edita_categoria(): void
    {
        $this->post(route('admin.categorias.store'), ['nombre' => 'Postres', 'orden' => 7, 'activo' => '1'])
            ->assertRedirect(route('admin.categorias.index'));

        $categoria = Categoria::where('nombre', 'Postres')->firstOrFail();

        // Sin el checkbox "activo", la categoría queda oculta
        $this->put(route('admin.categorias.update', $categoria), ['nombre' => 'Postres', 'orden' => 7])
            ->assertRedirect(route('admin.categorias.index'));

        $this->assertFalse($categoria->fresh()->activo);
    }

    // ---------- Menú público ----------

    public function test_menu_publico_muestra_agotado_y_vino_sugerido(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Tablas']);
        $vino = Producto::factory()->vino()->create(['nombre' => 'Malbec, copa']);
        Producto::factory()->for($categoria)->create(['nombre' => 'Tabla de embutidos', 'vino_sugerido_id' => $vino->id]);
        Producto::factory()->for($categoria)->agotado()->create(['nombre' => 'Tacos de longaniza']);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Tabla de embutidos')
            ->assertSee('Vino sugerido: Malbec, copa')
            ->assertSee('Tacos de longaniza')
            ->assertSee('Agotado');
    }

    public function test_menu_publico_oculta_categorias_inactivas_y_filtra(): void
    {
        $tablas = Categoria::factory()->create(['nombre' => 'Tablas']);
        $oculta = Categoria::factory()->create(['nombre' => 'Temporada', 'activo' => false]);
        Producto::factory()->for($tablas)->create(['nombre' => 'Tabla de quesos']);
        Producto::factory()->for($tablas)->create(['nombre' => 'Tabla de embutidos']);
        Producto::factory()->for($oculta)->create(['nombre' => 'Ponche navideño']);

        $this->get(route('menu.index'))->assertOk()->assertDontSee('Ponche navideño');

        $this->get(route('menu.index', ['buscar' => 'quesos']))
            ->assertSee('Tabla de quesos')
            ->assertDontSee('Tabla de embutidos');
    }

    public function test_solo_los_platos_disponibles_se_pueden_marcar_en_mi_seleccion(): void
    {
        $categoria = Categoria::factory()->create();
        $disponible = Producto::factory()->for($categoria)->create(['nombre' => 'Panini caprese']);
        $agotado = Producto::factory()->for($categoria)->agotado()->create(['nombre' => 'Tacos de longaniza']);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Mi selección')
            ->assertSee('data-seleccionar="'.$disponible->id.'"', false)
            ->assertDontSee('data-seleccionar="'.$agotado->id.'"', false);
    }

    public function test_menu_de_mesa_muestra_numero_y_da_404_si_el_token_no_existe(): void
    {
        $mesa = Mesa::factory()->create(['numero' => 5]);

        $this->get(route('menu.mesa', $mesa->token_qr))->assertOk()->assertSee('Mesa 5');
        $this->get(route('menu.mesa', 'no-existe'))->assertNotFound();
    }

    /**
     * PNG real de 1x1 píxel. No usamos UploadedFile::fake()->image()
     * porque necesita la extensión GD, que el contenedor no tiene.
     */
    private function imagenDePrueba(): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'foto');
        file_put_contents($ruta, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($ruta, 'tabla.png', 'image/png', null, true);
    }
}
