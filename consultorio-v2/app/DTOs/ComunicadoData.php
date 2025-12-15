<?php

namespace App\DTOs;

class ComunicadoData
{
    public function __construct(
        public readonly string $titulo,
        public readonly string $contenido,
        public readonly int $publicadoPorId
    ) {}
}
