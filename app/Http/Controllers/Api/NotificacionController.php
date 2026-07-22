<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $notificaciones = Notificacion::with('actor:id,name,email')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(40)
            ->get();

        return response()->json([
            'no_leidas' => $notificaciones->whereNull('leida_at')->count(),
            'notificaciones' => $notificaciones,
        ], 200);
    }

    public function marcarLeida(Request $request, Notificacion $notificacion)
    {
        if ((int) $notificacion->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'No tienes permisos para esta notificacion.'], 403);
        }

        $notificacion->update(['leida_at' => now()]);

        return response()->json($notificacion->fresh(), 200);
    }
}
