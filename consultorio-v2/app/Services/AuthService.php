<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $email, string $password, string $device = 'web'): array
    {
        // 1. Buscar usuario
        $user = User::where('email', $email)->first();

        // 2. Verificar contraseña
        if (! $user || ! Hash::check($password, $user->password)) {
            // Lanzamos una excepción de validación estándar de Laravel
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // 3. Generar Token (Sanctum)
        // Borramos tokens anteriores para no acumular basura (opcional, pero limpio)
        $user->tokens()->delete();
        
        $token = $user->createToken($device)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
