<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsistenteIAController extends Controller
{
    private function consultarGemini(string $prompt)
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}", [
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

    // Generar una receta a partir de ingredientes dados
    public function generarReceta(Request $request)
    {
        $validated = $request->validate([
            'ingredientes' => 'required|string',
        ]);

        $prompt = "Eres un asistente culinario. A partir de estos ingredientes: {$validated['ingredientes']}, genera una receta de cocina en español. Responde en este formato exacto:\n\nTítulo: [nombre de la receta]\nDescripción: [breve descripción]\nIngredientes: [lista completa con cantidades]\nPasos: [pasos numerados de preparación]";

        $resultado = $this->consultarGemini($prompt);

        if (!$resultado) {
            return response()->json(['message' => 'No se pudo generar la receta, intenta de nuevo'], 503);
        }

        return response()->json(['receta_generada' => $resultado], 200);
    }

    // Sugerir sustituciones de ingredientes
    public function sugerirSustitucion(Request $request)
    {
        $validated = $request->validate([
            'ingrediente' => 'required|string',
        ]);

        $prompt = "Eres un asistente culinario. Sugiere 3 posibles sustitutos para el ingrediente: {$validated['ingrediente']}, explicando brevemente cuándo usar cada uno. Responde en español, de forma breve y clara.";

        $resultado = $this->consultarGemini($prompt);

        if (!$resultado) {
            return response()->json(['message' => 'No se pudo generar la sugerencia, intenta de nuevo'], 503);
        }

        return response()->json(['sugerencia' => $resultado], 200);
    }

    // Responder preguntas generales sobre cocina
    public function preguntar(Request $request)
    {
        $validated = $request->validate([
            'pregunta' => 'required|string',
        ]);

        $prompt = "Eres un asistente culinario experto. Responde en español, de forma breve y clara, a la siguiente pregunta relacionada con cocina: {$validated['pregunta']}";

        $resultado = $this->consultarGemini($prompt);

        if (!$resultado) {
            return response()->json(['message' => 'No se pudo procesar la pregunta, intenta de nuevo'], 503);
        }

        return response()->json(['respuesta' => $resultado], 200);
    }
}
