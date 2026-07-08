<?php

use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('usuarios', UserController::class);
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('recetas', RecetaController::class);
Route::apiResource('comentarios', ComentarioController::class);
Route::apiResource('favoritos', FavoritoController::class)->except(['update']);
