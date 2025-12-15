<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventoRequest;
use App\DTOs\EventoData;
use App\Services\EventoService;
use App\Http\Resources\EventoResource;
use Illuminate\Http\JsonResponse;
// use App\Models\Evento; // No es necesario importar el Modelo aquí si usas el Service

class EventoController extends Controller
{
    public function __construct(
        protected EventoService $service
    ) {}

    // GET
    public function index(): JsonResponse
    {
        $eventos = $this->service->listarProximos();
        return response()->json([
            'data' => EventoResource::collection($eventos)
        ]);
    }

    // POST
    public function store(StoreEventoRequest $request): JsonResponse
    {
        $dto = new EventoData(
            titulo: $request->validated('titulo'),
            descripcion: $request->validated('descripcion'),
            fechaInicio: $request->validated('fecha_inicio'),
            fechaFin: $request->validated('fecha_fin'),
            lugar: $request->validated('lugar'),
            creadoPorId: $request->user()->id
        );

        $evento = $this->service->crearEvento($dto);

        return response()->json([
            'message' => 'Evento agendado correctamente',
            'data' => new EventoResource($evento)
        ], 201);
    }

    // --- AGREGAMOS ESTO PARA SOLUCIONAR EL ERROR 405 (PUT) ---
    public function update(StoreEventoRequest $request, string $id): JsonResponse
    {
        // 1. Convertimos los datos que vienen del frontend al DTO
        $dto = new EventoData(
            titulo: $request->validated('titulo'),
            descripcion: $request->validated('descripcion'),
            fechaInicio: $request->validated('fecha_inicio'),
            fechaFin: $request->validated('fecha_fin'),
            lugar: $request->validated('lugar'), // Asegúrate que el request traiga lugar o tenga default
            creadoPorId: $request->user()->id 
        );

        // 2. Llamamos a la función que SÍ existe en tu servicio
        $evento = $this->service->actualizarEvento($id, $dto);

        return response()->json([
            'message' => 'Evento actualizado correctamente',
            'data' => new EventoResource($evento)
        ]);
    }

    // --- AGREGAMOS ESTO PARA SOLUCIONAR EL ERROR 500 (DELETE) ---
    public function destroy(string $id): JsonResponse
    {
        $evento = \App\Models\Evento::findOrFail($id);
        
        // If this evento was created from a comunicado, delete the comunicado too
        if ($evento->comunicado_id) {
            \App\Models\Comunicado::where('id', $evento->comunicado_id)->delete();
        }
        
        $evento->delete();

        // Retornamos vacío con código 204 (éxito sin contenido)
        return response()->json(null, 204);
    }
}
