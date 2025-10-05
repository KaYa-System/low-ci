<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\LegalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin;

        $data = [
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $documents = LegalDocument::with('category')->orderBy('updated_at', 'desc')->get();
            $categories = LegalCategory::where('is_active', true)->orderBy('sort_order')->get();

            $data['adminStats'] = [
                'total' => $documents->count(),
                'published' => $documents->where('status', 'published')->count(),
                'drafts' => $documents->where('status', 'draft')->count(),
                'totalViews' => $documents->sum('views_count'),
            ];

            $data['documents'] = $documents->toArray();
            $data['categories'] = $categories->toArray();
        }

        return inertia('Dashboard', $data);
    }

    /**
     * Store a new document
     */
    public function storeDocument(Request $request)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return back()->with('error', 'Accès non autorisé.');
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255|unique:legal_documents,title',
                'reference_number' => 'nullable|string|max:100|unique:legal_documents,reference_number',
                'type' => 'required|in:constitution,loi,decret,arrete,code,ordonnance',
                'category_id' => 'nullable|exists:legal_categories,id',
                'status' => 'required|in:draft,published,archived',
                'publication_date' => 'nullable|date|before_or_equal:today',
                'effective_date' => 'nullable|date',
                'summary' => 'nullable|string|max:1000',
                'content' => 'nullable|string',
                'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB max
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

            $document = new LegalDocument();
            $document->fill($validated);
            
            // Generate unique slug
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (LegalDocument::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $document->slug = $slug;

            // Handle PDF upload
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $filename = time() . '_' . Str::slug($validated['title']) . '.pdf';
                $path = $pdfFile->storeAs('legal-documents', $filename, 'public');

                $document->pdf_url = Storage::url($path);
                $document->pdf_file_name = $pdfFile->getClientOriginalName();
                $document->pdf_file_size = $pdfFile->getSize();
            }

            $document->save();

            return back()->with('success', 'Document créé avec succès !');
            
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
}
