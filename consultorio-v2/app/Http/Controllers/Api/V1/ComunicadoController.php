<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ComunicadoService;
use App\Services\EventoService;
use App\Models\Comunicado;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ComunicadoController extends Controller
{
    public function __construct(
        protected ComunicadoService $service,
        protected EventoService $eventoService
    ) {}

    public function index(): JsonResponse
    {
        // Tu original llamaba a 'listarVigentes', el servicio debe tener ese método
        $comunicados = $this->service->listarVigentes();
        return response()->json(['data' => $comunicados]);
    }

public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string',
            'contenido' => 'required|string',
        ]);

        // 1. Crear Comunicado
        // Usamos create() directo del modelo para no depender del Servicio si este está desactualizado
        $comunicado = Comunicado::create([
            'titulo' => $request->input('titulo'),
            'contenido' => $request->input('contenido'),
            'publicado_por_id' => $request->user()->id, // <--- Correcto
            'es_activo' => true // <--- Default
        ]);

        // 2. Crear Evento (Lógica del calendario opcional)
        if ($request->boolean('agendar_evento') && $request->filled('fecha_evento')) {
            try {
                $fecha = $request->input('fecha_evento');
                $hora = $request->input('hora_evento', '09:00');
                $inicioStr = "{$fecha} {$hora}:00";

                $inicioObj = new \DateTime($inicioStr);
                $finObj = clone $inicioObj;
                $finObj->modify('+1 hour');

                // Llamamos directo al modelo Evento para evitar problemas de servicios intermedios
                \App\Models\Evento::create([
                    'titulo' => $request->input('titulo'),
                    'descripcion' => $request->input('contenido'),
                    'fecha_inicio' => $inicioStr,
                    'fecha_fin' => $finObj->format('Y-m-d H:i:s'),
                    'creado_por_id' => $request->user()->id,
                    'comunicado_id' => $comunicado->id,
                    'categoria' => 'reunion', // Obligatorio por BD
                    'lugar' => 'CESFAM'       // Opcional
                ]);

            } catch (\Exception $e) {
                Log::error("Fallo al crear evento: " . $e->getMessage());
                // No detenemos la respuesta, solo logueamos el error
            }
        }

        return response()->json([
            'message' => 'Comunicado publicado exitosamente',
            'data' => $comunicado
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $comunicado = Comunicado::findOrFail($id);
        $request->validate(['titulo' => 'required', 'contenido' => 'required']);
        $comunicado->update($request->only(['titulo', 'contenido']));
        return response()->json(['message' => 'Actualizado', 'data' => $comunicado]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->eliminar($id);
        return response()->json(['message' => 'Eliminado']);
    }
}
