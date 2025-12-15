<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class DocumentoData
{
    public function __construct(
        public readonly string $nombre,
        public readonly UploadedFile $archivo,
        public readonly ?string $categoria,
        public readonly int $subidoPorId
    ) {}
}
