<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLicenciaRequest;
use App\Models\Licencia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Importante para el index

class LicenciaController extends Controller
{
    // RF5: Registrar Licencia (Solo Subdirección)
    public function store(StoreLicenciaRequest $request): JsonResponse
    {
        // 1. Manejo del Archivo
        if ($request->hasFile('archivo')) {
            // Guardamos en 'private' porque son datos médicos sensibles
            // No usamos 'public' storage.
            $path = $request->file('archivo')->store('licencias', 'local');
        } else {
            return response()->json(['message' => 'El archivo de respaldo es obligatorio.'], 422);
        }

        // 2. Calcular Fecha Fin
        $inicio = Carbon::parse($request->validated('fecha_inicio'));
        $dias = (int) $request->validated('dias');
        // Restamos 1 día porque si inicia hoy y dura 1 día, termina hoy.
        $fin = $inicio->copy()->addDays($dias - 1);

        // 3. Crear Registro
        $licencia = Licencia::create([
            'funcionario_id' => $request->validated('funcionario_id'),
            'creado_por_id' => $request->user()->id, // El subdirector logueado
            'fecha_inicio' => $inicio,
            'dias' => $dias,
            'fecha_fin' => $fin,
            'tipo_licencia' => $request->validated('tipo_licencia'),
            'archivo_path' => $path,
            'observacion' => $request->validated('observacion'),
        ]);

        return response()->json([
            'message' => 'Licencia médica registrada correctamente.',
            'data' => $licencia
        ], 201);
    }

    // RF5: Listar Licencias (Ver historial)
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Licencia::with('funcionario:id,name,rut'); // Traemos datos básicos del funcionario

        // Si es Funcionario normal, solo ve las suyas
        if ($user->role === 'funcionario' || $user->role === 'jefatura') {
            $query->where('funcionario_id', $user->id);
        }
        // Si es Subdirección/Admin, ve todas (no filtramos)

        $licencias = $query->orderBy('fecha_inicio', 'desc')->get();

        return response()->json([
            'message' => 'Historial de licencias obtenido.',
            'data' => $licencias
        ]);
    }
}
