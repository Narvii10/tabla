<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cajero_id', 'fecha', 'efectivo_esperado', 'tarjeta_esperado', 'transferencia_esperado',
    'efectivo_contado', 'diferencia', 'notas',
])]
class CierreCaja extends Model
{
    // Laravel adivinaría "cierre_cajas"; por eso se indica el nombre
    protected $table = 'cierres_caja';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'efectivo_esperado' => 'decimal:2',
            'tarjeta_esperado' => 'decimal:2',
            'transferencia_esperado' => 'decimal:2',
            'efectivo_contado' => 'decimal:2',
            'diferencia' => 'decimal:2',
        ];
    }

    /**
     * Usuario (caja) que hizo el cierre.
     */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }
}
