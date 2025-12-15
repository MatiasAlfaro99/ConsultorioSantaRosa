<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreComunicadoRequest;
use App\DTOs\ComunicadoData;
use App\Services\ComunicadoService;
use App\Services\EventoService;
use App\DTOs\EventoData;
use App\Models\Comunicado;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; // Importante para debug

class ComunicadoController extends Controller
{
    public function __construct(
        protected ComunicadoService $service,
        protected EventoService $eventoService
    ) {}

    public function index(): JsonResponse
    {
        // Ordenamos por más reciente para que aparezca arriba
        // Si tu servicio no ordena, agregamos ->orderBy... aquí si usamos el modelo directo,
        // pero asumiremos que el servicio lo hace.
        $comunicados = $this->service->listarVigentes();
        return response()->json(['data' => $comunicados]);
    }

    public function store(StoreComunicadoRequest $request): JsonResponse
    {
        // 1. Crear Comunicado
        $dto = new ComunicadoData(
            titulo: $request->validated('titulo'),
            contenido: $request->validated('contenido'),
            publicadoPorId: $request->user()->id
        );

        $comunicado = $this->service->publicar($dto);

        // 2. Crear Evento (Si el usuario marcó el checkbox)
        if ($request->boolean('agendar_evento') && $request->filled('fecha_evento')) {
            try {
                $fecha = $request->input('fecha_evento'); // YYYY-MM-DD
                $hora = $request->input('hora_evento', '09:00'); // HH:mm (default 09:00)

                // Construir fecha completa compatible con MySQL (Y-m-d H:i:s)
                $inicioStr = "{$fecha} {$hora}:00";

                // Calcular fin (1 hora después)
                $fechaInicioObj = new \DateTime($inicioStr);
                $fechaFinObj = clone $fechaInicioObj;
                $fechaFinObj->modify('+1 hour');

                $finStr = $fechaFinObj->format('Y-m-d H:i:s');

                $eventoDto = new EventoData(
                    titulo: $request->validated('titulo'),
                    descripcion: $request->validated('contenido'),
                    fechaInicio: $inicioStr,
                    fechaFin: $finStr,
                    lugar: 'Intranet / Muro',
                    creadoPorId: $request->user()->id
                );

                $evento = $this->eventoService->crearEvento($eventoDto);

                // Asignamos categoría visual y vinculamos al comunicado
                $evento->categoria = 'importante';
                $evento->comunicado_id = $comunicado->id; // Link for cascade delete
                $evento->save();

                Log::info("Evento creado desde comunicado: ID " . $evento->id);

            } catch (\Exception $e) {
                // Logueamos el error pero NO detenemos la respuesta del comunicado
                Log::error("Fallo al crear evento desde comunicado: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Comunicado publicado exitosamente',
            'data' => $comunicado
        ], 201);
    }

    // ... (update y destroy se mantienen igual) ...
    public function update(Request $request, string $id): JsonResponse
    {
        $comunicado = Comunicado::findOrFail($id);
        $request->validate(['titulo' => 'required', 'contenido' => 'required']);
        $comunicado->update($request->only(['titulo', 'contenido']));
        return response()->json(['message' => 'Actualizado', 'data' => $comunicado]);
    }

    public function destroy(string $id): JsonResponse
    {
        $comunicado = Comunicado::findOrFail($id);
        
        // Delete linked evento if exists (due to ON DELETE CASCADE, this might be automatic,
        // but since CASCADE is on eventos.comunicado_id -> comunicados.id, we need reverse logic here)
        if ($comunicado->evento) {
            $comunicado->evento->delete();
        }
        
        $comunicado->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
