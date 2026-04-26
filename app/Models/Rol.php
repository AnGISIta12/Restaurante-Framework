<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @brief Modelo de rol del sistema.
 *
 * Mapea la tabla 'roles'.
 * Roles posibles: Administrador, Maitre, Mesero, Cocinero, Cliente.
 */
class Rol extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id_rol';
    public    $timestamps = false;

    protected $fillable = ['nombre'];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Usuarios que tienen este rol (many-to-many via actuaciones).
     * @return BelongsToMany
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'actuaciones',
            'rol_id',
            'usuario_id'
        );
    }

    /*------------------------------------------------------------------
     | Constantes de roles para evitar strings sueltos en el código
     ------------------------------------------------------------------*/
    const ADMINISTRADOR = 'Administrador';
    const MAITRE        = 'Maitre';
    const MESERO        = 'Mesero';
    const COCINERO      = 'Cocinero';
    const CLIENTE       = 'Cliente';
}