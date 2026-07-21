<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Receta;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $compras = Compra::with(['receta.usuario', 'receta.categoria'])
            ->where('usuario_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($compras, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
            'tarjeta.numero' => 'nullable|string|min:12|max:23',
            'tarjeta.nombre' => 'nullable|string|max:120',
            'tarjeta.expiracion' => 'nullable|string|max:7',
            'tarjeta.cvv' => 'nullable|string|min:3|max:4',
        ]);

        $receta = Receta::findOrFail($validated['receta_id']);
        [$compra, $error, $status] = $this->comprarUnaReceta($receta, $request->user(), $this->datosPago($request));

        if ($error) {
            return response()->json(['message' => $error], $status);
        }

        return response()->json([
            'message' => 'Compra realizada correctamente',
            'compra' => $compra,
        ], 201);
    }

    public function storeMany(Request $request)
    {
        $validated = $request->validate([
            'receta_ids' => 'required|array|min:1',
            'receta_ids.*' => 'required|exists:recetas,id',
            'tarjeta.numero' => 'nullable|string|min:12|max:23',
            'tarjeta.nombre' => 'nullable|string|max:120',
            'tarjeta.expiracion' => 'nullable|string|max:7',
            'tarjeta.cvv' => 'nullable|string|min:3|max:4',
        ]);

        $compras = [];
        $errores = [];
        $datosPago = $this->datosPago($request);

        foreach (array_unique($validated['receta_ids']) as $recetaId) {
            $receta = Receta::findOrFail($recetaId);
            [$compra, $error] = $this->comprarUnaReceta($receta, $request->user(), $datosPago);

            if ($compra) {
                $compras[] = $compra;
            } else {
                $errores[] = $error ?? 'No se pudo comprar una receta';
            }
        }

        return response()->json([
            'message' => count($compras) > 0 ? 'Compra simulada realizada' : 'No se pudo realizar la compra',
            'compras' => $compras,
            'errores' => $errores,
        ], count($compras) > 0 ? 201 : 422);
    }

    public function show(Request $request, Compra $compra)
    {
        if ($compra->usuario_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'No tienes permisos para ver esta compra'], 403);
        }

        $compra->load(['receta.usuario', 'receta.categoria']);

        return response()->json($compra, 200);
    }

    public function reporte(Request $request)
    {
        $totalVentas = Compra::sum('precio_pagado');
        $totalCompras = Compra::count();

        $ventasPorReceta = Compra::selectRaw('receta_id, COUNT(*) as veces_comprada, SUM(precio_pagado) as total_generado')
            ->with('receta:id,titulo,usuario_id')
            ->groupBy('receta_id')
            ->orderByDesc('total_generado')
            ->get();

        $comprasRecientes = Compra::with(['usuario:id,name,email', 'receta:id,titulo,precio,es_premium'])
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'resumen' => [
                'total_ventas' => $totalVentas,
                'total_compras' => $totalCompras,
            ],
            'ventas_por_receta' => $ventasPorReceta,
            'compras_recientes' => $comprasRecientes,
        ], 200);
    }

    public function destroy(Compra $compra)
    {
        $compra->delete();

        return response()->json(['message' => 'Venta eliminada correctamente'], 200);
    }

    private function datosPago(Request $request): array
    {
        $numero = preg_replace('/\D+/', '', (string) $request->input('tarjeta.numero', ''));

        return [
            'metodo_pago' => 'Tarjeta simulada',
            'tarjeta_ultimos4' => $numero ? substr($numero, -4) : null,
            'referencia_pago' => 'CHF-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),
        ];
    }

    private function comprarUnaReceta(Receta $receta, $usuario, array $datosPago = []): array
    {
        if (!$receta->es_premium) {
            return [null, 'Esta receta es gratuita, no necesitas comprarla', 400];
        }

        if ((int) $receta->usuario_id === (int) $usuario->id) {
            return [null, 'No puedes comprar tu propia receta', 400];
        }

        $yaComprada = Compra::where('usuario_id', $usuario->id)
            ->where('receta_id', $receta->id)
            ->first();

        if ($yaComprada) {
            return [null, 'Ya has comprado esta receta', 409];
        }

        $compra = Compra::create([
            'usuario_id' => $usuario->id,
            'receta_id' => $receta->id,
            'precio_pagado' => $receta->precio,
            'metodo_pago' => $datosPago['metodo_pago'] ?? 'Tarjeta simulada',
            'tarjeta_ultimos4' => $datosPago['tarjeta_ultimos4'] ?? null,
            'referencia_pago' => $datosPago['referencia_pago'] ?? null,
        ]);

        return [$compra, null, 201];
    }
}
