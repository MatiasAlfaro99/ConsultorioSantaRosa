<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta petición.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        // RF7: Ampliamos permisos para que coincida con el Frontend (Dirección, Jefatura, Admin)
        // Asegúrate de que estos 'strings' coincidan con los roles en tu BD (ej: 'direccion' vs 'director')
        return in_array($user->role, ['director', 'direccion', 'admin', 'jefatura', 'subdireccion']);
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'], // Correcto, ya es opcional
            'fecha_inicio' => ['required', 'date'],
            // La validación 'after' es la que daba error 422, pero con el fix de JS (+1 hora) ya pasará bien
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'], 
            'lugar' => ['nullable', 'string'],
            
            // --- AGREGADO: Importante para que el calendario tenga colores ---
            'categoria' => ['nullable', 'string'], 
        ];
    }
}
