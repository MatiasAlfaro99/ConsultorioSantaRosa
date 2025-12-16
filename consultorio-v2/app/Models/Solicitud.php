<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'user_id',
        'tipo',
        'es_por_horas',
        'hora_inicio',
        'hora_fin',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'motivo',
        'estado',
        'jefe_aprobador_id',
        'fecha_aprobacion_jefe',
        'director_aprobador_id',
        'fecha_aprobacion_director',
        'razon_rechazo',
    ];

    // RELACIÓN ORIGINAL
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- AGREGA ESTAS RELACIONES PARA QUE FUNCIONE TU PDF ---

    // 1. Alias 'solicitante' (Lo usa tu PdfService en línea 42)
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 2. Relación Jefe (Lo usa tu PdfService en línea 146)
    public function jefeAprobador()
    {
        return $this->belongsTo(User::class, 'jefe_aprobador_id');
    }

    // 3. Relación Director (Lo usa tu PdfService en línea 155)
    public function directorAprobador()
    {
        return $this->belongsTo(User::class, 'director_aprobador_id');
    }
}
