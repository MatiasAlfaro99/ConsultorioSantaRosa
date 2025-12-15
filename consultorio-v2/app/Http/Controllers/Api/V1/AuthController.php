<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $service
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        // El Request ya validó que vengan email y password
        
        try {
            $data = $this->service->login(
                $request->validated('email'),
                $request->validated('password'),
                $request->validated('device_name', 'postman') // 'postman' es un default
            );

            return response()->json([
                'message' => 'Autenticación exitosa',
                'token' => $data['token'],
                'user' => $data['user'], // Opcional: devolver datos del usuario
            ]);

        } catch (\Exception $e) {
            // Si es error de validación (credenciales malas), devolvemos 401 o 422
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
