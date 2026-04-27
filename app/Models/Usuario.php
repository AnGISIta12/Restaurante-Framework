<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['nombre', 'clave', 'fecha_clave'];

    protected $hidden = ['clave'];

    public function getAuthIdentifierName(): string { return 'id'; }
    public function getAuthPassword(): string { return $this->clave; }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'actuaciones', 'usuario_id', 'rol_id');
    }

    public function pedidosComoCliente(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id', 'id');
    }

    public function pedidosComoMesero(): HasMany
    {
        return $this->hasMany(Pedido::class, 'mesero_id', 'id');
    }

    public function reservaciones(): HasMany
    {
        return $this->hasMany(Reservacion::class, 'cliente_id', 'id');
    }

    public function getRolNombre(): string
    {
        return $this->roles()->value('nombre') ?? 'Sin rol';
    }

    public function tieneRol(string $rol): bool
    {
        return $this->roles()
            ->whereRaw('LOWER(roles.nombre) = ?', [strtolower($rol)])
            ->exists();
    }
}