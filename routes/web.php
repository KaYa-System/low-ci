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

// Page de chat IA
Route::get('/chat', function () {
    return Inertia::render('AiChat');
})->name('chat');

// Dashboard pour les utilisateurs connectés
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
