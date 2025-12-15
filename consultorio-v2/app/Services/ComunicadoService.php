<?php

namespace App\Services;

use App\DTOs\ComunicadoData;
use App\Models\Comunicado;

class ComunicadoService
{
    // RF3: Publicar comunicados
    public function publicar(ComunicadoData $data): Comunicado
    {
        return Comunicado::create([
            'titulo' => $data->titulo,
            'contenido' => $data->contenido,
            'publicado_por_id' => $data->publicadoPorId,
            'es_activo' => true
        ]);
    }

    // RF3: Visualizar comunicados vigentes
    public function listarVigentes()
    {
        return Comunicado::with(['autor:id,name,cargo', 'evento'])
            ->where('es_activo', true)
            ->latest()
            ->get();
    }
}
