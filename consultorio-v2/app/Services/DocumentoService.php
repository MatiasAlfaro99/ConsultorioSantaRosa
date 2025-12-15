<?php

namespace App\Services;

use App\DTOs\DocumentoData;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoService
{
    // Cargar y guardar el archivo en el disco y su metadata en BD
    public function subirDocumento(DocumentoData $data): Documento
    {
        // 1. Generar un nombre único para el archivo en el disco
        $extension = $data->archivo->getClientOriginalExtension();
        $nombreDisco = Str::uuid() . '.' . $extension;
        
        // 2. Guardar el archivo en la carpeta 'public/documentos'
        $rutaArchivo = $data->archivo->storeAs('documentos', $nombreDisco, 'public');

        // 3. Crear el registro en la base de datos
        return Documento::create([
            'nombre' => $data->nombre,
            'ruta_archivo' => $rutaArchivo,
            'categoria' => $data->categoria,
            'tipo_mime' => $data->archivo->getClientMimeType(),
            'subido_por_id' => $data->subidoPorId
        ]);
    }

    // Listar todos los documentos para descarga (RF2)
    public function listarDocumentos()
    {
        return Documento::orderBy('created_at', 'desc')->get();
    }
}
