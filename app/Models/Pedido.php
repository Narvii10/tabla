<?php

namespace App\Models;

use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['mesa_id', 'mesero_id', 'estado', 'propina', 'abierto_en', 'cerrado_en'])]
class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $table = 'pedidos';

    /**
     * Estados de un pedido (valor guardado => texto en pantalla).
     */
    public const ESTADOS = [
        'abierto' => 'Abierto',
        'cerrado' => 'Cerrado',
        'cancelado' => 'Cancelado',
    ];

    protected function casts(): array
    {
        return [
            'propina' => 'decimal:2',
            'abierto_en' => 'datetime',
            'cerrado_en' => 'datetime',
        ];
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    /**
     * Usuario (mesero) que abrió el pedido.
     */
    public function mesero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mesero_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function comandas(): HasMany
    {
        return $this->hasMany(Comanda::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Si el pedido sigue abierto (Pedidos y Caja no tocan uno cerrado o cancelado).
     */
    public function estaAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    /**
     * Suma de cantidad * precio_unitario de todos los productos.
     */
    public function subtotal(): float
    {
        return round($this->detalles->sum(fn (DetallePedido $detalle) => $detalle->totalLinea()), 2);
    }

    /**
     * Subtotal más propina.
     */
    public function total(): float
    {
        return round($this->subtotal() + (float) $this->propina, 2);
    }

    /**
     * Lo que ya se pagó.
     */
    public function pagado(): float
    {
        return round((float) $this->pagos->sum('monto'), 2);
    }

    /**
     * Lo que falta por pagar.
     */
    public function saldo(): float
    {
        return round($this->total() - $this->pagado(), 2);
    }
}
