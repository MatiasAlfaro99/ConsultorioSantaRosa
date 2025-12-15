<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RechazarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo jefatura o dirección pueden rechazar (si están autenticados)
        return in_array(auth()->user()?->role, ['jefatura', 'direccion']);
    }

    public function rules(): array
    {
        return [
            'razon_rechazo' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
