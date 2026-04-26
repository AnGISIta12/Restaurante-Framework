<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @brief Modelo de pedido (comanda).
 *
 * Mapea la tabla 'pedidos'.
 * Un pedido agrupa varias Ordenes (ítems), pertenece a un cliente
 * y es gestionado por un mesero.
 */
class Pedido extends Model
{
    protected $table      = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public    $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'mesero_id',
    ];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Cliente al que pertenece el pedido.
     * @return BelongsTo
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'id_usuario');
    }

    /**
     * @brief Mesero responsable del pedido.
     * @return BelongsTo
     */
    public function mesero(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'mesero_id', 'id_usuario');
    }

    /**
     * @brief Órdenes (ítems) que componen este pedido.
     * @return HasMany
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'pedido_id', 'id_pedido');
    }

    /*------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------*/

    /**
     * @brief Total monetario del pedido (suma de precio × cantidad de cada orden).
     * @return float
     */
    public function getTotal(): float
    {
        return (float) $this->ordenes()
            ->join('platos', 'ordenes.plato_id', '=', 'platos.id_plato')
            ->selectRaw('SUM(platos.precio * ordenes.cantidad) as total')
            ->value('total');
    }

    /**
     * @brief Número de ítems distintos en el pedido.
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->ordenes()->count();
    }
}