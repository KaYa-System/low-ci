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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'type' => 'required|in:constitution,loi,decret,arrete,code,ordonnance',
            'category_id' => 'nullable|exists:legal_categories,id',
            'status' => 'required|in:draft,published,archived',
            'publication_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB max
        ]);

        $document = new LegalDocument();
        $document->fill($validated);
        $document->slug = Str::slug($validated['title']);

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
            'message' => 'Document créé avec succès',
            'data' => $document->load('category')
        ], 201);
    }

    public function show(LegalDocument $document)
    {
        return response()->json([
            'data' => $document->load('category', 'sections.articles', 'articles')
        ]);
    }

    public function update(Request $request, LegalDocument $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'type' => 'required|in:constitution,loi,decret,arrete,code,ordonnance',
            'category_id' => 'nullable|exists:legal_categories,id',
            'status' => 'required|in:draft,published,archived',
            'publication_date' => 'nullable|date',
            'effective_date' => 'nullable|date',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $document->title) {
            $validated['slug'] = Str::slug($validated['title']);
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
            'message' => 'Document modifié avec succès',
            'data' => $document->load('category')
        ]);
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