<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorito;
use Illuminate\Http\Request;

class FavoritoController extends Controller
{
    // Listar todos los favoritos
    public function index()
    {
        $favoritos = Favorito::with(['usuario', 'receta'])->get();
        return response()->json($favoritos, 200);
    }

    // Marcar una receta como favorita
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'receta_id' => 'required|exists:recetas,id',
        ]);

        // Evitar duplicados: mismo usuario + misma receta
        $existe = Favorito::where('usuario_id', $validated['usuario_id'])
            ->where('receta_id', $validated['receta_id'])
            ->first();

        if ($existe) {
            return response()->json(['message' => 'Esta receta ya está en favoritos'], 409);
        }

        $favorito = Favorito::create($validated);

        return response()->json($favorito, 201);
    }

    // Mostrar un favorito específico
    public function show(Favorito $favorito)
    {
        $favorito->load(['usuario', 'receta']);
        return response()->json($favorito, 200);
    }

    // Quitar una receta de favoritos
    public function destroy(Favorito $favorito)
    {
        $favorito->delete();

        return response()->json(['message' => 'Receta eliminada de favoritos'], 200);
    }
}
