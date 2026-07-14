<?php

use App\Http\Controllers\Api\AsistenteIAController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ==========================
// Rutas de autenticación
// ==========================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==========================
// Rutas públicas (invitado): solo lectura
// ==========================
Route::get('/recetas', [RecetaController::class, 'index']);
Route::get('/recetas/{receta}', [RecetaController::class, 'show']);
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{categoria}', [CategoriaController::class, 'show']);
Route::get('/comentarios', [ComentarioController::class, 'index']);
Route::get('/comentarios/{comentario}', [ComentarioController::class, 'show']);

// ==========================
// Rutas protegidas (requieren estar autenticados)
// ==========================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // compras
    Route::get('/compras', [CompraController::class, 'index']);
    Route::post('/compras', [CompraController::class, 'store']);
    Route::get('/compras/{compra}', [CompraController::class, 'show']);

    // Asistente IA
    Route::post('/asistente/generar-receta', [AsistenteIAController::class, 'generarReceta']);
    Route::post('/asistente/sugerir-sustitucion', [AsistenteIAController::class, 'sugerirSustitucion']);
    Route::post('/asistente/preguntar', [AsistenteIAController::class, 'preguntar']);

    // Recetas: cualquier usuario autenticado puede crear
    Route::post('/recetas', [RecetaController::class, 'store']);
    Route::put('/recetas/{receta}', [RecetaController::class, 'update']);
    Route::patch('/recetas/{receta}', [RecetaController::class, 'update']);
    Route::delete('/recetas/{receta}', [RecetaController::class, 'destroy']);

    // Comentarios: cualquier usuario autenticado puede comentar
    Route::post('/comentarios', [ComentarioController::class, 'store']);
    Route::put('/comentarios/{comentario}', [ComentarioController::class, 'update']);

    // Favoritos: cualquier usuario autenticado
    Route::apiResource('favoritos', FavoritoController::class)->except(['update']);

    // ==========================
    // Solo administrador
    // ==========================
    Route::middleware('role:admin')->group(function () {
        Route::post('/categorias', [CategoriaController::class, 'store']);
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update']);
        Route::patch('/categorias/{categoria}', [CategoriaController::class, 'update']);
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy']);

        Route::delete('/comentarios/{comentario}', [ComentarioController::class, 'destroy']);

        Route::apiResource('usuarios', UserController::class)->parameters(['usuarios' => 'user']);
    });
});
