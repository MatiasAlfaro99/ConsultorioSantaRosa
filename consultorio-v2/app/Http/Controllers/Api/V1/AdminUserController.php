<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // Listar todos los usuarios (Con paginación para no saturar)
    public function index(): JsonResponse
    {
        // Traemos también el nombre del jefe para mostrarlo en la tabla
        $users = User::with('jefe:id,name')->orderBy('name')->get();

        return response()->json([
            'message' => 'Listado de personal obtenido.',
            'data' => $users
        ]);
    }

    // Crear nuevo funcionario
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Hashear contraseña antes de guardar
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'Usuario creado exitosamente.',
            'data' => $user
        ], 201);
    }

    // Mostrar un usuario específico
    public function show($id): JsonResponse
    {
        $user = User::with('jefe')->findOrFail($id);
        return response()->json(['data' => $user]);
    }

    // Actualizar datos
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        // Solo hasheamos si enviaron una nueva contraseña
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Evitamos sobreescribir con null
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user
        ]);
    }

    // Eliminar (Despido)
    public function destroy($id): JsonResponse
        {
            try {
                $user = User::findOrFail($id);

                if ($user->id === auth()->id()) {
                    return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 400);
                }

                // TRUCO: Antes de borrar, liberamos a sus subordinados
                // Si este usuario era jefe de alguien, esos empleados se quedan sin jefe (null)
                User::where('jefe_id', $user->id)->update(['jefe_id' => null]);

                $user->delete();

                return response()->json(['message' => 'Usuario eliminado del sistema.']);

            } catch (\Illuminate\Database\QueryException $e) {
                // Si falla por Comunicados o Solicitudes, avisamos
                if ($e->getCode() === '23000' || $e->getCode() === '23503') {
                    return response()->json([
                        'message' => 'No se puede eliminar porque tiene documentos o noticias asociadas. Por favor, DESACTÍVALO en su lugar.'
                    ], 409);
                }
                return response()->json(['message' => 'Error de base de datos.'], 500);
            }
        }
    public function toggleStatus($id): JsonResponse
        {
            $user = User::findOrFail($id);

            // Evitar desactivarse a uno mismo
            if ($user->id === auth()->id()) {
                return response()->json(['message' => 'No puedes desactivar tu propia cuenta.'], 400);
            }

            // Invertir el valor actual
            $user->is_active = !$user->is_active;
            $user->save();

            $estado = $user->is_active ? 'activado' : 'desactivado';
            return response()->json([
                'message' => "Usuario {$estado} correctamente.",
                'data' => $user
            ]);
        }}


