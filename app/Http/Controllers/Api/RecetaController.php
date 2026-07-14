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
            'es_premium' => 'sometimes|boolean',
            'precio' => 'required_if:es_premium,true|nullable|numeric|min:0',
        ]);

        $receta = Receta::create($validated);

        return response()->json($receta, 201);
    }

    // Mostrar una receta específica

    public function show(Request $request, Receta $receta)
    {
        $receta->load(['usuario', 'categoria', 'comentarios']);

        // Si la receta es gratuita, se muestra completa
        if (!$receta->es_premium) {
            return response()->json($receta, 200);
        }

        $user = $request->user();  // null si es invitado (no autenticado)

        $tieneAcceso = false;

        if ($user) {
            $esAutor = $receta->usuario_id === $user->id;
            $esAdmin = $user->role === 'admin';
            $yaComprada = $receta->compras()->where('usuario_id', $user->id)->exists();

            $tieneAcceso = $esAutor || $esAdmin || $yaComprada;
        }

        // Si no tiene acceso, ocultamos ingredientes y pasos
        if (!$tieneAcceso) {
            $receta->makeHidden(['ingredientes', 'pasos']);
            $receta->bloqueada = true;
        }

        return response()->json($receta, 200);
    }

    // Actualizar una receta existente

    public function update(Request $request, Receta $receta)
    {
        $this->authorize('update', $receta);

        $validated = $request->validate([
            'usuario_id' => 'sometimes|required|exists:users,id',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'ingredientes' => 'sometimes|required|string',
            'pasos' => 'sometimes|required|string',
            'tiempo_preparacion' => 'nullable|integer',
            'imagen' => 'nullable|string',
            'es_premium' => 'sometimes|boolean',
            'precio' => 'required_if:es_premium,true|nullable|numeric|min:0',
        ]);

        $receta->update($validated);

        return response()->json($receta, 200);
    }

    public function destroy(Receta $receta)
    {
        $this->authorize('delete', $receta);

        $receta->delete();

        return response()->json(['message' => 'Receta eliminada correctamente'], 200);
    }
}
