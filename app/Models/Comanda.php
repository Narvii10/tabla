<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comanda: una ronda de productos enviada a cocina.
 */
#[Fillable(['pedido_id', 'enviada_en', 'estado'])]
class Comanda extends Model
{
    protected $table = 'comandas';

    /**
     * Estados de una comanda (valor guardado => texto en pantalla).
     */
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'preparando' => 'Preparando',
        'listo' => 'Listo',
        'entregado' => 'Entregado',
    ];

    protected function casts(): array
    {
        return [
            'enviada_en' => 'datetime',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}
