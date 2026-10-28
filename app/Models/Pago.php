<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pedido_id', 'cajero_id', 'metodo', 'monto', 'recibido', 'vuelto'])]
class Pago extends Model
{
    protected $table = 'pagos';

    /**
     * Métodos de pago (valor guardado => texto en pantalla).
     */
    public const METODOS = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'recibido' => 'decimal:2',
            'vuelto' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Usuario (caja) que registró el pago.
     */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }
}
