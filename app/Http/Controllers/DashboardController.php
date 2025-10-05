<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\LegalCategory;
use App\Models\User;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Services\DocumentAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class DashboardController extends Controller
{
    protected DocumentAnalysisService $analysisService;
    
    public function __construct(DocumentAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin;

        // Calcul des statistiques générales
        $totalUsers = User::count();
        $activeUsers = User::where('updated_at', '>=', Carbon::now()->subDays(30))->count();
        
        // Sessions IA (avec et sans utilisateurs connectés)
        $totalAiSessions = AiChatSession::count();
        $anonAiSessions = AiChatSession::whereNull('user_id')->count();
        $userAiSessions = AiChatSession::whereNotNull('user_id')->count();
        
        // Messages IA dans les 30 derniers jours
        $recentAiMessages = AiChatMessage::where('sent_at', '>=', Carbon::now()->subDays(30))->count();
        
        // Documents consultés
        $totalDocumentViews = LegalDocument::sum('views_count');
        
        // Sessions actives récemment (7 derniers jours)
        $recentAiSessions = AiChatSession::where('last_activity', '>=', Carbon::now()->subDays(7))->count();

        $data = [
            'isAdmin' => $isAdmin,
            'generalStats' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'totalAiSessions' => $totalAiSessions,
                'anonAiSessions' => $anonAiSessions,
                'userAiSessions' => $userAiSessions,
                'recentAiMessages' => $recentAiMessages,
                'totalDocumentViews' => $totalDocumentViews,
                'recentAiSessions' => $recentAiSessions,
            ],
        ];

        if ($isAdmin) {
            $documents = LegalDocument::with('category')->orderBy('updated_at', 'desc')->get();
            $categories = LegalCategory::where('is_active', true)->orderBy('sort_order')->get();

            // Statistiques détaillées pour admin
            $data['adminStats'] = [
                'total' => $documents->count(),
                'published' => $documents->where('status', 'published')->count(),
                'drafts' => $documents->where('status', 'draft')->count(),
                'totalViews' => $documents->sum('views_count'),
            ];

            $data['documents'] = $documents->toArray();
            $data['categories'] = $categories->toArray();
            
            // Statistiques avancées pour admin
            $data['advancedStats'] = [
                'newUsersThisMonth' => User::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
                'messagesThisWeek' => AiChatMessage::where('sent_at', '>=', Carbon::now()->startOfWeek())->count(),
                'topDocuments' => LegalDocument::orderBy('views_count', 'desc')
                    ->select('title', 'views_count', 'slug')
                    ->limit(5)
                    ->get()
                    ->toArray(),
                'dailyAiUsage' => AiChatMessage::select(
                        DB::raw('DATE(sent_at) as date'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->where('sent_at', '>=', Carbon::now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->toArray(),
            ];
        }

        return inertia('Dashboard', $data);
    }

    /**
     * Store a new document avec analyse IA automatique
     */
    public function storeDocument(Request $request)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return back()->with('error', 'Accès non autorisé.');
        }

        try {
            // Validation simplifiée - seulement le PDF et les champs optionnels de correction
            $validated = $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:51200', // 50MB max
                'category_id' => 'nullable|exists:legal_categories,id',
                'status' => 'nullable|in:draft,published,archived',
                // Champs optionnels pour corriger l'analyse IA
                'title' => 'nullable|string|max:255',
                'reference_number' => 'nullable|string|max:100',
                'type' => 'nullable|in:constitution,loi,decret,arrete,code,ordonnance',
                'summary' => 'nullable|string|max:1000',
                'publication_date' => 'nullable|date|before_or_equal:today',
                'effective_date' => 'nullable|date',
            ], [
                'pdf_file.required' => 'Un fichier PDF est obligatoire.',
                'pdf_file.mimes' => 'Le fichier doit être un PDF.',
                'pdf_file.max' => 'Le fichier PDF ne peut pas dépasser 50MB.',
                'publication_date.before_or_equal' => 'La date de publication ne peut pas être dans le futur.',
                'summary.max' => 'Le résumé ne peut pas dépasser 1000 caractères.',
            ]);

            $pdfFile = $request->file('pdf_file');
            
            // 1. Analyser le document avec l'IA
            $aiAnalysis = $this->analysisService->analyzeDocument($pdfFile);
            
            // 2. Utiliser les corrections manuelles si fournies, sinon utiliser l'analyse IA
            $documentData = [
                'title' => $validated['title'] ?? $aiAnalysis['title'],
                'type' => $validated['type'] ?? $aiAnalysis['type'],
                'reference_number' => $validated['reference_number'] ?? $aiAnalysis['reference_number'],
                'summary' => $validated['summary'] ?? $aiAnalysis['summary'],
                'publication_date' => $validated['publication_date'] ?? $aiAnalysis['publication_date'],
                'effective_date' => $validated['effective_date'] ?? $aiAnalysis['effective_date'],
                'category_id' => $validated['category_id'],
                'status' => $validated['status'] ?? 'draft',
            ];
            
            // Vérifier l'unicité du titre
            $originalTitle = $documentData['title'];
            $counter = 1;
            while (LegalDocument::where('title', $documentData['title'])->exists()) {
                $documentData['title'] = $originalTitle . ' (' . $counter . ')';
                $counter++;
            }
            
            // Vérifier l'unicité du numéro de référence
            if ($documentData['reference_number'] && LegalDocument::where('reference_number', $documentData['reference_number'])->exists()) {
                $documentData['reference_number'] = $documentData['reference_number'] . '-' . time();
            }
            
            // 3. Générer le slug unique
            $baseSlug = Str::slug($documentData['title']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (LegalDocument::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $documentData['slug'] = $slug;
            
            // 4. Sauvegarder le PDF et extraire le contenu pour la recherche
            $filename = time() . '_' . Str::slug($documentData['title']) . '.pdf';
            $path = $pdfFile->storeAs('legal-documents', $filename, 'public');
            
            $documentData['pdf_url'] = Storage::url($path);
            $documentData['pdf_file_name'] = $pdfFile->getClientOriginalName();
            $documentData['pdf_file_size'] = $pdfFile->getSize();
            
            // Extraire le contenu pour la recherche (pas pour l'affichage)
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($pdfFile->getPathname());
                $extractedText = $pdf->getText();
                $documentData['content'] = $this->cleanExtractedText($extractedText);
            } catch (\Exception $e) {
                Log::warning('PDF text extraction failed: ' . $e->getMessage());
                $documentData['content'] = null;
            }
            
            // 5. Créer le document
            $document = LegalDocument::create($documentData);

            // Message de succès avec informations sur l'analyse
            $confidence = $aiAnalysis['confidence_score'] ?? 0;
            $source = $aiAnalysis['analysis_source'] ?? 'unknown';
            
            $message = "Document créé avec succès ! ";
            if ($source === 'ai' && $confidence > 0.7) {
                $message .= "(Analyse IA avec confiance élevée: " . round($confidence * 100) . "%)"; 
            } elseif ($source === 'ai') {
                $message .= "(Analyse IA avec confiance moyenne: " . round($confidence * 100) . "% - Vérifiez les informations)";
            } else {
                $message .= "(Analyse de base - Complétez les informations manuellement)";
            }

            return back()->with('success', $message);
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création du document: ' . $e->getMessage());
        }
    }

    /**
     * Update a document
     */
    public function updateDocument(Request $request, LegalDocument $document)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return back()->with('error', 'Accès non autorisé.');
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:legal_documents,title,' . $document->id,
                'reference_number' => 'nullable|string|max:100|unique:legal_documents,reference_number,' . $document->id,
                'type' => 'required|in:constitution,loi,decret,arrete,code,ordonnance',
                'category_id' => 'nullable|exists:legal_categories,id',
                'status' => 'required|in:draft,published,archived',
                'publication_date' => 'nullable|date|before_or_equal:today',
                'effective_date' => 'nullable|date',
                'summary' => 'nullable|string|max:1000',
                'content' => 'nullable|string',
                'pdf_file' => 'nullable|file|mimes:pdf|max:51200',
            ], [
                'title.required' => 'Le titre est obligatoire.',
                'title.unique' => 'Un document avec ce titre existe déjà.',
                'reference_number.unique' => 'Un document avec ce numéro de référence existe déjà.',
                'type.required' => 'Le type de document est obligatoire.',
                'status.required' => 'Le statut est obligatoire.',
                'publication_date.before_or_equal' => 'La date de publication ne peut pas être dans le futur.',
                'summary.max' => 'Le résumé ne peut pas dépasser 1000 caractères.',
                'pdf_file.mimes' => 'Le fichier doit être un PDF.',
                'pdf_file.max' => 'Le fichier PDF ne peut pas dépasser 50MB.',
            ]);

            // Update slug if title changed
            if ($validated['title'] !== $document->title) {
                $baseSlug = Str::slug($validated['title']);
                $slug = $baseSlug;
                $counter = 1;
                
                while (LegalDocument::where('slug', $slug)->where('id', '!=', $document->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $validated['slug'] = $slug;
            }

            $document->fill($validated);

            // Handle PDF upload
            if ($request->hasFile('pdf_file')) {
                // Delete old PDF if exists
                if ($document->pdf_url) {
                    $oldPath = str_replace('/storage/', '', $document->pdf_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $pdfFile = $request->file('pdf_file');
                $filename = time() . '_' . Str::slug($validated['title']) . '.pdf';
                $path = $pdfFile->storeAs('legal-documents', $filename, 'public');

                $document->pdf_url = Storage::url($path);
                $document->pdf_file_name = $pdfFile->getClientOriginalName();
                $document->pdf_file_size = $pdfFile->getSize();
                
                // Extract PDF content
                try {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($pdfFile->getPathname());
                    $extractedText = $pdf->getText();
                    
                    // Clean and store the extracted text
                    $document->content = $this->cleanExtractedText($extractedText);
                    
                    // Generate summary from first 500 characters if summary is empty
                    if (empty($validated['summary']) && $extractedText) {
                        $document->summary = Str::limit($this->cleanExtractedText($extractedText), 500);
                    }
                } catch (\Exception $e) {
                    // Log the error but don't fail the upload
                    Log::warning('PDF text extraction failed: ' . $e->getMessage());
                }
            }

            $document->save();

            return back()->with('success', 'Document modifié avec succès !');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la modification du document: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(LegalDocument $document)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return back()->with('error', 'Accès non autorisé.');
        }

        Log::info('Attempting to delete document', ['document_id' => $document->id, 'title' => $document->title]);

        try {
            // Delete PDF file if exists
            if ($document->pdf_url) {
                $path = str_replace('/storage/', '', $document->pdf_url);
                Storage::disk('public')->delete($path);
            }

            $document->delete();

            return back()->with('success', 'Document supprimé avec succès !');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du document: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a document
     */
    public function duplicateDocument(LegalDocument $document)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return back()->with('error', 'Accès non autorisé.');
        }

        try {
            $newDocument = $document->replicate();
            $newDocument->title = $document->title . ' (Copie)';
            
            // Generate unique slug
            $baseSlug = Str::slug($newDocument->title);
            $slug = $baseSlug;
            $counter = 1;
            
            while (LegalDocument::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $newDocument->slug = $slug;
            $newDocument->status = 'draft';
            $newDocument->views_count = 0;
            $newDocument->save();

            return back()->with('success', 'Document dupliqué avec succès !');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la duplication du document: ' . $e->getMessage());
        }
    }

    /**
     * Clean extracted text from PDF
     */
    private function cleanExtractedText($text)
    {
        if (!$text) {
            return '';
        }
        
        // Remove excessive whitespace and normalize line breaks
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[\r\n]+/', '\n', $text);
        
        // Remove common PDF artifacts
        $text = preg_replace('/\f/', '', $text); // Form feed characters
        $text = preg_replace('/\x{00A0}/u', ' ', $text); // Non-breaking spaces
        
        return trim($text);
    }
}
