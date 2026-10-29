<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ---------- Iniciar y cerrar sesión ----------

    public function test_el_login_se_ve_en_inicio_y_en_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Contraseña')->assertSee('Entrar');
        $this->get(route('login'))->assertOk()->assertSee('Entrar');
    }

    public function test_cada_rol_entra_a_su_area(): void
    {
        $areas = [
            'admin' => 'admin.index',
            'mesero' => 'mesero.index',
            'cocina' => 'cocina.index',
            'caja' => 'caja.index',
        ];

        foreach ($areas as $rol => $ruta) {
            $usuario = User::factory()->rol($rol)->create();

            $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'password'])
                ->assertRedirect(route($ruta));

            $this->assertAuthenticatedAs($usuario);
            $this->post(route('logout'));
        }
    }

    public function test_con_sesion_abierta_el_login_lo_manda_a_su_area(): void
    {
        $this->actingAs(User::factory()->rol('cocina')->create());

        $this->get('/')->assertRedirect(route('cocina.index'));
    }

    public function test_contrasena_incorrecta_no_entra(): void
    {
        $usuario = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $usuario->email, 'password' => 'otra-cosa'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correo_y_contrasena_son_obligatorios(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_usuario_desactivado_no_puede_entrar(): void
    {
        $usuario = User::factory()->inactivo()->create();

        $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_si_lo_desactivan_con_la_sesion_abierta_lo_saca_en_el_siguiente_clic(): void
    {
        $mesero = User::factory()->rol('mesero')->create();
        $this->actingAs($mesero)->get(route('mesero.index'))->assertOk();

        $mesero->update(['activo' => false]);

        $this->get(route('mesero.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_salir_cierra_la_sesion(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_despues_de_6_intentos_en_un_minuto_se_bloquea(): void
    {
        $usuario = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'mala']);
        }

        $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'password'])
            ->assertStatus(429);
    }

    // ---------- Permisos por rol ----------

    public function test_mesero_no_puede_entrar_a_admin(): void
    {
        $this->actingAs(User::factory()->rol('mesero')->create());

        $this->get(route('admin.index'))->assertForbidden();
        $this->get(route('admin.usuarios.index'))->assertForbidden();
        $this->get(route('cocina.index'))->assertForbidden();
        $this->get(route('mesero.index'))->assertOk();
    }

    public function test_el_admin_entra_a_todas_las_areas(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create());

        foreach (['admin.index', 'mesero.index', 'cocina.index', 'caja.index'] as $ruta) {
            $this->get(route($ruta))->assertOk();
        }
    }

    public function test_sin_sesion_las_areas_mandan_al_login_y_el_menu_es_publico(): void
    {
        foreach (['admin.index', 'mesero.index', 'cocina.index', 'caja.index'] as $ruta) {
            $this->get(route($ruta))->assertRedirect(route('login'));
        }

        $this->get(route('menu.index'))->assertOk();
    }

    public function test_la_barra_solo_muestra_el_area_del_rol(): void
    {
        $this->actingAs(User::factory()->rol('mesero')->create(['name' => 'Ana Mesera']));

        $this->get(route('mesero.index'))
            ->assertOk()
            ->assertSee('Ana Mesera')
            ->assertSee('Salir')
            ->assertDontSee(route('admin.index'))
            ->assertDontSee(route('caja.index'));
    }

    // ---------- Administración de usuarios ----------

    public function test_el_admin_crea_un_usuario_y_su_contrasena_queda_cifrada(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create());

        $this->post(route('admin.usuarios.store'), [
            'name' => 'María López',
            'email' => 'Maria@Tabla.test',
            'rol' => 'caja',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
        ])->assertRedirect(route('admin.usuarios.index'))->assertSessionHas('success');

        $usuario = User::where('email', 'maria@tabla.test')->firstOrFail();
        $this->assertSame('caja', $usuario->rol);
        $this->assertTrue($usuario->activo);
        $this->assertNotSame('secreta123', $usuario->password);
        $this->assertTrue(Hash::check('secreta123', $usuario->password));
    }

    public function test_correo_repetido_y_contrasena_corta_no_se_aceptan(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create());
        User::factory()->create(['email' => 'repetido@tabla.test']);

        $this->post(route('admin.usuarios.store'), [
            'name' => 'Otro',
            'email' => 'repetido@tabla.test',
            'rol' => 'mesero',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->post(route('admin.usuarios.store'), [
            'name' => 'Otro',
            'email' => 'otro@tabla.test',
            'rol' => 'jefe',
            'password' => 'secreta123',
            'password_confirmation' => 'diferente',
        ])->assertSessionHasErrors(['rol', 'password']);
    }

    public function test_el_admin_desactiva_y_reactiva_un_usuario(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create());
        $mesero = User::factory()->rol('mesero')->create();

        $this->patch(route('admin.usuarios.activo', $mesero))->assertSessionHas('success');
        $this->assertFalse($mesero->fresh()->activo);

        $this->patch(route('admin.usuarios.activo', $mesero));
        $this->assertTrue($mesero->fresh()->activo);
    }

    public function test_el_admin_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $admin = User::factory()->rol('admin')->create();
        $this->actingAs($admin);

        $this->patch(route('admin.usuarios.activo', $admin))->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->activo);

        $this->put(route('admin.usuarios.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'rol' => 'mesero',
        ])->assertSessionHasErrors('rol');
        $this->assertSame('admin', $admin->fresh()->rol);
    }

    public function test_editar_sin_contrasena_deja_la_anterior(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create());
        $usuario = User::factory()->rol('mesero')->create(['email' => 'ana@tabla.test']);
        $contrasenaAnterior = $usuario->password;

        $this->put(route('admin.usuarios.update', $usuario), [
            'name' => 'Ana Pérez', 'email' => 'ana@tabla.test', 'rol' => 'cocina',
            'password' => '', 'password_confirmation' => '',
        ])->assertRedirect(route('admin.usuarios.index'));

        $usuario->refresh();
        $this->assertSame('Ana Pérez', $usuario->name);
        $this->assertSame('cocina', $usuario->rol);
        $this->assertSame($contrasenaAnterior, $usuario->password);
    }

    public function test_listado_busca_por_nombre_y_filtra_por_rol(): void
    {
        $this->actingAs(User::factory()->rol('admin')->create(['name' => 'Jefe']));
        User::factory()->rol('mesero')->create(['name' => 'Carlos Mesero']);
        User::factory()->rol('cocina')->create(['name' => 'Lucía Cocinera']);

        $this->get(route('admin.usuarios.index', ['buscar' => 'carlos']))
            ->assertOk()->assertSee('Carlos Mesero')->assertDontSee('Lucía Cocinera');

        $this->get(route('admin.usuarios.index', ['rol' => 'cocina']))
            ->assertOk()->assertSee('Lucía Cocinera')->assertDontSee('Carlos Mesero');

        $this->get(route('admin.usuarios.create'))->assertOk();
    }
}
