<?php

use App\Http\Controllers\Api\LegalCategoryController;
use App\Http\Controllers\Api\LegalDocumentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\DocumentAnalysisController;
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

// Routes pour l'IA Chat
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

// Routes pour Analytics (admin seulement)
Route::prefix('analytics')->middleware(['auth:web', 'admin'])->group(function () {
    Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('countries', [AnalyticsController::class, 'countries']);
    Route::get('devices', [AnalyticsController::class, 'devices']);
    Route::get('browsers', [AnalyticsController::class, 'browsers']);
    Route::get('general', [AnalyticsController::class, 'general']);
    Route::get('export', [AnalyticsController::class, 'export']);
});

// Routes pour l'analyse de documents (admin seulement)
Route::prefix('documents')->middleware(['auth:web', 'admin'])->group(function () {
    Route::post('analyze', [DocumentAnalysisController::class, 'analyzeDocument']);
    Route::post('preview', [DocumentAnalysisController::class, 'previewDocument']);
});
