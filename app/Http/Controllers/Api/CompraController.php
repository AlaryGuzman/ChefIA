<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Receta;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    // Listar las compras del usuario autenticado
    public function index(Request $request)
    {
        $compras = Compra::with('receta')
            ->where('usuario_id', $request->user()->id)
            ->get();

        return response()->json($compras, 200);
    }

    // Simular la compra de una receta
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
        ]);

        $receta = Receta::findOrFail($validated['receta_id']);
        $usuario = $request->user();

        // Validar que la receta sea premium
        if (!$receta->es_premium) {
            return response()->json(['message' => 'Esta receta es gratuita, no necesitas comprarla'], 400);
        }

        // Validar que no sea el propio autor comprando su receta
        if ($receta->usuario_id === $usuario->id) {
            return response()->json(['message' => 'No puedes comprar tu propia receta'], 400);
        }

        // Validar que no la haya comprado ya
        $yaComprada = Compra::where('usuario_id', $usuario->id)
            ->where('receta_id', $receta->id)
            ->first();

        if ($yaComprada) {
            return response()->json(['message' => 'Ya has comprado esta receta'], 409);
        }

        // Simulación de la compra (sin pasarela real de pago)
        $compra = Compra::create([
            'usuario_id' => $usuario->id,
            'receta_id' => $receta->id,
            'precio_pagado' => $receta->precio,
        ]);

        return response()->json([
            'message' => 'Compra realizada correctamente',
            'compra' => $compra,
        ], 201);
    }

    // Ver el detalle de una compra específica
    public function show(Request $request, Compra $compra)
    {
        if ($compra->usuario_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permisos para ver esta compra'], 403);
        }

        $compra->load('receta');

        return response()->json($compra, 200);
    }
}
