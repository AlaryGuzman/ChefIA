<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Listar todas las categorías
    public function index()
    {
        $categorias = Categoria::all();
        return response()->json($categorias, 200);
    }

    // Crear una nueva categoría
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create($validated);

        return response()->json($categoria, 201);
    }

    // Mostrar una categoría específica
    public function show(Categoria $categoria)
    {
        return response()->json($categoria, 200);
    }

    // Actualizar una categoría existente
    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $categoria->update($validated);

        return response()->json($categoria, 200);
    }

    // Eliminar una categoría
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente'], 200);
    }
}
