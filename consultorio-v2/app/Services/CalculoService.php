<?php

namespace App\Services;

use Carbon\Carbon;

class CalculoService
{
    /**
     * Cuenta días hábiles entre dos fechas inclusive.
     * Solo cuenta días con dayOfWeek 1..5 (Lunes-Viernes).
     * Sábado siempre inhábil por regla.
     *
     * @param Carbon|string $fecha_inicio
     * @param Carbon|string $fecha_fin
     * @return int
     */
    public function calcularDiasHabiles($fecha_inicio, $fecha_fin): int
    {
        $inicio = $fecha_inicio instanceof Carbon ? $fecha_inicio->copy() : new Carbon($fecha_inicio);
        $fin = $fecha_fin instanceof Carbon ? $fecha_fin->copy() : new Carbon($fecha_fin);

        if ($fin->lessThan($inicio)) {
            return 0;
        }

        $diasHabiles = 0;
        $current = $inicio->copy();
        while ($current->lessThanOrEqualTo($fin)) {
            // Carbon::dayOfWeek: 0 (Sunday) .. 6 (Saturday)
            $dow = (int) $current->dayOfWeek;
            // Monday=1 .. Friday=5
            if ($dow >= Carbon::MONDAY && $dow <= Carbon::FRIDAY) {
                $diasHabiles++;
            }
            $current->addDay();
        }

        return $diasHabiles;
    }
}
