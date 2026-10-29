<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Mesero\MapaMesasController;
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
});

// Cocina (roles: cocina, admin)
Route::prefix('cocina')->name('cocina.')->middleware(['auth', 'rol:cocina,admin'])->group(function () {
    Route::view('/', 'cocina.index')->name('index');
});

// Caja (roles: caja, admin)
Route::prefix('caja')->name('caja.')->middleware(['auth', 'rol:caja,admin'])->group(function () {
    Route::view('/', 'caja.index')->name('index');
});

// Menú público (sin login)
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/mesa/{token_qr}', [MenuController::class, 'mesa'])->name('mesa');
});
