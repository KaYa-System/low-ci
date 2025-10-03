<?php

use App\Http\Controllers\Admin\DocumentsController;
use App\Http\Controllers\Api\LegalCategoryController;
use App\Http\Controllers\Api\LegalDocumentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AiChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes publiques pour la législation
Route::prefix('legal')->group(function () {
    // Catégories
    Route::get('categories', [LegalCategoryController::class, 'index']);
    Route::get('categories/tree', [LegalCategoryController::class, 'tree']);
    Route::get('categories/{category:slug}', [LegalCategoryController::class, 'show']);

    // Documents légaux
    Route::get('documents', [LegalDocumentController::class, 'index']);
    Route::get('documents/featured', [LegalDocumentController::class, 'featured']);
    Route::get('documents/recent', [LegalDocumentController::class, 'recent']);
    Route::get('documents/type/{type}', [LegalDocumentController::class, 'byType']);
    Route::get('documents/{document:slug}', [LegalDocumentController::class, 'show']);

    // Recherche
    Route::get('search', [SearchController::class, 'search']);
    Route::get('search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('search/advanced', [SearchController::class, 'advanced']);
});

// Routes pour l'IA Chat
Route::prefix('ai')->group(function () {
    Route::post('chat/sessions', [AiChatController::class, 'createSession']);
    Route::get('chat/sessions/{session:session_id}', [AiChatController::class, 'getSession']);
    Route::post('chat/sessions/{session:session_id}/messages', [AiChatController::class, 'sendMessage']);
    Route::get('chat/sessions/{session:session_id}/messages', [AiChatController::class, 'getMessages']);
    Route::get('chat/sessions', [AiChatController::class, 'getUserSessions']);
});

// Routes d'administration
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('documents', DocumentsController::class);
    Route::post('documents/{document}/duplicate', [DocumentsController::class, 'duplicate']);
});

// Routes authentifiées
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('ai')->group(function () {
        Route::delete('chat/sessions/{session:session_id}', [AiChatController::class, 'deleteSession']);
    });
});