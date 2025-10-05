<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Page d'accueil - Application de législation
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Pages de consultation des documents
Route::get('/documents/{slug}', function (string $slug) {
    return Inertia::render('legal/DocumentView', [
        'documentSlug' => $slug
    ]);
})->name('documents.show');

// Page de recherche
Route::get('/search', function () {
    return Inertia::render('legal/Search');
})->name('search');

// Page de navigation par catégorie
Route::get('/categories/{slug}', function (string $slug) {
    return Inertia::render('legal/CategoryView', [
        'categorySlug' => $slug
    ]);
})->name('categories.show');

// Pages des procédures
Route::get('/procedures', function () {
    return Inertia::render('legal/ProceduresIndex');
})->name('procedures.index');

Route::get('/procedures/{slug}', function (string $slug) {
    return Inertia::render('legal/ProcedureView', [
        'procedureSlug' => $slug
    ]);
})->name('procedures.show');

// Lecteur PDF
Route::get('/pdf/{slug}', function (string $slug) {
    return Inertia::render('legal/PdfViewer', [
        'documentSlug' => $slug
    ]);
})->name('pdf.viewer');

// Page de chat IA
Route::get('/chat/{session_id?}', function ($session_id = null) {
    return Inertia::render('AiChat', [
        'initialSessionId' => $session_id
    ]);
})->name('chat');

// Administration routes removed - functionality moved to dashboard

// Dashboard pour les utilisateurs connectés
Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Routes de gestion des documents (admin uniquement)
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::post('dashboard/documents', [App\Http\Controllers\DashboardController::class, 'storeDocument'])
        ->name('dashboard.documents.store');
    Route::put('dashboard/documents/{document:id}', [App\Http\Controllers\DashboardController::class, 'updateDocument'])
        ->name('dashboard.documents.update');
    Route::delete('dashboard/documents/{document:id}', [App\Http\Controllers\DashboardController::class, 'deleteDocument'])
        ->name('dashboard.documents.delete');
    Route::post('dashboard/documents/{document:id}/duplicate', [App\Http\Controllers\DashboardController::class, 'duplicateDocument'])
        ->name('dashboard.documents.duplicate');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
