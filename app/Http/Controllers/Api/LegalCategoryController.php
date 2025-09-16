<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LegalCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = LegalCategory::query()
            ->active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->roots()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories,
            'message' => 'Catégories récupérées avec succès'
        ]);
    }

    /**
     * Display the specified resource with its documents.
     */
    public function show(LegalCategory $category): JsonResponse
    {
        $category->load([
            'documents' => function ($query) {
                $query->active()
                    ->orderBy('publication_date', 'desc')
                    ->with('category');
            },
            'children.documents' => function ($query) {
                $query->active()
                    ->orderBy('publication_date', 'desc');
            }
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Catégorie récupérée avec succès'
        ]);
    }

    /**
     * Get category tree structure
     */
    public function tree(): JsonResponse
    {
        $categories = LegalCategory::query()
            ->active()
            ->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->roots()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories,
            'message' => 'Arbre des catégories récupéré avec succès'
        ]);
    }
}
