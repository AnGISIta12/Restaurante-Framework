<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $table      = 'mesas';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['sillas', 'estado', 'capacidad'];

    protected $casts = ['sillas' => 'integer', 'capacidad' => 'integer'];

    public function reservaciones(): HasMany
    {
        return $this->hasMany(Reservacion::class, 'mesa_id', 'id');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereNotIn('id', function ($sub) {
            $sub->select('mesa_id')
                ->from('reservaciones')
                ->whereNotNull('mesa_id')
                ->whereRaw("CAST(fecha || ' ' || hora AS TIMESTAMP) BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'");
        });
    }

    public function scopeConCapacidad($query, int $personas)
    {
        return $query->where('sillas', '>=', $personas);
    }

    public static function capacidadTotal(): int
    {
        return (int) static::sum('sillas');
    }
}