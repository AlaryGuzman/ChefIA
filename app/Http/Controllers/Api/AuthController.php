<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registro de un nuevo usuario
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Ese correo ya esta registrado.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'usuario',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'No pudimos iniciar sesion. Revisa tus datos o contacta al administrador.',
            ], 422);
        }

        if ($user->suspended_until && $user->suspended_until->isPast() && !$user->suspended_indefinitely) {
            $user->update([
                'suspended_until' => null,
                'suspended_indefinitely' => false,
                'suspension_reason' => null,
            ]);
            $user = $user->fresh();
        }

        if ($user->suspended_indefinitely || ($user->suspended_until && $user->suspended_until->isFuture())) {
            $motivo = $user->suspension_reason ?: 'Revision administrativa de la cuenta.';
            $mensaje = $user->suspended_indefinitely
                ? 'Tu cuenta esta suspendida por tiempo indefinido.'
                : 'Tu cuenta esta suspendida temporalmente.';

            return response()->json([
                'message' => $mensaje,
                'status' => 'suspended',
                'reason' => $motivo,
                'suspended_indefinitely' => (bool) $user->suspended_indefinitely,
                'suspended_until' => $user->suspended_until?->toIso8601String(),
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }

    // Obtener datos del usuario autenticado (útil para probar que el token funciona)
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|nullable|string',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'email.unique' => 'Ese correo ya esta registrado.',
            'current_password.required_with' => 'Para cambiar tu contrasena debes escribir tu contrasena actual.',
            'password.confirmed' => 'La nueva contrasena y la confirmacion no coinciden.',
            'password.min' => 'La nueva contrasena debe tener al menos 6 caracteres.',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            if (!Hash::check($validated['current_password'] ?? '', $user->password)) {
                return response()->json([
                    'message' => 'La contrasena actual no es correcta.',
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['current_password'], $validated['password_confirmation']);

        $user->update($validated);

        return response()->json($user->fresh(), 200);
    }
}
