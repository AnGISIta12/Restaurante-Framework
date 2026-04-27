<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservacion extends Model
{
    protected $table      = 'reservaciones';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['cliente_id', 'cantidad', 'estado'];

    protected $casts = ['cantidad' => 'integer', 'estado' => 'integer'];

    const ESTADO_PENDIENTE  = 0;
    const ESTADO_CONFIRMADA = 1;
    const ESTADO_ASIGNADA   = 2;

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'id');
    }

    public function horario(): HasOne
    {
        return $this->hasOne(Horario::class, 'reservacion_id', 'id');
    }

    public function scopeSinAsignar($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADA]);
    }

    public function scopeProximas($query)
    {
        return $query->whereHas('horario', function ($q) {
            $q->where('inicio', '>=', now());
        });
    }

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