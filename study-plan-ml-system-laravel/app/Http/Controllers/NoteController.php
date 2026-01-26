<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\NoteVersion;
use App\Services\PdfOcrService;
use App\Services\ContentStructureService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    protected PdfOcrService $pdfOcrService;
    protected ContentStructureService $contentStructureService;

    public function __construct(PdfOcrService $pdfOcrService, ContentStructureService $contentStructureService)
    {
        $this->pdfOcrService = $pdfOcrService;
        $this->contentStructureService = $contentStructureService;
    }
    /**
     * Display a listing of the notes.
     */
    public function index()
    {
        $notes = Note::where('user_id', auth()->id())
            ->latest()
            ->paginate(12);
        
        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new note.
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request)
    {
        // Set default note_type if not provided
        if (!$request->has('note_type') || !$request->input('note_type')) {
            $request->merge(['note_type' => 'text']);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:100',
            'content' => 'nullable|string',
            'tags' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'note_type' => 'required|in:text,pdf'
        ]);

        // Process tags - convert comma-separated string to array
        $tags = null;
        if ($request->tags) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags); // Remove empty tags
        }

        $noteData = [
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'subject_area' => $validated['subject_area'],
            'tags' => $tags,
        ];

        // Handle PDF upload and OCR
        if ($validated['note_type'] === 'pdf' && $request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            
            // Store PDF file
            $pdfPath = $file->store('pdfs', 'public');
            
            // Extract text using local OCR service
            $ocrResult = $this->pdfOcrService->extractTextFromPdf($file);
            
            if ($ocrResult['success']) {
                // Process the extracted text for structure
                $structuredResult = $this->contentStructureService->processPdfContent($ocrResult['text']);
                
                $noteData['pdf_path'] = $pdfPath;
                $noteData['extracted_text'] = $ocrResult['text'];
                $noteData['content'] = $ocrResult['text']; // Also store in content for compatibility
                $noteData['is_pdf_note'] = true;
                $noteData['structured_content'] = $structuredResult['structured_content'];
                $noteData['content_outline'] = $structuredResult['content_outline'];
                $noteData['content_sections'] = $structuredResult['content_sections'];
                $noteData['document_type'] = $structuredResult['document_type'];
                
                $note = Note::create($noteData);
                
                return redirect()->route('notes.show', $note)
                    ->with('success', 'PDF uploaded and text extracted successfully using ' . $ocrResult['method'] . '! Content has been structured for better navigation.');
            } else {
                // Clean up uploaded file if OCR fails
                Storage::disk('public')->delete($pdfPath);
                return back()->withErrors(['pdf_file' => 'Failed to extract text from PDF: ' . $ocrResult['error']])
                            ->withInput();
            }
        } else {
            // Handle regular text note
            if (empty($validated['content'])) {
                return back()->withErrors(['content' => 'Content is required for text notes.'])
                            ->withInput();
            }
            
            $noteData['content'] = $validated['content'];
            $noteData['is_pdf_note'] = false;
        }

        $note = Note::create($noteData);

        return redirect()->route('notes.show', $note)
            ->with('success', 'Note created successfully!');
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note)
    {
        // Ensure user can only view their own notes
        if ($note->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this note.');
        }

        // Get related notes (same subject area, same user)
        $relatedNotes = Note::where('subject_area', $note->subject_area)
            ->where('user_id', auth()->id())
            ->where('id', '!=', $note->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('notes.show', compact('note', 'relatedNotes'));
    }

    /**
     * Show the form for editing the specified note.
     */
    public function edit(Note $note)
    {
        // Ensure user can only edit their own notes
        if ($note->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this note.');
        }

        // Load versions for the sidebar (without the createdBy relationship for now)
        $versions = $note->versions()->orderBy('version_number', 'desc')->get();
        
        return view('notes.edit', compact('note', 'versions'));
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note)
    {
        // Ensure user can only update their own notes
        if ($note->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this note.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_area' => 'required|string|max:100',
            'content' => 'required|string',
            'tags' => 'nullable|string',
            'change_summary' => 'nullable|string|max:500',
        ]);

        // Process tags - convert comma-separated string to array
        $tags = null;
        if ($request->tags) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags); // Remove empty tags
        }

        // Create a new version with the updated content
        // DO NOT update the original note - preserve it forever
        $changeSummary = $validated['change_summary'] ?? 'Content updated';
        $note->createVersion(
            $validated['title'],
            $validated['content'],
            $validated['subject_area'],
            $tags,
            $note->is_pdf_note ? $validated['content'] : null, // For PDF notes, content becomes extracted_text
            $changeSummary,
            1 // TODO: Replace with auth()->id() when authentication is set up
        );

        return redirect()->route('notes.show', $note)
            ->with('success', 'Note updated successfully! New version created while preserving the original.');
    }

    /**
     * View a specific version of a note
     */
    public function viewVersion(Note $note, NoteVersion $version)
    {
        try {
            // Ensure the version belongs to the note
            if ($version->note_id !== $note->id) {
                return response()->json(['error' => 'Version does not belong to this note'], 404);
            }

            // Return JSON data for AJAX requests
            return response()->json([
                'id' => $version->id,
                'version_number' => $version->version_number,
                'title' => $version->title,
                'content' => $version->content,
                'subject_area' => $version->subject_area,
                'tags' => $version->tags,
                'tags_list' => $version->tags ? implode(', ', $version->tags) : '',
                'extracted_text' => $version->extracted_text,
                'display_content' => $version->display_content,
                'change_summary' => $version->change_summary,
                'word_count' => $version->word_count,
                'character_count' => $version->character_count,
                'created_at' => $version->created_at->format('M j, Y g:i A'),
                'created_by' => $version->created_by ? 'User ' . $version->created_by : 'System'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load version',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Restore a specific version of a note
     */
    public function restoreVersion(Request $request, Note $note, NoteVersion $version)
    {
        try {
            // Ensure the version belongs to the note
            if ($version->note_id !== $note->id) {
                return redirect()->route('notes.edit', $note)
                    ->with('error', 'Version does not belong to this note.');
            }

            // Create a new version with the restored content (keeping original intact)
            $note->createVersion(
                $version->title,
                $version->content,
                $version->subject_area,
                $version->tags,
                $version->extracted_text,
                'Restored from version ' . $version->version_number,
                1 // TODO: Replace with auth()->id() when authentication is set up
            );

            return redirect()->route('notes.show', $note)
                ->with('success', 'Version ' . $version->version_number . ' has been restored as the latest version! Original content remains preserved.');
        } catch (\Exception $e) {
            return redirect()->route('notes.edit', $note)
                ->with('error', 'Failed to restore version: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific version of a note
     */
    public function deleteVersion(Request $request, Note $note, NoteVersion $version)
    {
        try {
            // Ensure the version belongs to the note
            if ($version->note_id !== $note->id) {
                return redirect()->route('notes.edit', $note)
                    ->with('error', 'Version does not belong to this note.');
            }

            // Prevent deletion if this is the only version
            $totalVersions = $note->versions()->count();
            if ($totalVersions <= 1) {
                return redirect()->route('notes.edit', $note)
                    ->with('error', 'Cannot delete the only version. At least one version must remain.');
            }

            // Store version info for success message
            $versionNumber = $version->version_number;
            
            // Delete the version
            $version->delete();

            // Return appropriate response based on request type
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Version {$versionNumber} has been permanently deleted."
                ]);
            }

            return redirect()->route('notes.edit', $note)
                ->with('success', "Version {$versionNumber} has been permanently deleted.");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete version: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('notes.edit', $note)
                ->with('error', 'Failed to delete version: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        // Ensure user can only delete their own notes
        if ($note->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this note.');
        }

        // Delete PDF file if it exists
        if ($note->pdf_path) {
            Storage::disk('public')->delete($note->pdf_path);
        }

        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Note deleted successfully!');
    }

    /**
     * Toggle favorite status of the note.
     */
    public function toggleFavorite(Note $note)
    {
        // Ensure user can only toggle favorite on their own notes
        if ($note->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this note.');
        }

        $note->update([
            'is_favorite' => !$note->is_favorite
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $note->is_favorite
        ]);
    }

    /**
     * Search notes by title or content.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $subject = $request->get('subject');

        $notes = Note::where('user_id', auth()->id());

        if ($query) {
            $notes->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            });
        }

        if ($subject) {
            $notes->where('subject_area', $subject);
        }

        $notes = $notes->latest()->paginate(12);

        return view('notes.index', compact('notes'));
    }

    /**
     * Check PDF OCR service status
     */
    public function checkOcrService()
    {
        $status = $this->pdfOcrService->getServiceStatus();
        
        return response()->json($status);
    }
}
