<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * 8 mesas libres. El token_qr se genera aquí porque DatabaseSeeder
     * desactiva los eventos de los modelos (WithoutModelEvents).
     */
    public function run(): void
    {
        for ($numero = 1; $numero <= 8; $numero++) {
            Mesa::create([
                'numero' => $numero,
                'capacidad' => $numero <= 4 ? 4 : 2,
                'zona' => $numero <= 5 ? 'Salón' : 'Terraza',
                'token_qr' => Mesa::generarTokenQr(),
                'estado' => 'libre',
            ]);
        }
    }
}
