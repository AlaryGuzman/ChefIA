<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        $users = User::latest()->get();
        return response()->json($users, 200);
    }

    // Crear un nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,usuario',
            'suspended_until' => 'nullable|date',
            'suspended_indefinitely' => 'sometimes|boolean',
            'suspension_reason' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    // Mostrar un usuario específico
    public function show(User $user)
    {
        $user->load(['recetas', 'comentarios', 'favoritos']);
        return response()->json($user, 200);
    }

    // Actualizar un usuario existente
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|required|string|in:admin,usuario',
            'suspended_until' => 'nullable|date',
            'suspended_indefinitely' => 'sometimes|boolean',
            'suspension_reason' => 'nullable|string|max:255',
        ]);

        if (
            $request->user()?->id === $user->id
            && (
                ($validated['suspended_indefinitely'] ?? false)
                || !empty($validated['suspended_until'])
            )
        ) {
            return response()->json(['message' => 'No puedes suspender tu propia cuenta.'], 422);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (($validated['suspended_indefinitely'] ?? false) === true) {
            $validated['suspended_until'] = null;
        }

        if (array_key_exists('suspended_until', $validated) && empty($validated['suspended_until'])) {
            $validated['suspended_until'] = null;
        }

        if (
            array_key_exists('suspended_until', $validated)
            && $validated['suspended_until'] === null
            && !($validated['suspended_indefinitely'] ?? false)
        ) {
            $validated['suspension_reason'] = null;
        }

        $user->update($validated);

        if ($user->suspended_indefinitely || ($user->suspended_until && $user->suspended_until->isFuture())) {
            $user->tokens()->delete();
        }

        return response()->json($user, 200);
    }

    // Eliminar un usuario
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}
