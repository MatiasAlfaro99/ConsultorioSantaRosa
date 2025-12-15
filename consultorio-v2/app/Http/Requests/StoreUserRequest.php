<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin'; // Solo Admin
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Único en la tabla users
            'password' => 'required|string|min:8',
            'rut' => 'nullable|string|max:12|unique:users,rut',
            'cargo' => 'nullable|string|max:100',
            // Validamos que el rol sea uno de los permitidos
            'role' => 'required|in:admin,director,direccion,subdireccion,jefatura,funcionario',
            // Validamos que el jefe exista si se envía
            'jefe_id' => 'nullable|exists:users,id',
        ];
    }
}
