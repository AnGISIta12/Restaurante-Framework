<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $table      = 'mesas';
    protected $primaryKey = 'id_mesa';
    public    $timestamps = false;

    protected $fillable = ['sillas'];

    protected $casts = ['sillas' => 'integer'];

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'mesa_id', 'id_mesa');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereNotIn('id_mesa', function ($sub) {
            $sub->select('mesa_id')
                ->from('horarios')
                ->whereRaw("inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'");
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