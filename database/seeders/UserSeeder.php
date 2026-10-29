<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Un usuario por rol. Contraseña de todos: password
     */
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Administrador', 'email' => 'admin@tabla.test', 'rol' => 'admin'],
            ['name' => 'Mesero', 'email' => 'mesero@tabla.test', 'rol' => 'mesero'],
            ['name' => 'Cocina', 'email' => 'cocina@tabla.test', 'rol' => 'cocina'],
            ['name' => 'Caja', 'email' => 'caja@tabla.test', 'rol' => 'caja'],
        ];

        foreach ($usuarios as $usuario) {
            // El cast "hashed" del modelo cifra la contraseña al guardarla
            User::create($usuario + [
                'password' => 'password',
                'activo' => true,
            ]);
        }
    }
}
