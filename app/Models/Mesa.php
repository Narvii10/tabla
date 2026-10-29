<?php

namespace App\Models;

use Database\Factories\MesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['numero', 'capacidad', 'zona', 'token_qr', 'estado'])]
class Mesa extends Model
{
    /** @use HasFactory<MesaFactory> */
    use HasFactory;

    protected $table = 'mesas';

    /**
     * Estados de una mesa (valor guardado => texto en pantalla).
     */
    public const ESTADOS = [
        'libre' => 'Libre',
        'ocupada' => 'Ocupada',
        'esperando' => 'Esperando',
        'comiendo' => 'Comiendo',
        'cuenta' => 'Pidió la cuenta',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'capacidad' => 'integer',
        ];
    }

    /**
     * Si la mesa se crea sin token_qr, se genera uno aleatorio y único.
     */
    protected static function booted(): void
    {
        static::creating(function (Mesa $mesa) {
            $mesa->token_qr ??= static::generarTokenQr();
        });
    }

    public static function generarTokenQr(): string
    {
        do {
            $token = Str::random(32);
        } while (static::where('token_qr', $token)->exists());

        return $token;
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * El pedido abierto actual de la mesa (si hay).
     * El filtro va dentro de ofMany para que busque el más reciente entre los abiertos;
     * con ->where(...)->latestOfMany() tomaba el más reciente de todos y, si ese ya
     * estaba cerrado, la mesa parecía no tener pedido abierto.
     */
    public function pedidoAbierto(): HasOne
    {
        return $this->hasOne(Pedido::class)->ofMany(['id' => 'max'], function ($query) {
            $query->where('estado', 'abierto');
        });
    }
}
