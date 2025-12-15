<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    // Si tu tabla se llama 'solicitudes', no necesitas la propiedad $table
    // Pero si usaste 'SolicitudPermiso', asegúrate de definirla: protected $table = 'solicitudes_permiso';
    protected $table = 'solicitudes';
    protected $fillable = [
        'user_id',
        'tipo',
        'es_por_horas',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'dias_solicitados',
        'motivo',
        'estado',
        'razon_rechazo',
        'jefe_aprobador_id',
        'fecha_aprobacion_jefe',
        'director_aprobador_id',
        'fecha_aprobacion_director',
    ];

    // Relación con el solicitante (para cargar el nombre en MisSolicitudes.vue)
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias para PdfService
    public function funcionario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jefeAprobador()
    {
        return $this->belongsTo(User::class, 'jefe_aprobador_id');
    }

    public function directorAprobador()
    {
        return $this->belongsTo(User::class, 'director_aprobador_id');
    }
}
