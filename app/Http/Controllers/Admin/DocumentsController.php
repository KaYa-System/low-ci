<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Models\LegalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::with('category')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $documents->items(),
            'total' => $documents->total(),
            'current_page' => $documents->currentPage(),
            'last_page' => $documents->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
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
                'type.in' => 'Le type de document sélectionné n\'est pas valide.',
                'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
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

            return response()->json([
                'success' => true,
                'message' => 'Document créé avec succès',
                'data' => $document->load('category')
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(LegalDocument $document)
    {
        return response()->json([
            'data' => $document->load('category', 'sections.articles', 'articles')
        ]);
    }

    public function update(Request $request, LegalDocument $document)
    {
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
                'type.in' => 'Le type de document sélectionné n\'est pas valide.',
                'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
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

            return response()->json([
                'success' => true,
                'message' => 'Document modifié avec succès',
                'data' => $document->load('category')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(LegalDocument $document)
    {
        // Delete PDF file if exists
        if ($document->pdf_url) {
            $path = str_replace('/storage/', '', $document->pdf_url);
            Storage::disk('public')->delete($path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document supprimé avec succès'
        ]);
    }

    public function duplicate(LegalDocument $document)
    {
        $newDocument = $document->replicate();
        $newDocument->title = $document->title . ' (Copie)';
        $newDocument->slug = Str::slug($newDocument->title);
        $newDocument->status = 'draft';
        $newDocument->views_count = 0;
        $newDocument->save();

        return response()->json([
            'message' => 'Document dupliqué avec succès',
            'data' => $newDocument
        ]);
    }
}