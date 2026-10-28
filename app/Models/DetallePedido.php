<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un producto dentro de un pedido (con su cantidad, precio y notas).
 */
#[Fillable(['pedido_id', 'comanda_id', 'producto_id', 'cantidad', 'precio_unitario', 'notas'])]
class DetallePedido extends Model
{
    // Laravel adivinaría "detalle_pedidos"; por eso se indica el nombre
    protected $table = 'detalles_pedido';

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Comanda en la que se envió (null si aún no se envía a cocina).
     */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * cantidad * precio_unitario
     */
    public function totalLinea(): float
    {
        return round($this->cantidad * (float) $this->precio_unitario, 2);
    }
}
