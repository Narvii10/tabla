<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Caja\CajaController;
use App\Http\Controllers\Caja\CierreCajaController;
use App\Http\Controllers\Caja\PagoController;
use App\Http\Controllers\Cocina\CocinaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Mesero\DetallePedidoController;
use App\Http\Controllers\Mesero\MapaMesasController;
use App\Http\Controllers\Mesero\PedidoController;
use Illuminate\Support\Facades\Route;

/*
| Cada grupo pide sesión (auth) y un rol (rol:...). Sin sesión, manda al login;
| con un rol que no corresponde, responde 403. El menú público no pide sesión.
| Cada módulo agrega sus rutas dentro del grupo de su área.
*/

// Login: en "/" y en "/login". Si ya entró, lo manda a su área.
Route::get('/', [LoginController::class, 'create'])->name('inicio');
Route::get('/login', [LoginController::class, 'create'])->name('login');
// Máximo 6 intentos por minuto
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Administración (rol: admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'rol:admin'])->group(function () {
    Route::view('/', 'admin.index')->name('index');

    // Menú
    Route::resource('categorias', CategoriaController::class)->except('show');
    Route::resource('productos', ProductoController::class)->except('show');
    Route::patch('productos/{producto}/disponible', [ProductoController::class, 'alternarDisponible'])
        ->name('productos.disponible');

    // Mesas
    Route::get('mesas/imprimir', [MesaController::class, 'imprimir'])->name('mesas.imprimir');
    Route::resource('mesas', MesaController::class)->except('show');
    Route::patch('mesas/{mesa}/qr', [MesaController::class, 'regenerarQr'])->name('mesas.qr');

    // Usuarios (no se borran: se desactivan)
    Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
    Route::patch('usuarios/{usuario}/activo', [UsuarioController::class, 'alternarActivo'])->name('usuarios.activo');
});

// Mesero (roles: mesero, admin)
Route::prefix('mesero')->name('mesero.')->middleware(['auth', 'rol:mesero,admin'])->group(function () {
    // Mesas: el mapa es la pantalla de inicio del mesero
    Route::get('/', [MapaMesasController::class, 'index'])->name('index');
    Route::get('mesas/datos', [MapaMesasController::class, 'datos'])->name('mesas.datos');

    // Pedidos: mesas/{mesa}/pedido es la ruta a la que enlaza el mapa de mesas
    Route::get('mesas/{mesa}/pedido', [PedidoController::class, 'mesa'])->name('mesas.pedido');
    Route::post('mesas/{mesa}/pedido', [PedidoController::class, 'abrir'])->name('mesas.pedido.abrir');
    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::post('pedidos/{pedido}/enviar', [PedidoController::class, 'enviarACocina'])->name('pedidos.enviar');
    Route::patch('pedidos/{pedido}/cuenta', [PedidoController::class, 'pedirCuenta'])->name('pedidos.cuenta');
    Route::patch('pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    Route::patch('comandas/{comanda}/entregada', [PedidoController::class, 'marcarEntregada'])->name('comandas.entregada');
    Route::post('pedidos/{pedido}/detalles', [DetallePedidoController::class, 'store'])->name('pedidos.detalles.store');
    // scopeBindings: el detalle tiene que ser de ese pedido, si no responde 404
    Route::delete('pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'destroy'])
        ->scopeBindings()->name('pedidos.detalles.destroy');
});

// Cocina (roles: cocina, admin)
Route::prefix('cocina')->name('cocina.')->middleware(['auth', 'rol:cocina,admin'])->group(function () {
    Route::get('/', [CocinaController::class, 'index'])->name('index');
    Route::get('comandas/datos', [CocinaController::class, 'datos'])->name('comandas.datos');
    Route::patch('comandas/{comanda}/estado', [CocinaController::class, 'cambiarEstado'])->name('comandas.estado');
    Route::patch('productos/{producto}/agotado', [CocinaController::class, 'marcarAgotado'])->name('productos.agotado');
});

// Caja (roles: caja, admin)
Route::prefix('caja')->name('caja.')->middleware(['auth', 'rol:caja,admin'])->group(function () {
    Route::get('/', [CajaController::class, 'index'])->name('index');
    Route::get('pedidos/{pedido}', [CajaController::class, 'show'])->name('pedidos.show');
    Route::patch('pedidos/{pedido}/propina', [CajaController::class, 'actualizarPropina'])->name('pedidos.propina');
    Route::post('pedidos/{pedido}/pagos', [PagoController::class, 'store'])->name('pedidos.pagos.store');
    Route::patch('pedidos/{pedido}/cerrar', [CajaController::class, 'cerrar'])->name('pedidos.cerrar');
    Route::get('cierre', [CierreCajaController::class, 'create'])->name('cierre.create');
    Route::post('cierre', [CierreCajaController::class, 'store'])->name('cierre.store');
});

// Menú público (sin login)
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/mesa/{token_qr}', [MenuController::class, 'mesa'])->name('mesa');
});
