<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orden extends Model
{
    protected $table      = 'ordenes';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['plato_id', 'pedido_id', 'estado', 'cantidad', 'solicitado'];

    protected $casts = [
        'estado'     => 'integer',
        'cantidad'   => 'integer',
        'solicitado' => 'datetime',
    ];

    const ESTADO_PENDIENTE      = 0;
    const ESTADO_EN_PREPARACION = 1;
    const ESTADO_LISTA          = 2;
    const ESTADO_ENTREGADA      = 3;

    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'plato_id', 'id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id');
    }

    public function scopeEnCocina($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PREPARACION]);
    }

    public function scopeListas($query)
    {
        return $query->where('estado', self::ESTADO_LISTA);
    }

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

    public function getSubtotal(): float
    {
        return (float) ($this->plato->precio ?? 0) * $this->cantidad;
    }
}