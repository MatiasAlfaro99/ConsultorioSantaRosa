<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =========================================================================
// CONTROLADORES
// Se asume que todos los controladores están en App\Http\Controllers\Api\V1\
// =========================================================================
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComunicadoController;
use App\Http\Controllers\Api\V1\DocumentoController;
use App\Http\Controllers\Api\V1\EventoController;
use App\Http\Controllers\Api\V1\LicenciaMedicaController;
use App\Http\Controllers\Api\V1\SolicitudController;
use App\Http\Controllers\Api\V1\LicenciaController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\ExternalNewsController;
use App\Http\Controllers\Api\V1\SugerenciaController; // Asumo que ya lo creaste
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;
// =========================================================================
// RUTAS PÚBLICAS (v1)
// =========================================================================
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// =========================================================================
// RUTAS PROTEGIDAS (v1, Requieren Token Bearer)
// =========================================================================
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/dashboard/resumen', [DashboardController::class, 'obtenerResumen']);
    // --- PERFIL Y UTILIDADES ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/perfil', [ProfileController::class, 'update']);
    Route::get('/noticias-minsal', [ExternalNewsController::class, 'minsal']);

    // --- RF2: Documentos (Subida y Gestión) ---
    Route::get('/documents', [DocumentoController::class, 'index']);
    Route::post('/documents', [DocumentoController::class, 'store']);
    Route::get('/documents/{id}', [DocumentoController::class, 'show']);
    Route::put('/documents/{id}', [DocumentoController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentoController::class, 'destroy']);

    // --- RF3: Comunicados (Noticias Internas) ---
    Route::get('/comunicados', [ComunicadoController::class, 'index']);
    Route::post('/comunicados', [ComunicadoController::class, 'store']);
    Route::put('/comunicados/{id}', [ComunicadoController::class, 'update']);
    Route::delete('/comunicados/{id}', [ComunicadoController::class, 'destroy']);

    // --- RF4: Solicitudes de Permiso (Máquina de Estados) ---
    Route::get('/solicitudes', [SolicitudController::class, 'index']);      // Listar (MisSolicitudes.vue)
    Route::post('/solicitudes', [SolicitudController::class, 'store']);     // Crear (NuevaSolicitud.vue)
    Route::get('/solicitudes/{solicitud}', [SolicitudController::class, 'show']); // Ver detalle

    // Acciones de Flujo (MisSolicitudes.vue)
    Route::post('/solicitudes/{solicitud}/aprobar-jefe', [SolicitudController::class, 'aprobarJefe']);
    Route::post('/solicitudes/{solicitud}/aprobar-subdirector', [SolicitudController::class, 'aprobarSubdirector']);
    Route::post('/solicitudes/{solicitud}/aprobar-director', [SolicitudController::class, 'aprobarDirector']);
    Route::post('/solicitudes/{solicitud}/rechazar', [SolicitudController::class, 'rechazar']); // Método POST para enviar la razón
    Route::get('/solicitudes/{solicitud}/descargar', [SolicitudController::class, 'descargarComprobante']); // Pendiente de implementación

    // --- RF5: Licencias Médicas ---
    Route::get('/licencias-medicas', [LicenciaMedicaController::class, 'index']);
    Route::post('/licencias-medicas', [LicenciaMedicaController::class, 'store']);
    Route::get('/licencias-medicas/{licencia}/download', [LicenciaMedicaController::class, 'download']);

    // --- RF6: Gestión de Usuarios (CRUD Automático) ---
    Route::apiResource('users', AdminUserController::class);
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);

    // --- RF7: Eventos (Calendario, CRUD Automático) ---
    Route::apiResource('eventos', EventoController::class);

    // --- RF8: Buzón de Sugerencias ---
    Route::post('/sugerencias', [SugerenciaController::class, 'store']);
    Route::get('/sugerencias', [SugerenciaController::class, 'index']);
});
