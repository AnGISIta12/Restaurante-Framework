<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table      = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public    $timestamps = false;

    protected $fillable = ['cliente_id', 'mesero_id'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'id_usuario');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'mesero_id', 'id_usuario');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'pedido_id', 'id_pedido');
    }

    public function getTotal(): float
    {
        return (float) $this->ordenes()
            ->join('platos', 'ordenes.plato_id', '=', 'platos.id_plato')
            ->selectRaw('SUM(platos.precio * ordenes.cantidad) as total')
            ->value('total');
    }
}