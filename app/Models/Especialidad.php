<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Especialidad extends Model
{
    protected $table      = 'especialidades';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['cocinero_id', 'plato_id'];

    public function cocinero(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cocinero_id', 'id');
    }

    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class, 'plato_id', 'id');
    }
}