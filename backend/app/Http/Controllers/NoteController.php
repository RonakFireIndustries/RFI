<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'note' => 'required|string',
            'notable_id' => 'required|integer',
            'notable_type' => 'required|string',
        ]);

        $validated['created_by'] = Auth::id();

        $note = Note::create($validated);
        
        return response()->json($note->load('creator'), 201);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();
        
        return response()->noContent();
    }
}
