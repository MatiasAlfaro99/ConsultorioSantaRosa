<?php

namespace App\DTOs;

class LicenciaMedicaData
{
    public function __construct(
        public int $userId,
        public string $fechaInicio,
        public string $fechaFin,
        public int $subdirectorId,
        public ?string $observaciones = null,
    ) {}
}
