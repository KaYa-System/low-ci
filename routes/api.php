<?php

use App\Http\Controllers\Admin\DocumentsController;
use App\Http\Controllers\Api\LegalCategoryController;
use App\Http\Controllers\Api\LegalDocumentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AiChatController;
use Illuminate\Support\Facades\Route;

// Routes publiques pour la législation (nécessaires pour l'affichage public)
Route::prefix('legal')->group(function () {
    // Catégories - utilisées par le dashboard pour les sélecteurs
    Route::get('categories', [LegalCategoryController::class, 'index']);
    Route::get('categories/tree', [LegalCategoryController::class, 'tree']);
    Route::get('categories/{category:slug}', [LegalCategoryController::class, 'show']);

    // Documents légaux - utilisés pour l'affichage public des documents
    Route::get('documents', [LegalDocumentController::class, 'index']);
    Route::get('documents/featured', [LegalDocumentController::class, 'featured']);
    Route::get('documents/recent', [LegalDocumentController::class, 'recent']);
    Route::get('documents/type/{type}', [LegalDocumentController::class, 'byType']);
    Route::get('documents/{document:slug}', [LegalDocumentController::class, 'show']);

    // Recherche - utilisée par la page de recherche publique
    Route::get('search', [SearchController::class, 'search']);
    Route::get('search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('search/advanced', [SearchController::class, 'advanced']);
});

// Routes d'administration - utilisées par le dashboard
Route::middleware(['auth:web', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('documents', DocumentsController::class);
    Route::post('documents/{document}/duplicate', [DocumentsController::class, 'duplicate']);
});

// Routes pour l'IA Chat (si utilisées)
Route::prefix('ai')->group(function () {
    // Routes publiques pour créer une session
    Route::post('chat/sessions', [AiChatController::class, 'createSession']);
    Route::get('chat/sessions/{session:session_id}', [AiChatController::class, 'getSession']);
    Route::post('chat/sessions/{session:session_id}/messages', [AiChatController::class, 'sendMessage']);
    Route::get('chat/sessions/{session:session_id}/messages', [AiChatController::class, 'getMessages']);
    
    // Routes authentifiées
    Route::middleware('auth:web')->group(function () {
        Route::get('chat/sessions', [AiChatController::class, 'getUserSessions']);
        Route::delete('chat/sessions/{session:session_id}', [AiChatController::class, 'deleteSession']);
    });
});
