<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LegalDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LegalDocument::query()
            ->active()
            ->with(['category', 'sections', 'articles'])
            ->orderBy('publication_date', 'desc');

        // Filtrage par type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filtrage par catégorie
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Recherche simple
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $documents = $query->paginate($perPage);

        return response()->json($documents);
    }

    /**
     * Display the specified resource.
     */
    public function show(LegalDocument $document): JsonResponse
    {
        $document->load([
            'category',
            'sections.childrenSections.articles',
            'articles' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Incrémenter le compteur de vues
        $document->incrementViews();

        return response()->json([
            'data' => $document,
            'message' => 'Document récupéré avec succès'
        ]);
    }

    /**
     * Get featured documents
     */
    public function featured(): JsonResponse
    {
        $documents = LegalDocument::query()
            ->active()
            ->featured()
            ->with('category')
            ->orderBy('publication_date', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $documents,
            'message' => 'Documents vedettes récupérés avec succès'
        ]);
    }

    /**
     * Get recent documents
     */
    public function recent(): JsonResponse
    {
        $documents = LegalDocument::query()
            ->active()
            ->with('category')
            ->publishedAfter(now()->subMonths(6))
            ->orderBy('publication_date', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $documents,
            'message' => 'Documents récents récupérés avec succès'
        ]);
    }

    /**
     * Get documents by type
     */
    public function byType(string $type): JsonResponse
    {
        $documents = LegalDocument::query()
            ->active()
            ->byType($type)
            ->with('category')
            ->orderBy('publication_date', 'desc')
            ->paginate(20);

        return response()->json($documents);
    }
}
