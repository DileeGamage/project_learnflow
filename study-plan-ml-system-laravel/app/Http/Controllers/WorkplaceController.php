<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class WorkplaceController extends Controller
{
    /**
     * Display the workplace dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's notes with statistics
        $notes = Note::where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();
        
        $totalNotes = Note::where('user_id', $user->id)->count();
        $notesToday = Note::where('user_id', $user->id)
                         ->whereDate('created_at', today())
                         ->count();
        $notesThisWeek = Note::where('user_id', $user->id)
                            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                            ->count();
        
        // Get notes by type
        $textNotes = Note::where('user_id', $user->id)
                        ->where('document_type', 'manual')
                        ->count();
        
        $pdfNotes = Note::where('user_id', $user->id)
                       ->where('document_type', 'pdf')
                       ->count();
        
        $favoriteNotes = Note::where('user_id', $user->id)
                            ->where('is_favorite', true)
                            ->count();
        
        // Get recent notes for the list
        $recentNotes = Note::where('user_id', $user->id)
                          ->latest()
                          ->take(10)
                          ->get();
        
        return view('workplace.index', compact(
            'notes', 
            'totalNotes', 
            'notesToday', 
            'notesThisWeek',
            'textNotes',
            'pdfNotes',
            'favoriteNotes',
            'recentNotes'
        ));
    }
    
    /**
     * Show the form for creating a new note
     */
    public function create()
    {
        return view('workplace.create');
    }
    
    /**
     * Store a newly created note
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'subject_area' => 'nullable|string|max:100',
        ]);
        
        Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'subject_area' => $request->subject_area,
            'user_id' => Auth::id(),
            'document_type' => 'manual',
        ]);
        
        return redirect()->route('workplace.index')
                        ->with('success', 'Note created successfully!');
    }
}
