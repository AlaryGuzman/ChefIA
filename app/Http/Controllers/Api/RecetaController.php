<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class RecetaController extends Controller
{
    // Listar todas las recetas
    public function index(Request $request)
    {
        $user = $this->userFromOptionalToken($request);

        $recetas = Receta::with(['usuario', 'categoria'])
            ->withCount(['favoritos', 'comentarios', 'compras', 'resenas'])
            ->withAvg('resenas', 'calificacion')
            ->latest()
            ->get()
            ->map(fn (Receta $receta) => $this->prepareForUser($receta, $user));

        return response()->json($recetas, 200);
    }

    // Crear una nueva receta

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $validated['usuario_id'] = $request->user()->id;

        if (empty($validated['es_premium'])) {
            $validated['precio'] = null;
        }

        $receta = Receta::create($validated)->load(['usuario', 'categoria']);

        return response()->json($receta, 201);
    }

    // Mostrar una receta específica

    public function show(Request $request, Receta $receta)
    {
        $user = $this->userFromOptionalToken($request);

        $receta->load(['usuario', 'categoria', 'comentarios.usuario'])
            ->loadCount(['favoritos', 'comentarios', 'compras', 'resenas'])
            ->loadAvg('resenas', 'calificacion');

        $this->prepareForUser($receta, $user);

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

    private function userFromOptionalToken(Request $request)
    {
        if ($request->user()) {
            return $request->user();
        }

        $header = $request->bearerToken();
        if (!$header) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($header);

        return $accessToken?->tokenable;
    }

    private function prepareForUser(Receta $receta, $user): Receta
    {
        $tieneAcceso = !$receta->es_premium;
        $comprada = false;

        if ($user) {
            $esAutor = (int) $receta->usuario_id === (int) $user->id;
            $esAdmin = $user->role === 'admin';
            $comprada = $receta->compras()
                ->where('usuario_id', $user->id)
                ->whereIn('estado', ['pagado', 'enviado', 'entregado'])
                ->exists();
            $tieneAcceso = $tieneAcceso || $esAutor || $esAdmin || $comprada;
        }

        if (!$tieneAcceso) {
            $receta->makeHidden(['ingredientes', 'pasos']);
        }

        $receta->bloqueada = !$tieneAcceso;
        $receta->comprada = $comprada;

        return $receta;
    }
}
