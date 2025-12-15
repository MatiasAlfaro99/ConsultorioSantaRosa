<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Documento; // Asegúrate de que tu modelo esté en esta ruta
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    // RF2: Listar documentos
    public function index(): JsonResponse
    {
        // Traemos la relación 'user' para mostrar el nombre del autor en el frontend
        $documentos = Documento::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $documentos]);
    }

    // RF2: Crear documento
    public function store(Request $request): JsonResponse
    {
        // 1. Validamos
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,png'],
        ]);

        // 2. Verificamos Permisos (Tu lista autorizada)
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'director', 'direccion', 'subdireccion', 'jefatura'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // 3. Procesamos el Archivo
        $file = $request->file('archivo');
        // Guardamos en 'storage/app/public/documentos'
        $path = $file->store('documentos', 'public');

        // 4. Guardamos en BD usando los nombres REALES de tu migración
        $documento = new Documento();
        $documento->nombre = $request->input('nombre');
        $documento->categoria = $request->input('categoria');

        // CORRECCIÓN CRÍTICA: Usamos 'ruta_archivo' en lugar de 'archivo' o 'file_path'
        $documento->ruta_archivo = $path;

        // CORRECCIÓN CRÍTICA: La migración pide 'tipo_mime' obligatorio
        $documento->tipo_mime = $file->getClientMimeType();

        $documento->subido_por_id = $user->id;

        $documento->save();

        return response()->json([
            'message' => 'Documento cargado exitosamente',
            'data' => $documento
        ], 201);
    }

    // RF2: Actualizar documento
    public function update(Request $request, $id): JsonResponse
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        // 1. Permisos
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'director', 'direccion', 'subdireccion'])) {
            return response()->json(['message' => 'No autorizado para editar'], 403);
        }

        // 2. Validación (Archivo opcional al editar)
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,png'],
        ]);

        // 3. Actualizar textos
        $documento->nombre = $request->input('nombre');
        $documento->categoria = $request->input('categoria');

        // 4. Reemplazar archivo (solo si se sube uno nuevo)
        if ($request->hasFile('archivo')) {
            // Borramos el viejo si existe (usando ruta_archivo)
            if ($documento->ruta_archivo && Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            // Subimos el nuevo
            $file = $request->file('archivo');
            $path = $file->store('documentos', 'public');

            // Actualizamos las columnas correctas
            $documento->ruta_archivo = $path;
            $documento->tipo_mime = $file->getClientMimeType();
        }

        $documento->save();

        return response()->json([
            'message' => 'Documento actualizado correctamente',
            'data' => $documento
        ]);
    }

    // RF2: Eliminar documento
    public function destroy($id): JsonResponse
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        // Permisos
        $user = request()->user();
        if (!in_array($user->role, ['admin', 'director', 'direccion', 'subdireccion'])) {
            return response()->json(['message' => 'No autorizado para eliminar'], 403);
        }

        // Borrar archivo físico (usando ruta_archivo)
        if ($documento->ruta_archivo) {
            if (Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }
        }

        $documento->delete();

        return response()->json(['message' => 'Documento eliminado correctamente']);
    }
}
