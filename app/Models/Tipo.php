<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @brief Modelo de tipo/categoría de plato.
 *
 * Mapea la tabla 'tipos'.
 * Ejemplos: entrada, plato fuerte, bebida, postre.
 */
class Tipo extends Model
{
    protected $table      = 'tipos';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['nombre'];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Platos que pertenecen a este tipo.
     * @return HasMany
     */
    public function platos(): HasMany
    {
        return $this->hasMany(Plato::class, 'tipo_id', 'id');
    }
}