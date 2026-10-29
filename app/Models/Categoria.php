<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'orden', 'activo'])]
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $table = 'categorias';

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Solo categorías activas: Categoria::activas()->get()
     */
    public function scopeActivas(Builder $query): void
    {
        $query->where('activo', true);
    }

    /**
     * Orden en que se muestran en el menú: Categoria::ordenadas()->get()
     */
    public function scopeOrdenadas(Builder $query): void
    {
        $query->orderBy('orden')->orderBy('nombre');
    }
}
