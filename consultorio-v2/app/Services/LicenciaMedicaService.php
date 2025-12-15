<?php

namespace App\Services;

use App\DTOs\LicenciaMedicaData;
use App\Models\LicenciaMedica;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LicenciaMedicaService
{
    public function cargarLicencia(LicenciaMedicaData $dto, UploadedFile $file): LicenciaMedica
    {
        // Guardar en disco local (storage/app/licencias)
        $path = $file->store('licencias'); 

        return LicenciaMedica::create([
            'user_id' => $dto->userId,
            'subdirector_id' => $dto->subdirectorId,
            'fecha_inicio' => $dto->fechaInicio,
            'fecha_fin' => $dto->fechaFin,
            'nombre_archivo' => $path,
            'observaciones' => $dto->observaciones,
            'estado' => 'ingresada',
        ]);
    }

    public function listar(User $user)
    {
        // Subdirección (o Admin/Director si deseas) ve todo
        // Ajusta roles según tus necesidades exactas
        if ($user->role === 'subdireccion') {
            return LicenciaMedica::with(['solicitante', 'registrador'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Funcionario ve SOLO SUYAS
            return LicenciaMedica::where('user_id', $user->id)
                ->with(['registrador']) // Ver quién la subió
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function descargarArchivo(LicenciaMedica $licencia)
    {
        if (!Storage::exists($licencia->nombre_archivo)) {
            abort(404, "El archivo no existe.");
        }
        return Storage::download($licencia->nombre_archivo);
    }
}
