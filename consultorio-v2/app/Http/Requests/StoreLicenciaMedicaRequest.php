<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenciaMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo Subdirección puede cargar licencias
        return $this->user()->role === 'subdireccion';
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'observaciones' => 'nullable|string',
        ];
    }
}
