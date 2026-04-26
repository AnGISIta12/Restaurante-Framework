<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @brief Modelo de mesa del restaurante.
 *
 * Mapea la tabla 'mesas'.
 * Cada mesa tiene un número de sillas y puede tener
 * múltiples horarios (reservaciones asignadas).
 */
class Mesa extends Model
{
    protected $table      = 'mesas';
    protected $primaryKey = 'id_mesa';
    public    $timestamps = false;

    protected $fillable = ['sillas'];

    protected $casts = [
        'sillas' => 'integer',
    ];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Horarios (reservaciones asignadas) a esta mesa.
     * @return HasMany
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'mesa_id', 'id_mesa');
    }

    /*------------------------------------------------------------------
     | Scopes
     ------------------------------------------------------------------*/

    /**
     * @brief Mesas disponibles en este momento (sin horario activo ahora).
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisponibles($query)
    {
        return $query->whereNotIn('id_mesa', function ($sub) {
            $sub->select('mesa_id')
                ->from('horarios')
                ->whereRaw("inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'");
        });
    }

    /**
     * @brief Mesas con capacidad suficiente para N personas.
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $personas
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConCapacidad($query, int $personas)
    {
        return $query->where('sillas', '>=', $personas);
    }

    /*------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------*/

    /**
     * @brief Suma total de sillas de todas las mesas.
     * @return int
     */
    public static function capacidadTotal(): int
    {
        return (int) static::sum('sillas');
    }
}