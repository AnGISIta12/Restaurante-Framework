<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @brief Modelo de horario (asignación de mesa a reservación).
 *
 * Mapea la tabla 'horarios'.
 * Un horario vincula una Mesa con una Reservacion,
 * registrando el momento de inicio y la duración.
 */
class Horario extends Model
{
    protected $table      = 'horarios';
    protected $primaryKey = 'id_horario';
    public    $timestamps = false;

    protected $fillable = [
        'mesa_id',
        'reservacion_id',
        'inicio',
        'duracion',
    ];

    protected $casts = [
        'inicio' => 'datetime',
    ];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Mesa asignada en este horario.
     * @return BelongsTo
     */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id', 'id_mesa');
    }

    /**
     * @brief Reservación a la que pertenece este horario.
     * @return BelongsTo
     */
    public function reservacion(): BelongsTo
    {
        return $this->belongsTo(Reservacion::class, 'reservacion_id', 'id_reservacion');
    }

    /*------------------------------------------------------------------
     | Scopes
     ------------------------------------------------------------------*/

    /**
     * @brief Horarios activos ahora mismo (ventana de ±2 horas).
     */
    public function scopeActivos($query)
    {
        return $query->whereRaw(
            "inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'"
        );
    }

    /**
     * @brief Horarios de hoy.
     */
    public function scopeHoy($query)
    {
        return $query->whereRaw(
            "inicio BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '1 day'"
        );
    }

    /**
     * @brief Horarios que comienzan en los próximos N minutos.
     * @param  int $minutos  Default 30
     */
    public function scopeProximos($query, int $minutos = 30)
    {
        return $query->whereRaw(
            "inicio BETWEEN NOW() AND NOW() + INTERVAL '{$minutos} minutes'"
        );
    }
}