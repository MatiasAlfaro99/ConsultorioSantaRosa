<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Aquí definimos qué recibe el Frontend exactamente
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            // Formateamos la fecha para que FullCalendar la entienda sin problemas (ISO 8601)
            'fecha_inicio' => $this->fecha_inicio ? $this->fecha_inicio->toIso8601String() : null,
            'fecha_fin' => $this->fecha_fin ? $this->fecha_fin->toIso8601String() : null,
            'categoria' => $this->categoria ?? 'reunion', // Valor por defecto si viene nulo
            'lugar' => $this->lugar,
            'created_at' => $this->created_at,
        ];
    }
}
