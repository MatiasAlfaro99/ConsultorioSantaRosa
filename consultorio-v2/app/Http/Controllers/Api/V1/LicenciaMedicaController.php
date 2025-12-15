<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLicenciaMedicaRequest;
use App\Services\LicenciaMedicaService;
use App\DTOs\LicenciaMedicaData;
use App\Models\LicenciaMedica;
use Illuminate\Http\Request;

class LicenciaMedicaController extends Controller
{
    protected $service;

    public function __construct(LicenciaMedicaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $licencias = $this->service->listar($request->user());
        return response()->json($licencias);
    }

    public function store(StoreLicenciaMedicaRequest $request)
    {
        $dto = new LicenciaMedicaData(
            userId: $request->user_id,
            fechaInicio: $request->fecha_inicio,
            fechaFin: $request->fecha_fin,
            subdirectorId: $request->user()->id,
            observaciones: $request->observaciones
        );

        $licencia = $this->service->cargarLicencia($dto, $request->file('archivo'));

        return response()->json([
            'message' => 'Licencia médica cargada exitosamente.',
            'data' => $licencia
        ], 201);
    }

    public function download(LicenciaMedica $licencia)
    {
        // Autorización simple: Dueño o Subdirección
        $user = auth()->user();
        if ($user->id !== $licencia->user_id && $user->role !== 'subdireccion') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $this->service->descargarArchivo($licencia);
    }
}
