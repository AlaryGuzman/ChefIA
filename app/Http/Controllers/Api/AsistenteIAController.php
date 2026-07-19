<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsistenteIAController extends Controller
{
    private function consultarGemini(string $prompt): ?string
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return null;
        }

        $response = Http::timeout(25)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function generarReceta(Request $request)
    {
        $validated = $request->validate([
            'ingredientes' => 'required|string',
        ]);

        $prompt = "Eres un asistente culinario. A partir de estos ingredientes: {$validated['ingredientes']}, genera una receta de cocina en espanol. Responde en este formato exacto:\n\nTitulo: [nombre de la receta]\nDescripcion: [breve descripcion]\nIngredientes: [lista completa con cantidades]\nPasos: [pasos numerados de preparacion]";

        $resultado = $this->consultarGemini($prompt);

        if (!$resultado) {
            return response()->json(['message' => 'El asistente no esta configurado. Agrega GEMINI_API_KEY en el backend.'], 503);
        }

        return response()->json(['receta_generada' => $resultado], 200);
    }

    public function sugerirSustitucion(Request $request)
    {
        $validated = $request->validate([
            'ingrediente' => 'required|string',
        ]);

        $prompt = "Eres un asistente culinario. Sugiere 3 sustitutos para el ingrediente: {$validated['ingrediente']}. Explica brevemente cuando usar cada uno. Responde en espanol, breve y claro.";

        $resultado = $this->consultarGemini($prompt);

        if (!$resultado) {
            return response()->json(['message' => 'El asistente no esta configurado. Agrega GEMINI_API_KEY en el backend.'], 503);
        }

        return response()->json(['sugerencia' => $resultado], 200);
    }

    public function preguntar(Request $request)
    {
        $validated = $request->validate([
            'pregunta' => 'required|string|max:1200',
            'historial' => 'nullable|array',
        ]);

        $resultado = $this->consultarGemini(
            $this->construirPromptChat($request, $validated['pregunta'], $validated['historial'] ?? [])
        );

        if (!$resultado) {
            return response()->json(['message' => 'El asistente no esta configurado. Agrega GEMINI_API_KEY en el backend.'], 503);
        }

        return response()->json(['respuesta' => $resultado], 200);
    }

    private function construirPromptChat(Request $request, string $pregunta, array $historial): string
    {
        $user = $request->user();

        $recetas = Receta::with(['categoria', 'usuario'])
            ->withCount(['favoritos', 'comentarios', 'compras'])
            ->latest()
            ->limit(14)
            ->get()
            ->map(function (Receta $receta) use ($user) {
                $esAutor = (int) $receta->usuario_id === (int) $user->id;
                $esAdmin = $user->role === 'admin';
                $comprada = $receta->compras()->where('usuario_id', $user->id)->exists();
                $tieneAcceso = !$receta->es_premium || $esAutor || $esAdmin || $comprada;

                return [
                    'titulo' => $receta->titulo,
                    'categoria' => $receta->categoria?->nombre,
                    'autor' => $receta->usuario?->name,
                    'descripcion' => $receta->descripcion,
                    'tiempo_minutos' => $receta->tiempo_preparacion,
                    'premium' => (bool) $receta->es_premium,
                    'bloqueada_para_usuario' => !$tieneAcceso,
                    'favoritos' => $receta->favoritos_count,
                    'comentarios' => $receta->comentarios_count,
                    'ingredientes' => $tieneAcceso ? $receta->ingredientes : null,
                    'pasos' => $tieneAcceso ? $receta->pasos : null,
                ];
            })
            ->toArray();

        $historialLimpio = collect($historial)
            ->take(-6)
            ->map(fn ($mensaje) => [
                'rol' => $mensaje['role'] ?? 'usuario',
                'texto' => substr((string) ($mensaje['content'] ?? ''), 0, 600),
            ])
            ->values()
            ->toArray();

        return "Eres ChefIA, un asistente culinario amable dentro de una app de recetas.\n"
            . "Responde siempre en espanol, breve, claro y util.\n"
            . "Puedes sugerir recetas, tiempos, sustituciones, ideas de comida y explicar como usar la plataforma.\n"
            . "No inventes datos privados ni prometas compras reales. Las compras son simuladas.\n"
            . "Si una receta premium esta bloqueada_para_usuario=true, no reveles ingredientes ni pasos completos; sugiere comprarla o elegir una receta gratis.\n"
            . "Usuario actual: {$user->name}, rol: {$user->role}.\n"
            . "Historial reciente: " . json_encode($historialLimpio, JSON_UNESCAPED_UNICODE) . "\n"
            . "Recetas disponibles como contexto: " . json_encode($recetas, JSON_UNESCAPED_UNICODE) . "\n"
            . "Pregunta del usuario: {$pregunta}";
    }
}
