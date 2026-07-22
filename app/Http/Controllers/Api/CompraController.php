<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Notificacion;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    private const ESTADOS = ['pendiente_pago', 'pagado', 'enviado', 'entregado', 'cancelado', 'eliminado'];

    public function index(Request $request)
    {
        $compras = Compra::with(['receta.usuario', 'receta.categoria', 'resena'])
            ->where('usuario_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($compras, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receta_id' => 'required|exists:recetas,id',
            'metodo_pago' => 'nullable|string|in:tarjeta,efectivo_oxxo',
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
            'message' => $compra->estado === 'pendiente_pago'
                ? 'Pedido creado. Paga con tu referencia OXXO y espera la confirmacion del admin.'
                : 'Pago registrado correctamente. El admin debe enviar tu receta para que puedas recibirla.',
            'compra' => $compra,
        ], 201);
    }

    public function storeMany(Request $request)
    {
        $validated = $request->validate([
            'receta_ids' => 'required|array|min:1',
            'receta_ids.*' => 'required|exists:recetas,id',
            'metodo_pago' => 'nullable|string|in:tarjeta,efectivo_oxxo',
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
            'message' => count($compras) > 0 ? 'Pedido registrado correctamente' : 'No se pudo realizar el pedido',
            'compras' => $compras,
            'errores' => $errores,
        ], count($compras) > 0 ? 201 : 422);
    }

    public function show(Request $request, Compra $compra)
    {
        if ($compra->usuario_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'No tienes permisos para ver esta compra'], 403);
        }

        $compra->load(['receta.usuario', 'receta.categoria', 'resena']);

        return response()->json($compra, 200);
    }

    public function reporte(Request $request)
    {
        $totalVentas = Compra::whereIn('estado', ['pagado', 'enviado', 'entregado'])->sum('precio_pagado');
        $totalCompras = Compra::count();
        $pedidosPorEstado = Compra::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $ventasPorReceta = Compra::selectRaw('receta_id, COUNT(*) as veces_comprada, SUM(precio_pagado) as total_generado')
            ->with('receta:id,titulo,usuario_id')
            ->whereIn('estado', ['pagado', 'enviado', 'entregado'])
            ->groupBy('receta_id')
            ->orderByDesc('total_generado')
            ->get();

        $comprasRecientes = Compra::with(['usuario:id,name,email', 'receta:id,titulo,precio,es_premium,imagen'])
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'resumen' => [
                'total_ventas' => $totalVentas,
                'total_compras' => $totalCompras,
                'por_estado' => $pedidosPorEstado,
            ],
            'ventas_por_receta' => $ventasPorReceta,
            'compras_recientes' => $comprasRecientes,
        ], 200);
    }

    public function updateEstado(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:' . implode(',', self::ESTADOS),
            'motivo_cancelacion' => 'nullable|string|max:255',
        ]);

        [$ok, $message] = $this->puedeCambiarEstado($compra, $validated['estado']);

        if (!$ok) {
            return response()->json(['message' => $message], 422);
        }

        $datos = ['estado' => $validated['estado']];

        if ($validated['estado'] === 'pagado') {
            $datos['pagado_at'] = now();
        }

        if ($validated['estado'] === 'enviado') {
            $datos['enviado_at'] = now();
        }

        if ($validated['estado'] === 'entregado') {
            $datos['entregado_at'] = now();
        }

        if ($validated['estado'] === 'cancelado') {
            $datos['cancelado_at'] = now();
            $datos['motivo_cancelacion'] = $validated['motivo_cancelacion'] ?? 'Pedido cancelado por administracion.';
            $datos['referencia_reembolso'] = $this->referencia('RMB');
        }

        if ($validated['estado'] === 'eliminado') {
            $datos['eliminado_at'] = now();
        }

        $compra->update($datos);
        $this->notificarCambioEstado($compra->fresh()->load(['usuario', 'receta.usuario']), $request->user());

        return response()->json([
            'message' => 'Estado del pedido actualizado correctamente.',
            'compra' => $compra->fresh()->load(['usuario:id,name,email', 'receta:id,titulo,precio,es_premium,imagen']),
        ], 200);
    }

    public function confirmarEntrega(Request $request, Compra $compra)
    {
        $compra->load('receta');

        if ($compra->usuario_id !== $request->user()->id) {
            return response()->json(['message' => 'No tienes permisos para confirmar este pedido.'], 403);
        }

        if ($compra->estado !== 'enviado') {
            return response()->json(['message' => 'Solo puedes confirmar entrega cuando el pedido ya fue enviado.'], 422);
        }

        $compra->update([
            'estado' => 'entregado',
            'entregado_at' => now(),
        ]);
        $this->notificarAdmins(
            'Pedido entregado',
            $request->user()->name . ' recibio la receta "' . $compra->receta?->titulo . '".',
            $request->user(),
            ['compra_id' => $compra->id, 'receta_id' => $compra->receta_id, 'url' => '/pedidos']
        );

        return response()->json([
            'message' => 'Pedido marcado como entregado.',
            'compra' => $compra->fresh()->load(['receta.usuario', 'receta.categoria', 'resena']),
        ], 200);
    }

    public function destroy(Compra $compra)
    {
        $compra->update([
            'estado' => 'eliminado',
            'eliminado_at' => now(),
        ]);

        return response()->json(['message' => 'Pedido marcado como eliminado correctamente'], 200);
    }

    private function datosPago(Request $request): array
    {
        $numero = preg_replace('/\D+/', '', (string) $request->input('tarjeta.numero', ''));
        $metodo = $request->input('metodo_pago', $numero ? 'tarjeta' : 'tarjeta');

        if ($metodo === 'efectivo_oxxo') {
            return [
                'metodo_pago' => 'Efectivo OXXO',
                'estado' => 'pendiente_pago',
                'tarjeta_ultimos4' => null,
                'referencia_pago' => $this->referencia('PED'),
                'referencia_efectivo' => $this->referencia('OXXO'),
                'pagado_at' => null,
            ];
        }

        return [
            'metodo_pago' => 'Tarjeta simulada',
            'estado' => 'pagado',
            'tarjeta_ultimos4' => $numero ? substr($numero, -4) : null,
            'referencia_pago' => $this->referencia('PED'),
            'referencia_efectivo' => null,
            'pagado_at' => now(),
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
            ->whereNotIn('estado', ['cancelado', 'eliminado'])
            ->first();

        if ($yaComprada) {
            return [null, 'Ya tienes un pedido activo para esta receta.', 409];
        }

        $compra = Compra::create([
            'usuario_id' => $usuario->id,
            'receta_id' => $receta->id,
            'precio_pagado' => $receta->precio,
            'metodo_pago' => $datosPago['metodo_pago'] ?? 'Tarjeta simulada',
            'estado' => $datosPago['estado'] ?? 'pagado',
            'tarjeta_ultimos4' => $datosPago['tarjeta_ultimos4'] ?? null,
            'referencia_pago' => $datosPago['referencia_pago'] ?? null,
            'referencia_efectivo' => $datosPago['referencia_efectivo'] ?? null,
            'pagado_at' => $datosPago['pagado_at'] ?? now(),
        ]);
        $compra->load(['usuario', 'receta.usuario']);
        $this->notificarPedidoCreado($compra);

        return [$compra->load(['receta.usuario', 'receta.categoria']), null, 201];
    }

    private function referencia(string $prefijo): string
    {
        return $prefijo . '-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
    }

    private function puedeCambiarEstado(Compra $compra, string $nuevoEstado): array
    {
        if ($compra->estado === $nuevoEstado) {
            return [true, null];
        }

        if (in_array($compra->estado, ['cancelado', 'eliminado'], true)) {
            return [false, 'No puedes cambiar un pedido cancelado o eliminado.'];
        }

        if (in_array($nuevoEstado, ['cancelado', 'eliminado'], true)) {
            return [true, null];
        }

        $flujo = [
            'pendiente_pago' => ['pagado'],
            'pagado' => ['enviado'],
            'enviado' => [],
            'entregado' => [],
        ];

        if (in_array($nuevoEstado, $flujo[$compra->estado] ?? [], true)) {
            return [true, null];
        }

        return [false, 'El admin solo puede confirmar pago y enviar. El usuario debe recibir la receta para marcarla como entregada.'];
    }

    private function notificarPedidoCreado(Compra $compra): void
    {
        $mensaje = $compra->usuario->name . ' hizo un pedido de "' . $compra->receta->titulo . '".';

        $this->notificarAdmins('Nuevo pedido', $mensaje, $compra->usuario, [
            'compra_id' => $compra->id,
            'receta_id' => $compra->receta_id,
            'url' => '/pedidos',
        ]);

        if ($compra->receta->usuario_id && (int) $compra->receta->usuario_id !== (int) $compra->usuario_id) {
            $this->crearNotificacion(
                $compra->receta->usuario_id,
                $compra->usuario,
                'venta_receta',
                'Tu receta tiene un pedido',
                $mensaje,
                ['compra_id' => $compra->id, 'receta_id' => $compra->receta_id, 'url' => '/mis-recetas']
            );
        }
    }

    private function notificarCambioEstado(Compra $compra, User $admin): void
    {
        $titulo = match ($compra->estado) {
            'pagado' => 'Pago confirmado',
            'enviado' => 'Tu receta ha llegado',
            'cancelado' => 'Pedido cancelado',
            'eliminado' => 'Pedido eliminado',
            default => 'Pedido actualizado',
        };

        $mensaje = match ($compra->estado) {
            'pagado' => 'Tu pago de "' . $compra->receta->titulo . '" fue confirmado. Espera a que el admin envie la receta.',
            'enviado' => 'La receta "' . $compra->receta->titulo . '" ya fue enviada. Recibela para desbloquearla.',
            'cancelado' => 'Tu pedido de "' . $compra->receta->titulo . '" fue cancelado. Se genero reembolso con referencia ' . $compra->referencia_reembolso . '.',
            'eliminado' => 'Tu pedido de "' . $compra->receta->titulo . '" fue marcado como eliminado por administracion.',
            default => 'Tu pedido de "' . $compra->receta->titulo . '" cambio de estado.',
        };

        $this->crearNotificacion(
            $compra->usuario_id,
            $admin,
            'pedido_' . $compra->estado,
            $titulo,
            $mensaje,
            [
                'compra_id' => $compra->id,
                'receta_id' => $compra->receta_id,
                'estado' => $compra->estado,
                'accion' => $compra->estado === 'enviado' ? 'recibir' : null,
                'url' => $compra->estado === 'enviado' ? '/mis-compras' : '/mis-compras',
            ]
        );
    }

    private function notificarAdmins(string $titulo, string $mensaje, ?User $actor = null, array $data = []): void
    {
        User::where('role', 'admin')->get()->each(function (User $admin) use ($titulo, $mensaje, $actor, $data) {
            $this->crearNotificacion($admin->id, $actor, 'admin', $titulo, $mensaje, $data);
        });
    }

    private function crearNotificacion(int $userId, ?User $actor, string $tipo, string $titulo, string $mensaje, array $data = []): void
    {
        Notificacion::create([
            'user_id' => $userId,
            'actor_id' => $actor?->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'data' => $data,
        ]);
    }
}
