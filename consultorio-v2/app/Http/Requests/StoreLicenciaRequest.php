<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo la Subdirección (o Admin) puede subir licencias
        return in_array($this->user()->role, ['subdireccion', 'admin']);
    }

    public function rules(): array
    {
        return [
            'funcionario_id' => ['required', 'exists:users,id'], // El funcionario enfermo
            'fecha_inicio' => ['required', 'date'],
            'dias' => ['required', 'integer', 'min:1'],
            'tipo_licencia' => ['required', 'string', 'in:enfermedad_comun,maternal,accidente,grave_enfermedad_hijo'],
            // Validamos el archivo: PDF o Imagen, máx 5MB
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'], 
            'observacion' => ['nullable', 'string', 'max:500'],
        ];
    }
}
