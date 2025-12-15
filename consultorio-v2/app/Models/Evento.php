<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo', 
        'descripcion', 
        'fecha_inicio', 
        'fecha_fin', 
        'lugar', 
        'creado_por_id',
        'comunicado_id',
        'categoria'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function comunicado()
    {
        return $this->belongsTo(\App\Models\Comunicado::class);
    }
}
