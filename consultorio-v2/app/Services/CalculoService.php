<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\User;

class CalculoService
{
    /**
     * Calcula los días disponibles de Vacaciones y Administrativos
     */
    public function calcularSaldos(User $user)
    {
        // 1. Definir los Totales Legales
        // (Aquí podrías poner lógica extra si el usuario tiene más años de antigüedad)
        $totalVacaciones = 15;
        $totalAdministrativos = 6;

        // 2. Sumar días YA usados (Solo solicitudes APROBADAS totalmente)
        $usadosVacaciones = Solicitud::where('user_id', $user->id)
            ->where('tipo', 'vacaciones')
            ->where('estado', 'aprobado') // Solo las que aprobó el Director
            ->sum('dias_solicitados');

        $usadosAdmin = Solicitud::where('user_id', $user->id)
            ->where('tipo', 'administrativo')
            ->where('estado', 'aprobado')
            ->sum('dias_solicitados');

        // 3. Calcular Restantes
        return [
            'vacaciones' => [
                'total' => $totalVacaciones,
                'usados' => $usadosVacaciones,
                'disponibles' => max(0, $totalVacaciones - $usadosVacaciones),
            ],
            'administrativos' => [
                'total' => $totalAdministrativos,
                'usados' => $usadosAdmin,
                'disponibles' => max(0, $totalAdministrativos - $usadosAdmin),
            ]
        ];
    }
}
