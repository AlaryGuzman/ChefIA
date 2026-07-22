<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'imagen_archivo' => 'nullable|image|max:4096',
            'es_premium' => 'sometimes|boolean',
            'precio' => 'required_if:es_premium,true|nullable|numeric|min:0',
        ]);

        $validated['usuario_id'] = $request->user()->id;
        $validated = $this->procesarImagen($request, $validated);

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
            'imagen_archivo' => 'nullable|image|max:4096',
            'es_premium' => 'sometimes|boolean',
            'precio' => 'required_if:es_premium,true|nullable|numeric|min:0',
        ]);

        $validated = $this->procesarImagen($request, $validated);
        $receta->update($validated);
        $this->notificarRecetaActualizada($receta->fresh()->load('usuario'), $request->user());

        return response()->json($receta, 200);
    }

    public function destroy(Request $request, Receta $receta)
    {
        $this->authorize('delete', $receta);
        $titulo = $receta->titulo;
        $autorId = $receta->usuario_id;

        $receta->delete();
        $this->notificarRecetaEliminada($titulo, $autorId, $request->user());

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
                ->where('estado', 'entregado')
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

    private function procesarImagen(Request $request, array $validated): array
    {
        if ($request->hasFile('imagen_archivo')) {
            $path = $request->file('imagen_archivo')->store('recetas', 'public');
            $validated['imagen'] = $request->getSchemeAndHttpHost() . Storage::url($path);
        }

        unset($validated['imagen_archivo']);

        if (empty($validated['imagen'])) {
            $validated['imagen'] = '/img/fondo-login.webp';
        }

        return $validated;
    }

    private function notificarRecetaActualizada(Receta $receta, User $actor): void
    {
        if ($actor->role === 'admin' && (int) $receta->usuario_id !== (int) $actor->id) {
            $this->crearNotificacion(
                $receta->usuario_id,
                $actor,
                'receta_actualizada',
                'Tu receta fue actualizada',
                'El admin actualizo "' . $receta->titulo . '". Revisa los cambios en tu recetario.',
                ['receta_id' => $receta->id, 'url' => '/mis-recetas']
            );
            return;
        }

        if ($actor->role !== 'admin') {
            $this->notificarAdmins(
                'Receta actualizada',
                $actor->name . ' actualizo su receta "' . $receta->titulo . '".',
                $actor,
                ['receta_id' => $receta->id, 'url' => '/recetas/' . $receta->id]
            );
        }
    }

    private function notificarRecetaEliminada(string $titulo, int $autorId, User $actor): void
    {
        if ($actor->role === 'admin' && (int) $autorId !== (int) $actor->id) {
            $this->crearNotificacion(
                $autorId,
                $actor,
                'receta_eliminada',
                'Tu receta fue eliminada',
                'El admin elimino "' . $titulo . '" del recetario.',
                ['url' => '/mis-recetas']
            );
            return;
        }

        if ($actor->role !== 'admin') {
            $this->notificarAdmins(
                'Receta eliminada',
                $actor->name . ' elimino su receta "' . $titulo . '".',
                $actor,
                ['url' => '/dashboard']
            );
        }
    }

    private function notificarAdmins(string $titulo, string $mensaje, User $actor, array $data = []): void
    {
        User::where('role', 'admin')->get()->each(function (User $admin) use ($titulo, $mensaje, $actor, $data) {
            $this->crearNotificacion($admin->id, $actor, 'admin', $titulo, $mensaje, $data);
        });
    }

    private function crearNotificacion(int $userId, User $actor, string $tipo, string $titulo, string $mensaje, array $data = []): void
    {
        Notificacion::create([
            'user_id' => $userId,
            'actor_id' => $actor->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'data' => $data,
        ]);
    }
}
