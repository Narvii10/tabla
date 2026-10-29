<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'rol', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Roles disponibles (valor guardado => texto en pantalla).
     */
    public const ROLES = [
        'admin' => 'Administrador',
        'mesero' => 'Mesero',
        'cocina' => 'Cocina',
        'caja' => 'Caja',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Pedidos abiertos por este usuario (mesero).
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'mesero_id');
    }

    /**
     * Pagos registrados por este usuario (caja).
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cajero_id');
    }

    /**
     * Cierres de caja hechos por este usuario.
     */
    public function cierresCaja(): HasMany
    {
        return $this->hasMany(CierreCaja::class, 'cajero_id');
    }
}
