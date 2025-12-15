<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Services\SolicitudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudController extends Controller
{
    protected $service;

    public function __construct(SolicitudService $service = null)
    {
        $this->service = $service ?? new SolicitudService();
    }

    /**
     * Aprobar solicitud por director.
     */
    public function aprobarDirector(Request $request, $id)
    {
        $director = Auth::user();

        $solicitud = Solicitud::find($id);
        if (! $solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        try {
            $this->service->aprobarDirector($solicitud, $director);

            $func = $solicitud->funcionario;

            $vacacionesTotal = $func->vacaciones_total ?? (($func->region_sur ?? false) ? 20 : 15);
            $vacacionesUsadas = $func->vacaciones_usadas ?? 0;
            $diasAdminTotal = 6;
            $diasAdminUsados = $func->dias_admin_usados ?? 0;

            return response()->json([
                'solicitud' => $solicitud,
                'funcionario' => [
                    'id' => $func->id,
                    'vacaciones_total' => $vacacionesTotal,
                    'vacaciones_usadas' => $vacacionesUsadas,
                    'vacaciones_restantes' => max(0, $vacacionesTotal - $vacacionesUsadas),
                    'dias_admin_total' => $diasAdminTotal,
                    'dias_admin_usados' => $diasAdminUsados,
                    'dias_admin_restantes' => max(0, $diasAdminTotal - $diasAdminUsados),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\DTOs\SolicitudData;
use App\Http\Requests\StoreSolicitudRequest;
use App\Http\Requests\RechazarSolicitudRequest;
use App\Models\Solicitud;
use App\Services\SolicitudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudController extends Controller
{
    protected $solicitudService;

    public function __construct(SolicitudService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }
    /**
     * Almacena una nueva solicitud (POST /api/solicitudes)
     */
    public function store(StoreSolicitudRequest $request)
    {
        $data = new SolicitudData(
            tipo: $request->tipo,
            esPorHoras: $request->es_por_horas,
            fechaInicio: $request->fecha_inicio,
            fechaFin: $request->fecha_fin,
            horaInicio: $request->hora_inicio,
            horaFin: $request->hora_fin,
            userId: Auth::id(), // ID del usuario autenticado
            diasSolicitados: $request->dias_solicitados,
            motivo: $request->motivo
        );

        $solicitud = $this->solicitudService->crear($data);

        return response()->json([
            'message' => 'Solicitud creada exitosamente y pendiente de aprobación de jefatura.',
            'solicitud' => $solicitud
        ], 201);
    }

    /**
     * Lista las solicitudes para el usuario (GET /api/solicitudes)
     */
    public function index()
    {
        $solicitudes = $this->solicitudService->listarParaUsuario(Auth::user());

        return response()->json($solicitudes);
    }

    /**
     * Aprueba la solicitud a nivel de Jefatura (POST /api/solicitudes/{solicitud}/aprobar-jefe)
     */
    public function aprobarJefe(Solicitud $solicitud)
    {
        if (Auth::user()->role !== 'jefatura') {
            return response()->json(['message' => 'No tienes permiso para aprobar solicitudes de jefatura.'], 403);
        }

        try {
            $solicitudActualizada = $this->solicitudService->aprobarJefe($solicitud->id, Auth::user());

            return response()->json([
                'message' => 'Solicitud aprobada por jefatura. Ahora pendiente de Dirección.',
                'solicitud' => $solicitudActualizada
            ]);
        } catch (\Exception $e) {
             return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Rechaza la solicitud (POST /api/solicitudes/{solicitud}/rechazar)
     */
    public function rechazar(Solicitud $solicitud, RechazarSolicitudRequest $request)
    {
        if (!in_array(Auth::user()->role, ['jefatura', 'direccion'])) {
            return response()->json(['message' => 'No tienes permiso para rechazar solicitudes.'], 403);
        }

        $razon = $request->razon;

        try {
            $solicitudActualizada = $this->solicitudService->rechazar($solicitud->id, $razon);

            return response()->json([
                'message' => 'Solicitud rechazada.',
                'solicitud' => $solicitudActualizada
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Dejo fuera aprobarDirector por ahora, aunque está en el servicio, no lo necesita el frontend de Vue.
}
