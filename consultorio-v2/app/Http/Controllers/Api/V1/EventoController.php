<?php

namespace App\Http\Controllers\Api\V1; // <--- CORREGIDO: Debe coincidir con la carpeta

use App\Http\Controllers\Controller; // <--- AGREGADO: Necesario para importar la clase padre
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        return response()->json(Evento::orderBy('fecha_inicio', 'desc')->get());
    }

public function store(Request $request)
    {
        // Log para depuración (puedes quitarlo después)
        \Log::info('Creando evento', $request->all());

        $validated = $request->validate([
            'titulo'        => 'required|string|max:255',
            'categoria'     => 'required|string',
            'fecha_inicio'  => 'required',
            'fecha_fin'     => 'required',
            'descripcion'   => 'nullable|string',
            'lugar'         => 'nullable|string',
            'comunicado_id' => 'nullable'
        ]);

        // AQUÍ ESTÁ EL FIX PARA 'creado_por_id'
        // Intentamos obtener el usuario logueado.
        // Si no hay (porque estamos probando), usamos el ID 1 (Admin/Seeder) para que no falle.
        $validated['creado_por_id'] = auth()->id() ?? 1;

        $evento = Evento::create($validated);

        return response()->json([
            'message' => 'Evento creado exitosamente',
            'data' => $evento
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);

        $validated = $request->validate([
            'titulo'        => 'required|string|max:255',
            'categoria'     => 'required|string',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'descripcion'   => 'nullable|string',
            'lugar'         => 'nullable|string',
            'comunicado_id' => 'nullable|exists:comunicados,id'
        ]);

        $evento->update($validated);

        return response()->json([
            'message' => 'Evento actualizado correctamente',
            'data' => $evento
        ]);
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }
}
