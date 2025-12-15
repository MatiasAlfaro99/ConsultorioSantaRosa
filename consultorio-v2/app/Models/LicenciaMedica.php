<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LicenciaMedica extends Model
{
    protected $table = 'licencias_medicas';

    protected $fillable = [
        'user_id',
        'subdirector_id',
        'fecha_inicio',
        'fecha_fin',
        'nombre_archivo',
        'observaciones',
        'estado',
    ];

    // Funcionario afectado
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Subdirector que registró
    public function registrador()
    {
        return $this->belongsTo(User::class, 'subdirector_id');
    }
}
