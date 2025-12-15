<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    // Definimos la tabla por seguridad (opcional si sigue convención plural)
    protected $table = 'documentos';

    // Permitimos asignación masiva para estos campos
    protected $fillable = [
        'nombre',
        'ruta_archivo',  // El nombre real que vimos en la migración
        'categoria',
        'tipo_mime',
        'subido_por_id'  // Tu llave foránea personalizada
    ];

    /**
     * Relación: Un documento pertenece a un Usuario.
     */
    public function user()
    {
        // AQUÍ ESTÁ EL TRUCO PARA ARREGLAR EL ERROR 500:
        // El segundo parámetro ('subido_por_id') le dice a Laravel:
        // "No busques 'user_id', busca 'subido_por_id'".
        return $this->belongsTo(User::class, 'subido_por_id');
    }
}
