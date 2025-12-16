<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\User;
use App\DTOs\SolicitudData;
use Carbon\Carbon;

class SolicitudService
{
    /**
     * Listar solicitudes
     */
    public function listarParaUsuario(User $user)
    {
        $query = Solicitud::query();

        // 1. DIRECTOR
        if ($user->role === 'director') {
            $query->where('user_id', $user->id)
                  ->orWhere('estado', 'pendiente_direccion');

        // 2. JEFATURA
        } elseif ($user->role === 'jefatura') {
            $query->where('user_id', $user->id)
                  ->orWhere('estado', 'pendiente_jefatura');

        // 3. SUBDIRECCIÓN
        } elseif ($user->role === 'subdireccion') {
            $query->where('user_id', $user->id)
                  ->orWhereIn('estado', ['pendiente_jefatura', 'pendiente_direccion']);

        // 4. ADMIN
        } elseif ($user->role === 'admin') {
            // Ve todo

        // 5. FUNCIONARIO
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Crear solicitud
     */
    public function crear(SolicitudData $data): Solicitud
    {
        return Solicitud::create([
            'tipo'             => $data->tipo,
            'es_por_horas'     => $data->esPorHoras,
            'fecha_inicio'     => $data->fechaInicio,
            'fecha_fin'        => $data->fechaFin,
            'hora_inicio'      => $data->horaInicio,
            'hora_fin'         => $data->horaFin,
            'user_id'          => $data->userId,
            'dias_solicitados' => $data->diasSolicitados,
            'motivo'           => $data->motivo,
            'estado'           => 'pendiente_jefatura',
        ]);
    }

    /**
     * Aprobar Jefatura
     */
    public function aprobarJefe($id, User $aprobador): Solicitud
    {
        $solicitud = Solicitud::findOrFail($id);

        $solicitud->update([
            'estado' => 'pendiente_direccion',
            'jefe_aprobador_id' => $aprobador->id,
            'fecha_aprobacion_jefe' => Carbon::now(),
        ]);

        return $solicitud;
    }

    /**
     * Aprobar Director
     * CORRECCIÓN IMPORTANTE: Ahora recibe $id en lugar de objeto para compatibilidad con el Controller.
     */
    public function aprobarDirector($id, User $aprobador): Solicitud
    {
        // Buscamos la solicitud por ID primero
        $solicitud = Solicitud::findOrFail($id);

        $solicitud->update([
            'estado' => 'aprobado',
            'director_aprobador_id' => $aprobador->id,
            'fecha_aprobacion_director' => Carbon::now(),
        ]);

        return $solicitud;
    }

    /**
     * Rechazar
     */
    public function rechazar($id, string $razon): Solicitud
    {
        $solicitud = Solicitud::findOrFail($id);

        $solicitud->update([
            'estado' => 'rechazado',
            'razon_rechazo' => $razon,
        ]);

        return $solicitud;
    }

    /**
     * Aprobar Subdirector (Agregado por si tu controlador lo llama)
     */
    public function aprobarSubdirector($id, User $aprobador): Solicitud
    {
        $solicitud = Solicitud::findOrFail($id);

        $solicitud->update([
            'estado' => 'pendiente_direccion', // Asumo que pasa a dirección también
            // Si tienes columnas especificas para subdirector, agrégalas aquí
        ]);

        return $solicitud;
    }
}
