<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @brief Modelo de orden (ítem individual dentro de un pedido).
 *
 * Mapea la tabla 'ordenes'.
 *
 * Estados:
 *   0 = Pendiente       (recién agregada)
 *   1 = En preparación  (cocinero la tomó)
 *   2 = Lista           (cocinero la marcó lista)
 *   3 = Entregada       (mesero la entregó a la mesa)
 */
class Orden extends Model
{
    protected $table      = 'ordenes';
    protected $primaryKey = 'id_orden';
    public    $timestamps = false;

    protected $fillable = [
        'plato_id',
        'pedido_id',
        'estado',
        'cantidad',
        'solicitado',   // timestamp de cuándo se agregó al pedido
    ];

    protected $casts = [
        'estado'     => 'integer',
        'cantidad'   => 'integer',
        'solicitado' => 'datetime',
    ];

    /*------------------------------------------------------------------
     | Constantes de estado
     ------------------------------------------------------------------*/
    const ESTADO_PENDIENTE      = 0;
    const ESTADO_EN_PREPARACION = 1;
    const ESTADO_LISTA          = 2;
    const ESTADO_ENTREGADA      = 3;

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Plato pedido en esta orden.
     * @return BelongsTo
     */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'plato_id', 'id_plato');
    }

    /**
     * @brief Pedido al que pertenece esta orden.
     * @return BelongsTo
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id_pedido');
    }

    /*------------------------------------------------------------------
     | Scopes
     ------------------------------------------------------------------*/

    /**
     * @brief Órdenes activas en cocina (pendiente o en preparación).
     */
    public function scopeEnCocina($query)
    {
        return $query->whereIn('estado', [
            self::ESTADO_PENDIENTE,
            self::ESTADO_EN_PREPARACION,
        ]);
    }

    /**
     * @brief Órdenes listas para entregar.
     */
    public function scopeListas($query)
    {
        return $query->where('estado', self::ESTADO_LISTA);
    }

    /**
     * @brief Órdenes aún no entregadas.
     */
    public function scopeNoEntregadas($query)
    {
        return $query->where('estado', '<', self::ESTADO_ENTREGADA);
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
            self::ESTADO_PENDIENTE      => 'Pendiente',
            self::ESTADO_EN_PREPARACION => 'En preparación',
            self::ESTADO_LISTA          => 'Lista',
            self::ESTADO_ENTREGADA      => 'Entregada',
            default                     => "Estado {$this->estado}",
        };
    }

    /**
     * @brief Subtotal de esta orden (precio × cantidad).
     * @return float
     */
    public function getSubtotal(): float
    {
        return (float) ($this->plato->precio ?? 0) * $this->cantidad;
    }
}