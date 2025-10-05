<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DocumentAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentAnalysisController extends Controller
{
    protected DocumentAnalysisService $analysisService;

    public function __construct(DocumentAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Analyse un document PDF uploadé
     */
    public function analyzeDocument(Request $request): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'error' => 'Accès non autorisé'
            ], 403);
        }

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:51200' // 50MB max
        ], [
            'pdf_file.required' => 'Un fichier PDF est requis.',
            'pdf_file.mimes' => 'Le fichier doit être un PDF.',
            'pdf_file.max' => 'Le fichier PDF ne peut pas dépasser 50MB.',
        ]);

        try {
            $pdfFile = $request->file('pdf_file');
            
            // Analyser le document avec l'IA
            $analysisResults = $this->analysisService->analyzeDocument($pdfFile);
            
            return response()->json([
                'success' => true,
                'data' => $analysisResults,
                'message' => 'Document analysé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Document analysis API error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'analyse du document',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Preview du document analysé (sans sauvegarde)
     */
    public function previewDocument(Request $request): JsonResponse
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'error' => 'Accès non autorisé'
            ], 403);
        }

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:51200'
        ]);

        try {
            $pdfFile = $request->file('pdf_file');
            
            // Analyser le document
            $analysisResults = $this->analysisService->analyzeDocument($pdfFile);
            
            // Ajouter des informations sur le fichier
            $fileInfo = [
                'original_name' => $pdfFile->getClientOriginalName(),
                'size' => $pdfFile->getSize(),
                'mime_type' => $pdfFile->getMimeType(),
            ];
            
            return response()->json([
                'success' => true,
                'analysis' => $analysisResults,
                'file_info' => $fileInfo,
                'message' => 'Prévisualisation générée'
            ]);

        } catch (\Exception $e) {
            \Log::error('Document preview error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la prévisualisation',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}