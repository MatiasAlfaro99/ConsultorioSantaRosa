<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventoService
{
    /**
     * Listar eventos para el calendario
     */
    public function listarEventos()
    {
        return Evento::with('usuario')
            ->orderBy('fecha_inicio', 'asc')
            ->get()
            ->map(function ($evento) {
                // Formateamos para FullCalendar o tu frontend
                return [
                    'id' => $evento->id,
                    'title' => $evento->titulo,
                    'start' => $evento->fecha_inicio->toIso8601String(),
                    'end' => $evento->fecha_fin ? $evento->fecha_fin->toIso8601String() : null,
                    'description' => $evento->descripcion,
                    'color' => $evento->color ?? '#3B82F6', // Azul por defecto
                    'comunicado_id' => $evento->comunicado_id, // Para saber si viene de sync
                ];
            });
    }

    /**
     * Crear evento manual (Desde el Calendario)
     */
    public function crearEvento(array $datos)
    {
        return Evento::create([
            'titulo'       => $datos['titulo'],
            'descripcion'  => $datos['descripcion'] ?? null,
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin'    => $datos['fecha_fin'] ?? null,
            'lugar'        => $datos['lugar'] ?? null,
            'color'        => $datos['color'] ?? '#3B82F6',
            'user_id'      => Auth::id(),
        ]);
    }

    /**
     * SINCRONIZACIÓN: Crear evento automático desde Comunicado
     */
    public function crearEventoDesdeComunicado(array $datos)
    {
        // Esta función es la que llama tu ComunicadoController
        return Evento::create([
            'titulo'        => $datos['titulo'],
            // Usamos el contenido del comunicado como descripción
            'descripcion'   => strip_tags($datos['descripcion'] ?? ''), 
            'fecha_inicio'  => $datos['fecha_inicio'],
            'fecha_fin'     => $datos['fecha_fin'],
            'lugar'         => 'Intranet', // Por defecto si viene de comunicado
            'color'         => '#EF4444', // Rojo para destacar que es Importante/Comunicado
            'user_id'       => $datos['creado_por_id'],
            'comunicado_id' => $datos['comunicado_id'], // <--- VINCULACIÓN
        ]);
    }

    public function eliminar($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();
    }
}
