<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @brief Modelo de plato del menú.
 *
 * Mapea la tabla 'platos'.
 * Cada plato pertenece a un Tipo y puede aparecer
 * en múltiples Ordenes.
 */
class Plato extends Model
{
    protected $table      = 'platos';
    protected $primaryKey = 'id_plato';
    public    $timestamps = false;

    protected $fillable = [
        'nombre',
        'precio',
        'descripcion',
        'tipo_id',
        'tiempo',       // tiempo de preparación (interval de PostgreSQL, ej: "00:30:00")
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Tipo/categoría al que pertenece el plato.
     * @return BelongsTo
     */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(Tipo::class, 'tipo_id', 'id');
    }

    /**
     * @brief Órdenes en las que aparece este plato.
     * @return HasMany
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'plato_id', 'id_plato');
    }

    /*------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------*/

    /**
     * @brief Devuelve los minutos de preparación extraídos del campo 'tiempo'.
     * El campo viene como "HH:MM:SS" desde PostgreSQL interval.
     * @return int  Minutos de preparación, 0 si no está definido.
     */
    public function getMinutosPreparacion(): int
    {
        if (!$this->tiempo) {
            return 0;
        }
        // "00:30:00" → partes[1] = "30"
        $partes = explode(':', $this->tiempo);
        return isset($partes[1]) ? (int) $partes[1] : 0;
    }

    /**
     * @brief Precio formateado con 2 decimales.
     * @return string  Ej: "12.50"
     */
    public function getPrecioFormateado(): string
    {
        return number_format((float) $this->precio, 2);
    }
}