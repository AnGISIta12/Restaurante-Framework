<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @brief Modelo de reservación del restaurante.
 *
 * Mapea la tabla 'reservaciones'.
 *
 * Estados:
 *   0 = Pendiente  (recién creada, sin mesa asignada)
 *   1 = Confirmada (el maitre la confirmó)
 *   2 = Asignada   (tiene mesa y horario asignados)
 */
class Reservacion extends Model
{
    protected $table      = 'reservaciones';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'cantidad',
        'estado',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'estado'   => 'integer',
    ];

    /*------------------------------------------------------------------
     | Constantes de estado
     ------------------------------------------------------------------*/
    const ESTADO_PENDIENTE  = 0;
    const ESTADO_CONFIRMADA = 1;
    const ESTADO_ASIGNADA   = 2;

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Cliente que hizo la reservación.
     * @return BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'id');
    }

    /**
     * @brief Horario asignado a esta reservación (si tiene mesa).
     * @return HasOne
     */
    public function horario(): HasOne
    {
        return $this->hasOne(Horario::class, 'reservacion_id', 'id');
    }

    /*------------------------------------------------------------------
     | Scopes
     ------------------------------------------------------------------*/

    /**
     * @brief Reservaciones pendientes o confirmadas (sin mesa aún).
     */
    public function scopeSinAsignar($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADA]);
    }

    /**
     * @brief Reservaciones próximas (con horario futuro).
     */
    public function scopeProximas($query)
    {
        return $query->whereHas('horario', function ($q) {
            $q->where('inicio', '>=', now());
        });
    }

    /*------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------*/

    /**
     * @brief Etiqueta legible del estado actual.
     * @return string
     */
    public function getEtiquetaEstado(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE  => 'Pendiente',
            self::ESTADO_CONFIRMADA => 'Confirmada',
            self::ESTADO_ASIGNADA   => 'Asignada',
            default                 => "Estado {$this->estado}",
        };
    }
}