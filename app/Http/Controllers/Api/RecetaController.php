<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    // Listar todas las recetas
    public function index()
    {
        $recetas = Receta::with(['usuario', 'categoria'])->get();
        return response()->json($recetas, 200);
    }

    // Crear una nueva receta
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'categoria_id' => 'required|exists:categorias,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'ingredientes' => 'required|string',
            'pasos' => 'required|string',
            'tiempo_preparacion' => 'nullable|integer',
            'imagen' => 'nullable|string',
        ]);

        $receta = Receta::create($validated);

        return response()->json($receta, 201);
    }

    // Mostrar una receta específica
    public function show(Receta $receta)
    {
        $receta->load(['usuario', 'categoria', 'comentarios']);
        return response()->json($receta, 200);
    }

    // Actualizar una receta existente
    public function update(Request $request, Receta $receta)
    {
        $validated = $request->validate([
            'usuario_id' => 'sometimes|required|exists:users,id',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'ingredientes' => 'sometimes|required|string',
            'pasos' => 'sometimes|required|string',
            'tiempo_preparacion' => 'nullable|integer',
            'imagen' => 'nullable|string',
        ]);

        $receta->update($validated);

        return response()->json($receta, 200);
    }

    // Eliminar una receta
    public function destroy(Receta $receta)
    {
        $receta->delete();

        return response()->json(['message' => 'Receta eliminada correctamente'], 200);
    }
}
