<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Notificacion;
use App\Models\Receta;
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
            'receta_id' => 'required|exists:recetas,id',
            'contenido' => 'required|string',
        ]);

        $usuario = $request->user();
        $receta = Receta::findOrFail($validated['receta_id']);

        $tieneAcceso = !$receta->es_premium
            || (int) $receta->usuario_id === (int) $usuario->id
            || $usuario->role === 'admin'
            || $receta->compras()
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'entregado')
                ->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'message' => 'Recibe esta receta premium antes de comentar.',
            ], 403);
        }

        $validated['usuario_id'] = $usuario->id;
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

        if ($request->user()->role === 'admin' && (int) $comentario->usuario_id !== (int) $request->user()->id) {
            $this->crearNotificacion(
                $comentario->usuario_id,
                $request->user()->id,
                'comentario_actualizado',
                'Tu comentario fue actualizado',
                'El admin actualizo un comentario que hiciste en "' . $comentario->receta?->titulo . '".',
                ['receta_id' => $comentario->receta_id, 'url' => '/recetas/' . $comentario->receta_id]
            );
        }

        return response()->json($comentario->fresh()->load(['usuario', 'receta']), 200);
    }

    // Eliminar un comentario
    public function destroy(Request $request, Comentario $comentario)
    {
        $this->authorize('delete', $comentario);
        $usuarioId = $comentario->usuario_id;
        $recetaId = $comentario->receta_id;
        $recetaTitulo = $comentario->receta?->titulo;

        $comentario->delete();

        if ($request->user()->role === 'admin' && (int) $usuarioId !== (int) $request->user()->id) {
            $this->crearNotificacion(
                $usuarioId,
                $request->user()->id,
                'comentario_eliminado',
                'Tu comentario fue eliminado',
                'El admin elimino un comentario que hiciste en "' . $recetaTitulo . '".',
                ['receta_id' => $recetaId, 'url' => '/recetas/' . $recetaId]
            );
        }

        return response()->json(['message' => 'Comentario eliminado correctamente'], 200);
    }

    private function crearNotificacion(int $userId, int $actorId, string $tipo, string $titulo, string $mensaje, array $data = []): void
    {
        Notificacion::create([
            'user_id' => $userId,
            'actor_id' => $actorId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'data' => $data,
        ]);
    }
}
