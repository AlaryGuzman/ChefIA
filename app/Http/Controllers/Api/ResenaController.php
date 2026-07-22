<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Receta;
use App\Models\Resena;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    public function index(Request $request)
    {
        $query = Resena::with(['usuario:id,name,email', 'receta:id,titulo,imagen,precio,es_premium']);

        if ($request->user()->role !== 'admin') {
            $query->where('usuario_id', $request->user()->id);
        }

        return response()->json($query->latest()->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $compra = Compra::where('usuario_id', $request->user()->id)
            ->where('receta_id', $validated['receta_id'])
            ->whereIn('estado', ['pagado', 'enviado', 'entregado'])
            ->latest()
            ->first();

        if (!$compra) {
            return response()->json([
                'message' => 'Solo puedes resenar recetas premium que ya tengas pagadas.',
            ], 403);
        }

        $resena = Resena::updateOrCreate(
            [
                'usuario_id' => $request->user()->id,
                'receta_id' => $validated['receta_id'],
            ],
            [
                'compra_id' => $compra->id,
                'calificacion' => $validated['calificacion'],
                'comentario' => $validated['comentario'] ?? null,
            ]
        )->load(['usuario:id,name,email', 'receta:id,titulo,imagen,precio,es_premium']);

        return response()->json($resena, 201);
    }

    public function reporte(Request $request)
    {
        $resenas = Resena::with(['usuario:id,name,email', 'receta:id,titulo,imagen,precio,es_premium'])
            ->latest()
            ->get();

        $resumenPorReceta = Receta::withCount(['resenas', 'compras'])
            ->withAvg('resenas', 'calificacion')
            ->with(['usuario:id,name,email', 'categoria:id,nombre'])
            ->get()
            ->filter(fn (Receta $receta) => $receta->resenas_count > 0)
            ->map(function (Receta $receta) {
                return [
                    'id' => $receta->id,
                    'titulo' => $receta->titulo,
                    'categoria' => $receta->categoria?->nombre,
                    'autor' => $receta->usuario?->name,
                    'imagen' => $receta->imagen,
                    'es_premium' => $receta->es_premium,
                    'promedio' => round((float) $receta->resenas_avg_calificacion, 2),
                    'total_resenas' => $receta->resenas_count,
                    'total_ventas' => $receta->compras_count,
                ];
            })
            ->values();

        return response()->json([
            'resumen' => [
                'promedio_general' => round((float) $resenas->avg('calificacion'), 2),
                'total_resenas' => $resenas->count(),
                'buenas' => $resenas->where('calificacion', '>=', 4)->count(),
                'malas' => $resenas->where('calificacion', '<=', 2)->count(),
            ],
            'mejores' => $resumenPorReceta->sortByDesc('promedio')->values()->take(5)->values(),
            'peores' => $resumenPorReceta->sortBy('promedio')->values()->take(5)->values(),
            'por_receta' => $resumenPorReceta->sortByDesc('total_resenas')->values(),
            'resenas' => $resenas,
        ], 200);
    }
}
