<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asegúrate de que esto coincida con tus permisos reales.
        // Si un Director también puede editar, agrégalo aquí.
        return in_array($this->user()->role, ['admin', 'director']);
    }

    public function rules(): array
    {
        // CORRECCIÓN CRÍTICA:
        // En rutas resource ('users'), el parámetro se llama 'user', no 'id'.
        $userId = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                // Ahora sí funcionará el ignore porque $userId tendrá el valor correcto (ej: 1)
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'nullable|string|min:6', // Bajé a 6 para coincidir con tu frontend
            'rut' => [
                'nullable',
                'string',
                Rule::unique('users')->ignore($userId)
            ],
            'cargo' => 'nullable|string',
            'role' => 'sometimes|in:admin,director,direccion,subdireccion,jefatura,funcionario',
            'jefe_id' => 'nullable|exists:users,id',
	    'is_active' => 'boolean',
        ];
    }
}
