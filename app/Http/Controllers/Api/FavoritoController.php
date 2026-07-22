<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Favorito;
use App\Models\Receta;
use Illuminate\Http\Request;

class FavoritoController extends Controller
{
    // Listar todos los favoritos
    public function index(Request $request)
    {
        $user = $request->user();
        $favoritos = Favorito::with([
            'usuario',
            'receta.usuario',
            'receta.categoria',
        ])
            ->latest()
            ->get();

        $favoritos->each(function (Favorito $favorito) {
            if ($favorito->receta) {
                $favorito->receta->loadCount(['favoritos', 'comentarios', 'compras']);
            }
        });

        $favoritos->each(function (Favorito $favorito) use ($user) {
            if ($favorito->receta) {
                $favorito->setRelation('receta', $this->prepararReceta($favorito->receta, $user));
            }
        });

        return response()->json($favoritos, 200);
    }

    // Marcar una receta como favorita
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
        ]);
        $validated['usuario_id'] = $request->user()->id;

        // Evitar duplicados: mismo usuario + misma receta
        $existe = Favorito::where('usuario_id', $validated['usuario_id'])
            ->where('receta_id', $validated['receta_id'])
            ->first();

        if ($existe) {
            return response()->json(['message' => 'Esta receta ya está en favoritos'], 409);
        }

        $favorito = Favorito::create($validated);
        $favorito->load(['usuario', 'receta.usuario', 'receta.categoria']);
        $favorito->receta?->loadCount(['favoritos', 'comentarios', 'compras']);
        $favorito->setRelation('receta', $this->prepararReceta($favorito->receta, $request->user()));

        return response()->json($favorito, 201);
    }

    // Mostrar un favorito específico
    public function show(Request $request, Favorito $favorito)
    {
        $favorito->load(['usuario', 'receta.usuario', 'receta.categoria']);
        $favorito->receta?->loadCount(['favoritos', 'comentarios', 'compras']);
        $favorito->setRelation('receta', $this->prepararReceta($favorito->receta, $request->user()));

        return response()->json($favorito, 200);
    }

    // Quitar una receta de favoritos
    public function destroy(Favorito $favorito)
    {
        $favorito->delete();

        return response()->json(['message' => 'Receta eliminada de favoritos'], 200);
    }

    private function prepararReceta(?Receta $receta, $user): ?Receta
    {
        if (!$receta) {
            return null;
        }

        $pedido = $user
            ? Compra::where('usuario_id', $user->id)
                ->where('receta_id', $receta->id)
                ->whereNotIn('estado', ['cancelado', 'eliminado'])
                ->latest()
                ->first()
            : null;

        $comprada = $pedido?->estado === 'entregado';

        $esPropia = $user && (int) $receta->usuario_id === (int) $user->id;
        $esAdmin = $user && $user->role === 'admin';
        $bloqueada = $receta->es_premium && !$comprada && !$esPropia && !$esAdmin;

        if ($bloqueada) {
            $receta->ingredientes = null;
            $receta->pasos = null;
        }

        $receta->setAttribute('bloqueada', $bloqueada);
        $receta->setAttribute('comprada', $comprada);
        $receta->setAttribute('pedido_activo', $pedido ? [
            'id' => $pedido->id,
            'estado' => $pedido->estado,
            'metodo_pago' => $pedido->metodo_pago,
            'referencia_pago' => $pedido->referencia_pago,
            'referencia_efectivo' => $pedido->referencia_efectivo,
            'created_at' => $pedido->created_at,
        ] : null);

        return $receta;
    }
}
