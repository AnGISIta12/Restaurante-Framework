<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservacion extends Model
{
    protected $table      = 'reservaciones';
    protected $primaryKey = 'id_reservacion';
    public    $timestamps = false;

    protected $fillable = ['cliente_id', 'user_id', 'mesa_id', 'fecha', 'hora', 'cantidad_personas', 'cantidad', 'estado'];

    protected $casts = ['cantidad' => 'integer', 'cantidad_personas' => 'integer', 'estado' => 'integer'];

    const ESTADO_PENDIENTE  = 0;
    const ESTADO_CONFIRMADA = 1;
    const ESTADO_ASIGNADA   = 2;

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'id_usuario');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id', 'id');
    }

    public function scopeSinAsignar($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADA])
                     ->whereNull('mesa_id');
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