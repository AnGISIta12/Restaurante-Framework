<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preparacion extends Model
{
    protected $table      = 'preparaciones';
    protected $primaryKey = 'id_preparacion';
    public    $timestamps = false;

    protected $fillable = ['cocinero_id', 'orden_id'];

    public function cocinero(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cocinero_id', 'id_usuario');
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id_orden');
    }
}