<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plato extends Model
{
    protected $table      = 'platos';
    protected $primaryKey = 'id_plato';
    public    $timestamps = false;

    protected $fillable = ['nombre', 'precio', 'descripcion', 'tipo_id', 'tiempo'];

    protected $casts = ['precio' => 'decimal:2'];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(Tipo::class, 'tipo_id', 'id');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'plato_id', 'id_plato');
    }

    public function getMinutosPreparacion(): int
    {
        if (!$this->tiempo) return 0;
        $partes = explode(':', $this->tiempo);
        return isset($partes[1]) ? (int) $partes[1] : 0;
    }

    public function getPrecioFormateado(): string
    {
        return number_format((float) $this->precio, 2);
    }
}