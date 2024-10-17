<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

// Préfixe 'api' pour toutes les routes

// Routes pour l'authentification
Route::post('connexion', [UserController::class, 'login']); // login route
Route::post('inscription', [UserController::class, 'register']); // register route



// Routes pour les produits avec authentification
Route::middleware('auth:sanctum')->group(function () {
    // Routes pour les utilisateurs
    Route::apiResource('users', UserController::class);
    Route::get('logout', [UserController::class, 'logout']);
    Route::post('users/search', [UserController::class, 'search']);    // Route pour la recherche
    Route::apiResource('produits', ProductController::class); // Routes protégées pour les produits
    Route::post('produits/search', [ProductController::class, 'search']); // Route de recherche des produits
    Route::apiResource('paniers', CartController::class); // Routes protégées pour les paniers
});

