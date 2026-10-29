<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'categoria_id', 'nombre', 'descripcion', 'precio', 'costo', 'foto',
    'disponible', 'es_vino', 'vino_sugerido_id',
])]
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected $table = 'productos';

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'costo' => 'decimal:2',
            'disponible' => 'boolean',
            'es_vino' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Vino sugerido para este plato (maridaje).
     */
    public function vinoSugerido(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'vino_sugerido_id');
    }

    /**
     * Platos que sugieren este vino.
     */
    public function platosSugeridos(): HasMany
    {
        return $this->hasMany(Producto::class, 'vino_sugerido_id');
    }

    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    /**
     * Productos que no están agotados.
     */
    public function scopeDisponibles(Builder $query): void
    {
        $query->where('disponible', true);
    }

    /**
     * Solo vinos (para elegir el vino sugerido).
     */
    public function scopeVinos(Builder $query): void
    {
        $query->where('es_vino', true);
    }
}
