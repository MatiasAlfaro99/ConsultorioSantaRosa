<?php

namespace App\DTOs;

class SolicitudData
{
    public function __construct(
        public string $tipo,
        public bool $esPorHoras,
        public string $fechaInicio,
        public string $fechaFin,
        public ?string $horaInicio,
        public ?string $horaFin,
        public int $userId,
        public ?int $diasSolicitados,
        public ?string $motivo = null
    ) {}
}
