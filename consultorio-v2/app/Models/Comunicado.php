<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comunicado extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'contenido', 'publicado_por_id', 'es_activo'];

    public function autor()
    {
        return $this->belongsTo(User::class, 'publicado_por_id');
    }

    public function evento()
    {
        return $this->hasOne(\App\Models\Evento::class);
    }
}
