<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden crear una solicitud
        return auth()->check(); 
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:vacaciones,administrativo,sin_goce,devolucion,otros'],
            'es_por_horas' => ['required', 'boolean'],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'dias_solicitados' => ['nullable', 'integer', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:500'],

            // Validación condicional para el modo por horas
            'hora_inicio' => ['nullable', 'date_format:H:i:s', 'required_if:es_por_horas,true'],
            'hora_fin' => ['nullable', 'date_format:H:i:s', 'required_if:es_por_horas,true'],
            
            // Validación de fecha_fin: debe ser igual a fecha_inicio si es por horas
            'fecha_fin' => [
                'required', 
                'date_format:Y-m-d', 
                'after_or_equal:fecha_inicio',
                function ($attribute, $value, $fail) {
                    if ($this->input('es_por_horas') && $this->input('fecha_inicio') !== $value) {
                        $fail('Si la solicitud es por horas, la fecha de inicio y fin deben ser la misma.');
                    }
                },
            ]
        ];
    }
}
