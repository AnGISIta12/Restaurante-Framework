<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id_rol';
    public    $timestamps = false;

    protected $fillable = ['nombre'];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'actuaciones',
            'rol_id',
            'usuario_id'
        );
    }

    const ADMINISTRADOR = 'Administrador';
    const MAITRE        = 'Maitre';
    const MESERO        = 'Mesero';
    const COCINERO      = 'Cocinero';
    const CLIENTE       = 'Cliente';
}