<?php

namespace App\Services;

use App\Models\Comunicado;

class ComunicadoService
{
    public function listarVigentes()
    {
        // Traemos todos, ordenados por fecha
        return Comunicado::with('autor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function publicar(array $datos)
    {
        return Comunicado::create([
            'titulo' => $datos['titulo'],
            'contenido' => $datos['contenido'],
            'es_importante' => $datos['es_importante'] ?? false,
            'user_id' => $datos['publicado_por_id'],
            'fecha_publicacion' => now(),
        ]);
    }

    public function eliminar($id)
    {
        $comunicado = Comunicado::findOrFail($id);
        $comunicado->delete();
    }
}
