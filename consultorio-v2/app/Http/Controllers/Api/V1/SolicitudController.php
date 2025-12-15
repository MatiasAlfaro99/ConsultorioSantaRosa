<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RechazarSolicitudRequest;
use App\Http\Requests\StoreSolicitudRequest;
use App\Services\SolicitudService;
use App\Services\PdfService; // 1. Importamos el servicio de PDF
use App\DTOs\SolicitudData;
use App\Models\Solicitud; // Necesario para buscar la solicitud al descargar
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    // 2. Inyectamos ambos servicios en el constructor
    public function __construct(
        protected SolicitudService $service,
        protected PdfService $pdfService
    ) {}

    // RF4: Crear Solicitud
    public function store(StoreSolicitudRequest $request): JsonResponse
        {
            // La validación se hace automáticamente con StoreSolicitudRequest
            $validated = $request->validated();

            $dto = new SolicitudData(
                tipo: $validated['tipo'],
                esPorHoras: $request->boolean('es_por_horas'),
                fechaInicio: $validated['fecha_inicio'],
                fechaFin: $validated['fecha_fin'],
                horaInicio: $validated['hora_inicio'],
                horaFin: $validated['hora_fin'],

                // 🛑 CORRECCIÓN DEL ERROR DE PARAMETRO DESCONOCIDO:
                userId: $request->user()->id, // Usamos el nombre 'userId' que el DTO espera.

                diasSolicitados: $validated['dias_solicitados'] ?? null,
                motivo: $validated['motivo'] ?? null
            );

            $this->service->crear($dto);

            return response()->json(['message' => 'Solicitud enviada para aprobación.'], 201);
        }
    // RF4: Aprobación Nivel 1 (Jefatura)
    public function aprobarJefe(Request $request, $id): JsonResponse
    {
        try {
            $solicitud = $this->service->aprobarJefe($id, $request->user());

            return response()->json([
                'message' => 'Solicitud aprobada por Jefatura. Derivada a Dirección.',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // RF4: Aprobación Nivel 2 (Subdirección)
    public function aprobarSubdirector(Request $request, $id): JsonResponse
    {
        if ($request->user()->role !== 'subdireccion') {
            return response()->json(['message' => 'Solo la Subdirección puede dar esta aprobación.'], 403);
        }

        try {
            $solicitud = $this->service->aprobarSubdirector($id, $request->user());

            return response()->json([
                'message' => 'Solicitud aprobada por Subdirección. Derivada a Dirección.',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // RF4: Aprobación Nivel 3 (Dirección - Final)
    public function aprobarDirector(Request $request, $id): JsonResponse
    {
        // Aceptamos rol 'director' o 'direccion'
        if (!in_array($request->user()->role, ['director', 'direccion'])) {
            return response()->json(['message' => 'Solo la Dirección puede dar la aprobación final.'], 403);
        }

        try {
            $solicitud = $this->service->aprobarDirector($id, $request->user());

            return response()->json([
                'message' => 'Solicitud finalizada exitosamente. Días descontados.',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // RF4: Rechazo (Jefatura o Dirección)
    public function rechazar(RechazarSolicitudRequest $request, $id): JsonResponse
    {
        // La validación ya fue hecha por el FormRequest

        if (!in_array($request->user()->role, ['jefatura', 'director', 'subdireccion'])) {
             return response()->json(['message' => 'No tienes permisos para rechazar solicitudes.'], 403);
        }

        try {
            $solicitud = $this->service->rechazar(
                $id,
                $request->validated()['razon_rechazo']
            );

            return response()->json([
                'message' => 'Solicitud rechazada correctamente.',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // RF4: Listado Inteligente (Según Rol)
    public function index(Request $request): JsonResponse
        {
            // 🛑 CORRECCIÓN DEL ERROR DE CALL TO UNDEFINED METHOD:
            $solicitudes = $this->service->listarParaUsuario($request->user());

            // NOTA: Asegúrate de que el Service esté devolviendo los datos con la relación 'solicitante' cargada.
            return response()->json($solicitudes);
        }

    // RF4: Descarga de PDF (Nuevo Método)
    public function descargarComprobante($id)
    {
        // Buscamos la solicitud con sus relaciones necesarias para el PDF
        $solicitud = Solicitud::with(['solicitante', 'jefeAprobador', 'directorAprobador'])
                     ->findOrFail($id);

        // Validamos que esté aprobada (Opcional, pero recomendado)
        // Corregido: El estado final es 'aprobado', no 'aprobado_final'
        if ($solicitud->estado !== 'aprobado') {
             return response()->json(['message' => 'El documento solo está disponible cuando la solicitud está finalizada.'], 400);
        }

        // Generamos el PDF usando el PdfService
        $contenidoPdf = $this->pdfService->generarComprobante($solicitud);

        // Devolvemos el archivo al navegador (stream)
        return response($contenidoPdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="solicitud_'.$id.'.pdf"');
    }
}
