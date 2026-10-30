<?php

namespace Tests\Unit;

use App\Models\Pedido;
use PHPUnit\Framework\TestCase;

class DividirCuentaTest extends TestCase
{
    public function test_dividir_100_entre_3_suma_exacto(): void
    {
        $partes = Pedido::dividir(10000, 3);

        $this->assertSame([3334, 3333, 3333], $partes);
        $this->assertSame(10000, array_sum($partes));
    }

    public function test_con_menos_de_una_persona_no_se_divide(): void
    {
        $this->assertSame([10000], Pedido::dividir(10000, 0));
    }
}
