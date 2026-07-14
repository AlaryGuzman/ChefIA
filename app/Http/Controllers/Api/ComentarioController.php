<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    // Listar todos los comentarios
    public function index()
    {
        $comentarios = Comentario::with(['usuario', 'receta'])->get();
        return response()->json($comentarios, 200);
    }

    // Crear un nuevo comentario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'receta_id' => 'required|exists:recetas,id',
            'contenido' => 'required|string',
        ]);

        $comentario = Comentario::create($validated);

        return response()->json($comentario, 201);
    }

    // Mostrar un comentario específico
    public function show(Comentario $comentario)
    {
        $comentario->load(['usuario', 'receta']);
        return response()->json($comentario, 200);
    }

    // Actualizar un comentario existente
    public function update(Request $request, Comentario $comentario)
    {
        $this->authorize('update', $comentario);
        $validated = $request->validate([
            'contenido' => 'required|string',
        ]);

        $comentario->update($validated);

        return response()->json($comentario, 200);
    }

    // Eliminar un comentario
    public function destroy(Comentario $comentario)
    {
        $this->authorize('delete', $comentario);

        $comentario->delete();

        return response()->json(['message' => 'Comentario eliminado correctamente'], 200);
    }
}
