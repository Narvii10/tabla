<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\DesarrolloController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Mesero\MapaMesasController;
use Illuminate\Support\Facades\Route;

/*
| Mientras el login no esté listo, los grupos no llevan middleware.
| El módulo de Login agrega ->middleware(['auth', 'rol:...']) a cada grupo.
| Cada módulo agrega sus rutas dentro del grupo de su área.
*/

// Página de desarrollo con enlaces a cada área
Route::get('/', DesarrolloController::class)->name('inicio');

// Administración (rol: admin)
Route::prefix('admin')->name('admin.')->group(function () {
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
});

// Mesero (roles: mesero, admin)
Route::prefix('mesero')->name('mesero.')->group(function () {
    // Mesas: el mapa es la pantalla de inicio del mesero
    Route::get('/', [MapaMesasController::class, 'index'])->name('index');
    Route::get('mesas/datos', [MapaMesasController::class, 'datos'])->name('mesas.datos');
});

// Cocina (roles: cocina, admin)
Route::prefix('cocina')->name('cocina.')->group(function () {
    Route::view('/', 'cocina.index')->name('index');
});

// Caja (roles: caja, admin)
Route::prefix('caja')->name('caja.')->group(function () {
    Route::view('/', 'caja.index')->name('index');
});

// Menú público (sin login)
Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/mesa/{token_qr}', [MenuController::class, 'mesa'])->name('mesa');
});
