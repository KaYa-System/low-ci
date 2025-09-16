<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Models\LegalArticle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search across all legal content
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'all');
        $category = $request->input('category');
        $perPage = $request->input('per_page', 15);

        if (empty($query)) {
            return response()->json([
                'documents' => [],
                'articles' => [],
                'message' => 'Veuillez saisir un terme de recherche'
            ]);
        }

        $results = [];

        // Recherche dans les documents
        if ($type === 'all' || $type === 'documents') {
            $documentsQuery = LegalDocument::query()
                ->active()
                ->with('category')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%")
                      ->orWhere('summary', 'like', "%{$query}%")
                      ->orWhere('reference_number', 'like', "%{$query}%");
                });

            if ($category) {
                $documentsQuery->where('category_id', $category);
            }

            $results['documents'] = $documentsQuery->paginate($perPage);
        }

        // Recherche dans les articles
        if ($type === 'all' || $type === 'articles') {
            $articlesQuery = LegalArticle::query()
                ->with(['document.category'])
                ->whereHas('document', function ($q) {
                    $q->active();
                })
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%")
                      ->orWhere('commentary', 'like', "%{$query}%")
                      ->orWhere('number', 'like', "%{$query}%");
                });

            if ($category) {
                $articlesQuery->whereHas('document', function ($q) use ($category) {
                    $q->where('category_id', $category);
                });
            }

            $results['articles'] = $articlesQuery->paginate($perPage);
        }

        return response()->json([
            'data' => $results,
            'query' => $query,
            'message' => 'Recherche effectuée avec succès'
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 3) {
            return response()->json(['data' => []]);
        }

        $documents = LegalDocument::query()
            ->active()
            ->where('title', 'like', "%{$query}%")
            ->select('id', 'title', 'type')
            ->limit(5)
            ->get();

        $articles = LegalArticle::query()
            ->with('document:id,title,type')
            ->whereHas('document', function ($q) {
                $q->active();
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('number', 'like', "%{$query}%");
            })
            ->select('id', 'number', 'title', 'document_id')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'documents' => $documents,
                'articles' => $articles
            ]
        ]);
    }

    /**
     * Advanced search with filters
     */
    public function advanced(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $filters = $request->input('filters', []);
        $perPage = $request->input('per_page', 15);

        $documentsQuery = LegalDocument::query()
            ->active()
            ->with('category');

        // Recherche textuelle
        if (!empty($query)) {
            $documentsQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%")
                  ->orWhere('reference_number', 'like', "%{$query}%");
            });
        }

        // Application des filtres
        if (isset($filters['type'])) {
            $documentsQuery->byType($filters['type']);
        }

        if (isset($filters['category_id'])) {
            $documentsQuery->where('category_id', $filters['category_id']);
        }

        if (isset($filters['date_from'])) {
            $documentsQuery->where('publication_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $documentsQuery->where('publication_date', '<=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $documentsQuery->where('status', $filters['status']);
        }

        $documents = $documentsQuery->paginate($perPage);

        return response()->json($documents);
    }
}
