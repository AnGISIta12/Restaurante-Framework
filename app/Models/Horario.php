<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table      = 'horarios';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['mesa_id', 'reservacion_id', 'inicio', 'duracion'];

    protected $casts = ['inicio' => 'datetime'];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id', 'id');
    }

    public function reservacion(): BelongsTo
    {
        return $this->belongsTo(Reservacion::class, 'reservacion_id', 'id');
    }

    public function scopeActivos($query)
    {
        return $query->whereRaw(
            "inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'"
        );
    }

    public function scopeHoy($query)
    {
        return $query->whereRaw(
            "inicio BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '1 day'"
        );
    }

    public function scopeProximos($query, int $minutos = 30)
    {
        return $query->whereRaw(
            "inicio BETWEEN NOW() AND NOW() + INTERVAL '{$minutos} minutes'"
        );
    }
}