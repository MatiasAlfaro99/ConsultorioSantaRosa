<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'funcionario_id',
        'creado_por_id',
        'fecha_inicio',
        'dias',
        'fecha_fin',
        'tipo_licencia',
        'archivo_path',
        'observacion'
    ];

    // Relación: Pertenece a un Funcionario
    public function funcionario()
    {
        return $this->belongsTo(User::class, 'funcionario_id');
    }

    // Relación: Fue subida por (Subdirector)
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}
