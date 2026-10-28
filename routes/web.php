<?php

use App\Http\Controllers\DesarrolloController;
use App\Models\Mesa;
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
});

// Mesero (roles: mesero, admin)
Route::prefix('mesero')->name('mesero.')->group(function () {
    Route::view('/', 'mesero.index')->name('index');
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
    Route::view('/', 'menu.index')->name('index');

    // Provisional: el módulo de Menú lo reemplaza por su controlador
    Route::get('/mesa/{token_qr}', function (string $tokenQr) {
        $mesa = Mesa::where('token_qr', $tokenQr)->firstOrFail();

        return view('menu.index', ['mesa' => $mesa]);
    })->name('mesa');
});
