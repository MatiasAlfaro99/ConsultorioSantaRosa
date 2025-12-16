<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comunicado extends Model
{
    use HasFactory;

    protected $table = 'comunicados';

    protected $fillable = [
        'titulo',
        'contenido',
        'publicado_por_id', // <--- CORREGIDO (Antes decía user_id)
        'es_activo',        // <--- AGREGADO (Existe en tu tabla)
        // 'es_importante', <--- QUITADO (No existe en tu tabla)
        // 'fecha_publicacion' <--- QUITADO (No existe en tu tabla)
    ];

    protected $casts = [
        'es_activo' => 'boolean',
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'publicado_por_id');
    }
}
