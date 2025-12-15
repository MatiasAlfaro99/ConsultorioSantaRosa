<?php

namespace App\DTOs;

class EventoData
{
    public function __construct(
        public readonly string $titulo,
        public readonly ?string $descripcion,
        public readonly string $fechaInicio,
        public readonly string $fechaFin,
        public readonly ?string $lugar,
        public readonly int $creadoPorId
    ) {}
}
