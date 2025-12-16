<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    // AQUÍ ESTÁ LA CLAVE: Debemos listar TODOS los campos de todas las migraciones
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'categoria',      // Viene de la migración del día 15
        'comunicado_id',  // Viene de la otra migración del día 15
        'user_id',
        'creado_por_id',         // Es probable que quieras saber quién creó el evento (opcional)
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    // Opcional: Si quieres acceder al comunicado desde el evento
    public function comunicado()
    {
        return $this->belongsTo(Comunicado::class);
    }
}
