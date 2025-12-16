<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CalculoService;
use App\Models\Solicitud; // <--- Agregar importación
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected CalculoService $calculoService
    ) {}

    public function obtenerResumen(Request $request)
    {
        $user = $request->user();

        // 1. Calcular Saldos
        $saldos = $this->calculoService->calcularSaldos($user);

        // 2. Contar Solicitudes Pendientes POR APROBAR (No las mías)
        $pendientesPorAprobar = 0;

        if ($user->role === 'jefatura') {
            // Jefatura ve las que están en estado 'pendiente_jefatura' (excluyendo las suyas si aplica)
            $pendientesPorAprobar = Solicitud::where('estado', 'pendiente_jefatura')
                                             ->where('user_id', '!=', $user->id)
                                             ->count();
        }
        elseif ($user->role === 'subdireccion') {
             // Subdirección no aprueba directamente en tu flujo actual (solo ve),
             // pero si en el futuro aprueba, sería 'pendiente_subdireccion'
             $pendientesPorAprobar = Solicitud::where('estado', 'pendiente_subdireccion')->count();
        }
        elseif ($user->role === 'direccion' || $user->role === 'director') {
             // Director aprueba lo que ya pasó por jefatura
             $pendientesPorAprobar = Solicitud::where('estado', 'pendiente_direccion')->count();
        }

        // Opcional: Si es admin, quizás quiere ver todas las pendientes
        if ($user->role === 'admin') {
             $pendientesPorAprobar = Solicitud::where('estado', 'like', 'pendiente%')->count();
        }

        return response()->json([
            'usuario' => $user,
            'saldos' => $saldos,
            'conteo_pendientes' => $pendientesPorAprobar // <--- Ahora sí muestra el trabajo pendiente
        ]);
    }}
