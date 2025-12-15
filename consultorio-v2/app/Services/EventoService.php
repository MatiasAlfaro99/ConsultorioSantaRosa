<?php

namespace App\Services;

use App\DTOs\EventoData;
use App\Models\Evento;

class EventoService
{
    public function crearEvento(EventoData $data): Evento
    {
        return Evento::create([
            'titulo' => $data->titulo,
            'descripcion' => $data->descripcion,
            'fecha_inicio' => $data->fechaInicio,
            'fecha_fin' => $data->fechaFin,
            'lugar' => $data->lugar,
            'creado_por_id' => $data->creadoPorId,
        ]);
    }

    public function listarProximos()
    {
        // Traemos las futuras ordenadas por fecha
        return Evento::where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio', 'asc')
            ->get();
    }
    public function actualizarEvento(string $id, EventoData $dto): Evento
    {
        $evento = Evento::findOrFail($id);

        $evento->update([
            'titulo'       => $dto->titulo,
            'descripcion'  => $dto->descripcion,
            'fecha_inicio' => $dto->fechaInicio,
            'fecha_fin'    => $dto->fechaFin,
            'lugar'        => $dto->lugar,
            // 'user_id' no lo actualizamos usualmente, pero depende de tu lógica
        ]);

        return $evento;
    }
    public function eliminarEvento(string $id): void
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();
    }
}
